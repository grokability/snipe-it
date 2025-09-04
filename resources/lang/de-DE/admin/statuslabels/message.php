<?php

return [

    'does_not_exist' => 'Diese Statusbezeichnung existiert nicht.',
    'deleted_label' => 'Gelöschte Statusbezeichnung',
    'assoc_assets'	 => 'Auf diese Statusbezeichnung bezieht sich momentan mindestens ein Asset und kann daher nicht gelöscht werden. Bitte sorgen Sie dafür, dass sich kein Asset mehr auf diese Statusbezeichnung bezieht und versuchen Sie es erneut. ',

    'create' => [
        'error'   => 'Statusbezeichnung wurde nicht erstellt. Bitte versuchen Sie es erneut.',
        'success' => 'Statusbezeichnung wurde erfolgreich erstellt.',
    ],

    'update' => [
        'error'   => 'Statusbezeichnung wurde nicht aktualisiert. Bitte versuchen Sie es erneut.',
        'success' => 'Statusbezeichnung wurde erfolgreich aktualisiert.',
    ],

    'delete' => [
        'confirm'   => 'Sind Sie sicher, dass Sie diese Statusbezeichnung löschen wollen?',
        'error'   => 'Es trat ein Fehler beim Löschen der Statusbezeichnung auf. Bitte versuchen Sie es erneut.',
        'success' => 'Die Statusbezeichnung wurde erfolgreich gelöscht.',
    ],

    'help' => [
        'undeployable'   => 'Diese Assets können niemandem zugeordnet werden.',
        'deployable'   => 'Diese Assets können ausgecheckt werden. Sobald sie zugewiesen sind, nehmen sie den Meta-Status <i class="fas fa-circle text-blue"></i> <strong>Herausgegeben</strong> an.',
        'archived'   => 'Diese Assets können nicht ausgecheckt werden und erscheinen nur in der Ansicht "Archiviert". Dies ist nützlich, um Informationen zu Assets für Budgetierungs- / historische Zwecke beizubehalten, aber sie aus der täglichen Assetliste herauszuhalten.',
        'pending'   => 'Diese Assets können vorübergehend niemandem zugewiesen werden. Wird häufig für Gegenstände verwendet, die in Reparatur sind, aber voraussichtlich in den Kreislauf zurückkehren werden.',
    ],

];
