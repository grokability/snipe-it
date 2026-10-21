#!/usr/bin/env python3
"""Create a candidate immutable snapshot from already authenticated APT lists.

Build-only Ubuntu helper entry point. It never installs runtime packages, cuts a
root, changes a lock in place, or accepts an unauthenticated index.
"""
import argparse
import datetime as dt
import email.utils
import gzip
import hashlib
import json
import pathlib
import re
import subprocess
import tempfile

BOOTSTRAP = 'docker.io/library/ubuntu@sha256:534baea6a22c03a63003dbc8dbe78fe34bc0d7e595d9a9dc9834884ff530eb55'
SIGNER = 'F6ECB3762474EDA9D21B7022871920D1991BC93C'
SUITES = ('noble', 'noble-security', 'noble-updates')
COMPONENTS = ('main', 'universe')


def sha(data):
    return hashlib.sha256(data).hexdigest()


def sections(text):
    for block in text.strip().split('\n\n'):
        fields = {}
        key = None
        for line in block.splitlines():
            if line.startswith((' ', '\t')) and key:
                fields[key] += '\n' + line[1:]
            elif ':' in line:
                key, value = line.split(':', 1)
                if key in fields:
                    raise ValueError('duplicate metadata field: ' + key)
                fields[key] = value.strip()
        yield fields


def release_body(data):
    text = data.decode()
    if not text.startswith('-----BEGIN PGP SIGNED MESSAGE-----\n'):
        raise ValueError('unsigned metadata')
    body = text.split('\n\n', 1)[1].split('-----BEGIN PGP SIGNATURE-----', 1)[0]
    return '\n'.join(line[2:] if line.startswith('- ') else line for line in body.splitlines())


def authenticate(data, keyring):
    with tempfile.TemporaryDirectory() as temp:
        path = pathlib.Path(temp) / 'InRelease'
        path.write_bytes(data)
        result = subprocess.run(['gpgv', '--status-fd=1', '--keyring', str(keyring), str(path)],
                                capture_output=True, text=True)
    if result.returncode or ('[GNUPG:] VALIDSIG ' + SIGNER + ' ') not in result.stdout:
        raise ValueError('Ubuntu signature verification failed: ' + result.stderr)
    return result.stdout + result.stderr


