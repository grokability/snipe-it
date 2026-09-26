<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrintDocumentRequest;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use App\Services\Documents\DocumentPrintService;
use Illuminate\Http\RedirectResponse;

class PrintDocumentsController extends Controller
{
    public function store(PrintDocumentRequest $request, DocumentPrintService $printing): RedirectResponse
    {
        $checkout = ['notes' => $request->input('notes', '')];
        if ($request->input('source_type') === 'acceptance') {
            $acceptance = CheckoutAcceptance::findOrFail($request->integer('source_id'));
            abort_unless($acceptance->checkoutable_type === Asset::class, 422);
            $user = User::findOrFail($acceptance->assigned_to_id);
            $printing->authorizeUser($user);
            $assets = collect([Asset::findOrFail($acceptance->checkoutable_id)]);
            $checkout['date'] = $acceptance->created_at->format('Y-m-d');
            $checkout['acceptance_id'] = $acceptance->id;
        } else {
            $user = User::findOrFail($request->integer('source_id'));
            $printing->authorizeUser($user);
            $assets = $user->assets()->limit(101)->get();
        }
        $document = $printing->generate($request->integer('document_template_version_id'), $user, $assets, $checkout);

        return redirect()->route('documents.pdf', ['document' => $document, 'inline' => 1]);
    }
}
