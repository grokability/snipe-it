<?php

return array(

    'does_not_exist' => 'Vöruflokkur er ekki til.',
    'assoc_models'	 => 'Þessi vöruflokkur er tengdur við eitt módel og ekki er hægt að eyða honum. Vinsamlegast uppfærðu módelin, þannig að þau tengist ekki þessum vöruflokk og reyndu aftur. ',
    'assoc_items'	 => 'Þessi vöruflokkur er tengdur við eina tegund búnaðar og ekki er hægt að eyða honum. Vinsamlegast uppfærðu tegund búnaðar (asset_type), þannig að þau tengist ekki þessum vöruflokk og reyndu aftur. ',

    'create' => array(
        'error'   => 'Vöruflokkur var ekki skráður, vinsamlegast reyndu aftur.',
        'success' => 'Vöruflokkur var stofnaður.'
    ),

    'update' => array(
        'error'   => 'Vöruflokkur var ekki uppfærður, vinsamlegast reyndu aftur',
        'success' => 'Vöruflokkur var uppfærður.',
        'cannot_change_category_type'   => 'You cannot change the category type once it has been created',
    ),

    'delete' => array(
        'confirm'                => 'Ertu viss um að þú viljir eyða vöruflokk?',
        'error'                  => 'Það var villa við að eyða vöruflokk. Vinsamlegast reyndu aftur.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
