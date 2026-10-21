#!/usr/bin/env python3
"""Read-only immutable input verification (build tooling only)."""
import json
import argparse
import datetime as dt
import gzip
import re
import sys
from pathlib import Path
from capture_snapshot import sha, sections, release_body, authenticate, freshness, SIGNER, SUITES, COMPONENTS, BOOTSTRAP


def blob(root, obj):
    if set(obj) != {'sha256', 'size'} or not re.fullmatch('[0-9a-f]{64}', obj['sha256']):
        raise ValueError('invalid snapshot object identity')
    if type(obj['size']) is not int or obj['size'] < 0:
        raise ValueError('invalid snapshot object size')
    path = root / 'blobs' / 'sha256' / obj['sha256']
    if path.is_symlink() or not path.is_file() or not path.resolve().is_relative_to(root.resolve()):
        raise ValueError('missing or symlinked snapshot object: ' + obj['sha256'])
    data = path.read_bytes()
    if sha(data) != obj['sha256'] or len(data) != obj['size']:
        raise ValueError('changed snapshot object: ' + obj['sha256'])
    return data


def validate_identities(lock):
    required = {'format', 'architecture', 'ubuntu', 'created_at', 'bootstrap', 'bootstrap_validation',
                'verifier', 'trust_anchor', 'signer', 'identities', 'repositories', 'packages', 'inputs'}
    if not required.issubset(lock) or set(lock) - required - {'finalized_at'}:
        raise ValueError('missing or unexpected lock fields')
    for field in ('created_at', 'finalized_at'):
        if field in lock and dt.datetime.fromisoformat(lock[field]).tzinfo is None:
            raise ValueError('lock timestamps require a timezone')
    identities = lock['identities']
    architecture = lock.get('architecture')
    if lock['format'] != 1 or architecture not in ('amd64', 'arm64') or lock['ubuntu'] != '24.04':
        raise ValueError('unapproved format/release/architecture')
    if lock['bootstrap'] != BOOTSTRAP or identities['builder'] != BOOTSTRAP or identities['platform'] != 'linux/' + architecture:
        raise ValueError('unapproved bootstrap/builder/platform')
    if lock['signer'] != SIGNER or lock['verifier']['name'] != 'gpgv':
        raise ValueError('unapproved signature verifier/signer')
    if sha(Path('/usr/bin/gpgv').read_bytes()) != lock['verifier']['binary_sha256']:
        raise ValueError('changed verifier binary')
    for key in ('frontend',):
        if not re.fullmatch(r'[^\s]+@sha256:[0-9a-f]{64}', identities[key]):
            raise ValueError('unpinned ' + key)
    for key in ('chisel', 'official_sdfs', 'custom_sdfs', 'source', 'go', 'composer', 'package_selection'):
        if not identities.get(key):
            raise ValueError('missing input identity: ' + key)
    for key in ('official_sdfs', 'custom_sdfs'):
        if not re.fullmatch('[0-9a-f]{64}', identities[key]['archive_sha256']):
            raise ValueError('missing SDF content identity: ' + key)
    if not re.fullmatch('[0-9a-f]{64}', identities['chisel']['binary_sha256']):
        raise ValueError('missing Chisel content identity')
    for key in ('go', 'composer'):
        if not re.fullmatch(r'[^\s]+@sha256:[0-9a-f]{64}', identities[key]['builder']):
            raise ValueError('unpinned ' + key + ' builder')
    for field in ('commit', 'tree'):
        if not re.fullmatch('[0-9a-f]{40}', identities['source'][field]):
            raise ValueError('invalid source identity')


