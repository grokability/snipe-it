"""Select an architecture-specific lock without changing its original bytes."""
import hashlib
import json
from pathlib import Path


def load_lock(base: Path, architecture: str):
    if architecture not in ('amd64', 'arm64'):
        raise ValueError('unsupported runtime architecture: ' + architecture)
    accepted = json.loads((base / 'accepted-inputs.json').read_text())
    expected = accepted['lock_sha256'][architecture]
    path = base / ('input-lock.' + architecture + '.json')
    data = path.read_bytes()
    if hashlib.sha256(data).hexdigest() != expected:
        raise ValueError('input lock differs from accepted identity: ' + architecture)
    lock = json.loads(data)
    if lock['architecture'] != architecture:
        raise ValueError('runtime target architecture differs from accepted input lock')
    return path, lock, expected
