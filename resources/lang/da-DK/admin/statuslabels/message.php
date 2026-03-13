<?php

return [

    'does_not_exist' => 'Statuslabel eksisterer ikke.',
    'deleted_label' => 'Slettede Status Label',
    'assoc_assets'	 => 'Dette statusmærke er i øjeblikket forbundet med mindst én aktiv og kan ikke slettes. Opdater dine aktiver for ikke længere at henvise til denne status, og prøv igen.',

    'create' => [
        'error'   => 'Statuslabel blev ikke oprettet, prøv igen.',
        'success' => 'Status Label oprettes med succes.',
    ],

    'update' => [
        'error'   => 'Statuslabel blev ikke opdateret, prøv igen',
        'success' => 'Statuslabel opdateret med succes.',
    ],

    'delete' => [
        'confirm'   => 'Er du sikker på, at du vil slette denne statuslabel?',
        'error'   => 'Der opstod et problem ved at slette statusetiketten. Prøv igen.',
        'success' => 'Statusmærket blev slettet.',
    ],

    'help' => [
        'undeployable'   => 'Disse aktiver kan ikke tildeles nogen.',
        'deployable'   => 'Disse aktiver kan tjekkes ud. Når de er tildelt, antager de en metastatus på <i class="fas fa-circle text-blue"></i> <strong>Deployed</strong>.',
        'archived'   => 'Disse aktiver kan ikke tjekkes ud, og vises kun i arkiveret visning. Dette er nyttigt for at bevare oplysninger om aktiver til budgettering / historiske formål, men bevare dem ud af den daglige aktivliste.',
        'pending'   => 'Disse aktiver kan endnu ikke tildeles nogen, der ofte bruges til genstande, der er ude til reparation, men forventes at vende tilbage til omløb.',
    ],

];
