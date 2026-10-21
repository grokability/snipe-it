"""Regression coverage for selecting preserved locks and rejecting drift."""
import hashlib
import json
from pathlib import Path
import shutil
import tempfile
import unittest

from runtime_inputs import load_lock


BASE = Path(__file__).resolve().parent
EXPECTED = {
    'amd64': '19237448f305871021cd394e41ebca138a550a022d58c80c2af6da4b0fbd0e53',
    'arm64': 'afc79bf0256d58d15ede82ea70a82d25164a93a5a42f96d5268d470679c5148e',
}


class RuntimeInputsTest(unittest.TestCase):
    def test_both_original_locks_are_selectable_without_mutation(self):
        for architecture, expected in EXPECTED.items():
            with self.subTest(architecture=architecture):
                path, lock, digest = load_lock(BASE, architecture)
                self.assertEqual(lock['architecture'], architecture)
                self.assertEqual(digest, expected)
                self.assertEqual(hashlib.sha256(path.read_bytes()).hexdigest(), expected)

    def test_unsupported_architecture_is_rejected(self):
        with self.assertRaisesRegex(ValueError, 'unsupported runtime architecture'):
            load_lock(BASE, 'riscv64')

    def test_changed_lock_is_rejected_for_each_architecture(self):
        for architecture in EXPECTED:
            with self.subTest(architecture=architecture), tempfile.TemporaryDirectory() as tmp:
                base = Path(tmp)
                shutil.copyfile(BASE / 'accepted-inputs.json', base / 'accepted-inputs.json')
                name = 'input-lock.' + architecture + '.json'
                (base / name).write_bytes((BASE / name).read_bytes() + b'\n')
                with self.assertRaisesRegex(ValueError, 'input lock differs from accepted identity'):
                    load_lock(base, architecture)

    def test_wrong_architecture_cannot_be_accepted_by_hash_alone(self):
        with tempfile.TemporaryDirectory() as tmp:
            base = Path(tmp)
            accepted = json.loads((BASE / 'accepted-inputs.json').read_text())
            accepted['lock_sha256']['arm64'] = EXPECTED['amd64']
            (base / 'accepted-inputs.json').write_text(json.dumps(accepted))
            shutil.copyfile(BASE / 'input-lock.amd64.json', base / 'input-lock.arm64.json')
            with self.assertRaisesRegex(ValueError, 'runtime target architecture differs'):
                load_lock(base, 'arm64')

    def test_missing_lock_does_not_fall_back_to_other_architecture(self):
        with tempfile.TemporaryDirectory() as tmp:
            base = Path(tmp)
            shutil.copyfile(BASE / 'accepted-inputs.json', base / 'accepted-inputs.json')
            shutil.copyfile(BASE / 'input-lock.arm64.json', base / 'input-lock.arm64.json')
            with self.assertRaises(FileNotFoundError):
                load_lock(base, 'amd64')


if __name__ == '__main__':
    unittest.main()
