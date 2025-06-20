<?php

return array(

    'deleted' => 'Model de activ șters',
    'does_not_exist' => 'Modelul nu există.',
    'no_association' => 'ATENȚIE! Modelul de activ pentru acest element este invalid sau lipsește!',
    'no_association_fix' => 'Acest lucru va cauza probleme ciudate și oribile. Editează acest activ acum pentru a-i aloca un model.',
    'assoc_users'    => 'Acest model este asociat în prezent cu unul sau mai multe active și nu poate fi șters. Te rog să ștergi activele, apoi să încerci din nou să ștergi modelul.',
    'invalid_category_type' => 'Această categorie trebuie să fie o categorie de active.',

    'create' => array(
        'error'   => 'Modelul nu a fost creat, te rog să încerci din nou.',
        'success' => 'Modelul a fost creat cu succes.',
        'duplicate_set' => 'Un model de activ cu același nume, producător și număr de model există deja.',
    ),

    'update' => array(
        'error'   => 'Modelul nu a fost actualizat, te rog să încerci din nou',
        'success' => 'Modelul a fost actualizat cu succes.'
    ),

    'delete' => array(
        'confirm'   => 'Ești sigur că dorești să ștergi acest model de activ?',
        'error'   => 'A apărut o problemă la ștergerea modelului. Te rog să încerci din nou.',
        'success' => 'Modelul a fost șters cu succes.'
    ),

    'restore' => array(
        'error'         => 'Modelul nu a fost restaurat, te rog să încerci din nou',
        'success'       => 'Modelul a fost restaurat cu succes.'
    ),

    'bulkedit' => array(
        'error'         => 'Niciun câmp nu a fost modificat, deci nimic nu a fost actualizat.',
        'success'       => 'Model actualizat cu succes. |:model_count modele actualizate cu succes.',
        'warn'          => 'Ești pe cale să actualizezi proprietățile următorului model:|Ești pe cale să editezi proprietățile următoarelor :model_count modele:',

    ),

    'bulkdelete' => array(
        'error'           => 'Niciun model nu a fost selectat, deci nimic nu a fost șters.',
        'success'         => 'Model șters!|:success_count modele șterse!',
        'success_partial' => ':success_count model(e) au fost șterse, însă :fail_count nu au putut fi șterse deoarece încă au active asociate.'
    ),

);