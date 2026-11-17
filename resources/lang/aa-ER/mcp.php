<?php

return [

    // -----------------------------------------------------------------
    // Generic errors
    // -----------------------------------------------------------------
    'not_authenticated' => 'crwdns18321:0crwdne18321:0',
    'unauthorized' => 'crwdns18323:0crwdne18323:0',
    'scope_denied' => 'crwdns18325:0crwdne18325:0',
    'scope_handshake_required' => 'crwdns18327:0crwdne18327:0',
    'scope_session_description' => 'crwdns18329:0crwdne18329:0',
    'scope_read_description' => 'crwdns18331:0crwdne18331:0',
    'scope_write_description' => 'crwdns18333:0crwdne18333:0',
    'scope_delete_description' => 'crwdns18335:0crwdne18335:0',
    'scope_checkout_description' => 'crwdns18337:0crwdne18337:0',
    'scope_checkin_description' => 'crwdns18339:0crwdne18339:0',
    'scope_superadmin_description' => 'crwdns18341:0crwdne18341:0',
    'id_or_name_required' => 'crwdns18343:0crwdne18343:0',
    'id_username_or_email_required' => 'crwdns18345:0crwdne18345:0',
    'provide_user_or_asset' => 'crwdns18347:0crwdne18347:0',

    // -----------------------------------------------------------------
    // "Not found" errors
    // -----------------------------------------------------------------
    'object_not_found' => 'crwdns18349:0crwdne18349:0',
    'asset_not_found' => 'crwdns18351:0crwdne18351:0',
    'user_not_found' => 'crwdns18353:0crwdne18353:0',
    'accessory_not_found' => 'crwdns18355:0crwdne18355:0',
    'accessory_checkout_not_found' => 'crwdns18357:0crwdne18357:0',
    'component_not_found' => 'crwdns18359:0crwdne18359:0',
    'component_checkout_not_found' => 'crwdns18361:0crwdne18361:0',
    'consumable_not_found' => 'crwdns18363:0crwdne18363:0',
    'license_not_found' => 'crwdns18365:0crwdne18365:0',
    'license_seat_not_found' => 'crwdns18367:0crwdne18367:0',
    'department_not_found' => 'crwdns18369:0crwdne18369:0',
    'company_not_found' => 'crwdns18371:0crwdne18371:0',
    'category_not_found' => 'crwdns18373:0crwdne18373:0',
    'manufacturer_not_found' => 'crwdns18375:0crwdne18375:0',
    'supplier_not_found' => 'crwdns18377:0crwdne18377:0',
    'status_label_not_found' => 'crwdns18379:0crwdne18379:0',
    'location_not_found' => 'crwdns18381:0crwdne18381:0',
    'asset_model_not_found' => 'crwdns18383:0crwdne18383:0',
    'depreciation_not_found' => 'crwdns18385:0crwdne18385:0',
    'group_not_found' => 'crwdns18387:0crwdne18387:0',
    'checkout_target_not_found' => 'crwdns18389:0crwdne18389:0',

    // -----------------------------------------------------------------
    // Business rule errors
    // -----------------------------------------------------------------
    'asset_not_available' => 'crwdns18391:0crwdne18391:0',
    'asset_not_checked_out' => 'crwdns18393:0crwdne18393:0',
    'asset_not_deleted' => 'crwdns18395:0crwdne18395:0',
    'user_not_deleted' => 'crwdns18397:0crwdne18397:0',
    'user_cannot_delete_self' => 'crwdns18399:0crwdne18399:0',
    'user_has_items' => 'crwdns18401:0crwdne18401:0',
    'cannot_edit_auth_fields' => 'crwdns18403:0crwdne18403:0',
    'username_taken' => 'crwdns18405:0crwdne18405:0',
    'password_reset_sent' => 'crwdns18407:0crwdne18407:0',
    'password_reset_user_inactive' => 'crwdns18409:0crwdne18409:0',
    'password_reset_no_email' => 'crwdns18411:0crwdne18411:0',
    'password_reset_ldap_user' => 'crwdns18413:0crwdne18413:0',
    'password_reset_send_failed' => 'crwdns18415:0crwdne18415:0',
    'invalid_permissions_format' => 'crwdns18417:0crwdne18417:0',
    'invalid_permission_key' => 'crwdns18419:0crwdne18419:0',
    'invalid_permission_value' => 'crwdns18421:0crwdne18421:0',
    'superadmin_required_for_groups' => 'crwdns18423:0crwdne18423:0',
    'no_available_seats' => 'crwdns18425:0crwdne18425:0',
    'no_free_seat' => 'crwdns18427:0crwdne18427:0',
    'seat_not_checked_out' => 'crwdns18429:0crwdne18429:0',
    'no_units_available' => 'crwdns18431:0crwdne18431:0',
    'no_units_remaining' => 'crwdns18433:0crwdne18433:0',
    'accessory_has_checkouts' => 'crwdns18435:0crwdne18435:0',
    'component_has_checkouts' => 'crwdns18437:0crwdne18437:0',
    'consumable_has_checkouts' => 'crwdns18439:0crwdne18439:0',
    'license_has_seats_assigned' => 'crwdns18441:0crwdne18441:0',
    'department_has_users' => 'crwdns18443:0crwdne18443:0',
    'location_has_child_locations' => 'crwdns18445:0crwdne18445:0',
    'location_has_users' => 'crwdns18447:0crwdne18447:0',
    'status_label_has_assets' => 'crwdns18449:0crwdne18449:0',
    'model_has_assets' => 'crwdns18451:0crwdne18451:0',
    'bulk_confirm_count_required' => 'crwdns18453:0crwdne18453:0',
    'bulk_confirm_count_mismatch' => 'crwdns18455:0crwdne18455:0',
    'bulk_batch_has_undeletable' => 'crwdns18457:0crwdne18457:0',
    'bulk_dry_run_summary' => 'crwdns18459:0crwdne18459:0',
    'bulk_delete_completed' => 'crwdns18461:0crwdne18461:0',
    'bulk_delete_failed' => 'crwdns18463:0crwdne18463:0',

    // -----------------------------------------------------------------
    // Operation failures
    // -----------------------------------------------------------------
    'create_failed' => 'crwdns18465:0crwdne18465:0',
    'update_failed' => 'crwdns18467:0crwdne18467:0',
    'delete_failed' => 'crwdns18469:0crwdne18469:0',
    'delete_failed_error' => 'crwdns18471:0crwdne18471:0',
    'checkin_failed' => 'crwdns18473:0crwdne18473:0',
    'checkin_failed_error' => 'crwdns18475:0crwdne18475:0',
    'checkout_failed' => 'crwdns18477:0crwdne18477:0',
    'audit_failed' => 'crwdns18479:0crwdne18479:0',
    'note_save_failed' => 'crwdns18481:0crwdne18481:0',

    // -----------------------------------------------------------------
    // Asset messages
    // -----------------------------------------------------------------
    'asset_found' => 'crwdns18483:0crwdne18483:0',
    'asset_created' => 'crwdns18485:0crwdne18485:0',
    'asset_updated' => 'crwdns18487:0crwdne18487:0',
    'asset_deleted' => 'crwdns18489:0crwdne18489:0',
    'asset_restored' => 'crwdns18491:0crwdne18491:0',
    'asset_checked_out' => 'crwdns18493:0crwdne18493:0',
    'asset_checked_in' => 'crwdns18495:0crwdne18495:0',
    'asset_audited' => 'crwdns18497:0crwdne18497:0',
    'note_added_to_asset' => 'crwdns18499:0crwdne18499:0',
    'note_added_successfully' => 'crwdns18501:0crwdne18501:0',

    // -----------------------------------------------------------------
    // User messages
    // -----------------------------------------------------------------
    'current_user' => 'crwdns18503:0crwdne18503:0',
    'user_found' => 'crwdns18505:0crwdne18505:0',
    'user_created' => 'crwdns18507:0crwdne18507:0',
    'user_updated' => 'crwdns18509:0crwdne18509:0',
    'user_deleted' => 'crwdns18511:0crwdne18511:0',
    'user_restored' => 'crwdns18513:0crwdne18513:0',
    'two_factor_reset' => 'crwdns18515:0crwdne18515:0',
    'user_assets_found' => 'crwdns18517:0crwdne18517:0',
    'user_permissions_found' => 'crwdns18519:0crwdne18519:0',
    'profile_updated' => 'crwdns18521:0crwdne18521:0',

    // -----------------------------------------------------------------
    // Accessory messages
    // -----------------------------------------------------------------
    'accessory_created' => 'crwdns18523:0crwdne18523:0',
    'accessory_updated' => 'crwdns18525:0crwdne18525:0',
    'accessory_deleted' => 'crwdns18527:0crwdne18527:0',
    'accessory_checked_out' => 'crwdns18529:0crwdne18529:0',
    'accessory_checked_in' => 'crwdns18531:0crwdne18531:0',

    // -----------------------------------------------------------------
    // Component messages
    // -----------------------------------------------------------------
    'component_created' => 'crwdns18533:0crwdne18533:0',
    'component_updated' => 'crwdns18535:0crwdne18535:0',
    'component_deleted' => 'crwdns18537:0crwdne18537:0',
    'component_checked_out' => 'crwdns18539:0crwdne18539:0',
    'component_checked_in' => 'crwdns18541:0crwdne18541:0',

    // -----------------------------------------------------------------
    // Consumable messages
    // -----------------------------------------------------------------
    'consumable_found' => 'crwdns18543:0crwdne18543:0',
    'consumable_created' => 'crwdns18545:0crwdne18545:0',
    'consumable_updated' => 'crwdns18547:0crwdne18547:0',
    'consumable_deleted' => 'crwdns18549:0crwdne18549:0',
    'consumable_checked_out' => 'crwdns18551:0crwdne18551:0',

    // -----------------------------------------------------------------
    // License messages
    // -----------------------------------------------------------------
    'license_found' => 'crwdns18553:0crwdne18553:0',
    'license_created' => 'crwdns18555:0crwdne18555:0',
    'license_updated' => 'crwdns18557:0crwdne18557:0',
    'license_deleted' => 'crwdns18559:0crwdne18559:0',
    'license_seat_checked_out_user' => 'crwdns18561:0crwdne18561:0',
    'license_seat_checked_out_asset' => 'crwdns18563:0crwdne18563:0',
    'license_seat_checked_in' => 'crwdns18565:0crwdne18565:0',

    // -----------------------------------------------------------------
    // Department messages
    // -----------------------------------------------------------------
    'department_created' => 'crwdns18567:0crwdne18567:0',
    'department_updated' => 'crwdns18569:0crwdne18569:0',
    'department_deleted' => 'crwdns18571:0crwdne18571:0',

    // -----------------------------------------------------------------
    // Company messages
    // -----------------------------------------------------------------
    'company_found' => 'crwdns18573:0crwdne18573:0',
    'company_created' => 'crwdns18575:0crwdne18575:0',
    'company_updated' => 'crwdns18577:0crwdne18577:0',
    'company_deleted' => 'crwdns18579:0crwdne18579:0',

    // -----------------------------------------------------------------
    // Category messages
    // -----------------------------------------------------------------
    'category_found' => 'crwdns18581:0crwdne18581:0',
    'category_created' => 'crwdns18583:0crwdne18583:0',
    'category_updated' => 'crwdns18585:0crwdne18585:0',
    'category_deleted' => 'crwdns18587:0crwdne18587:0',
    'category_delete_failed' => 'crwdns18589:0crwdne18589:0',

    // -----------------------------------------------------------------
    // Manufacturer messages
    // -----------------------------------------------------------------
    'manufacturer_found' => 'crwdns18591:0crwdne18591:0',
    'manufacturer_created' => 'crwdns18593:0crwdne18593:0',
    'manufacturer_updated' => 'crwdns18595:0crwdne18595:0',
    'manufacturer_deleted' => 'crwdns18597:0crwdne18597:0',

    // -----------------------------------------------------------------
    // Supplier messages
    // -----------------------------------------------------------------
    'supplier_found' => 'crwdns18599:0crwdne18599:0',
    'supplier_created' => 'crwdns18601:0crwdne18601:0',
    'supplier_updated' => 'crwdns18603:0crwdne18603:0',
    'supplier_deleted' => 'crwdns18605:0crwdne18605:0',

    // -----------------------------------------------------------------
    // Status label messages
    // -----------------------------------------------------------------
    'status_label_found' => 'crwdns18607:0crwdne18607:0',
    'status_label_created' => 'crwdns18609:0crwdne18609:0',
    'status_label_updated' => 'crwdns18611:0crwdne18611:0',
    'status_label_deleted' => 'crwdns18613:0crwdne18613:0',

    // -----------------------------------------------------------------
    // Location messages
    // -----------------------------------------------------------------
    'location_found' => 'crwdns18615:0crwdne18615:0',
    'location_created' => 'crwdns18617:0crwdne18617:0',
    'location_updated' => 'crwdns18619:0crwdne18619:0',
    'location_deleted' => 'crwdns18621:0crwdne18621:0',

    // -----------------------------------------------------------------
    // Asset model messages
    // -----------------------------------------------------------------
    'asset_model_found' => 'crwdns18623:0crwdne18623:0',
    'asset_model_created' => 'crwdns18625:0crwdne18625:0',
    'asset_model_updated' => 'crwdns18627:0crwdne18627:0',
    'asset_model_deleted' => 'crwdns18629:0crwdne18629:0',

    // -----------------------------------------------------------------
    // Depreciation messages
    // -----------------------------------------------------------------
    'depreciation_found' => 'crwdns18631:0crwdne18631:0',
    'depreciation_created' => 'crwdns18633:0crwdne18633:0',
    'depreciation_updated' => 'crwdns18635:0crwdne18635:0',
    'depreciation_deleted' => 'crwdns18637:0crwdne18637:0',

    // -----------------------------------------------------------------
    // Group messages
    // -----------------------------------------------------------------
    'group_found' => 'crwdns18639:0crwdne18639:0',
    'group_created' => 'crwdns18641:0crwdne18641:0',
    'group_updated' => 'crwdns18643:0crwdne18643:0',
    'group_deleted' => 'crwdns18645:0crwdne18645:0',

    // -----------------------------------------------------------------
    // Maintenance messages
    // -----------------------------------------------------------------
    'maintenance_created' => 'crwdns18647:0crwdne18647:0',

    // -----------------------------------------------------------------
    // List messages
    // -----------------------------------------------------------------
    'list_assets' => 'crwdns18649:0crwdne18649:0',
    'list_users' => 'crwdns18651:0crwdne18651:0',
    'list_consumables' => 'crwdns18653:0crwdne18653:0',
    'list_licenses' => 'crwdns18655:0crwdne18655:0',
    'list_companies' => 'crwdns18657:0crwdne18657:0',
    'list_categories' => 'crwdns18659:0crwdne18659:0',
    'list_manufacturers' => 'crwdns18661:0crwdne18661:0',
    'list_suppliers' => 'crwdns18663:0crwdne18663:0',
    'list_status_labels' => 'crwdns18665:0crwdne18665:0',
    'list_locations' => 'crwdns18667:0crwdne18667:0',
    'list_asset_models' => 'crwdns18669:0crwdne18669:0',
    'list_depreciations' => 'crwdns18671:0crwdne18671:0',
    'list_groups' => 'crwdns18673:0crwdne18673:0',
    'list_maintenances' => 'crwdns18675:0crwdne18675:0',
    'list_activity' => 'crwdns18677:0crwdne18677:0',
    'list_accessories' => 'crwdns18679:0crwdne18679:0',
    'list_maintenance_types' => 'crwdns18681:0crwdne18681:0',
    'maintenance_type_created' => 'crwdns18683:0crwdne18683:0',
    'find_similar_models_identifier_required' => 'crwdns18685:0crwdne18685:0',
    'find_similar_models_summary' => 'crwdns18687:0crwdne18687:0',

    // -----------------------------------------------------------------
    // Upload tools
    // -----------------------------------------------------------------
    'id_or_asset_tag_required' => 'crwdns18689:0crwdne18689:0',
    'upload_image_decode_failed' => 'crwdns18691:0crwdne18691:0',
    'upload_image_tempfile_failed' => 'crwdns18693:0crwdne18693:0',
    'upload_image_save_failed' => 'crwdns18695:0crwdne18695:0',
    'upload_image_success' => 'crwdns18697:0crwdne18697:0',
    'list_asset_notes' => 'crwdns18699:0crwdne18699:0',
    'list_uploads' => 'crwdns18701:0crwdne18701:0',
    'list_history' => 'crwdns18703:0crwdne18703:0',

    // -----------------------------------------------------------------
    // Offboard user tool
    // -----------------------------------------------------------------
    'offboard_identifier_required' => 'crwdns18705:0crwdne18705:0',
    'offboard_ambiguous_user' => 'crwdns18707:0crwdne18707:0',
    'offboard_dry_run_message' => 'crwdns18709:0crwdne18709:0',
    'offboard_dry_run_summary' => 'crwdns18711:0:username:crwdne18711:0',
    'offboard_completed_message' => 'crwdns18713:0crwdne18713:0',
    'offboard_completed_summary' => 'crwdns18715:0:username:crwdne18715:0',
    'offboard_failed' => 'crwdns18717:0crwdne18717:0',
    'offboard_cross_company_blocked' => 'crwdns18719:0crwdne18719:0',
    'fk_not_found' => 'crwdns18721:0crwdne18721:0',
    'fk_ambiguous' => 'crwdns18723:0crwdne18723:0',

    // -----------------------------------------------------------------
    // Transfer user assets tool
    // -----------------------------------------------------------------
    'transfer_identifier_required' => 'crwdns18725:0crwdne18725:0',
    'transfer_user_not_found' => 'crwdns18727:0crwdne18727:0',
    'transfer_ambiguous_user' => 'crwdns18729:0crwdne18729:0',
    'transfer_same_user' => 'crwdns18731:0crwdne18731:0',
    'transfer_dry_run_message' => 'crwdns18733:0crwdne18733:0',
    'transfer_dry_run_summary' => 'crwdns18735:0:to:crwdne18735:0',
    'transfer_completed_message' => 'crwdns18737:0crwdne18737:0',
    'transfer_completed_summary' => 'crwdns18739:0:to:crwdne18739:0',
    'transfer_failed' => 'crwdns18741:0crwdne18741:0',

];
