"""Recover exact locked inputs from official sources inside the build stage."""
import argparse
import concurrent.futures
import gzip
import hashlib
import io
import json
import os
from pathlib import Path, PurePosixPath
import re
import shutil
import subprocess
import tarfile
import tempfile
from urllib.parse import unquote

from capture_snapshot import authenticate
from runtime_inputs import load_lock
from verify_snapshot import verify_snapshot


def snapshot_base(accepted, architecture, suite):
    if architecture not in ('amd64', 'arm64') or suite not in ('noble', 'noble-security', 'noble-updates'):
        raise ValueError('unsupported snapshot target or suite')
    stamp = accepted['snapshot_ids'][architecture][suite]
    if not re.fullmatch(r'\d{8}T\d{6}Z', stamp):
        raise ValueError('invalid historical snapshot ID')
    return 'https://snapshot.ubuntu.com/ubuntu/' + stamp + '/'


def package_url(base, filename):
    decoded = unquote(filename)
    if (not filename.startswith('pool/') or '..' in PurePosixPath(decoded).parts
            or any(char in decoded for char in ('?', '#', '\\'))
            or not re.fullmatch(r'pool/[A-Za-z0-9/_.+%:~-]+', filename)):
        raise ValueError('invalid locked package path')
    return base + filename


def download(url):
    return subprocess.check_output(['curl', '--fail', '--silent', '--show-error',
                                    '--location', '--retry', '2', '--max-time', '180', url])


def verified_download(url, identity, fetch=download):
    data = fetch(url)
    if len(data) != identity['size'] or hashlib.sha256(data).hexdigest() != identity['sha256']:
        raise ValueError('locked byte identity mismatch: ' + url)
    return data


def recover_inputs(base, architecture, destination, composer_lock, go_mod):
    lock_path, lock, digest = load_lock(base, architecture)
    accepted = json.loads((base / 'accepted-inputs.json').read_text())
    if destination.exists():
        raise ValueError('input destination already exists; refusing cache reuse')
    destination.parent.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='locked-inputs-', dir=destination.parent) as temporary:
        work = Path(temporary)
        root = work / 'snapshot'
        blobs = root / 'blobs/sha256'
        blobs.mkdir(parents=True)
        objects = []

        def retain(data, identity, origin):
            if len(data) != identity['size'] or hashlib.sha256(data).hexdigest() != identity['sha256']:
                raise ValueError('locked byte identity mismatch: ' + origin)
            path = blobs / identity['sha256']
            path.write_bytes(data)
            path.chmod(0o444)
            objects.append({'sha256': identity['sha256'], 'size': len(data), 'origin': origin})
            return path

        keyring = retain(Path('/usr/share/keyrings/ubuntu-archive-keyring.gpg').read_bytes(),
                         lock['trust_anchor'], 'pinned-bootstrap:keyring')
        bases = {}
        for repository in lock['repositories']:
            suite = repository['suite']
            url_base = snapshot_base(accepted, architecture, suite)
            bases[suite] = url_base
            url = url_base + 'dists/' + suite + '/InRelease'
            data = verified_download(url, repository['inrelease'])
            retain(data, repository['inrelease'], url)
            authenticate(data, keyring)
            for index in repository['indexes']:
                url = url_base + 'dists/' + suite + '/' + index['gzip_path']
                data = verified_download(url, index['gzip'])
                retain(data, index['gzip'], url)
                retain(gzip.decompress(data), index['packages'], 'gunzip:' + url)

        def archive(package):
            url = package_url(bases[package['suite']], package['filename'])
            return verified_download(url, package['archive']), package['archive'], url

        with concurrent.futures.ThreadPoolExecutor(max_workers=4) as pool:
            for data, identity, url in pool.map(archive, lock['packages']):
                retain(data, identity, url)

        inputs = lock['inputs']
        source_inputs = {
            'composer_lock': composer_lock.read_bytes(),
            'go_mod': go_mod.read_bytes(),
            'package_allowlist': ('\n'.join(sorted(p['name'] for p in lock['packages'])) + '\n').encode(),
        }
        for name, data in source_inputs.items():
            retain(data, inputs[name], 'tracked-source:' + name)
        custom = io.BytesIO()
        with tarfile.open(fileobj=custom, mode='w') as archive_file:
            for path in sorted((base / 'slices').glob('*.yaml')):
                data = path.read_bytes()
                entry = tarfile.TarInfo(path.name)
                entry.mode = 0o644
                entry.size = len(data)
                archive_file.addfile(entry, io.BytesIO(data))
        retain(custom.getvalue(), inputs['custom_sdfs'], 'tracked-source:custom-sdfs')

        official = work / 'official-sdfs'
        commit = lock['identities']['official_sdfs']['commit']
        if not re.fullmatch(r'[0-9a-f]{40}', commit):
            raise ValueError('invalid official SDF commit')
        subprocess.run(['git', 'init', '-q', str(official)], check=True)
        subprocess.run(['git', '-C', str(official), 'fetch', '--quiet', '--depth=1',
                        'https://github.com/canonical/chisel-releases.git', commit], check=True)
        tree = subprocess.check_output(['git', '-C', str(official), 'rev-parse',
                                        'FETCH_HEAD^{tree}'], text=True).strip()
        if tree != lock['identities']['official_sdfs']['tree']:
            raise ValueError('official SDF tree mismatch')
        data = subprocess.check_output(['git', '-C', str(official), 'archive', '--format=tar', commit])
        retain(data, inputs['official_sdfs'], 'canonical/chisel-releases@' + commit)
        version = lock['identities']['chisel']['version']
        if not re.fullmatch(r'v\d+\.\d+\.\d+', version):
            raise ValueError('invalid Chisel version')
        url = f'https://github.com/canonical/chisel/releases/download/{version}/chisel_{version}_linux_amd64.tar.gz'
        with tarfile.open(fileobj=io.BytesIO(download(url)), mode='r:gz') as archive_file:
            member = archive_file.getmember('chisel')
            if not member.isfile() or member.size != inputs['chisel']['size']:
                raise ValueError('invalid Chisel binary archive member')
            retain(archive_file.extractfile(member).read(), inputs['chisel'], url + '#chisel')
        verify_snapshot(root, lock_path, digest)
        report = {'status': 'recovered-exact-bytes', 'architecture': architecture,
                  'lock_sha256': digest, 'snapshot_ids': accepted['snapshot_ids'][architecture],
                  'uses_local_snapshot': False, 'live_resolution': False, 'objects': objects}
        (root / 'recovery-report.json').write_text(json.dumps(report, indent=2) + '\n')
        shutil.move(str(root), destination)
    print(json.dumps({'status': report['status'], 'architecture': architecture,
                      'lock_sha256': digest, 'packages': len(lock['packages']),
                      'uses_local_snapshot': False, 'live_resolution': False}))


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--destination', type=Path, default=Path('/snapshot'))
    parser.add_argument('--composer-lock', type=Path, default=Path('/application-composer.lock'))
    parser.add_argument('--go-mod', type=Path, default=Path('/application-go.mod'))
    args = parser.parse_args()
    recover_inputs(Path(__file__).resolve().parent, os.environ['CHISEL_TARGET_ARCH'],
                   args.destination, args.composer_lock, args.go_mod)
