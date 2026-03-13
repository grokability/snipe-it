<?php

return [

    'does_not_exist' => 'Oznaka statusa ne postoji.',
    'deleted_label' => 'Deleted Status Label',
    'assoc_assets'	 => 'Ova Statusna oznaka trenutačno je povezana s barem jednom Assetom i ne može se izbrisati. Ažurirajte svoju aktivu da više ne referira na taj status i pokušajte ponovo.',

    'create' => [
        'error'   => 'Oznaka statusa nije izrađena, pokušajte ponovo.',
        'success' => 'Oznaka statusa uspješno je izrađena.',
    ],

    'update' => [
        'error'   => 'Oznaka statusa nije ažurirana, pokušajte ponovo',
        'success' => 'Oznaka statusa uspješno je ažurirana.',
    ],

    'delete' => [
        'confirm'   => 'Jeste li sigurni da želite izbrisati ovu oznaku statusa?',
        'error'   => 'Došlo je do problema s brisanjem oznake stanja. Molim te pokušaj ponovno.',
        'success' => 'Oznaka statusa uspješno je izbrisana.',
    ],

    'help' => [
        'undeployable'   => 'Ova se imovina ne može dodijeliti nikome.',
        'deployable'   => 'Ova se imovina može provjeriti. Nakon dodjeljivanja, oni će preuzeti meta status <i class="fas fa-circle text-blue"></i> <strong>Deployed</strong>.',
        'archived'   => 'Ove se imovine ne može izbrisati, a prikazat će se samo u arhiviranom vlasničkom pregledu. To je korisno za zadržavanje informacija o imovini za proračunsko / povijesne svrhe, ali ih ne bi smjelo ostati iz dnevnog popisa imovine.',
        'pending'   => 'Ovu imovinu još se ne može dodijeliti nikome, često se koristi za predmete koji se vraćaju na popravak, ali se očekuje povratak u promet.',
    ],

];
