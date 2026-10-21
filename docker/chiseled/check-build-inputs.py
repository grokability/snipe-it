#!/usr/bin/env python3
"""Fail before Chisel if the supplied lock or reviewed source inputs drift."""
import hashlib
import io
import os
from pathlib import Path
import tarfile

from runtime_inputs import load_lock

base = Path(__file__).resolve().parent
_, lock, _ = load_lock(base, os.environ['CHISEL_TARGET_ARCH'])
for name, path in [('composer_lock', '/application-composer.lock'),
                   ('go_mod', '/application-go.mod')]:
    if hashlib.sha256(Path(path).read_bytes()).hexdigest() != lock['inputs'][name]['sha256']:
        raise ValueError('application source lock drift: ' + name)
archive = io.BytesIO()
with tarfile.open(fileobj=archive, mode='w') as tar:
    for path in sorted((base / 'slices').glob('*.yaml')):
        data = path.read_bytes()
        entry = tarfile.TarInfo(path.name)
        entry.mode = 0o644
        entry.size = len(data)
        tar.addfile(entry, io.BytesIO(data))
if hashlib.sha256(archive.getvalue()).hexdigest() != lock['inputs']['custom_sdfs']['sha256']:
    raise ValueError('reviewed source SDF bytes differ from locked archive')
print('Accepted lock, reviewed SDFs, Composer lock and Go module identities match.')
