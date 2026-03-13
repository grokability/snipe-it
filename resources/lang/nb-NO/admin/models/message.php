<?php

return array(

    'deleted' => 'Slettet ressursmodell',
    'does_not_exist' => 'Modell eksisterer ikke.',
    'no_association' => 'ADVARSEL! Ressursmodellen for dette elementet er ugyldig eller mangler!',
    'no_association_fix' => 'Dette vil ødelegge ting på merkelige og forferdelige måte. Rediger denne ressursen nå for å tildele den en modell.',
    'assoc_users'	 => 'Denne modellen er tilknyttet en eller flere eiendeler og kan ikke slettes. Slett eiendelene, og prøv å slette modellen igjen. ',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'Modellen ble ikke opprettet. Prøv igjen.',
        'success' => 'Opprettelse av modell var vellykket.',
        'duplicate_set' => 'En eiendel med dette navnet, produsenten og modelnummeret eksisterer allerede.',
    ),

    'update' => array(
        'error'   => 'Modell ble ikke oppdatert. Prøv igjen',
        'success' => 'Oppdatering av modell vellykket.',
    ),

    'delete' => array(
        'confirm'   => 'Er du sikker på at du vil slette denne modellen?',
        'error'   => 'Det oppstod et problem under sletting av modellen. Prøv igjen.',
        'success' => 'Sletting av modell vellykket.'
    ),

    'restore' => array(
        'error'   		=> 'Modell ble ikke gjenopprettet. Prøv igjen',
        'success' 		=> 'Vellykket gjenoppretting av modell.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Ingen felt ble endret, så ingenting ble oppdatert.',
        'success' 		=> 'Modelloppdatering vellyket.| :model_count modeller oppdatert.',
        'warn'          => 'Du er i ferd med å oppdatere egenskapene til følgende modell: Du er i ferd med å redigere egenskapene for følgende modeller: model_count modeller:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Ingen modeller ble valgt, så ingenting ble slettet.',
        'success' 		    => 'Modellen ble slettet!g_:success_count modeller slettet!',
        'success_partial' 	=> ':Success_count-modell(ene) ble slettet, men fail_count kunne ikke slettes fordi de fortsatt har eiendeler knyttet til dem.'
    ),

);
