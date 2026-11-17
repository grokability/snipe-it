<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | such as the size rules. Feel free to tweak each of these messages.
    |
    */

    'accepted' => ':attribute väli peab olema aktsepteeritud.',
    'accepted_if' => 'Väli :attribute tuleb kinnitada, kui välja :other väärtus on :value.',
    'active_url' => 'Väli :attribute peab olema kehtiv URL.',
    'after' => 'Välja :attribute kuupäev peab olema hilisem kui :date.',
    'after_or_equal' => 'Välja :attribute kuupäev peab olema :date või hilisem.',
    'alpha' => 'Väli :attribute tohib sisaldada ainult tähti.',
    'alpha_dash' => 'Väli :attribute tohib sisaldada ainult tähti, numbreid, side- ja allkriipse.',
    'alpha_num' => 'Väli :attribute tohib sisaldada ainult tähti ja numbreid.',
    'array' => 'Väli :attribute peab olema massiiv.',
    'ascii' => 'Väli :attribute tohib sisaldada ainult ühebaidiseid tähti, numbreid ja sümboleid.',
    'before' => 'Välja :attribute kuupäev peab olema varasem kui :date.',
    'before_or_equal' => 'Välja :attribute kuupäev peab olema :date või varasem.',
    'between' => [
        'array' => 'Väli :attribute peab sisaldama :min kuni :max elementi.',
        'file' => 'Välja :attribute faili suurus peab olema :min kuni :max kilobaiti.',
        'numeric' => 'Välja :attribute väärtus peab olema :min kuni :max.',
        'string' => 'Väli :attribute peab sisaldama :min kuni :max tähemärki.',
    ],
    'valid_regex' => 'Regulaaravaldis on vigane.',
    'boolean' => 'Atribuudiväljadele :attribute peab olema tõene või vale.',
    'can' => 'Väli :attribute sisaldab lubamatut väärtust.',
    'confirmed' => 'Välja :attribute kinnitus ei vasta sisestatud väärtusele.',
    'contains' => 'Väljal :attribute puudub kohustuslik väärtus.',
    'current_password' => 'Parool on vale.',
    'date' => 'Väli :attribute peab olema kehtiv kuupäev.',
    'date_equals' => 'Välja :attribute kuupäev peab olema :date.',
    'date_format' => 'Väli :attribute peab vastama vormingule :format.',
    'decimal' => 'Väljal :attribute peab olema :decimal kohta pärast koma.',
    'declined' => 'Väli :attribute tuleb tagasi lükata.',
    'declined_if' => 'Väli :attribute tuleb tagasi lükata, kui välja :other väärtus on :value.',
    'different' => 'Väljade :attribute ja :other väärtused peavad erinema.',
    'digits' => 'Väli :attribute peab koosnema :digits numbrist.',
    'digits_between' => 'Väli :attribute peab koosnema :min kuni :max numbrist.',
    'dimensions' => 'Väljal :attribute oleva pildi mõõtmed on vigased.',
    'distinct' => 'Atribuudiväljal on duplikaadi väärtus.',
    'doesnt_end_with' => 'Väli :attribute ei tohi lõppeda ühegagi järgmistest: :values.',
    'doesnt_start_with' => 'Väli :attribute ei tohi alata ühegagi järgmistest: :values.',
    'email' => 'Väli :attribute peab olema kehtiv e-posti aadress.',
    'ends_with' => 'Väli :attribute peab lõppema ühega järgmistest: :values.',
    'enum' => 'Valitud :attrubute on kehtetu.',
    'exists' => 'Valitud atribuut on kehtetu.',
    'extensions' => 'Välja :attribute failil peab olema üks järgmistest laienditest: :values.',
    'file' => 'Väli :attribute peab olema fail.',
    'filled' => 'Atribuudiväljal peab olema väärtus.',
    'gt' => [
        'array' => 'Väli :attribute peab sisaldama rohkem kui :value elementi.',
        'file' => 'Välja :attribute faili suurus peab olema üle :value kilobaidi.',
        'numeric' => 'Välja :attribute väärtus peab olema suurem kui :value.',
        'string' => 'Väli :attribute peab sisaldama rohkem kui :value tähemärki.',
    ],
    'gte' => [
        'array' => 'Väli :attribute peab sisaldama vähemalt :value elementi.',
        'file' => 'Välja :attribute faili suurus peab olema vähemalt :value kilobaiti.',
        'numeric' => 'Välja :attribute väärtus peab olema vähemalt :value.',
        'string' => 'Väli :attribute peab sisaldama vähemalt :value tähemärki.',
    ],
    'hex_color' => 'Väli :attribute peab olema kehtiv kuueteistkümnendkujul värvikood.',
    'image' => 'Väli :attribute peab olema pilt.',
    'import_field_empty' => 'Välja :fieldname väärtus ei tohi olla null.',
    'in' => 'Valitud atribuut on kehtetu.',
    'in_array' => 'Väli :attribute peab leiduma väljal :other.',
    'integer' => 'Väli :attribute peab olema täisarv.',
    'ip' => 'Väli :attribute peab olema kehtiv IP-aadress.',
    'ipv4' => 'Väli :attribute peab olema kehtiv IPv4-aadress.',
    'ipv6' => 'Väli :attribute peab olema kehtiv IPv6-aadress.',
    'json' => 'Väli :attribute peab olema kehtiv JSON-string.',
    'list' => 'Väli :attribute peab olema loend.',
    'lowercase' => 'Väli :attribute peab olema väiketähtedega.',
    'lt' => [
        'array' => 'Väli :attribute peab sisaldama vähem kui :value elementi.',
        'file' => 'Välja :attribute faili suurus peab olema alla :value kilobaidi.',
        'numeric' => 'Välja :attribute väärtus peab olema väiksem kui :value.',
        'string' => 'Väli :attribute peab sisaldama vähem kui :value tähemärki.',
    ],
    'lte' => [
        'array' => 'Väli :attribute ei tohi sisaldada üle :value elemendi.',
        'file' => 'Välja :attribute faili suurus ei tohi ületada :value kilobaiti.',
        'numeric' => 'Välja :attribute väärtus ei tohi olla suurem kui :value.',
        'string' => 'Väli :attribute ei tohi sisaldada üle :value tähemärgi.',
    ],
    'mac_address' => 'Väli :attribute peab olema kehtiv MAC-aadress.',
    'max' => [
        'array' => 'Väli :attribute ei tohi sisaldada üle :max elemendi.',
        'file' => 'Välja :attribute faili suurus ei tohi ületada :max kilobaiti.',
        'numeric' => 'Välja :attribute väärtus ei tohi olla suurem kui :max.',
        'string' => 'Väli :attribute ei tohi sisaldada üle :max tähemärgi.',
    ],
    'max_digits' => 'Väli :attribute ei tohi sisaldada üle :max numbri.',
    'mimes' => 'Väli :attribute peab olema järgmist tüüpi fail: :values.',
    'mimetypes' => 'Väli :attribute peab olema järgmist tüüpi fail: :values.',
    'min' => [
        'array' => 'Väli :attribute peab sisaldama vähemalt :min elementi.',
        'file' => 'Välja :attribute faili suurus peab olema vähemalt :min kilobaiti.',
        'numeric' => 'Välja :attribute väärtus peab olema vähemalt :min.',
        'string' => 'Väli :attribute peab sisaldama vähemalt :min tähemärki.',
    ],
    'min_digits' => 'Väli :attribute peab sisaldama vähemalt :min numbrit.',
    'missing' => 'Väli :attribute peab puuduma.',
    'missing_if' => 'Väli :attribute peab puuduma, kui välja :other väärtus on :value.',
    'missing_unless' => 'Väli :attribute peab puuduma, välja arvatud juhul, kui välja :other väärtus on :value.',
    'missing_with' => 'Väli :attribute peab puuduma, kui :values on olemas.',
    'missing_with_all' => 'Väli :attribute peab puuduma, kui kõik järgmised väljad on olemas: :values.',
    'multiple_of' => 'Välja :attribute väärtus peab olema arvu :value kordne.',
    'not_in' => 'Valitud atribuut on kehtetu.',
    'not_regex' => 'Välja :attribute vorming on vigane.',
    'numeric' => 'Väli :attribute peab olema arv.',
    'password' => [
        'letters' => 'Väli :attribute peab sisaldama vähemalt üht tähte.',
        'mixed' => 'Väli :attribute peab sisaldama vähemalt üht suur- ja üht väiketähte.',
        'numbers' => 'Väli :attribute peab sisaldama vähemalt üht numbrit.',
        'symbols' => 'Väli :attribute peab sisaldama vähemalt üht sümbolit.',
        'uncompromised' => 'Sisestatud :attribute on esinenud andmelekkes. Vali mõni muu :attribute.',
    ],
    'percent' => 'Amortisatsiooni alampiir peab olema 0 kuni 100, kui amortisatsiooni tüüp on protsent.',

    'present' => 'Atribuudiväli peab olema kohal.',
    'present_if' => 'Väli :attribute peab olema olemas, kui välja :other väärtus on :value.',
    'present_unless' => 'Väli :attribute peab olema olemas, välja arvatud juhul, kui välja :other väärtus on :value.',
    'present_with' => 'Väli :attribute peab olema olemas, kui :values on olemas.',
    'present_with_all' => 'Väli :attribute peab olema olemas, kui kõik järgmised väljad on olemas: :values.',
    'prohibited' => 'Väli :attribute on keelatud.',
    'prohibited_if' => 'Väli :attribute on keelatud, kui välja :other väärtus on :value.',
    'prohibited_unless' => 'Väli :attribute on keelatud, välja arvatud juhul, kui välja :other väärtus kuulub hulka :values.',
    'prohibits' => 'Kui väli :attribute on olemas, ei tohi väli :other olemas olla.',
    'regex' => 'Välja :attribute vorming on vigane.',
    'required' => 'Atribuudiväljandus on kohustuslik.',
    'required_array_keys' => 'Väli :attribute peab sisaldama järgmiste võtmetega kirjeid: :values.',
    'required_if' => 'Atribuudiväljastus on vajalik, kui: muu on: väärtus.',
    'required_if_accepted' => 'Väli :attribute on kohustuslik, kui :other on kinnitatud.',
    'required_if_declined' => 'Väli :attribute on kohustuslik, kui :other on tagasi lükatud.',
    'required_unless' => 'Atribuudiväljandus on vajalik, välja arvatud juhul, kui: muu on: väärtused.',
    'required_with' => 'Atribuudiväljund on vajalik, kui: väärtused on olemas.',
    'required_with_all' => 'Väli :attribute on kohustuslik, kui kõik järgmised väljad on olemas: :values.',
    'required_without' => 'Atribuudiväljund on vajalik, kui: väärtusi ei leidu.',
    'required_without_all' => 'Atribuudiväljund on vajalik, kui ükski: väärtusest pole olemas.',
    'same' => 'Välja :attribute väärtus peab vastama välja :other väärtusele.',
    'size' => [
        'array' => 'Väli :attribute peab sisaldama :size elementi.',
        'file' => 'Välja :attribute faili suurus peab olema :size kilobaiti.',
        'numeric' => 'Välja :attribute väärtus peab olema :size.',
        'string' => 'Väli :attribute peab sisaldama :size tähemärki.',
    ],
    'starts_with' => 'Väli :attribute peab algama ühega järgmistest: :values.',
    'string' => 'Atribuut peab olema string.',
    'two_column_unique_undeleted' => 'Väli :attribute peab olema tabelites :table1 ja :table2 ühine kordumatu väärtus. ',
    'unique_undeleted' => ':attribute peab olema ainulaadne.',
    'non_circular' => 'Väli :attribute ei tohi luua ringviidet.',
    'parent_must_be_top_level' => 'Valitud :attribute peab ise olema tipptaseme kirje. Lubatud on ainult üks hierarhiatase.',
    'must_have_no_children' => 'Sellel kirjel on juba alamkirjeid, seega ei saa sellele ülemkirjet määrata.',
    'parent_within_scope' => 'Valitud :attribute jääb väljapoole sulle määratud ulatust.',
    'not_array' => ':attribute ei tohi olla massiiv.',
    'disallow_same_pwd_as_user_fields' => 'Parool ei saa sisaldada kasutajanime.',
    'letters' => 'Parool peab sisaldama vähemalt ühte tähte.',
    'numbers' => 'Parool peab sisaldama vähemalt ühte numbrit.',
    'case_diff' => 'Parool peab sisaldama väike- ja suurtähti.',
    'symbols' => 'Parool peab sisaldama sümboleid.',
    'timezone' => 'Väli :attribute peab olema kehtiv ajavöönd.',
    'unique' => 'Atribuut: on juba võetud.',
    'uploaded' => 'Atribuut ei õnnestunud üles laadida.',
    'uppercase' => 'Väli :attribute peab olema suurtähtedega.',
    'url' => 'Väli :attribute peab olema kehtiv URL.',
    'external_url' => 'Väli :attribute peab olema kehtiv väline URL (http:// või https://), mis ei viita privaatsele ega kohalikule aadressile.',
    'asset_tag_pattern_placeholder' => 'Inventarinumbri muster peab sisaldama vähemalt üht kohatäitjat loendist :placeholders, muidu saaks iga sünkroonitud vahend sama numbri ja salvestataks ainult esimene.',
    'custom_http_extras_json' => [
        'not_json' => 'Kohandatud lisaväljad peavad olema kehtiv JSON. Parseri viga: :error',
        'not_array' => 'Kohandatud lisaväljad peavad olema JSON-massiiv (algama märgiga [ ja lõppema märgiga ]).',
        'entry_not_object' => 'Kohandatud lisaväljade kirje nr :index peab olema JSON-objekt väljadega key, label ja path.',
        'missing_field' => 'Kohandatud lisaväljade kirjel nr :index puudub kohustuslik väli „:field”.',
    ],
    'ulid' => 'Väli :attribute peab olema kehtiv ULID.',
    'uuid' => 'Väli :attribute peab olema kehtiv UUID.',
    'valid_css_color' => 'Väli :attribute peab olema kehtiv CSS-värv (hex, rgb, rgba, hsl või hsla).',
    'eloquent_model_class' => 'Väli :attribute peab olema Eloquenti mudeli täielikult kvalifitseeritud klassinimi.',
    'fmcs_company' => 'Väli :attribute on kohustuslik, sest täielik mitme ettevõtte tugi on lubatud ja ettevõtteta kirjed pole lubatud.',
    'fmcs_location' => 'Asukoht „:location” kuulub ettevõttele :location_company, mis ei vasta valitud ettevõttele.',
    'is_unique_across_company_and_location' => 'Väli :attribute peab olema valitud ettevõtte ja asukoha piires kordumatu.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'email_array' => 'Üks või mitu e-posti aadressi on kehtetu.',
    'checkboxes' => ':attribute sisaldab vigaseid valikuid.',
    'radio_buttons' => ':attribute on vigane.',
    'image_file_too_large' => 'Väli :attribute ei tohi olla suurem kui :max.',

    'custom' => [
        'alpha_space' => 'Atribuudi väli sisaldab märk, mis pole lubatud.',

        'hashed_pass' => 'Teie praegune parool on vale',
        'dumbpwd' => 'See parool on liiga levinud.',
        'statuslabel_type' => 'Peate valima kehtiva olekutüübi tüübi',
        'custom_field_not_found' => 'Seda välja ei paista olemas olevat. Kontrolli kohandatud väljade nimesid.',
        'custom_field_not_found_on_model' => 'See väli paistab olemas olevat, kuid pole selle vahendimudeli väljakomplektis saadaval.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format' => 'Väli :attribute peab olema kehtiv kuupäev vormingus YYYY-MM-DD',
        'last_audit_date.date_format' => 'Väli :attribute peab olema kehtiv kuupäev vormingus YYYY-MM-DD hh:mm:ss',
        'expiration_date.date_format' => 'Väli :attribute peab olema kehtiv kuupäev vormingus YYYY-MM-DD',
        'termination_date.date_format' => 'Väli :attribute peab olema kehtiv kuupäev vormingus YYYY-MM-DD',
        'expected_checkin.date_format' => 'Väli :attribute peab olema kehtiv kuupäev vormingus YYYY-MM-DD',
        'start_date.date_format' => 'Väli :attribute peab olema kehtiv kuupäev vormingus YYYY-MM-DD',
        'end_date.date_format' => 'Väli :attribute peab olema kehtiv kuupäev vormingus YYYY-MM-DD',
        'invalid_value_in_field' => 'Sellele väljale on sisestatud vigane väärtus',

        'ldap_username_field' => [
            'not_in' => '<code>sAMAccountName</code> (suur- ja väiketähtedega) tõenäoliselt ei tööta. Kasuta selle asemel <code>samaccountname</code> (väiketähtedega).',
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> pole tõenäoliselt kehtiv autentimisfilter. Tõenäoliselt vajad väärtust <code>uid=</code> '],
        'ldap_filter' => ['regex' => 'See väärtus ei peaks tõenäoliselt olema sulgudes.'],

    ],
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'serials.*' => 'Seerianumber',
        'asset_tags.*' => 'Vahendi silt',
    ],

    /*
    |--------------------------------------------------------------------------
    | Generic Validation Messages - we use these in the jquery validation where we don't have
    | access to the :attribute
    |--------------------------------------------------------------------------
    */

    'generic' => [
        'invalid_value_in_field' => 'Sellele väljale on sisestatud vigane väärtus',
        'required' => 'See väli on kohustuslik',
        'email' => 'Sisesta kehtiv e-posti aadress',
    ],

];
