<?php

return array(
    'about_licenses_title'      => '關於授權',
    'about_licenses'            => '授權是用來追踨可供借出的軟體數量。',
    'checkin'  					=> '繳回授權數量',
    'checkout_history'  		=> '借出歷史記錄',
    'checkout'  				=> '借出授權數量',
    'edit'  					=> '編輯授權',
    'filetype_info'				=> '允許檔案類型：png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar。',
    'clone'  					=> '複製授權',
    'history_for'  				=> '歷史記錄',
    'in_out'  					=> '進/出',
    'info'  					=> '授權訊息',
    'license_seats'  			=> '授權數量',
    'seat'  					=> '數量',
    'seat_count'  				=> '授權數 :count',
    'seats'  					=> '數量',
    'software_licenses'  		=> '軟體授權',
    'user'  					=> '使用者
',
    'view'  					=> '檢視授權',
    'delete_disabled'           => '此授權尚不能刪除，因為一些名額仍在借出中。',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => '歸還所有名額',
                'modal'             => '此操作將歸還一個授權數量。| 此操作將歸還全部 :checkedout_seats_count 個授權數量。',
                'enabled_tooltip'   => '從使用者和資產中歸還此授權的所有名額',
                'disabled_tooltip'  => '此功能已停用，因為目前沒有名額在借出中',
                'disabled_tooltip_reassignable'  => 'This is disabled because the License is not reassignable',
                'success'           => '授權成功歸還！| 所有授權都成功歸還！',
                'log_msg'           => 'Checked in via bulk license checkin in license GUI',
            ],

            'checkout_all'              => [
                'button'                => '借出所有座位',
                'modal'                 => '此操作將借出一個名額給第一個可用的使用者。| 此操作將借出所有 :available_seats_count 名額給第一個可用的使用者。如果他們尚未借出此授權，並且他們的使用者帳戶上已啟用自動分配授權屬性，則將使用者視為此名額的可用。',
                'enabled_tooltip'   => '借出所有名額（或者可用的名額）給所有使用者',
                'disabled_tooltip'  => '此功能已停用，因為目前沒有名額可用',
                'success'           => '執照成功借出! | :count 份執照成功被借出!',
                'error_no_seats'    => '此授權已無剩餘名額。',
                'warn_not_enough_seats'    => ':count 位使用者被分配了此授權，但我們的授權名額已用完。',
                'warn_no_avail_users'    => '無需任何操作. 所有使用者皆已分配此執照.',
                'log_msg'           => '透過圖形化介面的執照大量借出功能借出執照.',


            ],
    ],

    'below_threshold' => 'There are only :remaining_count seats left for this license with a minimum quantity of :min_amt. You may want to consider purchasing more seats.',
    'below_threshold_short' => 'This item is below the minimum required quantity.',
);
