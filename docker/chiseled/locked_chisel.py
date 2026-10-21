#!/usr/bin/env python3
"""Verify inputs, then feed pinned Chisel from a closed retained HTTP replay.

Run inside a Linux build-only container with Docker --network none. The replay
never forwards a request. Original Ubuntu signatures/index bytes are preserved.
This is an immutable repository adapter, not a second package/file resolver.
"""
import argparse
import http.server
import io
import json
import os
from pathlib import Path
import shutil
import subprocess
import tarfile
import tempfile
import threading
import urllib.parse

from verify_snapshot import verify_snapshot, blob


def replay_objects(lock):
    mapping = {}
    for repo in lock['repositories']:
        suite = repo['suite']
        mapping['dists/' + suite + '/InRelease'] = repo['inrelease']
        for index in repo['indexes']:
            base = 'dists/' + suite + '/'
            mapping[base + index['path']] = index['packages']
            mapping[base + index['gzip_path']] = index['gzip']
            prefix = base + index['path'].rsplit('/', 1)[0] + '/by-hash/SHA256/'
            for name in ('gzip', 'packages'):
                mapping[prefix + index[name]['sha256']] = index[name]
    for package in lock['packages']:
        previous = mapping.get(package['filename'])
        if previous and previous != package['archive']:
            raise ValueError('conflicting retained package paths')
        mapping[package['filename']] = package['archive']
    return mapping


class RetainedRepository(http.server.ThreadingHTTPServer):
    daemon_threads = True

    def __init__(self, root, lock):
        self.root = root
        self.objects = replay_objects(lock)
        self.repositories = {repo['suite']: repo['url'] for repo in lock['repositories']}
        self.requests = []
        self.guard = threading.Lock()
        super().__init__(('127.0.0.1', 0), RetainedRequest)


class RetainedRequest(http.server.BaseHTTPRequestHandler):
    def log_message(self, *args):
        pass

    def do_GET(self):
        url = urllib.parse.urlsplit(self.path)
        path = urllib.parse.unquote(url.path)
        allowed_base = next((base for base in self.server.repositories.values()
                             if url.netloc == urllib.parse.urlsplit(base).netloc
                             and path.startswith(urllib.parse.urlsplit(base).path)), None)
        base_path = urllib.parse.urlsplit(allowed_base).path if allowed_base else None
        # Chisel's repository client issues HTTP requests through this loopback-only
        # adapter. The retained lock records the authenticated source URL; accepting
        # this request never opens a connection to that URL.
        allowed = (url.scheme in ('http', 'https') and allowed_base is not None
                   and not url.query and not url.fragment)
        key = path[len(base_path):] if allowed else None
        obj = self.server.objects.get(key)
        try:
            if not obj:
                raise ValueError('unapproved repository request')
            data = blob(self.server.root, obj)
        except (ValueError, OSError):
            with self.server.guard:
                self.server.requests.append({'url': self.path, 'status': 'rejected'})
            self.send_error(403, 'unapproved or changed retained input')
            return
        with self.server.guard:
            self.server.requests.append({'url': self.path, 'status': 'retained', 'sha256': obj['sha256']})
        self.send_response(200)
        self.send_header('Content-Length', str(len(data)))
        self.end_headers()
        self.wfile.write(data)


def extract_sdfs(data, destination):
    # git archive content is pinned, but path safety is still enforced before
    # extraction. No archive member may write through or create a symlink.
    with tarfile.open(fileobj=io.BytesIO(data)) as archive:
        for member in archive.getmembers():
            path = Path(member.name)
            if path.is_absolute() or '..' in path.parts or not (member.isfile() or member.isdir()):
                raise ValueError('unsafe SDF archive member: ' + member.name)
        archive.extractall(destination, filter='data')


def require_offline():
    # Linux may expose inactive GRE/tunnel interfaces even with --network none.
    # Reject every UP non-loopback interface and non-loopback route.
    active = []
    for path in Path('/sys/class/net').iterdir():
        flags = path / 'flags'
        if path.name != 'lo' and flags.is_file() and int(flags.read_text().strip(), 16) & 1:
            active.append(path.name)
    routes = Path('/proc/net/route').read_text().splitlines()[1:]
    if active or any(line.split()[0] != 'lo' for line in routes if line.strip()):
        raise ValueError('locked Chisel requires Docker --network none (loopback only)')


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--snapshot', type=Path, required=True)
    parser.add_argument('--lock', type=Path, required=True)
    parser.add_argument('--approved-sha256', required=True)
    parser.add_argument('--root', type=Path, required=True)
    parser.add_argument('--evidence', type=Path, required=True)
    parser.add_argument('--custom', action='store_true')
    parser.add_argument('slices', nargs='+')
    args = parser.parse_args()
    require_offline()
    lock = verify_snapshot(args.snapshot, args.lock, args.approved_sha256)
    if args.custom and 'pending' in lock['identities']['custom_sdfs']['status']:
        raise ValueError('production custom SDF review pending (Task 1.3)')
    if args.root.exists():
        raise ValueError('output root already exists; refusing to overwrite')
    args.root.parent.mkdir(parents=True, exist_ok=True)
    args.evidence.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(dir=args.root.parent) as temp:
        temp = Path(temp)
        release = temp / 'release'
        extract_sdfs(blob(args.snapshot, lock['inputs']['official_sdfs']), release)
        if args.custom:
            extract_sdfs(blob(args.snapshot, lock['inputs']['custom_sdfs']), release / 'slices')
        chisel = temp / 'chisel'
        chisel.write_bytes(blob(args.snapshot, lock['inputs']['chisel']))
        chisel.chmod(0o500)
        environment = {key: value for key, value in os.environ.items()
                       if key.lower() not in ('http_proxy', 'https_proxy', 'all_proxy', 'no_proxy')
                       and not key.startswith('CHISEL_')}
        # Avoid any previous cached package/index state. Never repurpose HOME.
        environment['XDG_CACHE_HOME'] = str(temp / 'empty-cache')
        server = RetainedRepository(args.snapshot, lock)
        environment['HTTP_PROXY'] = f'http://127.0.0.1:{server.server_port}'
        environment['HTTPS_PROXY'] = environment['HTTP_PROXY']
        thread = threading.Thread(target=server.serve_forever, daemon=True)
        thread.start()
        (temp / 'root').mkdir()
        command = [str(chisel), 'cut', '--arch', lock['architecture'], '--release', str(release),
                   '--root', str(temp / 'root'), *args.slices]
        try:
            result = subprocess.run(command, env=environment, capture_output=True, text=True)
        finally:
            server.shutdown()
            server.server_close()
            thread.join()
        args.evidence.joinpath('chisel-output.txt').write_text(result.stdout + result.stderr)
        args.evidence.joinpath('requests.json').write_text(json.dumps(server.requests, indent=2) + '\n')
        if result.returncode or any(r['status'] != 'retained' for r in server.requests):
            raise ValueError('locked Chisel cut failed; staged root was not promoted: ' + result.stderr)
        shutil.move(str(temp / 'root'), args.root)
        print(json.dumps({'status': 'cut', 'lock_sha256': args.approved_sha256,
                          'network': 'loopback-only', 'requests': len(server.requests), 'live_resolution': False}))


if __name__ == '__main__':
    main()
