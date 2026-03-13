<?php

return array(

    'does_not_exist' => 'Байршил байхгүй байна.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Энэ байршил нь одоогоор нэгээс доошгүй активтай холбоотой бөгөөд устгах боломжгүй байна. Энэ байршлыг лавлагаа болгохоо болихын тулд өөрийн хөрөнгийг шинэчлээд дахин оролдоно уу.',
    'assoc_child_loc'	 => 'Энэ байршил нь одоогоор хамгийн багадаа нэг хүүхдийн байрлалын эцэг эх бөгөөд устгах боломжгүй байна. Энэ байршлыг лавшруулахгүй болгохын тулд байршлаа шинэчлээд дахин оролдоно уу.',
    'assigned_assets' => 'Assigned Assets',
    'current_location' => 'Current Location',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Байршалт үүсээгүй байна, дахин оролдоно уу.',
        'success' => 'Байршил амжилттай болсон.'
    ),

    'update' => array(
        'error'   => 'Байршил шинэчлэгдсэнгүй, дахин оролдоно уу',
        'success' => 'Байршил амжилттай шинэчлэгдсэн.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'Та энэ байршлыг устгахыг хүсч байна уу?',
        'error'   => 'Байршил устгах асуудал гарлаа. Дахин оролдоно уу.',
        'success' => 'Байршил амжилттай устгагдсан байна.'
    )

);
