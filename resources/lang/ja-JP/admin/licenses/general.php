<?php

return array(
    'about_licenses_title'      => 'ライセンスについて',
    'about_licenses'            => 'ライセンスはソフトウェアの追跡に使用されます。指定した数を個人にチェックアウトすることができます。',
    'checkin'  					=> 'ライセンスシートをチェックイン',
    'checkout_history'  		=> '履歴をチェックアウト',
    'checkout'  				=> 'ライセンスシートをチェックアウト',
    'edit'  					=> 'ライセンスを編集',
    'filetype_info'				=> '許可するファイルタイプ（png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, and rar）',
    'clone'  					=> 'ライセンスを複製',
    'history_for'  				=> '履歴 ',
    'in_out'  					=> 'In/Out',
    'info'  					=> 'ライセンス情報',
    'license_seats'  			=> 'ライセンスシート',
    'seat'  					=> 'シート',
    'seat_count'  				=> 'シート :count',
    'seats'  					=> 'シート数',
    'software_licenses'  		=> 'ソフトウェア・ライセンス',
    'user'  					=> '利用者',
    'view'  					=> 'ライセンスを表示',
    'delete_disabled'           => 'いくつかのシートがまだチェックアウトされているため、このライセンスはまだ削除できません。',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => '全てのシートをチェックイン',
                'modal'             => 'This action will checkin one seat. | This action will checkin all :checkedout_seats_count seats for this license.',
                'enabled_tooltip'   => 'ユーザーとアセットの両方から、このライセンスのすべてのシートをチェックインします',
                'disabled_tooltip'  => '現在チェックアウトされているシートがないため、これは無効です',
                'disabled_tooltip_reassignable'  => 'ライセンスが再割り当てできないため、これは無効です',
                'success'           => 'ライセンスのチェックインに成功しました! | すべてのライセンスは正常にチェックインされました!',
                'log_msg'           => 'Checked in via bulk license checkin in license GUI',
            ],

            'checkout_all'              => [
                'button'                => 'すべてのシートをチェックアウト',
                'modal'                 => 'このアクションは、最初の利用可能なユーザーに1つのシートをチェックアウトします。 | このアクションは、最初の利用可能なユーザーにすべての:available_seats_countシートをチェックアウトします。 ユーザーがこのライセンスをチェックアウトしていない場合、このシートで利用可能とみなされます。 そして、format@@0 プロパティがユーザーアカウントで有効になります。',
                'enabled_tooltip'   => 'すべてのユーザーにすべてのシートをチェックアウト(または利用可能な枚数)',
                'disabled_tooltip'  => '現在利用可能なシートがないため、これは無効です',
                'success'           => 'ライセンスは正常にチェックアウトされました！ | :count ライセンスは正常にチェックアウトされました！',
                'error_no_seats'    => 'このライセンスに残っているシートはありません。',
                'warn_not_enough_seats'    => ':count 人のユーザーにこのライセンスが割り当てられましたが、使用可能なライセンス・シートが切れてしまいました。',
                'warn_no_avail_users'    => '何もすることはありません。このライセンスが割り当てられていないユーザーはいません。',
                'log_msg'           => 'ライセンスGUIで一括ライセンスチェックアウトを行いました',


            ],
    ],

    'below_threshold' => ':min_amt の最小数量とこのライセンスのために残されている唯一の :remaining_count シートがあります. あなたはより多くの席を購入することを検討したいかもしれません.',
    'below_threshold_short' => 'この商品は最低限の必要量を下回っています。',
);
