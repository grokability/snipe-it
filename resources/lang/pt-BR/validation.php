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

    'accepted' => 'O campo :attribute deve ser aceito.',
    'accepted_if' => 'O campo :attribute deve ser aceito quando :other for :value.',
    'active_url' => 'O campo :attribute deve ser uma URL válida.',
    'after' => 'O campo :attribute deve ser uma data posterior a :date.',
    'after_or_equal' => 'O campo :attribute deve ser uma data posterior ou igual a :date.',
    'alpha' => 'O campo :attribute deve conter apenas letras.',
    'alpha_dash' => 'O campo :attribute deve conter apenas letras, números, traços e sublinhados.',
    'alpha_num' => 'O campo :attribute deve conter apenas letras e números.',
    'array' => 'O campo :attribute deve ser um vetor.',
    'ascii' => 'O campo :attribute deve conter apenas caracteres e símbolos alfanuméricos de um único byte.',
    'before' => 'O campo :attribute deve ser uma data anterior a :date.',
    'before_or_equal' => 'O campo :attribute deve ser uma data anterior ou igual a :date.',
    'between' => [
        'array' => 'O campo :attribute deve ter entre :min e :max itens.',
        'file' => 'O campo :attribute deve ter entre :min e :max kilobytes.',
        'numeric' => 'O campo :attribute deve estar entre :min e :max.',
        'string' => 'O campo :attribute deve ter entre :min e :max caracteres.',
    ],
    'valid_regex' => 'A expressão regular é inválida.',
    'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
    'can' => 'O campo :attribute contém um valor não autorizado.',
    'confirmed' => 'A confirmação do campo :attribute não corresponde.',
    'contains' => 'O campo "atributo" não contém um valor obrigatório.',
    'current_password' => 'A senha esta incorreta.',
    'date' => 'O campo :attribute deve ser uma data válida.',
    'date_equals' => 'O campo :attribute deve ser uma data igual a :date.',
    'date_format' => 'O campo :attribute deve ser igual ao formato :format.',
    'decimal' => 'O campo :attribute deve ter :decimal casas decimais.',
    'declined' => 'O campo :attribute deve ser recusado.',
    'declined_if' => 'O campo :attribute deve ser recusado quando :other é :value.',
    'different' => 'O campo :attribute e :other devem ser diferentes.',
    'digits' => 'O :attribute deve ter :digits dígitos.',
    'digits_between' => 'O campo :attribute deve ter entre :min e :max dígitos.',
    'dimensions' => 'O campo :attribute tem dimensões de imagem inválidas.',
    'distinct' => 'O :attribute tem um valor duplicado.',
    'doesnt_end_with' => 'O campo :attribute não deve terminar com um dos seguintes: :values.',
    'doesnt_start_with' => 'O campo :attribute não deve começar com um dos seguintes: :values.',
    'email' => 'O campo :attribute deve ser um email válido.',
    'ends_with' => 'O campo :attribute deve terminar com um dos seguintes: :values.',
    'enum' => 'O :attribute selecionado é inválido.',
    'exists' => 'O :attribute selecionado é inválido.',
    'extensions' => 'O campo :attribute deve ter uma das seguintes extensões: :values.',
    'file' => 'O campo :attribute deve ser um arquivo.',
    'filled' => 'O :attribute deve ter um valor.',
    'gt' => [
        'array' => 'O :attribute não pode ter mais que :max items.',
        'file' => 'O campo :attribute deve ser maior que :value kilobytes.',
        'numeric' => 'O :attribute deve ser maior que :value.',
        'string' => 'O campo :attribute deve ser maior que :value caracteres.',
    ],
    'gte' => [
        'array' => 'O campo :attribute deve ter :value items ou mais.',
        'file' => 'O campo :attribute deve ser maior ou igual a :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser maior ou igual a :value.',
        'string' => 'O campo :attribute deve ser maior ou igual a :value caracteres.',
    ],
    'hex_color' => 'O campo :attribute deve ser uma cor hexadecimal válida.',
    'image' => 'O campo :attribute deve ser uma imagem.',
    'import_field_empty' => 'O valor para :fieldname não pode ser nulo.',
    'in' => 'O :attribute selecionado é inválido.',
    'in_array' => 'O campo :attribute deve existir em :other.',
    'integer' => 'The :attribute field must be an integer.',
    'ip' => 'O campo :attribute deve ser um endereço IP válido.',
    'ipv4' => 'O campo :attribute deve ser um endereço IPv4 válido.',
    'ipv6' => 'O campo :attribute deve ser um endereço IPv6 válido.',
    'json' => 'The :attribute field must be a valid JSON string.',
    'list' => 'O campo :attribute deve ser uma lista.',
    'lowercase' => 'The :attribute field must be lowercase.',
    'lt' => [
        'array' => 'O campo :attribute deve ter menos de :value itens.',
        'file' => 'O campo :attribute deve ser menor que :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser menor que :value.',
        'string' => 'O campo :attribute deve ser menor que :value caracteres.',
    ],
    'lte' => [
        'array' => 'O campo :attribute não deve ter mais de :value itens.',
        'file' => 'O campo :attribute deve ser menor ou igual a :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser menor ou igual a :value.',
        'string' => 'O campo :attribute deve ser menor ou igual a :value caracteres.',
    ],
    'mac_address' => 'O campo :attribute deve ser um endereço MAC válido.',
    'max' => [
        'array' => 'O campo :attribute não deve ter mais de :max itens.',
        'file' => 'O campo :attribute não deve ser maior que :max kilobytes.',
        'numeric' => 'O campo :attribute não deve ser maior que :max.',
        'string' => 'O campo :attribute não deve ser maior que :max caracteres.',
    ],
    'max_digits' => 'O campo :attribute não deve ter mais de :max dígitos.',
    'mimes' => 'O campo :attribute deve ser um arquivo de tipo: :values.',
    'mimetypes' => 'O campo :attribute deve ser um arquivo de tipo: :values.',
    'min' => [
        'array' => 'O campo :attribute deve ter pelo menos :min itens.',
        'file' => 'O campo :attribute deve ser pelo menos :min kilobytes.',
        'numeric' => 'O campo :attribute deve ser pelo menos :min.',
        'string' => 'O campo :attribute deve ser pelo menos :min caracteres.',
    ],
    'min_digits' => 'O campo :attribute deve ter pelo menos :min dígitos.',
    'missing' => 'O campo :attribute deve estar ausente.',
    'missing_if' => 'O campo :attribute deve estar ausente quando :other for :value.',
    'missing_unless' => 'O campo :attribute deve estar ausente a menos que :other seja :value.',
    'missing_with' => 'O campo :attribute deve estar ausente quando :values estiver presente.',
    'missing_with_all' => 'O campo :attribute deve estar ausente quando :values estiverem presente.',
    'multiple_of' => 'O campo :attribute deve ser um múltiplo de :value.',
    'not_in' => 'O :attribute selecionado é inválido.',
    'not_regex' => 'O campo :attribute deve ser um formato válido.',
    'numeric' => 'O campo :attribute deve ser um número.',
    'password' => [
        'letters' => 'O campo :attribute deve conter pelo menos uma letra.',
        'mixed' => 'O campo :attribute deve conter pelo menos uma letra maiúscula e uma letra minúscula.',
        'numbers' => 'O campo :attribute deve conter pelo menos um número.',
        'symbols' => 'O campo :attribute deve conter pelo menos um símbolo.',
        'uncompromised' => 'O :attribute fornecido apareceu em um vazamento de dados. Por favor, escolha um :attribute diferente.',
    ],
    'percent' => 'A depreciação mínima deve estar entre 0 e 100 quando o tipo de depreciação for percentual.',

    'present' => 'O campo:attribute deve estar presente.',
    'present_if' => 'O campo :attribute deve estar presente quando :other for :value.',
    'present_unless' => 'O campo :attribute deve estar presente a menos que :other seja :value.',
    'present_with' => 'O campo :attribute deve estar presente quando :values estiver presente.',
    'present_with_all' => 'O campo :attribute deve estar presente quando :values estiverem presente.',
    'prohibited' => 'O campo :attribute está proibido.',
    'prohibited_if' => 'O campo :attribute está proibido quando :other for :value.',
    'prohibited_unless' => 'O campo :attribute está proibido a menos que :other seja :value.',
    'prohibits' => 'O campo :attribute proíbe :other de estar presente.',
    'regex' => 'O campo :attribute deve ser um formato válido.',
    'required' => 'O campo de :attribute é requerido.',
    'required_array_keys' => 'O campo :attribute deve conter entradas para: :values.',
    'required_if' => 'O campo de :attribute é requerido quando :other é :value.',
    'required_if_accepted' => 'O campo :attribute é requerido quando :other é aceito.',
    'required_if_declined' => 'O campo :attribute é requerido quando :other é recusado.',
    'required_unless' => 'O campo:attribute é obrigatório a não ser que: :other estiver em : :values.',
    'required_with' => 'O campo de :attribute é requerido quando :values está presente.',
    'required_with_all' => 'O campo :attribute é requerido quando :values estiverem presente.',
    'required_without' => 'O campo de :attribute é requerido quando :values não está presente.',
    'required_without_all' => 'O campo :attribute é obrigatório nenhum dos :values está presente.',
    'same' => 'O campo :attribute deve corresponder a :other.',
    'size' => [
        'array' => 'O campo :attribute deve conter :size itens.',
        'file' => 'O campo :attribute deve ser :size kilobytes.',
        'numeric' => 'O campo :attribute deve ser :size.',
        'string' => 'O campo :attribute deve ser :size caracteres.',
    ],
    'starts_with' => 'O campo :attribute deve começar com um dos seguintes: :values.',
    'string' => 'O :attribute deve ser string.',
    'two_column_unique_undeleted' => 'O :attribute deve ser único entre :table1 e :table2. ',
    'unique_undeleted' => 'O :attribute deve ser único.',
    'non_circular' => 'O :attribute não pode criar uma referência circular.',
    'not_array' => ':attribute não pode ser um array.',
    'disallow_same_pwd_as_user_fields' => 'A senha não pode ser igual ao nome de usuário.',
    'letters' => 'A senha deve conter pelo menos uma letra.',
    'numbers' => 'A senha deve conter pelo menos um número.',
    'case_diff' => 'A senha deve conter letras maiúsculas e minusculas.',
    'symbols' => 'A senha deve conter símbolos.',
    'timezone' => 'O campo :attribute deve ser um fuso horário válido.',
    'unique' => 'O :attribute já foi tomado.',
    'uploaded' => 'O :attribute falhou no upload.',
    'uppercase' => 'O campo :attribute deve ser maiúsculo.',
    'url' => 'O campo :attribute deve ser uma URL válida.',
    'ulid' => 'O campo :attribute deve ser um ULID válido.',
    'uuid' => 'O campo :attribute deve ser um UUID válido.',
    'fmcs_location' => 'Full multiple company support and location scoping está habilitado nas Configurações de Administração, e o local e a empresa selecionados não são compatíveis.',
    'is_unique_across_company_and_location' => 'O :attribute deve ser único dentro da empresa e local selecionados.',

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

    'email_array' => 'Um ou mais e-mails são inválidos.',
    'checkboxes' => ':attribute contém opções inválidas.',
    'radio_buttons' => ':attribute é inválido.',

    'custom' => [
        'alpha_space' => 'O campo :attribute contém um caractere que não é permitido.',

        'hashed_pass' => 'Sua senha atual está incorreta',
        'dumbpwd' => 'Essa senha é muito comum.',
        'statuslabel_type' => 'Você deve selecionar um tipo de etiqueta de situação válida',
        'custom_field_not_found' => 'Este campo parece não existir, verifique novamente os nomes dos seus campos personalizados.',
        'custom_field_not_found_on_model' => 'Este campo parece existir, mas não está disponível no fieldset deste Modelo de Ativo.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format' => 'O :attribute deve ser uma data válida em formato DD-MM-YYYY',
        'last_audit_date.date_format' => 'O :attribute deve ser uma data válida em formato DD-MM-YYYY hh:mm:ss',
        'expiration_date.date_format' => 'O :attribute deve ser uma data válida em formato DD-MM-YYYY',
        'termination_date.date_format' => 'O :attribute deve ser uma data válida em formato DD-MM-YYYY',
        'expected_checkin.date_format' => 'O :attribute deve ser uma data válida em formato DD-MM-YYYY',
        'start_date.date_format' => 'O :attribute deve ser uma data válida em formato DD-MM-YYYY',
        'end_date.date_format' => 'O :attribute deve ser uma data válida em formato DD-MM-YYYY',
        'invalid_value_in_field' => 'Valor inválido incluído neste campo',

        'ldap_username_field' => [
            'not_in' => '<code>sAMAccountName</code> (misto case) não funcionará. Você deve usar <code>samaccountname</code> (minúsculo) em vez disso.',
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> is probably not a valid auth filter. You probably want <code>uid=</code> '],
        'ldap_filter' => ['regex' => 'Este valor provavelmente não deve ser envolvido em parênteses.'],

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
        'serials.*' => 'Número de Série',
        'asset_tags.*' => 'Marcação do Ativo',
    ],

    /*
    |--------------------------------------------------------------------------
    | Generic Validation Messages - we use these in the jquery validation where we don't have
    | access to the :attribute
    |--------------------------------------------------------------------------
    */

    'generic' => [
        'invalid_value_in_field' => 'Valor inválido incluído neste campo',
        'required' => 'Este campo é obrigatório',
        'email' => 'Por favor, insira um endereço de e-mail válido',
    ],

];