def freshness(fields, now):
    issued = email.utils.parsedate_to_datetime(fields['Date'])
    if issued > now + dt.timedelta(minutes=10):
        raise ValueError('future repository metadata')
    # Noble's original release pocket is frozen. Mutable security/update pockets
    # must have been published in the last 48 hours even without Valid-Until.
    deadline = None if fields['Suite'] == 'noble' else issued + dt.timedelta(hours=48)
    if fields.get('Valid-Until'):
        signed_deadline = email.utils.parsedate_to_datetime(fields['Valid-Until'])
        deadline = min(deadline, signed_deadline) if deadline else signed_deadline
    if deadline and now > deadline:
        raise ValueError('expired live repository metadata: ' + fields['Suite'])
    return {'date': fields['Date'], 'valid_until': fields.get('Valid-Until'),
            'live_deadline': deadline.isoformat() if deadline else None,
            'policy': 'frozen-release' if fields['Suite'] == 'noble' else '48-hour-publication-window'}


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--output', type=pathlib.Path, required=True)
    parser.add_argument('--packages', type=pathlib.Path, required=True)
    parser.add_argument('--identities', type=pathlib.Path, required=True)
    parser.add_argument('--architecture', choices=('amd64', 'arm64'), default='amd64')
    parser.add_argument('--expected-versions', type=pathlib.Path)
    args = parser.parse_args()
    output = args.output
    output.mkdir(parents=True, exist_ok=True)
    blobs = output / 'blobs' / 'sha256'
    blobs.mkdir(parents=True, exist_ok=True)
    evidence = output / 'evidence'
    evidence.mkdir(exist_ok=True)
    keyring = pathlib.Path('/usr/share/keyrings/ubuntu-archive-keyring.gpg')
    now = dt.datetime.now(dt.timezone.utc)

    def retain(data):
        digest = sha(data)
        target = blobs / digest
        if target.exists():
            if target.read_bytes() != data:
                raise ValueError('content-address collision: ' + digest)
        else:
            target.write_bytes(data)
            target.chmod(0o444)
        return {'sha256': digest, 'size': len(data)}

    def download(url, expected, size):
        cached = blobs / expected
        if cached.exists():
            data = cached.read_bytes()
        else:
            with tempfile.TemporaryDirectory() as temp:
                path = pathlib.Path(temp) / 'download'
                subprocess.run(['curl', '--fail', '--location', '--retry', '3',
                                '--silent', '--show-error', url, '-o', str(path)], check=True)
                data = path.read_bytes()
        if sha(data) != expected or len(data) != int(size):
            raise ValueError('authenticated download hash/size mismatch: ' + url)
        return data

    repositories = []
    candidates = {}
    wanted = args.packages.read_text().splitlines()
    expected_versions = json.loads(args.expected_versions.read_text()) if args.expected_versions else {}
    if len(set(wanted)) != len(wanted) or not wanted:
        raise ValueError('invalid package allowlist')
    for suite in SUITES:
        if args.architecture == 'arm64':
            host, repository_path, scheme = 'ports.ubuntu.com', 'ubuntu-ports', 'https'
        else:
            host = 'security.ubuntu.com' if suite == 'noble-security' else 'archive.ubuntu.com'
            repository_path, scheme = 'ubuntu', 'http'
        prefix = f'{host}_{repository_path}_dists_{suite}'
        data = (pathlib.Path('/var/lib/apt/lists') / (prefix + '_InRelease')).read_bytes()
        evidence.joinpath(suite + '-signature.txt').write_text(authenticate(data, keyring))
        fields = next(sections(release_body(data)))
        if fields['Origin'] != 'Ubuntu' or fields['Suite'] != suite or fields['Codename'] != 'noble':
            raise ValueError('repository identity mismatch')
        checksums = {}
        for line in fields['SHA256'].splitlines():
            if not line.strip():
                continue
            digest, size, path = line.split()
            checksums[path] = (digest, int(size))
        repo = {'suite': suite, 'origin': 'Ubuntu', 'codename': 'noble',
                'url': f'{scheme}://{host}/{repository_path}/', 'inrelease': retain(data),
                'signer': SIGNER, 'freshness': freshness(fields, now), 'indexes': []}
        for component in COMPONENTS:
            index_path = f'{component}/binary-{args.architecture}/Packages'
            cached_path = pathlib.Path('/var/lib/apt/lists') / (prefix + '_' + component + '_binary-' + args.architecture + '_Packages.lz4')
            raw = subprocess.check_output(['/usr/lib/apt/apt-helper', 'cat-file', str(cached_path)])
            expected, size = checksums[index_path]
            if sha(raw) != expected or len(raw) != size:
                raise ValueError('retained APT index hash mismatch: ' + index_path)
            compressed_path = index_path + '.gz'
            digest, compressed_size = checksums[compressed_path]
            gz = download(repo['url'] + f'dists/{suite}/{component}/binary-{args.architecture}/by-hash/SHA256/{digest}', digest, compressed_size)
            if gzip.decompress(gz) != raw:
                raise ValueError('compressed/uncompressed index mismatch')
            index = {'path': index_path, 'component': component, 'packages': retain(raw),
                     'gzip_path': compressed_path, 'gzip': retain(gz)}
            repo['indexes'].append(index)
            for package in sections(raw.decode()):
                name = package.get('Package')
                if name not in wanted:
                    continue
                if name in expected_versions and package['Version'] != expected_versions[name]:
                    continue
                if package['Architecture'] not in ('all', args.architecture):
                    raise ValueError('unexpected package architecture')
                if name in candidates:
                    old = candidates[name][0]
                    newer = subprocess.run(['dpkg', '--compare-versions', package['Version'], 'gt', old['Version']]).returncode == 0
                    if not newer:
                        continue
                candidates[name] = (package, repo, index)
        repositories.append(repo)
        print('Retained authenticated metadata/indexes:', suite, flush=True)
    selected = []
    for name in sorted(wanted):
        if name not in candidates:
            raise ValueError('package not found: ' + name)
        package, repo, index = candidates[name]
        data = download(repo['url'] + package['Filename'], package['SHA256'], package['Size'])
        source = package.get('Source', name)
        match = re.fullmatch(r'(\S+)(?: \(([^)]+)\))?', source)
        if not match:
            raise ValueError('invalid source identity')
        selected.append({'name': name, 'version': package['Version'],
                         'source': match[1], 'source_version': match[2] or package['Version'],
                         'architecture': package['Architecture'], 'repository': repo['url'],
                         'suite': repo['suite'], 'component': index['component'],
                         'filename': package['Filename'], 'archive': retain(data)})
        print('Retained archive:', name, package['Version'], package['Architecture'], flush=True)
    identities = json.loads(args.identities.read_text())
    identities['platform'] = 'linux/' + args.architecture
    lock = {'format': 1, 'architecture': args.architecture, 'ubuntu': '24.04',
            'created_at': now.isoformat(), 'bootstrap': BOOTSTRAP,
            'bootstrap_validation': 'Externally validated digest supplied by user; exact digest executed successfully in this session.',
            'verifier': {'name': 'gpgv', 'binary_sha256': sha(pathlib.Path('/usr/bin/gpgv').read_bytes()),
                         'version': subprocess.check_output(['gpgv', '--version'], text=True).splitlines()[0]},
            'trust_anchor': retain(keyring.read_bytes()), 'signer': SIGNER,
            'identities': identities,
            'repositories': repositories, 'packages': selected}
    data = (json.dumps(lock, sort_keys=True, indent=2) + '\n').encode()
    lock_id = sha(data)
    target = output / ('lock-' + lock_id + '.json')
    if target.exists() and target.read_bytes() != data:
        raise ValueError('lock content-address collision')
    target.write_bytes(data)
    target.chmod(0o444)
    print('Snapshot lock:', target.name, 'packages:', len(selected), flush=True)


if __name__ == '__main__':
    main()
