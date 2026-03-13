<?php

return array(

    'deleted' => 'Deleted asset model',
    'does_not_exist' => 'Загвар байхгүй байна.',
    'no_association' => 'WARNING! The asset model for this item is invalid or missing!',
    'no_association_fix' => 'This will break things in weird and horrible ways. Edit this asset now to assign it a model.',
    'assoc_users'	 => 'Энэ загвар одоогоор нэг буюу хэд хэдэн хөрөнгөтэй холбоотой бөгөөд устгаж болохгүй. Хөрөнгө устгаж, дараа нь устгахыг оролдоно уу.',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'Загвар үүсгэгдсэнгүй, дахин оролдоно уу.',
        'success' => 'Загвар амжилттай болсон.',
        'duplicate_set' => 'Тухайн нэр, үйлдвэрлэгч болон загварын дугаар бүхий хөрөнгийн загвар аль хэдийн гарсан байна.',
    ),

    'update' => array(
        'error'   => 'Загвар шинэчлэгдсэнгүй, дахин оролдоно уу',
        'success' => 'Загвар амжилттай болсон.',
    ),

    'delete' => array(
        'confirm'   => 'Та энэ хөрөнгийн загварыг устгахыг хүсэж байна уу?',
        'error'   => 'Загварыг устгахад асуудал гарлаа. Дахин оролдоно уу.',
        'success' => 'Загвар амжилттай устгагдсан байна.'
    ),

    'restore' => array(
        'error'   		=> 'Загвар сэргээгээгүй, дахин оролдоно уу',
        'success' 		=> 'Загвар амжилттай болсон.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Ямар ч талбар өөрчлөгдсөнгүй тул шинэчлэгдээгүй байна.',
        'success' 		=> 'Model successfully updated. |:model_count models successfully updated.',
        'warn'          => 'You are about to update the properties of the following model:|You are about to edit the properties of the following :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Ямар ч загвар сонгогдоогүй тул юу ч устаагүй.',
        'success' 		    => 'Model deleted!|:success_count models deleted!',
        'success_partial' 	=> ':success_count ширхэг загвар устсан ба :fail_count ширхэг загвар одоо хүртэл хөрөнгөтэй холбоотой байгаа тул устаагүй.'
    ),

);
