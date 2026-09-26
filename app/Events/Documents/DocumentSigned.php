<?php

namespace App\Events\Documents;

use App\Models\Document;
use App\Models\DocumentSignature;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentSigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Document $document,
        public DocumentSignature $signature,
    ) {}
}
