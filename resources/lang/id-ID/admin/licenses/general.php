<?php

return array(
    'about_licenses_title'      => 'Tentang Lisensi',
    'about_licenses'            => 'Lisensi digunakan untuk melacak perangkat lunak. Mereka memiliki sejumlah kursi yang bisa diperiksa ke individu',
    'checkin'  					=> 'Pemberian kapasitas lisensi',
    'checkout_history'  		=> 'Riwayat Pemberian',
    'checkout'  				=> 'Pemberian kapasitas lisensi',
    'edit'  					=> 'Sunting lisensi',
    'filetype_info'				=> 'Jenis berkas diizinkan adalah png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, dan rar.',
    'clone'  					=> 'Klon lisensi',
    'history_for'  				=> 'Riwayat untuk ',
    'in_out'  					=> 'Masuk/Keluar',
    'info'  					=> 'Info Lisensi',
    'license_seats'  			=> 'Kapasitas Lisensi',
    'seat'  					=> 'Kapasitas',
    'seat_count'  				=> 'Slot :count',
    'seats'  					=> 'Kapasitas',
    'software_licenses'  		=> 'Lisensi Perangkat Lunak',
    'user'  					=> 'Pengguna',
    'view'  					=> 'Tampilkan Lisensi',
    'delete_disabled'           => 'Lisensi ini belum dapat dihapus karena beberapa slot lisensi masih dipinjam atau diambil.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Tandai Masuk Semua Slot Lisensi',
                'modal'             => 'Tindakan ini akan mengembalikan satu slot lisensi. | Tindakan ini akan mengembalikan semua :checkedout_seats_count slot lisensi untuk lisensi ini.',
                'enabled_tooltip'   => 'Kembalikan SEMUA slot lisensi untuk lisensi ini dari pengguna dan aset',
                'disabled_tooltip'  => 'Fitur ini dinonaktifkan karena tidak ada slot lisensi yang sedang dipinjam atau diambil',
                'disabled_tooltip_reassignable'  => 'Fitur ini dinonaktifkan karena Lisensi tidak dapat dialihkan atau diberikan ulang',
                'success'           => 'Lisensi berhasil dikembalikan! | Semua lisensi berhasil dikembalikan!',
                'log_msg'           => 'Dikembalikan melalui pengembalian lisensi massal di GUI lisensi',
            ],

            'checkout_all'              => [
                'button'                => 'Berikan Semua Slot Lisensi',
                'modal'                 => 'Tindakan ini akan meminjamkan atau memberikan satu slot lisensi kepada pengguna pertama yang tersedia. | Tindakan ini akan meminjamkan atau memberikan semua :available_seats_count slot lisensi kepada pengguna pertama yang tersedia. Seorang pengguna dianggap tersedia untuk slot lisensi ini jika mereka belum meminjam atau menerima lisensi ini, dan properti Otomatiskan Penugasan Lisensi diaktifkan pada akun pengguna mereka.',
                'enabled_tooltip'   => 'Pinjam atau Berikan SEMUA slot lisensi (atau sebanyak yang tersedia) kepada SEMUA pengguna',
                'disabled_tooltip'  => 'Fitur ini dinonaktifkan karena tidak ada slot lisensi yang tersedia saat ini',
                'success'           => 'Lisensi berhasil dipinjamkan atau diberikan! | :count lisensi berhasil dipinjamkan atau diberikan!',
                'error_no_seats'    => 'Tidak ada slot tersisa untuk lisensi ini.',
                'warn_not_enough_seats'    => ':count pengguna telah ditetapkan pada lisensi ini, tetapi kami kehabisan slot yang tersedia.',
                'warn_no_avail_users'    => 'Tidak ada yang perlu dilakukan. Tidak ada pengguna yang belum ditetapkan pada lisensi ini.',
                'log_msg'           => 'Dipinjamkan atau Diberikan melalui peminjaman atau pemberian lisensi massal di GUI lisensi',


            ],
    ],

    'below_threshold' => 'Hanya ada :remaining_count slot lisensi tersisa untuk lisensi ini dengan jumlah minimum :min_amt. Anda mungkin perlu mempertimbangkan untuk membeli lebih banyak slot lisensi.',
    'below_threshold_short' => 'Jumlah item ini di bawah kuantitas minimum yang dibutuhkan.',
);
