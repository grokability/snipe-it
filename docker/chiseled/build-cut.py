#!/usr/bin/env python3
"""Offline candidate cut from inputs recovered and verified during the build."""
import os
from pathlib import Path
import subprocess

from runtime_inputs import load_lock

base = Path(__file__).resolve().parent
lock_path, _, lock_sha256 = load_lock(base, os.environ['CHISEL_TARGET_ARCH'])
subprocess.run(['python3', str(base / 'check-build-inputs.py')], check=True)
subprocess.run(['python3', str(base / 'locked_chisel.py'), '--snapshot', '/snapshot',
                '--lock', str(lock_path), '--approved-sha256',
                lock_sha256, '--custom', '--root', '/rootfs',
                '--evidence', '/cut-evidence', *(base / 'slices.txt').read_text().splitlines()],
               check=True)
