<?php

return array(
    'about_licenses_title'      => '关于许可证',
    'about_licenses'            => '软件许可证用于监控软件的使用情况。它们拥有一定数量的席位，可以分配给个人使用',
    'checkin'  					=> '归还许可证席位',
    'checkout_history'  		=> '签出历史记录',
    'checkout'  				=> '签出许可证数量',
    'edit'  					=> '编辑许可证',
    'filetype_info'				=> '允许的文件类型有： png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar',
    'clone'  					=> '克隆许可证',
    'history_for'  				=> '历史记录',
    'in_out'  					=> '进/出',
    'info'  					=> '授权信息',
    'license_seats'  			=> '授权数量',
    'seat'  					=> '席位',
    'seat_count'  				=> '席位 :count',
    'seats'  					=> '席位',
    'software_licenses'  		=> '软件许可证',
    'user'  					=> '用户',
    'view'  					=> '查看许可证',
    'delete_disabled'           => '此许可证不能被删除，因为仍有席位被签出。',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => '归还所有席位',
                'modal'             => '此操作将归还一个席位。| 此操作将归还这个许可证的所有共 :checkedout_seas_count 个席位。',
                'enabled_tooltip'   => '从用户和资产中归还此许可证的所有席位',
                'disabled_tooltip'  => '此功能已禁用，因为当前没有签出的席位',
                'disabled_tooltip_reassignable'  => '此功能已禁用，因为许可证不可重新分配。',
                'success'           => '许可证归还成功！| 所有许可证都已归还成功！',
                'log_msg'           => '通过许可证GUI中的“批量归还许可证”进行归还',
            ],

            'checkout_all'              => [
                'button'                => '签出所有席位',
                'modal'                 => '此操作将签出一个席位给第一个可用的用户。| 此操作将签出所有共 :available _seas_count 个席位给第一个可用的用户。 如果此许可证尚未签出给用户，并且在该用户账户上启用了“自动分配许可证”属性，则认定该用户可以使用此席位。',
                'enabled_tooltip'   => '向所有用户签出所有（或尽可能多）的席位',
                'disabled_tooltip'  => '此功能已禁用，因为当前没有可用的席位',
                'success'           => '许可证成功签出！ | :count 个许可证成功签出！',
                'error_no_seats'    => '此许可证已无剩余席位。',
                'warn_not_enough_seats'    => ':count 个用户被分配了此许可证，但我们没有可用的许可证席位。',
                'warn_no_avail_users'    => '没有什么要做的。没有尚未分配此许可证的用户。',
                'log_msg'           => '在许可证GUI中通过“批量许可证签出”签出',


            ],
    ],

    'below_threshold' => '此许可证仅剩:remaining_count个席位，并且最小数量为:min_amt。你可能需要考虑购买更多席位。',
    'below_threshold_short' => '该项低于最低要求数量。',
);
