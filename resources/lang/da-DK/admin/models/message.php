<?php

return array(

    'deleted' => 'Slettede asset-model',
    'does_not_exist' => 'Model findes ikke.',
    'no_association' => 'ADVARSEL! Aktivmodellen for dette element er ugyldig eller mangler!',
    'no_association_fix' => 'Dette vil ødelægge ting på underlige og forfærdelige måder. Rediger dette aktiv nu for at tildele det en model.',
    'assoc_users'	 => 'Denne model er knyttet til en eller flere aktiver og ikke kan slettes. Slet venligst aktiver, og prøv derefter at slette igen. ',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'Modellen blev ikke oprettet, prøve igen.',
        'success' => 'Model oprettet.',
        'duplicate_set' => 'Der findes allerede en model med det navn, producent og modelnummer eksisterer allerede.',
    ),

    'update' => array(
        'error'   => 'Modellen blev ikke opdateret, prøv igen',
        'success' => 'Model opdateret.',
    ),

    'delete' => array(
        'confirm'   => 'Er du sikker på du vil slette dette aktiv model?',
        'error'   => 'Der opstod et problem under sletning af modellen. Prøv venligst igen.',
        'success' => 'Modellen blev slettet.'
    ),

    'restore' => array(
        'error'   		=> 'Modellen blev ikke gendannet, prøv igen',
        'success' 		=> 'Model gendannet.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Ingen felter blev ændret, så intet er blevet opdateret.',
        'success' 		=> 'Modellen blev opdateret. opdateret: model_count modeller blev opdateret.',
        'warn'          => 'Du er ved at opdatere egenskaberne for følgende ~~~: Du er ved at redigere egenskaberne for følgende :model_count modeller:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Ingen modeller blev valgt, så intet blev slettet.',
        'success' 		    => 'Model slettet! :success_count modeller slettet!',
        'success_partial' 	=> ':success_count model(ler) blev slettet; men :fail_count kunne ikke slettes fordi de stadig har aktiver knyttet til sig.'
    ),

);
