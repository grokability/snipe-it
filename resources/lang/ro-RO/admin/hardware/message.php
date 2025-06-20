<?php

return [

    'undeployable'                      => 'Următoarele active nu pot fi implementate și au fost eliminate din alocare: :asset_tags',
    'does_not_exist'                    => 'Activul nu există.',
    'does_not_exist_var'                => 'Activul cu eticheta :asset_tag nu a fost găsit.',
    'no_tag'                            => 'Nu a fost furnizată nicio etichetă de activ.',
    'does_not_exist_or_not_requestable' => 'Acel activ nu există sau nu este solicitabil.',
    'assoc_users'                       => 'Acest activ este în prezent alocat unui utilizator și nu poate fi șters. Te rog să înregistrezi mai întâi returnarea activului, apoi să încerci din nou să-l ștergi.',
    'warning_audit_date_mismatch'       => 'Următoarea dată de audit a acestui activ (:next_audit_date) este înainte de ultima dată de audit (:last_audit_date). Te rog să actualizezi următoarea dată de audit.',
    'labels_generated'                  => 'Etichetele au fost generate cu succes.',
    'error_generating_labels'           => 'Eroare la generarea etichetelor.',
    'no_assets_selected'                => 'Niciun activ selectat.',

    'create' => [
        'error'                         => 'Activul nu a fost creat, te rog să încerci din nou. :(',
        'success'                       => 'Activul a fost creat cu succes. :)',
        'success_linked'                => 'Activul cu eticheta :tag a fost creat cu succes. <strong><a href=":link" style="color: white;">Click aici pentru a vizualiza</a></strong>.',
        'multi_success_linked'          => 'Activul cu eticheta :links a fost creat cu succes.|:count active au fost create cu succes. :links.',
        'partial_failure'               => 'Un activ nu a putut fi creat. Motiv: :failures|:count active nu au putut fi create. Motive: :failures',
        'target_not_found' => [
            'user'                      => 'Utilizatorul alocat nu a putut fi găsit.',
            'asset'                     => 'Activul alocat nu a putut fi găsit.',
            'location'                  => 'Locația alocată nu a putut fi găsită.',
        ],
    ],

    'update' => [
        'error'                         => 'Activul nu a fost actualizat, te rog să încerci din nou',
        'success'                       => 'Activul a fost actualizat cu succes.',
        'encrypted_warning'             => 'Activul a fost actualizat cu succes, dar câmpurile personalizate criptate nu au fost actualizate din cauza permisiunilor',
        'nothing_updated'               => 'Nu au fost selectate câmpuri, deci nimic nu a fost actualizat.',
        'no_assets_selected'            => 'Nimic nu a fost actualizat deoarece nu au fost selectate active.',
        'assets_do_not_exist_or_are_invalid' => 'Activele selectate nu pot fi actualizate.',
    ],

    'restore' => [
        'error'                         => 'Activul nu a fost restaurat, te rog să încerci din nou',
        'success'                       => 'Activul a fost restaurat cu succes.',
        'bulk_success'                  => 'Activul a fost restaurat cu succes.',
        'nothing_updated'               => 'Nimic nu a fost restaurat deoarece nu au fost selectate active.',
    ],

    'audit' => [
        'error'                         => 'Audit activ eșuat: :error ',
        'success'                       => 'Audit activ înregistrat cu succes.',
    ],


    'deletefile' => [
        'error'                         => 'Fișierul nu a fost șters. Te rog să încerci din nou.',
        'success'                       => 'Fișierul a fost șters cu succes.',
    ],

    'upload' => [
        'error'                         => 'Fișierul/fișierele nu au fost încărcate. Te rog să încerci din nou.',
        'success'                       => 'Fișierul/fișierele au fost încărcate cu succes.',
        'nofiles'                       => 'Nu ai selectat niciun fișier pentru încărcare, sau fișierul pe care încerci să-l încarci este prea mare',
        'invalidfiles'                  => 'Unul sau mai multe dintre fișierele tale sunt prea mari sau au un tip de fișier nepermis. Tipurile de fișiere permise sunt png, gif, jpg, doc, docx, pdf și txt.',
    ],

    'import' => [
        'import_button'                 => 'Procesează importul',
        'error'                         => 'Unele elemente nu au importat corect.',
        'errorDetail'                   => 'Următoarele elemente nu au fost importate din cauza erorilor.',
        'success'                       => 'Fișierul tău a fost importat',
        'file_delete_success'           => 'Fișierul tău a fost șters cu succes',
        'file_delete_error'             => 'Fișierul nu a putut fi șters',
        'file_missing'                  => 'Fișierul selectat lipsește',
        'file_already_deleted'          => 'Fișierul selectat a fost deja șters',
        'header_row_has_malformed_characters' => 'Unul sau mai multe atribute din rândul antetului conțin caractere UTF-8 incorect formate',
        'content_row_has_malformed_characters' => 'Unul sau mai multe atribute din primul rând de conținut conțin caractere UTF-8 incorect formate',
        'transliterate_failure'         => 'Transliterarea de la :encoding la UTF-8 a eșuat din cauza caracterelor invalide la intrare'
    ],


    'delete' => [
        'confirm'                       => 'Ești sigur că dorești să ștergi acest activ?',
        'error'                         => 'A apărut o problemă la ștergerea activului. Te rog să încerci din nou.',
        'assigned_to_error'             => '{1}Eticheta activului: :asset_tag este în prezent alocată. Înregistrează returnarea acestui dispozitiv înainte de ștergere.|[2,*]Etichetele activelor: :asset_tag sunt în prezent alocate. Înregistrează returnarea acestor dispozitive înainte de ștergere.',
        'nothing_updated'               => 'Niciun activ nu a fost selectat, deci nimic nu a fost șters.',
        'success'                       => 'Activul a fost șters cu succes.',
    ],

    'checkout' => [
        'error'                         => 'Activul nu a fost alocat, te rog să încerci din nou',
        'success'                       => 'Activul a fost alocat cu succes.',
        'user_does_not_exist'           => 'Acel utilizator este invalid. Te rog să încerci din nou.',
        'not_available'                 => 'Acel activ nu este disponibil pentru alocare!',
        'no_assets_selected'            => 'Trebuie să selectezi cel puțin un activ din listă',
    ],

    'multi-checkout' => [
        'error'                         => 'Activul nu a fost alocat, te rog să încerci din nou|Activele nu au fost alocate, te rog să încerci din nou',
        'success'                       => 'Activul a fost alocat cu succes.|Activele au fost alocate cu succes.',
    ],

    'checkin' => [
        'error'                         => 'Activul nu a fost returnat, te rog să încerci din nou',
        'success'                       => 'Activul a fost returnat cu succes.',
        'user_does_not_exist'           => 'Acel utilizator este invalid. Te rog să încerci din nou.',
        'already_checked_in'            => 'Acel activ este deja returnat.',

    ],

    'requests' => [
        'error'                         => 'Solicitarea nu a avut succes, te rog să încerci din nou.',
        'success'                       => 'Solicitarea a fost trimisă cu succes.',
        'canceled'                      => 'Solicitarea a fost anulată cu succes.',
        'cancel'                        => 'Anulează această solicitare de element',
    ],

];