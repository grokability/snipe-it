<?php

namespace Tests\Unit\Rules;

use App\Rules\AllowedUploadExtension;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AllowedUploadExtensionTest extends TestCase
{
    // Real UploadedFile pointed at a real temp file. UploadedFile::fake()
    // bypasses finfo (its getMimeType() reads MimeType::from($name), which
    // is extension-only), so it can't reproduce the sniff-vs-extension
    // mismatch this rule exists to tolerate.
    private function realUpload(string $clientName, string $content): UploadedFile
    {
        $path = $this->trackTempFile(tempnam(sys_get_temp_dir(), 'snipeit_rule_'));
        file_put_contents($path, $content);

        return new UploadedFile($path, $clientName, null, null, true);
    }

    private function passes(UploadedFile $file, array $extensions = ['txt', 'csv', 'jpg', 'pdf']): bool
    {
        return Validator::make(
            ['file' => $file],
            ['file' => [new AllowedUploadExtension($extensions)]],
        )->passes();
    }

    #[Test]
    public function accepts_plain_text_file_with_matching_extension(): void
    {
        $this->assertTrue($this->passes(
            $this->realUpload('notes.txt', "hello world\nmore text\n"),
        ));
    }

    // Issue #12460: empty text upload. finfo returns application/x-empty,
    // which does not reverse-map to any extension. Laravel's `mimes:txt`
    // would reject; this rule accepts the client-supplied extension.
    #[Test]
    public function accepts_empty_txt_file(): void
    {
        $this->assertTrue($this->passes(
            $this->realUpload('empty.txt', ''),
        ));
    }

    // Issue #12460 (TechWilk repro): plain text whose first byte is `;`
    // and whose fields are tab-separated matches libmagic's INI heuristic.
    // finfo returns application/x-wine-extension-ini; guessExtension()
    // returns null. The rule should still accept it because the client
    // extension is on the allowlist.
    #[Test]
    public function accepts_txt_file_that_libmagic_misidentifies_as_ini(): void
    {
        $this->assertTrue($this->passes(
            $this->realUpload('sample.txt', ";Bob[A]\tSmith[B]\r\n50\t0.8"),
        ));
    }

    // Issue #10387: CSVs whose sniffed MIME is unhelpful (Windows/IIS
    // finfo commonly returns application/octet-stream) or that trip a
    // non-CSV magic signature. The UploadFileRequest path should still
    // accept those when the extension is on the allowlist.
    #[Test]
    public function accepts_csv_when_content_sniff_yields_octet_stream(): void
    {
        $this->assertTrue($this->passes(
            $this->realUpload('inventory.csv', "\x00\x01\x02random,binary,bytes\n"),
            ['csv', 'txt'],
        ));
    }

    // The extension-check backstop still catches the classic mislabel:
    // a PNG (well-known magic bytes) renamed to .txt. Client extension
    // passes, but guessExtension() returns 'png' and 'png' is not in the
    // allowlist, so the rule rejects.
    #[Test]
    public function rejects_binary_file_whose_sniff_disagrees_with_allowlist(): void
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='
        );

        $this->assertFalse($this->passes(
            $this->realUpload('sneaky.txt', $png),
            ['txt', 'csv'], // png deliberately absent
        ));
    }

    #[Test]
    public function rejects_extension_not_on_allowlist(): void
    {
        $this->assertFalse($this->passes(
            $this->realUpload('installer.exe', "MZ\x90\x00"),
        ));
    }

    #[Test]
    public function rejects_php_executable_extension_even_if_allowlisted(): void
    {
        // Even a maliciously-permissive caller can't punch a PHP file
        // through. This mirrors Laravel's own shouldBlockPhpUpload guard.
        $this->assertFalse($this->passes(
            $this->realUpload('shell.php', "<?php system(\$_GET['c']);"),
            ['txt', 'php'],
        ));
    }

    // The webshell case: PHP source dressed up as an image. finfo sniffs
    // to text/x-php, which has no reverse map in Symfony's guesser (so the
    // sniff crosscheck can't catch it), and my "uninformative sniff"
    // fallback would otherwise let it through. The explicit executable-MIME
    // belt catches it.
    #[Test]
    public function rejects_php_content_disguised_as_an_image(): void
    {
        $this->assertFalse($this->passes(
            $this->realUpload('shell.jpg', "<?php system(\$_GET['c']); ?>"),
        ));
    }

    #[Test]
    public function rejects_php_content_disguised_as_text(): void
    {
        $this->assertFalse($this->passes(
            $this->realUpload('notes.txt', "<?php echo 'hi'; ?>"),
        ));
    }

    // Shebang scripts stay allowed. Snipe-IT does not execute uploads and
    // legitimate script snippets end up in .txt support-ticket attachments
    // often enough that rejecting them is user-hostile.
    #[Test]
    public function accepts_shell_script_content_in_txt(): void
    {
        $this->assertTrue($this->passes(
            $this->realUpload('snippet.txt', "#!/bin/bash\necho hi\n"),
        ));
    }

    #[Test]
    public function rejects_non_file_values(): void
    {
        $this->assertFalse(Validator::make(
            ['file' => 'not-a-file'],
            ['file' => [new AllowedUploadExtension(['txt'])]],
        )->passes());
    }
}
