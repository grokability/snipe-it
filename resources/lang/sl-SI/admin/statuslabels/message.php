<?php

return [

    'does_not_exist' => 'Oznaka statusa ne obstaja.',
    'deleted_label' => 'Izbrisana statusna oznaka',
    'assoc_assets'	 => 'Ta oznaka statusa je trenutno povezana z vsaj enim sredstvom in je ni mogoče izbrisati. Posodobite svoja sredstva, da ne bodo več v tem stanju in poskusite znova. ',

    'create' => [
        'error'   => 'Oznaka statusa ni bila ustvarjena, poskusite znova.',
        'success' => 'Oznaka statusa je bil uspešno ustvarjena.',
    ],

    'update' => [
        'error'   => 'Oznaka statusa ni bila posodobljena, poskusite znova',
        'success' => 'Oznaka statusa je bila uspešno posodobljena.',
    ],

    'delete' => [
        'confirm'   => 'Ali ste prepričani, da želite izbrisati to oznako statusa?',
        'error'   => 'Prišlo je do težave z izbrisom oznake statusa. Prosim poskusite ponovno.',
        'success' => 'Oznaka statusa je bila uspešno izbrisana.',
    ],

    'help' => [
        'undeployable'   => 'Tega sredstva ni mogoče dodeliti nikomur.',
        'deployable'   => 'Teh sredstev ni mogoče izdati. Ko bodo dodeljeni, bodo prevzeli meta status <i class="fas fa-circle text-blue"></i> <strong>Razporejeno</strong>.',
        'archived'   => 'Teh sredstev ni mogoče izdati in se bodo prikazala samo v pogledu Arhivirano. To je koristno za ohranjanje informacij o sredstvih za računovodske namene / zgodovinske namene, vendar jih ni na seznamu uporabnih sredstev.',
        'pending'   => 'Teh sredstev trnutno ni mogoče dodeliti nikomur, pogosto se uporablja za sredstva, ki so ne popravilu, in se pričakuje, da se bodo vrnila v obtok.',
    ],

];
