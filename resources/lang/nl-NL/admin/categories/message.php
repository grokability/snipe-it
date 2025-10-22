<?php

return array(

    'does_not_exist' => 'Categorie bestaat niet.',
    'assoc_models'	 => 'Deze categorie is momenteel gekoppeld met ten minste 1 model en kan niet verwijderd worden. Pas uw modellen aan zodat deze categorie niet langer gebruikt wordt en probeer opnieuw. ',
    'assoc_items'	 => 'Deze categorie is momenteel gekoppeld aan ten minste één: asset_type en kan niet worden verwijderd. Zorg dat uw: asset_type niet langer verwijst naar deze categorie en probeer het opnieuw. ',

    'create' => array(
        'error'   => 'Categorie is niet aangemaakt. Probeer het opnieuw.',
        'success' => 'Categorie is aangemaakt.'
    ),

    'update' => array(
        'error'   => 'Categorie is niet aangepast. Probeer het opnieuw.',
        'success' => 'Categorie is aangepast.',
        'cannot_change_category_type'   => 'U kunt het categorietype niet meer wijzigen nadat het is aangemaakt',
    ),

    'delete' => array(
        'confirm'                => 'Weet u zeker dat u deze categorie wilt verwijderen?',
        'error'                  => 'Er is een probleem opgetreden bij het verwijderen van deze categorie. Probeer het opnieuw.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
