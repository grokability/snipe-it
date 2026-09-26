<?php

use App\Http\Controllers\Documents\DocumentsController;
use App\Http\Controllers\Documents\DocumentTemplatesController;
use App\Http\Controllers\Documents\PrintDocumentsController;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

// Documents (custom Phase 1 document engine)
Route::group(['middleware' => ['auth']], function () {

    Route::get('documents', [DocumentsController::class, 'index'])
        ->name('documents.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home', route('home'))
            ->push(trans('documents.general.documents'), route('documents.index'))
        );

    Route::get('documents/create', [DocumentsController::class, 'create'])
        ->name('documents.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('documents.index')
            ->push(trans('documents.general.create'))
        );

    Route::post('documents', [DocumentsController::class, 'store'])
        ->name('documents.store');

    Route::post('documents/print', [PrintDocumentsController::class, 'store'])
        ->name('documents.print');

    Route::get('documents/{document}', [DocumentsController::class, 'show'])
        ->name('documents.show')
        ->breadcrumbs(fn (Trail $trail, $document) => $trail->parent('documents.index')
            ->push($document->number, route('documents.show', $document))
        );

    Route::get('documents/{document}/print', [DocumentsController::class, 'printView'])
        ->name('documents.web-print')
        ->breadcrumbs(fn (Trail $trail, $document) => $trail->parent('documents.show', $document)
            ->push(trans('documents.custom.web_print')));

    Route::get('documents/{document}/pdf', [DocumentsController::class, 'pdf'])
        ->name('documents.pdf');

    Route::post('documents/{document}/sign', [DocumentsController::class, 'sign'])
        ->name('documents.sign');

    Route::post('documents/{document}/pending', [DocumentsController::class, 'markPending'])
        ->name('documents.pending');

    Route::post('documents/{document}/cancel', [DocumentsController::class, 'cancel'])
        ->name('documents.cancel');

    // Document Templates
    Route::get('document-templates', [DocumentTemplatesController::class, 'index'])
        ->name('documents.templates.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home', route('home'))
            ->push(trans('documents.general.templates'), route('documents.templates.index'))
        );

    Route::get('document-templates/create', [DocumentTemplatesController::class, 'create'])
        ->name('documents.templates.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('documents.templates.index')
            ->push(trans('documents.general.create_template'))
        );

    Route::post('document-templates/markdown-preview', [DocumentTemplatesController::class, 'markdownPreview'])
        ->name('documents.templates.markdown-preview');

    Route::post('document-templates', [DocumentTemplatesController::class, 'store'])
        ->name('documents.templates.store');

    Route::get('document-templates/{template}', [DocumentTemplatesController::class, 'show'])
        ->name('documents.templates.show')
        ->breadcrumbs(fn (Trail $trail, $template) => $trail->parent('documents.templates.index')
            ->push($template->name, route('documents.templates.show', $template))
        );

    Route::get('document-templates/{template}/preview', [DocumentTemplatesController::class, 'preview'])
        ->name('documents.templates.preview')
        ->breadcrumbs(fn (Trail $trail, $template) => $trail->parent('documents.templates.show', $template)
            ->push(trans('documents.general.preview')));

    Route::get('document-templates/{template}/edit', [DocumentTemplatesController::class, 'edit'])
        ->name('documents.templates.edit')
        ->breadcrumbs(fn (Trail $trail, $template) => $trail->parent('documents.templates.show', $template)
            ->push(trans('general.edit'))
        );

    Route::post('document-templates/{template}', [DocumentTemplatesController::class, 'update'])
        ->name('documents.templates.update');

    Route::post('document-templates/{template}/publish', [DocumentTemplatesController::class, 'publish'])
        ->name('documents.templates.publish');

    Route::delete('document-templates/{template}', [DocumentTemplatesController::class, 'destroy'])
        ->name('documents.templates.destroy');
});
