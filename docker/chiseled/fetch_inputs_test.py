"""Network input identity and source selection regression coverage."""
import hashlib
import unittest

from fetch_inputs import snapshot_base, verified_download, package_url


class FetchInputsTest(unittest.TestCase):
    def setUp(self):
        self.accepted = {'snapshot_ids': {
            'amd64': {'noble': '20261008T022632Z',
                      'noble-security': '20261022T020000Z',
                      'noble-updates': '20261007T180500Z'},
            'arm64': {suite: '20261008T042112Z'
                      for suite in ('noble', 'noble-security', 'noble-updates')},
        }}

    def test_selects_target_and_pocket_without_live_repository_resolution(self):
        self.assertEqual(snapshot_base(self.accepted, 'amd64', 'noble-security'),
                         'https://snapshot.ubuntu.com/ubuntu/20261022T020000Z/')
        self.assertEqual(snapshot_base(self.accepted, 'arm64', 'noble-security'),
                         'https://snapshot.ubuntu.com/ubuntu/20261008T042112Z/')

    def test_verified_download_accepts_exact_bytes(self):
        data = b'locked package'
        identity = {'sha256': hashlib.sha256(data).hexdigest(), 'size': len(data)}
        self.assertEqual(verified_download('https://example.test/package', identity,
                                          fetch=lambda url: data), data)

    def test_changed_bytes_and_truncation_fail_closed(self):
        identity = {'sha256': hashlib.sha256(b'original').hexdigest(), 'size': 8}
        for payload in (b'modified', b'orig'):
            with self.subTest(payload=payload), self.assertRaisesRegex(ValueError, 'locked byte identity mismatch'):
                verified_download('https://example.test/package', identity,
                                  fetch=lambda url: payload)

    def test_missing_or_invalid_snapshot_mapping_has_no_fallback(self):
        with self.assertRaises((ValueError, KeyError)):
            snapshot_base(self.accepted, 'riscv64', 'noble')
        self.accepted['snapshot_ids']['amd64']['noble'] = 'latest'
        with self.assertRaises(ValueError):
            snapshot_base(self.accepted, 'amd64', 'noble')

    def test_package_url_is_confined_to_official_snapshot_pool(self):
        base = snapshot_base(self.accepted, 'arm64', 'noble')
        self.assertEqual(package_url(base, 'pool/main/p/php8.3/package.deb'),
                         base + 'pool/main/p/php8.3/package.deb')
        for filename in ('https://other.test/file', '../file', 'pool/../file',
                         '/pool/file', 'pool/file?redirect=other'):
            with self.subTest(filename=filename), self.assertRaises(ValueError):
                package_url(base, filename)


if __name__ == '__main__':
    unittest.main()
