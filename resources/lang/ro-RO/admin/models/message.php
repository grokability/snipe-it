<?php

return array(

    'deleted' => 'Model de activ șters',
    'does_not_exist' => 'Modelul nu exista.',
    'no_association' => 'AVERTISMENT! Modelul de activ pentru acest articol este invalid sau lipsește!',
    'no_association_fix' => 'Acest lucru va strica lucrurile în moduri ciudate și oribile. Editează acest bun acum pentru a-l atribui un model.',
    'assoc_users'	 => 'Acest model este momentan asociat cu cel putin unul sau mai multe active si nu poate fi sters. Va rugam sa stergeti activul si dupa incercati iar. ',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'Modelul nu a fost creat, incercati iar.',
        'success' => 'Modelul a fost creat.',
        'duplicate_set' => 'Un model de activ cu numele, producătorul și numărul modelului există deja.',
    ),

    'update' => array(
        'error'   => 'Modelul nu a fost actualizat, va rugam incercati iar',
        'success' => 'Modelul a fost actualizat.',
    ),

    'delete' => array(
        'confirm'   => 'Sunteti sigur ca doriti sa stergeti acest model de activ?',
        'error'   => 'A aparut o problema la stergerea modelului. Incercati iar.',
        'success' => 'Modelul a fost sters.'
    ),

    'restore' => array(
        'error'   		=> 'Modelul nu a fost restabilit, încercați din nou',
        'success' 		=> 'Modelul a fost restaurat cu succes.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Nu au fost modificate câmpuri, deci nimic nu a fost actualizat.',
        'success' 		=> 'Modelul a fost actualizat cu succes. <unk> :model_count modele actualizate cu succes.',
        'warn'          => 'Sunteți pe cale să actualizați proprietățile următorului model: Sunteți pe cale să editați proprietățile următoarelor modele :model_count:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Nu au fost selectate câmpuri, deci nimic nu a fost actualizat.',
        'success' 		    => 'Modelul a fost șters!<unk> :success_count modele șterse!',
        'success_partial' 	=> 'Au fost șterse :success_count modele, cu toate acestea :fail_count nu au putut fi șterse deoarece au în continuare active asociate cu acestea.'
    ),

);
