<?php

namespace Tests\Unit\Helpers;

use App\Helpers\StorageHelper;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Cubre StorageHelper::getMediaType (todas las ramas del switch), allowSafeInline
 * y getFiletype usando un disco fake con archivos de distintas extensiones.
 */
class StorageHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('default');
    }

    public static function mediaTypeProvider(): array
    {
        return [
            ['foto.jpg', 'image'],
            ['icono.png', 'image'],
            ['anim.gif', 'image'],
            ['vector.svg', 'image'],
            ['moderno.webp', 'image'],
            ['doc.pdf', 'pdf'],
            ['audio.mp3', 'audio'],
            ['sonido.wav', 'audio'],
            ['video.mp4', 'video'],
            ['clip.mov', 'video'],
            ['carta.doc', 'document'],
            ['carta.docx', 'document'],
            ['plano.txt', 'text'],
            ['hoja.xls', 'spreadsheet'],
            ['hoja.xlsx', 'spreadsheet'],
            ['raro.zzz', 'zzz'],
        ];
    }

    #[DataProvider('mediaTypeProvider')]
    public function test_get_media_type(string $filename, string $expected): void
    {
        Storage::put($filename, 'contenido');

        $this->assertSame($expected, StorageHelper::getMediaType($filename));
    }

    public function test_get_media_type_null_when_missing(): void
    {
        $this->assertNull(StorageHelper::getMediaType('no-existe.jpg'));
    }

    public function test_allow_safe_inline(): void
    {
        Storage::put('seguro.pdf', 'x');
        Storage::put('peligroso.exe', 'x');

        $this->assertTrue(StorageHelper::allowSafeInline('seguro.pdf'));
        $this->assertFalse(StorageHelper::allowSafeInline('peligroso.exe'));
        $this->assertFalse(StorageHelper::allowSafeInline('no-existe.pdf'));
    }

    public function test_get_filetype(): void
    {
        Storage::put('archivo.docx', 'x');

        $this->assertSame('docx', StorageHelper::getFiletype('archivo.docx'));
        $this->assertNull(StorageHelper::getFiletype('no-existe.docx'));
    }
}
