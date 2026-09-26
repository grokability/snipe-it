<?php

namespace Tests\Feature\Documents;

use App\Models\Asset;
use App\Models\DocumentTemplateVersion;
use App\Models\User;
use App\Services\Documents\DocumentService;
use App\Services\Documents\TemplateService;
use Tests\TestCase;

class DocumentTemplateVersionTest extends TestCase
{
    private TemplateService $templates;

    protected function setUp(): void
    {
        parent::setUp();
        $this->templates = app(TemplateService::class);
    }

    private function template(): \App\Models\DocumentTemplate
    {
        return $this->templates->create([
            'name' => 'Handover '.uniqid(),
            'type' => 'handover',
            'language' => 'en',
            'body' => 'v1 body',
        ], User::factory()->superuser()->create());
    }

    public function test_publish_creates_immutable_version_with_snapshot()
    {
        $template = $this->template();
        $version = $this->templates->publishVersion($template, ['eula_enabled' => false], User::factory()->superuser()->create());

        $this->assertSame(1, $version->version);
        $this->assertSame('v1 body', $version->snapshotField('body'));
        $this->assertSame('handover', $version->snapshotField('type'));
        $this->assertSame(['employee', 'it_representative'], $version->snapshotField('signature_config.roles'));
    }

    public function test_published_version_cannot_be_updated()
    {
        $template = $this->template();
        $version = $this->templates->publishVersion($template, [], User::factory()->superuser()->create());

        $version->snapshot = ['body' => 'tampered'];
        $this->assertFalse($version->save());
        $this->assertSame('v1 body', $version->fresh()->snapshotField('body'));
    }

    public function test_sequential_publishes_freeze_working_copy_changes()
    {
        $template = $this->template();
        $v1 = $this->templates->publishVersion($template, [], User::factory()->superuser()->create());

        // Edit the working copy, publish again
        $this->templates->update($template, ['name' => $template->name, 'type' => 'handover', 'body' => 'v2 body'], User::factory()->superuser()->create());
        $v2 = $this->templates->publishVersion($template, [], User::factory()->superuser()->create());

        $this->assertSame(2, $v2->version);
        $this->assertSame('v2 body', $v2->snapshotField('body'));
        $this->assertSame('v1 body', $v1->fresh()->snapshotField('body')); // v1 untouched
    }

    public function test_version_referenced_by_documents_cannot_be_deleted()
    {
        $template = $this->template();
        $version = $this->templates->publishVersion($template, [], User::factory()->superuser()->create());

        app(DocumentService::class)->generate($version, User::factory()->create(), collect([Asset::factory()->create()]), []);

        $this->assertFalse($version->delete());
        $this->assertNotNull(DocumentTemplateVersion::find($version->id));
    }

    public function test_unpublished_template_cannot_generate_documents()
    {
        $template = $this->template(); // no publish

        $this->assertNull($template->currentVersion()->first());
    }
}
