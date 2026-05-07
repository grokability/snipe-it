<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    | The following language lines are used in the user permissions system.
    | Each permission has a 'name' and a 'note' that describes
    | the permission in detail.
    |
    | DO NOT edit the keys (left-hand side) of each permission as these are
    | used throughout the system for translations.
    |---------------------------------------------------------------------------
    */

    'superuser' => [
        'name' => 'Süper Kullanıcı',
        'note' => 'Yöneticinin tüm özelliklerine kullanıcının tam erişimi olup olmadığını belirler. Bu ayar, sistem genelindeki TÜM daha spesifik ve kısıtlayıcı izinleri geçersiz kılar.',
    ],
    'admin' => [
        'name' => 'Yönetici Erişimi',
        'note' => 'Kullanıcının Sistem Yönetici Ayarları HARİÇ sistemin çoğu bölümüne erişimi olup olmadığını belirler. Bu kullanıcılar kullanıcıları, konumları, kategorileri vb. yönetebilir, ancak etkinse Tam Çoklu Şirket Desteği tarafından SINIRLANDIRILIR.',
    ],

    'import' => [
        'name' => 'CSV İçe Aktarma',
        'note' => 'Bu, başka yerlerde kullanıcılar, varlıklar vb. erişimi engellenmiş olsa bile kullanıcıların içe aktarma yapmasına izin verir.',
    ],

    'reports' => [
        'name' => 'Raporlara Erişim',
        'note' => 'Kullanıcının uygulamanın Raporlar bölümüne erişimi olup olmadığını belirler.',
    ],

    'assets' => [
        'name' => 'Varlıklar',
        'note' => 'Uygulamanın Varlıklar bölümüne erişim sağlar.',
    ],

    'assetsview' => [
        'name' => 'Varlıkları Görüntüle',
    ],

    'assetscreate' => [
        'name' => 'Yeni Varlık Oluştur',
    ],

    'assetsedit' => [
        'name' => 'Varlıkları Düzenle',
    ],

    'assetsdelete' => [
        'name' => 'Varlıkları Sil',
    ],

    'assetscheckin' => [
        'name' => 'Teslim Al',
        'note' => 'Şu anda zimmette olan varlıkları tekrar envantere geri al.',
    ],

    'assetscheckout' => [
        'name' => 'Zimmete Ver',
        'note' => 'Envanterdeki varlıkları zimmete vererek atama yap.',
    ],

    'assetsaudit' => [
        'name' => 'Varlıkları Denetle',
        'note' => 'Kullanıcının bir varlığı fiziksel olarak sayılmış (envanteri yapılmış) olarak işaretlemesine izin verir.',
    ],

    'assetsviewrequestable' => [
        'name' => 'Talep Edilebilir Varlıkları Görüntüle',
        'note' => 'Kullanıcının talep edilebilir olarak işaretlenmiş varlıkları görüntülemesine izin verir.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'Şifrelenmiş Özel Alanları Görüntüle',
        'note' => 'Kullanıcının varlıklar üzerindeki şifrelenmiş özel alanları görüntülemesine ve düzenlemesine izin verir.',
    ],

    'accessories' => [
        'name' => 'Aksesuarlar',
        'note' => 'Uygulamanın Aksesuarlar bölümüne erişim sağlar.',
    ],

    'accessoriesview' => [
        'name' => 'Aksesuarları Görüntüle',
    ],
    'accessoriescreate' => [
        'name' => 'Yeni Aksesuar Oluştur',
    ],
    'accessoriesedit' => [
        'name' => 'Aksesuarları Düzenle',
    ],
    'accessoriesdelete' => [
        'name' => 'Aksesuarları Sil',
    ],
    'accessoriescheckout' => [
        'name' => 'Aksesuarları Zimmete Ver',
        'note' => 'Envanterdeki aksesuarları zimmete vererek atama yap.',
    ],
    'accessoriescheckin' => [
        'name' => 'Aksesuarları Teslim Al',
        'note' => 'Şu anda zimmette olan aksesuarları tekrar envantere geri al.',
    ],
    'accessoriesfiles' => [
        'name' => 'Aksesuar Dosyalarını Yönet',
        'note' => 'Kullanıcının aksesuarlarla ilişkili dosyaları yüklemesine, indirmesine ve silmesine izin verir. (Bu yalnızca görüntüleme yetkisi veya daha üstüyle anlamlıdır.)',
    ],

    'assetsfiles' => [
        'name' => 'Varlık Dosyalarını Yönet',
        'note' => 'Kullanıcının varlıklarla ilişkili dosyaları yüklemesine, indirmesine ve silmesine izin verir. (Bu yalnızca görüntüleme yetkisi veya daha üstüyle anlamlıdır.)',
    ],

    'usersfiles' => [
        'name' => 'Kullanıcı Dosyalarını Yönet',
        'note' => 'Kullanıcının kullanıcılarla ilişkili dosyaları yüklemesine, indirmesine ve silmesine izin verir. (Bu yalnızca görüntüleme yetkisi veya daha üstüyle anlamlıdır.)',
    ],

    'modelsfiles' => [
        'name' => 'Model Dosyalarını Yönet',
        'note' => 'Kullanıcının hem model görünümü hem de varlık görünümü ekranlarında, varlık modelleriyle ilişkili dosyaları yüklemesine, indirmesine ve silmesine olanak tanır. (Bu özellik yalnızca görüntüleme veya daha yüksek yetkilerle anlamlıdır.)',
    ],

    'departmentsfiles' => [
        'name' => 'Departman Dosyalarını Yönet',
        'note' => 'Kullanıcının departmanlarla ilişkili dosyaları yüklemesine, indirmesine ve silmesine olanak tanır. (Bu özellik yalnızca görüntüleme veya daha yüksek yetkilerle anlamlıdır.)',
    ],

    'suppliersfiles' => [
        'name' => 'Tedarikçi Dosyalarını Yönet',
        'note' => 'Kullanıcının tedarikçilerle ilişkili dosyaları yüklemesine, indirmesine ve silmesine olanak tanır. (Bu özellik yalnızca görüntüleme veya daha yüksek yetkilerle anlamlıdır.)',
    ],

    'locationsfiles' => [
        'name' => 'Lokasyon Dosyalarını Yönet',
        'note' => 'Kullanıcının lokasyonlarla ilişkili dosyaları yüklemesine, indirmesine ve silmesine olanak tanır. (Bu özellik yalnızca görüntüleme veya daha yüksek yetkilerle anlamlıdır.)',
    ],

    'companiesfiles' => [
        'name' => 'Şirket Dosyalarını Yönet',
        'note' => 'Kullanıcının şirketlerle ilişkili dosyaları yüklemesine, indirmesine ve silmesine olanak tanır. (Bu özellik yalnızca görüntüleme veya daha yüksek yetkilerle anlamlıdır.)',
    ],

    'consumablesfiles' => [
        'name' => 'Sarf Malzemesi Dosyalarını Yönet',
        'note' => 'Kullanıcının sarf malzemeleriyle ilişkili dosyaları yüklemesine, indirmesine ve silmesine olanak tanır. (Bu özellik yalnızca görüntüleme veya daha yüksek yetkilerle anlamlıdır.)',
    ],

    'consumables' => [
        'name' => 'Sarf Malzemeleri',
        'note' => 'Uygulamanın Sarf Malzemeleri bölümüne erişim sağlar.',
    ],
    'consumablesview' => [
        'name' => 'Sarf Malzemelerini Görüntüle',
    ],
    'consumablescreate' => [
        'name' => 'Yeni Sarf Malzemesi Oluştur',
    ],
    'consumablesedit' => [
        'name' => 'Sarf Malzemelerini Düzenle',
    ],
    'consumablesdelete' => [
        'name' => 'Sarf Malzemelerini Sil',
    ],
    'consumablescheckout' => [
        'name' => 'Sarf Malzemelerini Ödünç Ver (Zimmetle)',
        'note' => 'Envanterdeki sarf malzemelerini ödünç vererek (zimmetleyerek) ata.',
    ],

    'licenses' => [
        'name' => 'Lisanslar',
        'note' => 'Uygulamanın Lisanslar bölümüne erişim sağlar.',
    ],
    'licensesview' => [
        'name' => 'Lisansları Görüntüle',
    ],
    'licensescreate' => [
        'name' => 'Yeni Lisanslar Oluştur',
    ],
    'licensesedit' => [
        'name' => 'Lisansları Düzenle',
    ],
    'licensesdelete' => [
        'name' => 'Lisansları Sil',
    ],
    'licensescheckout' => [
        'name' => 'Lisansları Ata',
        'note' => 'Kullanıcının lisansları varlıklara veya kullanıcılara atamasına izin verir.',
    ],
    'licensescheckin' => [
        'name' => 'Lisans Atamasını Kaldır',
        'note' => 'Kullanıcının lisansların varlıklar veya kullanıcılar üzerindeki atamasını kaldırmasına izin verir.',
    ],
    'licensesfiles' => [
        'name' => 'Lisans Dosyalarını Yönet',
        'note' => 'Kullanıcının lisanslarla ilişkili dosyaları yüklemesine, indirmesine ve silmesine izin verir.',
    ],
    'componentsfiles' => [
        'name' => 'Bileşen Dosyalarını Yönet',
        'note' => 'Kullanıcının bileşenlerle ilişkili dosyaları yüklemesine, indirmesine ve silmesine izin verir.',
    ],

    'licenseskeys' => [
        'name' => 'Lisans Anahtarlarını Yönet',
        'note' => 'Kullanıcının lisanslarla ilişkili ürün anahtarlarını görüntülemesine izin verir.',
    ],
    'components' => [
        'name' => 'Bileşenler',
        'note' => 'Uygulamanın Bileşenler bölümüne erişim sağlar.',
    ],
    'componentsview' => [
        'name' => 'Bileşenleri Görüntüle',
    ],
    'componentscreate' => [
        'name' => 'Yeni Bileşenler Oluştur',
    ],
    'componentsedit' => [
        'name' => 'Bileşenleri Düzenle',
    ],
    'componentsdelete' => [
        'name' => 'Bileşenleri Sil',
    ],

    'componentscheckout' => [
        'name' => 'Bileşenleri Çıkış Yap',
        'note' => 'Envanterdeki bileşenleri çıkış yaparak atar.',
    ],
    'componentscheckin' => [
        'name' => 'Bileşenleri Geri Al',
        'note' => 'Şu anda çıkış yapılmış olan bileşenleri tekrar envantere alır.',
    ],
    'kits' => [
        'name' => 'Ön Tanımlı Setler',
        'note' => 'Uygulamanın Ön Tanımlı Kitler bölümüne erişim sağlar.',
    ],
    'kitsview' => [
        'name' => 'Ön Tanımlı Kitleri Görüntüle',
    ],
    'kitscreate' => [
        'name' => 'Yeni Ön Tanımlı Kitler Oluştur',
    ],
    'kitsedit' => [
        'name' => 'Ön Tanımlı Kitleri Düzenle',
    ],
    'kitsdelete' => [
        'name' => 'Ön Tanımlı Kitleri Sil',
    ],
    'users' => [
        'name' => 'Kullanıcılar',
        'note' => 'Uygulamanın Kullanıcılar bölümüne erişim sağlar.',
    ],
    'usersview' => [
        'name' => 'Kullanıcıları Görüntüle',
    ],
    'userscreate' => [
        'name' => 'Yeni Kullanıcılar Oluştur',
    ],
    'usersedit' => [
        'name' => 'Kullanıcıları Düzenle',
    ],
    'usersdelete' => [
        'name' => 'Kullanıcıları Sil',
    ],
    'models' => [
        'name' => 'Modeller',
        'note' => 'Uygulamanın Modeller bölümüne erişim sağlar.',
    ],
    'modelsview' => [
        'name' => 'Modelleri görüntüle',
    ],

    'modelscreate' => [
        'name' => 'Yeni Modeller Oluştur',
    ],
    'modelsedit' => [
        'name' => 'Modelleri Düzenle',
    ],
    'modelsdelete' => [
        'name' => 'Modelleri Sil',
    ],
    'categories' => [
        'name' => 'Kategoriler',
        'note' => 'Uygulamanın Kategoriler bölümüne erişim sağlar.',
    ],
    'categoriesview' => [
        'name' => 'Kategorileri Görüntüle',
    ],
    'categoriescreate' => [
        'name' => 'Yeni Kategoriler Oluştur',
    ],
    'categoriesedit' => [
        'name' => 'Kategorileri Düzenle',
    ],
    'categoriesdelete' => [
        'name' => 'Kategorileri Sil',
    ],
    'departments' => [
        'name' => 'Bölümler',
        'note' => 'Uygulamanın Departmanlar bölümüne erişim sağlar.',
    ],
    'departmentsview' => [
        'name' => 'Departmanları Görüntüle',
    ],
    'departmentscreate' => [
        'name' => 'Yeni Departmanlar Oluştur',
    ],
    'departmentsedit' => [
        'name' => 'Departmanları Düzenle',
    ],
    'departmentsdelete' => [
        'name' => 'Departmanları Sil',
    ],
    'locations' => [
        'name' => 'Konumlar',
        'note' => 'Uygulamanın Lokasyonlar bölümüne erişim sağlar.',
    ],
    'locationsview' => [
        'name' => 'Lokasyonları Görüntüle',
    ],
    'locationscreate' => [
        'name' => 'Yeni Lokasyonlar Oluştur',
    ],
    'locationsedit' => [
        'name' => 'Lokasyonları Düzenle',
    ],
    'locationsdelete' => [
        'name' => 'Lokasyonları Sil',
    ],
    'status-labels' => [
        'name' => 'Durum Etiketleri',
        'note' => 'Varlıklar tarafından kullanılan uygulamanın Durum Etiketleri bölümüne erişim sağlar.',
    ],
    'statuslabelsview' => [
        'name' => 'Durum Etiketlerini Görüntüle',
    ],
    'statuslabelscreate' => [
        'name' => 'Yeni Durum Etiketleri Oluştur',
    ],
    'statuslabelsedit' => [
        'name' => 'Durum Etiketlerini Düzenle',
    ],
    'statuslabelsdelete' => [
        'name' => 'Durum Etiketlerini Sil',
    ],
    'custom-fields' => [
        'name' => 'Özel alanlar',
        'note' => 'Varlıklar tarafından kullanılan uygulamanın Özel Alanlar bölümüne erişim sağlar.',
    ],
    'customfieldsview' => [
        'name' => 'Özel Alanları Görüntüle',
    ],
    'customfieldscreate' => [
        'name' => 'Yeni Özel Alanlar Oluştur',
    ],
    'customfieldsedit' => [
        'name' => 'Özel Alanları Düzenle',
    ],
    'customfieldsdelete' => [
        'name' => 'Özel Alanları Sil',
    ],
    'suppliers' => [
        'name' => 'Tedarikçiler',
        'note' => 'Uygulamanın Tedarikçiler bölümüne erişim sağlar.',
    ],
    'suppliersview' => [
        'name' => 'Tedarikçileri Görüntüle',
    ],
    'supplierscreate' => [
        'name' => 'Yeni Tedarikçiler Oluştur',
    ],
    'suppliersedit' => [
        'name' => 'Tedarikçileri Düzenle',
    ],
    'suppliersdelete' => [
        'name' => 'Tedarikçileri Sil',
    ],
    'manufacturers' => [
        'name' => 'Üreticiler',
        'note' => 'Uygulamanın Üreticiler bölümüne erişim sağlar.',
    ],
    'manufacturersview' => [
        'name' => 'Üreticileri Görüntüle',
    ],
    'manufacturerscreate' => [
        'name' => 'Yeni Üreticiler Oluştur',
    ],
    'manufacturersedit' => [
        'name' => 'Üreticileri Düzenle',
    ],
    'manufacturersdelete' => [
        'name' => 'Üreticileri Sil',
    ],
    'companies' => [
        'name' => 'Şirketler',
        'note' => 'Uygulamanın Şirketler bölümüne erişim sağlar.',
    ],
    'companiesview' => [
        'name' => 'Şirketleri Görüntüle',
    ],
    'companiescreate' => [
        'name' => 'Yeni Şirketler Oluştur',
    ],
    'companiesedit' => [
        'name' => 'Şirketleri Düzenle',
    ],
    'companiesdelete' => [
        'name' => 'Şirketleri Sil',
    ],
    'user-self-accounts' => [
        'name' => 'Kullanıcı Kendi Hesapları',
        'note' => 'Yönetici olmayan kullanıcılara kendi kullanıcı hesaplarının belirli yönlerini yönetme yetkisi verir.',
    ],
    'selftwo-factor' => [
        'name' => 'İki Faktörlü Kimlik Doğrulamayı Yönet',
        'note' => 'Kullanıcıların kendi hesapları için iki faktörlü kimlik doğrulamayı etkinleştirmesine, devre dışı bırakmasına ve yönetmesine olanak tanır.',
    ],
    'selfapi' => [
        'name' => 'API Tokenlarını Yönet',
        'note' => 'Kullanıcıların kendi API tokenlarını oluşturmasına, görüntülemesine ve iptal etmesine olanak tanır. Kullanıcı tokenları, onları oluşturan kullanıcı ile aynı yetkilere sahip olacaktır.',
    ],
    'selfedit-location' => [
        'name' => 'Lokasyonu Düzenle',
        'note' => 'Kullanıcıların kendi kullanıcı hesaplarıyla ilişkilendirilmiş lokasyonu düzenlemesine olanak tanır.',
    ],
    'selfcheckout-assets' => [
        'name' => 'Varlıkları Kendine Teslim Alma',
        'note' => 'Kullanıcıların yönetici müdahalesi olmadan varlıkları kendilerine teslim almasına olanak tanır.',
    ],
    'selfview-purchase-cost' => [
        'name' => 'Satın Alma Maliyetini Görüntüle',
        'note' => 'Kullanıcıların hesap görünümlerinde öğelerin satın alma maliyetini görüntülemesine olanak tanır.',
    ],

    'depreciations' => [
        'name' => 'Amortisman Yönetimi',
        'note' => 'Kullanıcıların varlık amortisman detaylarını yönetmesine ve görüntülemesine olanak tanır.',
    ],
    'depreciationsview' => [
        'name' => 'Amortisman Detaylarını Görüntüle',
    ],
    'depreciationsedit' => [
        'name' => 'Amortisman Ayarlarını Düzenle',
    ],
    'depreciationsdelete' => [
        'name' => 'Amortisman Kayıtlarını Sil',
    ],
    'depreciationscreate' => [
        'name' => 'Amortisman Kayıtları Oluştur',
    ],

    'grant_all' => ':area için tüm izinleri ver',
    'deny_all' => ':area için tüm izinleri reddet',
    'inherit_all' => ':area için tüm izinleri izin gruplarından devral',
    'grant' => ':area için izin ver',
    'deny' => ':area için izni reddet',
    'inherit' => ':area için izni izin gruplarından devral',
    'use_groups' => 'Daha kolay yönetim için tek tek izin atamak yerine İzin Grupları kullanmanızı şiddetle öneririz.',

];
