#!/usr/bin/env python3
"""Disposable application build tools only; no files become runtime packages."""
import json
from pathlib import Path
import subprocess

from runtime_inputs import load_lock

base = Path(__file__).resolve().parent
# This stage and the retained Chisel executable run on linux/amd64.
# Build-tool versions come from the AMD64 lock, independently of the target.
_, lock, _ = load_lock(base, 'amd64')
accepted = json.loads((base / 'accepted-inputs.json').read_text())
snapshot = accepted['historical_snapshot_id']
if snapshot != '20261022T020000Z':
    raise ValueError('build-tool snapshot mapping requires a reviewed update')
for path in Path('/etc/apt/sources.list.d').iterdir():
    if path.is_file():
        path.unlink()
Path('/etc/apt/sources.list').write_text('')
Path('/etc/apt/sources.list.d/build-tools.sources').write_text(
    'Types: deb\nURIs: https://snapshot.ubuntu.com/ubuntu/' + snapshot + '/\n'
    'Suites: noble noble-security noble-updates\nComponents: main universe\n'
    'Architectures: amd64\nSigned-By: /usr/share/keyrings/ubuntu-archive-keyring.gpg\n')
# The helper is constructed anew; no inherited package index selects tool versions.
import shutil
shutil.rmtree('/var/lib/apt/lists', ignore_errors=True)
packages = {p['name']: p for p in lock['packages']}
names = ['php8.3-cli', 'php8.3-bcmath', 'php8.3-curl', 'php8.3-gd',
         'php8.3-igbinary', 'php8.3-ldap', 'php8.3-mbstring', 'php8.3-memcached',
         'php8.3-msgpack', 'php8.3-mysql', 'php8.3-redis', 'php8.3-xml', 'php8.3-zip']
subprocess.run(['apt-get', 'update'], check=True)
subprocess.run(['apt-get', 'install', '-y', '--no-install-recommends',
                *[name + '=' + packages[name]['version'] for name in names]], check=True)
Path('/build-tool-packages.txt').write_bytes(subprocess.check_output(['dpkg-query', '-W']))
shutil.rmtree('/var/lib/apt/lists', ignore_errors=True)
shutil.rmtree('/var/cache/apt', ignore_errors=True)
