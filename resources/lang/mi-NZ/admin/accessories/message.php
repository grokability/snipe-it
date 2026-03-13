<?php

return array(

    'does_not_exist' => 'The accessory [:id] does not exist.',
    'not_found' => 'That accessory was not found.',
    'assoc_users'	 => 'Ko tenei taputapu i tenei wa: ko nga nama kua taatatia ki nga kaiwhakamahi. Titiro koa ki nga taputapu me te ngana ano.',

    'create' => array(
        'error'   => 'Kaore i hangaia te taputapu, ngana ano ngana.',
        'success' => 'I angitu te waihanga i te taputapu.'
    ),

    'update' => array(
        'error'   => 'Kāore i te whakahouhia te taputapu, tēnā whakamātau anō',
        'success' => 'Kua pai te whakahoutanga o te taputapu.'
    ),

    'delete' => array(
        'confirm'   => 'Kei te hiahia koe ki te muku i tenei taputapu?',
        'error'   => 'He raruraru kei te whakakore i te taputapu. Tena ngana ano.',
        'success' => 'Kua whakakorehia te taputapu.'
    ),

     'checkout' => array(
        'error'   		=> 'Kaore ano kia uruhia te uru, ka ngana ano',
        'success' 		=> 'He pai te tirotiro i te Accessory.',
        'unavailable'   => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'He muhu te kaiwhakamahi. Tena ngana ano.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Kaore i te takiuruhia te Accessory, tēnā whakamātau anō',
        'success' 		=> 'Whakaratohia te Accessory i te angitu.',
        'user_does_not_exist' => 'He muhu te kaiwhakamahi. Tena ngana ano.'
    )


);