def verify_snapshot(root, lock_path, approved_sha256, mode='historical', now=None, requested=None):
    root = Path(root)
    if mode not in ('live', 'historical'):
        raise ValueError('invalid verification mode')
    now = now or dt.datetime.now(dt.timezone.utc)
    raw = Path(lock_path).read_bytes()
    if sha(raw) != approved_sha256:
        raise ValueError('unapproved lock identity')
    lock = json.loads(raw)
    validate_identities(lock)
    architecture = lock['architecture']
    keyring = root / 'blobs' / 'sha256' / lock['trust_anchor']['sha256']
    blob(root, lock['trust_anchor'])
    if [repo['suite'] for repo in lock['repositories']] != list(SUITES):
        raise ValueError('unapproved repository set/order')
    selected = lock['packages']
    names = [p['name'] for p in selected]
    if not names or len(set(names)) != len(names):
        raise ValueError('empty or duplicate package selection')
    if requested is not None:
        # The caller supplies the actual proposed resolution. All selected
        # identities must match; a name-only allowlist is insufficient.
        by_name = {p['name']: p for p in selected}
        seen = set()
        for package in requested:
            name = package.get('name')
            if name in seen or package != by_name.get(name):
                raise ValueError('unapproved package resolution: ' + str(name))
            seen.add(name)
    authenticated_records = {}
    for repo in lock['repositories']:
        data = blob(root, repo['inrelease'])
        authenticate(data, keyring)
        fields = next(sections(release_body(data)))
        if fields.get('Origin') != 'Ubuntu' or fields.get('Label') != 'Ubuntu' or fields.get('Codename') != 'noble' or fields.get('Suite') != repo['suite']:
            raise ValueError('repository signed identity mismatch')
        if architecture not in fields['Architectures'].split() or repo['signer'] != SIGNER:
            raise ValueError('repository architecture/signer mismatch')
        if architecture == 'arm64':
            expected_url = 'https://ports.ubuntu.com/ubuntu-ports/'
        else:
            expected_host = 'security.ubuntu.com' if repo['suite'] == 'noble-security' else 'archive.ubuntu.com'
            expected_url = f'http://{expected_host}/ubuntu/'
        if repo['url'] != expected_url:
            raise ValueError('unapproved repository URL')
        if mode == 'live':
            fresh = freshness(fields, now)
            if fresh != repo['freshness']:
                raise ValueError('freshness policy identity mismatch')
        elif fields['Date'] != repo['freshness']['date'] or fields.get('Valid-Until') != repo['freshness']['valid_until']:
            raise ValueError('signed metadata identity mismatch')
        checksums = {}
        for line in fields['SHA256'].splitlines():
            if line.strip():
                digest, size, path = line.split()
                if path in checksums:
                    raise ValueError('duplicate signed index identity')
                checksums[path] = {'sha256': digest, 'size': int(size)}
        if [index['component'] for index in repo['indexes']] != list(COMPONENTS):
            raise ValueError('unapproved component set/order')
        for index in repo['indexes']:
            expected_path = index['component'] + '/binary-' + architecture + '/Packages'
            if index['path'] != expected_path or index['gzip_path'] != expected_path + '.gz':
                raise ValueError('unapproved index path')
            if checksums.get(index['path']) != index['packages'] or checksums.get(index['gzip_path']) != index['gzip']:
                raise ValueError('index differs from authenticated signed metadata')
            raw_index = blob(root, index['packages'])
            compressed = blob(root, index['gzip'])
            if gzip.decompress(compressed) != raw_index:
                raise ValueError('compressed index identity mismatch')
            for package in sections(raw_index.decode()):
                if package.get('Package') in names:
                    identity = (repo['suite'], index['component'], package['Package'], package['Version'], package['Architecture'])
                    if identity in authenticated_records:
                        raise ValueError('duplicate authenticated package identity')
                    authenticated_records[identity] = package
    for package in selected:
        if package['architecture'] not in (architecture, 'all'):
            raise ValueError('unapproved selected package architecture')
        identity = (package['suite'], package['component'], package['name'], package['version'], package['architecture'])
        record = authenticated_records.get(identity)
        if not record:
            raise ValueError('package absent from authenticated retained index: ' + package['name'])
        expected_archive = {'sha256': record['SHA256'], 'size': int(record['Size'])}
        source = record.get('Source', record['Package'])
        match = re.fullmatch(r'(\S+)(?: \(([^)]+)\))?', source)
        if not match or package['source'] != match[1] or package['source_version'] != (match[2] or record['Version']):
            raise ValueError('package source identity mismatch: ' + package['name'])
        repo = next(repo for repo in lock['repositories'] if repo['suite'] == package['suite'])
        if package['filename'] != record['Filename'] or package['repository'] != repo['url'] or package['archive'] != expected_archive:
            raise ValueError('package archive/repository identity mismatch: ' + package['name'])
        blob(root, package['archive'])
    inputs = lock['inputs']
    expected_inputs = {
        'chisel': lock['identities']['chisel']['binary_sha256'],
        'official_sdfs': lock['identities']['official_sdfs']['archive_sha256'],
        'custom_sdfs': lock['identities']['custom_sdfs']['archive_sha256'],
        'package_allowlist': lock['identities']['package_selection']['allowlist_sha256'],
        'go_mod': lock['identities']['go']['go_mod_sha256'],
        'composer_lock': lock['identities']['composer']['lock_sha256'],
    }
    if set(inputs) != set(expected_inputs):
        raise ValueError('missing or unexpected locked tool/source inputs')
    for name, digest in expected_inputs.items():
        if inputs[name]['sha256'] != digest:
            raise ValueError('input identity mismatch: ' + name)
        data = blob(root, inputs[name])
        if name == 'package_allowlist' and sorted(data.decode().splitlines()) != sorted(names):
            raise ValueError('package selection differs from locked allowlist')
    return lock


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--snapshot', type=Path, required=True)
    parser.add_argument('--lock', type=Path, required=True)
    parser.add_argument('--approved-sha256', required=True)
    parser.add_argument('--mode', choices=('live', 'historical'), default='historical')
    parser.add_argument('--requested', type=Path)
    args = parser.parse_args()
    try:
        requested = json.loads(args.requested.read_text()) if args.requested else None
        lock = verify_snapshot(args.snapshot, args.lock, args.approved_sha256, args.mode, requested=requested)
    except (ValueError, OSError, KeyError, TypeError) as error:
        print('REJECTED:', error, file=sys.stderr)
        return 1
    print(json.dumps({'status': 'verified', 'mode': args.mode,
                      'architecture': lock['architecture'], 'packages': len(lock['packages']),
                      'signature_authenticity': 'verified', 'archive_hashes': 'verified',
                      'freshness': 'enforced' if args.mode == 'live' else 'historical-expiry-allowed',
                      'live_resolution': False, 'lock_sha256': args.approved_sha256}))
    return 0


if __name__ == '__main__':
    sys.exit(main())
