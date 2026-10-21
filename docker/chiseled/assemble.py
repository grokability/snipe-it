#!/usr/bin/env python3
"""Declared project assembly only; never copies or resolves package files."""
import os
from pathlib import Path
import shutil

base = Path(__file__).resolve().parent
root = Path('/rootfs')
import json
operations = json.loads((base / 'assembly-exceptions.json').read_text())
for directory in operations['generated_directories']:
    path = root / directory.lstrip('/')
    path.mkdir(parents=True, exist_ok=True)
    path.chmod(0o1777 if directory == '/tmp' else 0o755)
    if directory != '/tmp':
        os.chown(path, 10000, 50)
runtime_links = json.loads((base / 'task1.6-runtime-links.json').read_text())['generated_links']
for relative, target in {**operations['generated_links'], **runtime_links}.items():
    path = root / relative.lstrip('/')
    if os.path.lexists(path):
        raise ValueError('project link would overwrite existing package path: ' + relative)
    path.symlink_to(target)
for name, destination, old, new in [
        ('000-default.conf', 'sites-enabled', '*:80', '*:8080'),
        ('001-default-ssl.conf', 'sites-available', '_default_:443', '_default_:8443')]:
    path = root / 'etc/apache2' / destination / name
    if os.path.lexists(path):
        raise ValueError('project config would overwrite package path')
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text((Path('/project-config') / name).read_text().replace(old, new))
    path.chmod(0o644)
if os.path.lexists(root / 'etc/nsswitch.conf'):
    raise ValueError('project NSS policy would overwrite existing package path')
shutil.copyfile(base / 'nsswitch.conf', root / 'etc/nsswitch.conf')
(root / 'etc/nsswitch.conf').chmod(0o644)
