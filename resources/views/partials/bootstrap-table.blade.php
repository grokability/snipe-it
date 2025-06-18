@push('css')
    <link rel="stylesheet" href="{{ url(mix('css/dist/bootstrap-table.css')) }}">
@endpush

@push('js')

<script src="{{ url(mix('js/dist/bootstrap-table.js')) }}"></script>
<script src="{{ url(mix('js/dist/bootstrap-table-locale-all.min.js')) }}"></script>

<!-- load english again here, even though it's in the all.js file, because if BS table doesn't have the translation, it otherwise defaults to chinese. See https://bootstrap-table.com/docs/api/table-options/#locale -->
<script src="{{ url(mix('js/dist/bootstrap-table-en-US.min.js')) }}"></script>

<script nonce="{{ csrf_token() }}">
    $(function () {


        var blockedFields = "searchable,sortable,switchable,title,visible,formatter,class".split(",");

        var keyBlocked = function(key) {
            for(var j in blockedFields) {
                if (key === blockedFields[j]) {
                    return true;
                }
            }
            return false;
        }

        $('.snipe-table').bootstrapTable('destroy').each(function () {

            data_export_options = $(this).attr('data-export-options');
            export_options = data_export_options ? JSON.parse(data_export_options) : {};
            export_options['htmlContent'] = false; // this is already the default; but let's be explicit about it
            export_options['jspdf'] = {
                "orientation": "l",
                "autotable": {
                        "styles": {
                            overflow: 'linebreak'
                        },
                        tableWidth: 'wrap'
                }
            };
            // tableWidth: 'wrap',
            // the following callback method is necessary to prevent XSS vulnerabilities
            // (this is taken from Bootstrap Tables's default wrapper around jQuery Table Export)
            export_options['onCellHtmlData'] = function (cell, rowIndex, colIndex, htmlData) {
                if (cell.is('th')) {
                    return cell.find('.th-inner').text()
                }
                return htmlData
            }
            $(this).bootstrapTable({
                classes: 'table table-responsive table-no-bordered',
                ajaxOptions: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                },
                // reorderableColumns: true,
                stickyHeader: true,
                stickyHeaderOffsetLeft: parseInt($('body').css('padding-left'), 10),
                stickyHeaderOffsetRight: parseInt($('body').css('padding-right'), 10),
                undefinedText: '',
                iconsPrefix: 'fa',
                cookieStorage: '{{ config('session.bs_table_storage') }}',
                cookie: true,
                cookieExpire: '2y',
                showColumnsToggleAll: true,
                minimumCountColumns: 2,
                mobileResponsive: true,
                maintainSelected: true,
                trimOnSearch: false,
                showSearchClearButton: true,
                addrbar: {{ (config('session.bs_table_addrbar') == 'true') ? 'true' : 'false'}}, // deeplink search phrases, sorting, etc
                paginationFirstText: "{{ trans('general.first') }}",
                paginationLastText: "{{ trans('general.last') }}",
                paginationPreText: "{{ trans('general.previous') }}",
                paginationNextText: "{{ trans('general.next') }}",
                pageList: ['10', '20', '30', '50', '100', '150', '200'{!! ((config('app.max_results') > 200) ? ",'500'" : '') !!}{!! ((config('app.max_results') > 500) ? ",'".config('app.max_results')."'" : '') !!}],
                pageSize: {{  (($snipeSettings->per_page!='') && ($snipeSettings->per_page > 0)) ? $snipeSettings->per_page : 20 }},
                paginationVAlign: 'both',
                queryParams: function (params) {
                    var newParams = {};
                    for (var i in params) {
                        if (!keyBlocked(i)) { // only send the field if it's not in blockedFields
                            newParams[i] = params[i];
                        }
                    }
                    return newParams;
                },
                formatLoadingMessage: function () {
                    return '<h2><x-icon type="spinner" /> {{ trans('general.loading') }} </h2>';
                },
                icons: {
                    advancedSearchIcon: 'fas fa-search-plus',
                    paginationSwitchDown: 'fa-caret-square-o-down',
                    paginationSwitchUp: 'fa-caret-square-o-up',
                    fullscreen: 'fa-expand',
                    columns: 'fa-columns',
                    refresh: 'fas fa-sync-alt',
                    export: 'fa-download',
                    clearSearch: 'fa-times'
                },
                locale: '{{ app()->getLocale() }}',
                exportOptions: export_options,
                exportTypes: ['xlsx', 'excel', 'csv', 'pdf', 'json', 'xml', 'txt', 'sql', 'doc'],
                onLoadSuccess: function () { // possible 'fixme'? this might be for contents, not for headers?
                    $('[data-tooltip="true"]').tooltip(); // Needed to attach tooltips after ajax call
                },
                onPostHeader: function () {
                    var lookup = {};
                    var lookup_initialized = false;
                    var ths = $('th');
                    ths.each(function (index, element) {
                        th = $(element);
                        //only populate the lookup table once; don't need to keep doing it.
                        if (!lookup_initialized) {
                            // th -> tr -> thead -> table
                            var table = th.parent().parent().parent()
                            var column_data = table.data('columns')
                            for (var column in column_data) {
                                lookup[column_data[column].field] = column_data[column].titleTooltip;
                            }
                            lookup_initialized = true
                        }

                        field = th.data('field'); // find fieldname this column refers to
                        title = lookup[field];
                        if (title) {
                            th.attr('data-toggle', 'tooltip');
                            th.attr('data-placement', 'top');
                            // th.attr('title', title) //this causes 'double-titles' which looks gross
                            th.tooltip({container: 'body', title: title});
                        }
                    })
                },
                formatNoMatches: function () {
                    return '{{ trans('table.no_matching_records') }}';
                }

            });

        });
    });





    function dateRowCheckStyle(value) {
        if ((value.days_to_next_audit) && (value.days_to_next_audit < {{ $snipeSettings->audit_warning_days ?: 0 }})) {
            return { classes : "danger" }
        }
        return {};
    }


    // These methods dynamically add/remove hidden input values in the bulk actions form
    $('.snipe-table').on('check.bs.table .btSelectItem', function (row, $element) {
        var buttonName =  $(this).data('bulk-button-id');
        var tableId =  $(this).data('id-table');

        $(buttonName).removeAttr('disabled');
        $(buttonName).after('<input id="' + tableId + '_checkbox_' + $element.id + '" type="hidden" name="ids[]" value="' + $element.id + '">');
    });

    $('.snipe-table').on('check-all.bs.table', function (event, rowsAfter) {

        var buttonName =  $(this).data('bulk-button-id');
        $(buttonName).removeAttr('disabled');
        var tableId =  $(this).data('id-table');

        for (var i in rowsAfter) {
            // Do not select things that were already selected
            if($('#'+ tableId + '_checkbox_' + rowsAfter[i].id).length == 0) {
                $(buttonName).after('<input id="' + tableId + '_checkbox_' + rowsAfter[i].id + '" type="hidden" name="ids[]" value="' + rowsAfter[i].id + '">');
            }
        }
    });


    $('.snipe-table').on('uncheck.bs.table .btSelectItem', function (row, $element) {
        var tableId =  $(this).data('id-table');
        $( "#" + tableId + "_checkbox_" + $element.id).remove();
    });


    // Handle whether the edit button should be disabled
    $('.snipe-table').on('uncheck.bs.table', function () {
        var buttonName =  $(this).data('bulk-button-id');

        if ($(this).bootstrapTable('getSelections').length == 0) {

            $(buttonName).attr('disabled', 'disabled');
        }
    });

    $('.snipe-table').on('uncheck-all.bs.table', function (event, rowsAfter, rowsBefore) {

        var buttonName =  $(this).data('bulk-button-id');
        $(buttonName).attr('disabled', 'disabled');
        var tableId =  $(this).data('id-table');

        for (var i in rowsBefore) {
            $('#' + tableId + "_checkbox_" + rowsBefore[i].id).remove();
        }

    });

    // Initialize sort-order for bulk actions (label-generation) for snipe-tables
    $('.snipe-table').each(function (i, table) {
        table_cookie_segment = $(table).data('cookie-id-table');
        sort = '';
        order = '';
        cookies = document.cookie.split(";");
        for(i in cookies) {
            cookiedef = cookies[i].split("=", 2);
            cookiedef[0] = cookiedef[0].trim();
            if (cookiedef[0] == table_cookie_segment + ".bs.table.sortOrder") {
                order = cookiedef[1];
            }
            if (cookiedef[0] == table_cookie_segment + ".bs.table.sortName") {
                sort = cookiedef[1];
            }
        }
        if (sort && order) {
            domnode = $($(this).data('bulk-form-id')).get(0);
            if ( domnode && domnode.elements && domnode.elements.sort ) {
                domnode.elements.sort.value = sort;
                domnode.elements.order.value = order;
            }
        }
    });

    // If sort order changes, update the sort-order for bulk-actions (for label-generation)
    $('.snipe-table').on('sort.bs.table', function (event, name, order) {
       domnode = $($(this).data('bulk-form-id')).get(0);
       // make safe in case there isn't a bulk-form-id, or it's not found, or has no 'sort' element
       if ( domnode && domnode.elements && domnode.elements.sort ) {
           domnode.elements.sort.value = name;
           domnode.elements.order.value = order;
       }
    });



    // This specifies the footer columns that should have special styles associated
    // (usually numbers)
    window.footerStyle = column => ({
        remaining: {
            classes: 'text-padding-number-footer-cell'
        },
        qty: {
            classes: 'text-padding-number-footer-cell',
        },
        purchase_cost: {
            classes: 'text-padding-number-footer-cell'
        },
        checkouts_count: {
            classes: 'text-padding-number-footer-cell'
        },
        assets_count: {
            classes: 'text-padding-number-footer-cell'
        },
        seats: {
            classes: 'text-padding-number-footer-cell'
        },
        free_seats_count: {
            classes: 'text-padding-number-footer-cell'
        },
    }[column.field]);




    // This only works for model index pages because it uses the row's model ID
    function genericRowLinkFormatter(destination) {
        return function (value,row) {
            if (value) {
                return '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '">' + value + '</a>';
            }
        };
    }

    // Use this when we're introspecting into a column object and need to link
    function genericColumnObjLinkFormatter(destination) {
        return function (value,row) {
            if ((value) && (value.status_meta)) {

                var text_color;
                var icon_style;
                var text_help;
                var status_meta = {
                  'deployed': '{{ strtolower(trans('general.deployed')) }}',
                  'deployable': '{{ strtolower(trans('admin/hardware/general.deployable')) }}',
                  'archived': '{{ strtolower(trans('general.archived')) }}',
                  'pending': '{{ strtolower(trans('general.pending')) }}'
                }

                switch (value.status_meta) {
                    case 'deployed':
                        text_color = 'blue';
                        icon_style = 'fa-circle';
                        text_help = '<label class="label label-default">{{ trans('general.deployed') }}</label>';
                    break;
                    case 'deployable':
                        text_color = 'green';
                        icon_style = 'fa-circle';
                        text_help = '';
                    break;
                    case 'pending':
                        text_color = 'orange';
                        icon_style = 'fa-circle';
                        text_help = '';
                        break;
                    default:
                        text_color = 'red';
                        icon_style = 'fa-times';
                        text_help = '';
                }

                return '<nobr><a href="{{ config('app.url') }}/' + destination + '/' + value.id + '" data-tooltip="true" title="'+ status_meta[value.status_meta] + '"> <i class="fa ' + icon_style + ' text-' + text_color + '"></i> ' + value.name + ' ' + text_help + ' </a> </nobr>';
            } else if ((value) && (value.name)) {

                // Add some overrides for any funny urls we have
                var dest = destination;
                var dpolymorphicItemFormatterest = '';
                if (destination=='fieldsets') {
                    var dpolymorphicItemFormatterest = 'fields/';
                }

                return '<nobr><a href="{{ config('app.url') }}/' + dpolymorphicItemFormatterest + dest + '/' + value.id + '">' + value.name + '</a></span>';
            }
        };
    }


    function licenseKeyFormatter(value, row) {
        return '<code class="single-line"><span class="js-copy-link" data-clipboard-target=".js-copy-key-' + row.id + '" aria-hidden="true" data-tooltip="true" data-placement="top" title="{{ trans('general.copy_to_clipboard') }}"><span class="js-copy-key-' + row.id + '">' + value + '</span></span></code>';
    }



    function hardwareAuditFormatter(value, row) {
        return '<a href="{{ config('app.url') }}/hardware/' + row.id + '/audit" class="actions btn btn-sm btn-primary" data-tooltip="true" title="{{ trans('general.audit') }}"><x-icon type="audit" /><span class="sr-only">{{ trans('general.audit') }}</span></a>&nbsp;';
    }


    // Make the edit/delete buttons
    function genericActionsFormatter(owner_name, element_name) {
        if (!element_name) {
            element_name = '';
        }


        return function (value,row) {
            var actions = '<nobr>';

            // Add some overrides for any funny urls we have
            var dest = owner_name;

            if (dest =='groups') {
                var dest = 'admin/groups';
            }


            if(element_name != '') {
                dest = dest + '/' + row.owner_id + '/' + element_name;
            }

            if ((row.available_actions) && (row.available_actions.clone === true)) {
                actions += '<a href="{{ config('app.url') }}/' + dest + '/' + row.id + '/clone" class="actions btn btn-sm btn-info" data-tooltip="true" title="{{ trans('general.clone_item') }}"><x-icon type="clone" /><span class="sr-only">{{ trans('general.clone_item') }}</span></a>&nbsp;';
            }

            if ((row.available_actions) && (row.available_actions.audit === true)) {
                actions += '<a href="{{ config('app.url') }}/' + dest + '/' + row.id + '/audit" class="actions btn btn-sm btn-primary" data-tooltip="true" title="{{ trans('general.audit') }}"><x-icon type="audit" /><span class="sr-only">{{ trans('general.audit') }}</span></a>&nbsp;';
            }

            if ((row.available_actions) && (row.available_actions.update === true)) {
                actions += '<a href="{{ config('app.url') }}/' + dest + '/' + row.id + '/edit" class="actions btn btn-sm btn-warning" data-tooltip="true" title="{{ trans('general.update') }}"><x-icon type="edit" /><span class="sr-only">{{ trans('general.update') }}</span></a>&nbsp;';
            } else {
                if ((row.available_actions) && (row.available_actions.update != true)) {
                    actions += '<span data-tooltip="true" title="{{ trans('general.cannot_be_edited') }}"><a class="btn btn-warning btn-sm disabled" onClick="return false;"><x-icon type="edit" /></a></span>&nbsp;';
                }
            }

            if ((row.available_actions) && (row.available_actions.delete === true)) {

                // use the asset tag if no name is provided

                if (row.name) {
                    var name_for_box = row.name
                } else if (row.title) {
                    var name_for_box = row.title
                } else if (row.asset_tag) {
                    var name_for_box = row.asset_tag
                }


                
                actions += '<a href="{{ config('app.url') }}/' + dest + '/' + row.id + '" '
                    + ' class="actions btn btn-danger btn-sm delete-asset" data-tooltip="true"  '
                    + ' data-toggle="modal" '
                    + ' data-content="{{ trans('general.sure_to_delete') }}: ' + name_for_box + '?" '
                    + ' data-title="{{  trans('general.delete') }}" onClick="return false;">'
                    + '<x-icon type="delete" /><span class="sr-only">{{ trans('general.delete') }}</span></a>&nbsp;';
            } else {
                // Do not show the delete button on things that are already deleted
                if ((row.available_actions) && (row.available_actions.restore != true)) {
                    actions += '<span data-tooltip="true" title="{{ trans('general.cannot_be_deleted') }}"><a class="btn btn-danger btn-sm delete-asset disabled" onClick="return false;"><x-icon type="delete" /><span class="sr-only">{{ trans('general.cannot_be_deleted') }}</span></a></span>&nbsp;';
                }

            }


            if ((row.available_actions) && (row.available_actions.restore === true)) {
                actions += '<form style="display: inline;" method="POST" action="{{ config('app.url') }}/' + dest + '/' + row.id + '/restore"> ';
                actions += '@csrf';
                actions += '<button class="btn btn-sm btn-warning" data-tooltip="true" title="{{ trans('general.restore') }}"><x-icon type="restore" /><span class="sr-only">{{ trans('general.restore') }}</span></button>&nbsp;';
            }

            actions +='</nobr>';
            return actions;

        };
    }


    // This handles the icons and display of polymorphic entries
    function polymorphicItemFormatter(value) {

        var item_destination = '';
        var item_icon;

        if ((value) && (value.type)) {

            if (value.type == 'asset') {
                item_destination = 'hardware';
                item_icon = 'fas fa-barcode';
            } else if (value.type == 'accessory') {
                item_destination = 'accessories';
                item_icon = 'far fa-keyboard';
            } else if (value.type == 'component') {
                item_destination = 'components';
                item_icon = 'far fa-hdd';
            } else if (value.type == 'consumable') {
                item_destination = 'consumables';
                item_icon = 'fas fa-tint';
            } else if (value.type == 'license') {
                item_destination = 'licenses';
                item_icon = 'far fa-save';
            } else if (value.type == 'user') {
                item_destination = 'users';
                item_icon = 'fas fa-user';
            } else if (value.type == 'location') {
                item_destination = 'locations'
                item_icon = 'fas fa-map-marker-alt';
            } else if (value.type == 'model') {
                item_destination = 'models'
                item_icon = '';
            }

            // display the username if it's checked out to a user, but don't do it if the username's there already
            if (value.username && !value.name.match('\\(') && !value.name.match('\\)')) {
                value.name = value.name + ' (' + value.username + ')';
            }

            return '<nobr><a href="{{ config('app.url') }}/' + item_destination +'/' + value.id + '" data-tooltip="true" title="' + value.type + '"><i class="' + item_icon + ' text-{{ $snipeSettings->skin!='' ? $snipeSettings->skin : 'blue' }} "></i> ' + value.name + '</a></nobr>';

        } else {
            return '';
        }


    }

    // This just prints out the item type in the activity report
    function itemTypeFormatter(value, row) {

        if ((row) && (row.item) && (row.item.type)) {
            return row.item.type;
        }
    }


    // Convert line breaks to <br>
    function notesFormatter(value) {
        if (value) {
            return value.replace(/(?:\r\n|\r|\n)/g, '<br />');
        }
    }

    // Check if checkbox should be selectable
    // Selectability is determined by the API field "selectable" which is set at the Presenter/API Transformer
    // However since different bulk actions have different requirements, we have to walk through the available_actions object
    // to determine whether to disable it
    function checkboxEnabledFormatter (value, row) {

        // add some stuff to get the value of the select2 option here?

        if ((row.available_actions) && (row.available_actions.bulk_selectable) && (row.available_actions.bulk_selectable.delete !== true)) {
            return {
                disabled:true,
                //checked: false, <-- not sure this will work the way we want?
            }
        }
    }


    // We need a special formatter for license seats, since they don't work exactly the same
    // Checkouts need the license ID, checkins need the specific seat ID

    function licenseSeatInOutFormatter(value, row) {
        // The user is allowed to check the license seat out and it's available
        if ((row.available_actions.checkout === true) && (row.user_can_checkout === true) && ((!row.asset_id) && (!row.assigned_to))) {
            return '<a href="{{ config('app.url') }}/licenses/' + row.license_id + '/checkout/'+row.id+'" class="btn btn-sm bg-maroon" data-tooltip="true" title="{{ trans('general.checkout_tooltip') }}">{{ trans('general.checkout') }}</a>';
        } else {
            return '<a href="{{ config('app.url') }}/licenses/' + row.id + '/checkin" class="btn btn-sm bg-purple" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkin') }}</a>';
        }

    }

    function genericCheckinCheckoutFormatter(destination) {
        return function (value, row) {

            // The user is allowed to check items out, AND the item is deployable
            if ((row.available_actions.checkout == true) && (row.user_can_checkout == true) && ((!row.asset_id) && (!row.assigned_to))) {

                    return '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '/checkout" class="btn btn-sm bg-maroon" data-tooltip="true" title="{{ trans('general.checkout_tooltip') }}">{{ trans('general.checkout') }}</a>';

            // The user is allowed to check items out, but the item is not able to be checked out
            } else if (((row.user_can_checkout == false)) && (row.available_actions.checkout == true) && (!row.assigned_to)) {

                // We use slightly different language for assets versus other things, since they are the only
                // item that has a status label
                if (destination =='hardware') {
                    return '<span  data-tooltip="true" title="{{ trans('admin/hardware/general.undeployable_tooltip') }}"><a class="btn btn-sm bg-maroon disabled">{{ trans('general.checkout') }}</a></span>';
                } else {
                    return '<span  data-tooltip="true" title="{{ trans('general.undeployable_tooltip') }}"><a class="btn btn-sm bg-maroon disabled">{{ trans('general.checkout') }}</a></span>';
                }

            // The user is allowed to check items in
            } else if (row.available_actions.checkin == true)  {
                if (row.assigned_to) {
                    return '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '/checkin" class="btn btn-sm bg-purple" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkin') }}</a>';
                } else if (row.assigned_pivot_id) {
                    return '<a href="{{ config('app.url') }}/' + destination + '/' + row.assigned_pivot_id + '/checkin" class="btn btn-sm bg-purple" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkin') }}</a>';
                }

            }

        }


    }


    // This is only used by the requestable assets section
    function assetRequestActionsFormatter (row, value) {
        if (value.assigned_to_self == true){
            return '<button class="btn btn-danger btn-sm btn-block disabled" data-tooltip="true" title="{{ trans('admin/hardware/message.requests.cancel') }}">{{ trans('button.cancel') }}</button>';
        } else if (value.available_actions.cancel == true)  {
            return '<form action="{{ config('app.url') }}/account/request-asset/' + value.id + '/cancel" method="POST">@csrf<button class="btn btn-danger btn-block btn-sm" data-tooltip="true" title="{{ trans('admin/hardware/message.requests.cancel') }}">{{ trans('button.cancel') }}</button></form>';
        } else if (value.available_actions.request == true)  {
            return '<form action="{{ config('app.url') }}/account/request-asset/'+ value.id + '" method="POST">@csrf<button class="btn btn-block btn-primary btn-sm" data-tooltip="true" title="{{ trans('general.request_item') }}">{{ trans('button.request') }}</button></form>';
        }

    }



    var formatters = [
        'hardware',
        'accessories',
        'consumables',
        'components',
        'locations',
        'users',
        'manufacturers',
        'maintenances',
        'statuslabels',
        'models',
        'licenses',
        'categories',
        'suppliers',
        'departments',
        'companies',
        'depreciations',
        'fieldsets',
        'groups',
        'kits'
    ];

    for (var i in formatters) {
        window[formatters[i] + 'LinkFormatter'] = genericRowLinkFormatter(formatters[i]);
        window[formatters[i] + 'LinkObjFormatter'] = genericColumnObjLinkFormatter(formatters[i]);
        window[formatters[i] + 'ActionsFormatter'] = genericActionsFormatter(formatters[i]);
        window[formatters[i] + 'InOutFormatter'] = genericCheckinCheckoutFormatter(formatters[i]);
    }

    var child_formatters = [
        ['kits', 'models'],
        ['kits', 'licenses'],
        ['kits', 'consumables'],
        ['kits', 'accessories'],
    ];

    for (var i in child_formatters) {
        var owner_name = child_formatters[i][0];
        var child_name = child_formatters[i][1];
        window[owner_name + '_' + child_name + 'ActionsFormatter'] = genericActionsFormatter(owner_name, child_name);
    }



    // This is  gross, but necessary so that we can package the API response
    // for custom fields in a more useful way.
    function customFieldsFormatter(value, row) {


            if ((!this) || (!this.title)) {
                return '';
            }

            var field_column = this.title;

            // Pull out any HTMl that might be passed via the presenter
            // (for example, the locked icon for encrypted fields)
            var field_column_plain = field_column.replace(/<(?:.|\n)*?> ?/gm, '');
            if ((row.custom_fields) && (row.custom_fields[field_column_plain])) {

                // If the field type needs special formatting, do that here
                if ((row.custom_fields[field_column_plain].field_format) && (row.custom_fields[field_column_plain].value)) {
                    if (row.custom_fields[field_column_plain].field_format=='URL') {
                        return '<a href="' + row.custom_fields[field_column_plain].value + '" target="_blank" rel="noopener">' + row.custom_fields[field_column_plain].value + '</a>';
                    } else if (row.custom_fields[field_column_plain].field_format=='BOOLEAN') {
                        return (row.custom_fields[field_column_plain].value == 1) ? "<span class='fas fa-check-circle' style='color:green'>" : "<span class='fas fa-times-circle' style='color:red' />";
                    } else if (row.custom_fields[field_column_plain].field_format=='EMAIL') {
                        return '<a href="mailto:' + row.custom_fields[field_column_plain].value + '" style="white-space: nowrap" data-tooltip="true" title="{{ trans('general.send_email') }}"><x-icon type="email" /> ' + row.custom_fields[field_column_plain].value + '</a>';
                    }
                }
                return row.custom_fields[field_column_plain].value;

            }

    }


    function createdAtFormatter(value) {
        if ((value) && (value.formatted)) {
            return value.formatted;
        }
    }

    function externalLinkFormatter(value) {

        if (value) {
            if ((value.indexOf("{") === -1) || (value.indexOf("}") ===-1)) {
                return '<nobr><a href="' + value + '" target="_blank" title="{{ trans('general.external_link_tooltip') }} ' + value + '" data-tooltip="true"><x-icon type="external-link" /> ' + value + '</a></nobr>';
            }
            return value;
        }
    }

    function groupsFormatter(value) {

        if (value) {
            var groups = '';
            for (var index in value.rows) {
                groups += '<a href="{{ config('app.url') }}/admin/groups/' + value.rows[index].id + '" class="label label-default">' + value.rows[index].name + '</a> ';
            }
            return groups;
        }
    }



    function changeLogFormatter(value) {

        var result = '';
        var pretty_index = '';

            for (var index in value) {


                // Check if it's a custom field
                if (index.startsWith('_snipeit_')) {
                    pretty_index = index.replace("_snipeit_", "Custom:_");
                } else {
                    pretty_index = index;
                }

                extra_pretty_index = prettyLog(pretty_index);

                result += extra_pretty_index + ': <del>' + value[index].old + '</del>  <x-icon type="long-arrow-right" /> ' + value[index].new + '<br>'
            }

        return result;

    }

    function prettyLog(str) {
        let frags = str.split('_');
        for (let i = 0; i < frags.length; i++) {
            frags[i] = frags[i].charAt(0).toUpperCase() + frags[i].slice(1);
        }
        return frags.join(' ');
    }

    // Show the warning if below min qty
    function minAmtFormatter(row, value) {

        if ((row) && (row!=undefined)) {
            
            if (value.remaining <= value.min_amt) {
                return  '<span class="text-danger text-bold" data-tooltip="true" title="{{ trans('admin/licenses/general.below_threshold_short') }}"><x-icon type="warning" class="text-yellow" /> ' + value.min_amt + '</span>';
            }
            return value.min_amt
        }
        return '--';
    }

    

    // Create a linked phone number in the table list
    function phoneFormatter(value) {
        if (value) {
            return  '<span style="white-space: nowrap;"><a href="tel:' + value + '" data-tooltip="true" title="{{ trans('general.call') }}"><x-icon type="phone" /> ' + value + '</a></span>';
        }
    }


    function deployedLocationFormatter(row, value) {
        if ((row) && (row!=undefined)) {
            return '<a href="{{ config('app.url') }}/locations/' + row.id + '">' + row.name + '</a>';
        } else if (value.rtd_location) {
            return '<a href="{{ config('app.url') }}/locations/' + value.rtd_location.id + '">' + value.rtd_location.name + '</a>';
        }

    }

    function groupsAdminLinkFormatter(value, row) {
        return '<a href="{{ config('app.url') }}/admin/groups/' + row.id + '">' + value + '</a>';
    }

    function assetTagLinkFormatter(value, row) {
        if ((row.asset) && (row.asset.id)) {
            if (row.asset.deleted_at) {
                return '<span style="white-space: nowrap;"><x-icon type="x" class="text-danger" /><span class="sr-only">{{ trans('admin/hardware/general.deleted') }}</span> <del><a href="{{ config('app.url') }}/hardware/' + row.asset.id + '" data-tooltip="true" title="{{ trans('admin/hardware/general.deleted') }}">' + row.asset.asset_tag + '</a></del></span>';
            }
            return '<a href="{{ config('app.url') }}/hardware/' + row.asset.id + '">' + row.asset.asset_tag + '</a>';
        }
        return '';

    }

    function departmentNameLinkFormatter(value, row) {
        if ((row.assigned_user) && (row.assigned_user.department) && (row.assigned_user.department.name)) {
            return '<a href="{{ config('app.url') }}/departments/' + row.assigned_user.department.id + '">' + row.assigned_user.department.name + '</a>';
        }

    }

    function assetNameLinkFormatter(value, row) {
        if ((row.asset) && (row.asset.name)) {
            return '<a href="{{ config('app.url') }}/hardware/' + row.asset.id + '">' + row.asset.name + '</a>';
        }
    }

    function assetSerialLinkFormatter(value, row) {

        if ((row.asset) && (row.asset.serial)) {
            if (row.asset.deleted_at) {
                return '<span style="white-space: nowrap;"><x-icon type="x" class="text-danger" /><span class="sr-only">deleted</span> <del><a href="{{ config('app.url') }}/hardware/' + row.asset.id + '" data-tooltip="true" title="{{ trans('admin/hardware/general.deleted') }}">' + row.asset.serial + '</a></del></span>';
            }
            return '<a href="{{ config('app.url') }}/hardware/' + row.asset.id + '">' + row.asset.serial + '</a>';
        }
        return '';
    }

    function trueFalseFormatter(value) {
        if ((value) && ((value == 'true') || (value == '1'))) {
            return '<x-icon type="checkmark" class="text-success" /><span class="sr-only">{{ trans('general.true') }}</span>';
        } else {
            return '<x-icon type="x" class="text-danger" /><span class="sr-only">{{ trans('general.false') }}</span>';
        }
    }

    function dateDisplayFormatter(value) {
        if (value) {
            return  value.formatted;
        }
    }

    function iconFormatter(value) {
        if (value) {
            return '<i class="' + value + '  icon-med"></i>';
        }
    }

    function emailFormatter(value) {
        if (value) {
            return '<a href="mailto:' + value + '" style="white-space: nowrap" data-tooltip="true" title="{{ trans('general.send_email') }}"><x-icon type="email" /> ' + value + '</a>';
        }
    }

    function linkFormatter(value) {
        if (value) {
            return '<a href="' + value + '">' + value + '</a>';
        }
    }

    function assetCompanyFilterFormatter(value, row) {
        if (value) {
            return '<a href="{{ config('app.url') }}/hardware/?company_id=' + row.id + '">' + value + '</a>';
        }
    }

    function assetCompanyObjFilterFormatter(value, row) {
        if ((row) && (row.company)) {
            return '<a href="{{ config('app.url') }}/hardware/?company_id=' + row.company.id + '">' + row.company.name + '</a>';
        }
    }

    function usersCompanyObjFilterFormatter(value, row) {
        if (value) {
            return '<a href="{{ config('app.url') }}/users/?company_id=' + row.id + '">' + value + '</a>';
        } else {
            return value;
        }
    }

    function locationCompanyObjFilterFormatter(value, row) {
        if (value) {
            return '<a href="{{ url('/') }}/locations/?company_id=' + row.company.id + '">' + row.company.name + '</a>';
        } else {
            return value;
        }
    }

    function employeeNumFormatter(value, row) {

        if ((row) && (row.assigned_to) && ((row.assigned_to.employee_number))) {
            return '<a href="{{ config('app.url') }}/users/' + row.assigned_to.id + '">' + row.assigned_to.employee_number + '</a>';
        }
    }

    function orderNumberObjFilterFormatter(value, row) {
        if (value) {
            return '<a href="{{ config('app.url') }}/hardware/?order_number=' + row.order_number + '">' + row.order_number + '</a>';
        }
    }

    function auditImageFormatter(value){
        if (value){
            return '<a href="' + value.url + '" data-toggle="lightbox" data-type="image"><img src="' + value.url + '" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive" alt=""></a>'
        }
    }


   function imageFormatter(value, row) {

        if (value) {

            // This is a clunky override to handle unusual API responses where we're presenting a link instead of an array
            if (row.avatar) {
                var altName = '';
            }
            else if (row.name) {
                var altName = row.name;
            }
            else if ((row) && (row.model)) {
                var altName = row.model.name;
           }
            return '<a href="' + value + '" data-toggle="lightbox" data-type="image"><img src="' + value + '" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive" alt="' + altName + '"></a>';
        }
    }
    function downloadFormatter(value) {
        if (value) {
            return '<a href="' + value + '" target="_blank"><x-icon type="download" /></a>';
        }
    }

    function fileUploadFormatter(value) {
        if ((value) && (value.url) && (value.inlineable)) {
            return '<a href="' + value.url + '" data-toggle="lightbox" data-type="image"><img src="' + value.url + '" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive" alt=""></a>';
        } else if ((value) && (value.url)) {
            return '<a href="' + value.url + '" class="btn btn-default"><x-icon type="download" /></a>';
        }
    }


    function fileUploadNameFormatter(value) {
        console.dir(value);
        if ((value) && (value.filename) && (value.url)) {
            return '<a href="' + value.url + '">' + value.filename + '</a>';
        }
    }

    function linkToUserSectionBasedOnCount (count, id, section) {
        if (count) {
            return '<a href="{{ config('app.url') }}/users/' + id + '#' + section +'">' + count + '</a>';
        }

        return count;
    }

    function linkNumberToUserAssetsFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'asset');
    }

    function linkNumberToUserLicensesFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'licenses');
    }

    function linkNumberToUserConsumablesFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'consumables');
    }

    function linkNumberToUserAccessoriesFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'accessories');
    }

    function linkNumberToUserManagedUsersFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'managed-users');
    }

    function linkNumberToUserManagedLocationsFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'managed-locations');
    }

    function labelPerPageFormatter(value, row, index, field) {
        if (row) {
            if (!row.hasOwnProperty('sheet_info')) { return 1; }
            else { return row.sheet_info.labels_per_page; }
        }
    }

    function labelRadioFormatter(value, row, index, field) {
        if (row) {
            return row.name == '{{ str_replace("\\", "\\\\", $snipeSettings->label2_template) }}';
        }
    }

    function labelSizeFormatter(value, row) {
        if (row) {
            return row.width + ' x ' + row.height + ' ' + row.unit;
        }
    }

    function cleanFloat(number) {
        if(!number) { // in a JavaScript context, meaning, if it's null or zero or unset
            return 0.0;
        }
        if ("{{$snipeSettings->digit_separator}}" == "1.234,56") {
            // yank periods, change commas to periods
            periodless = number.toString().replace(/\./g,"");
            decimalfixed = periodless.replace(/,/g,".");
        } else {
            // yank commas, that's it.
            decimalfixed = number.toString().replace(/\,/g,"");
        }
        return parseFloat(decimalfixed);
    }


    function qtySumFormatter(data) {
        var currentField = this.field;
        var total = 0;
        var fieldname = this.field;

        $.each(data, function() {
            var r = this;
            total += this[currentField];
        });
        return total;
    }

    function sumFormatter(data) {
        if (Array.isArray(data)) {
            var field = this.field;
            var total_sum = data.reduce(function(sum, row) {
                
                return (sum) + (cleanFloat(row[field]) || 0);
            }, 0);
            
            return numberWithCommas(total_sum.toFixed(2));
        }
        return 'not an array';
    }

    function sumFormatterQuantity(data){
        if(Array.isArray(data)) {
            
            // Prevents issues on page load where data is an empty array
            if(data[0] == undefined){
                return 0.00
            }
            // Check that we are actually trying to sum cost from a table
            // that has a quantity column. We must perform this check to
            // support licences which use seats instead of qty
            if('qty' in data[0]) {
                var multiplier = 'qty';
            } else if('seats' in data[0]) {
                var multiplier = 'seats';
            } else {
                return 'no quantity';
            }
            var total_sum = data.reduce(function(sum, row) {
                return (sum) + (cleanFloat(row["purchase_cost"])*row[multiplier] || 0);
            }, 0);
            return numberWithCommas(total_sum.toFixed(2));
        }
        return 'not an array';
    }

    function numberWithCommas(value) {
        
        if ((value) && ("{{$snipeSettings->digit_separator}}" == "1.234,56")){
            var parts = value.toString().split(".");
             parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
             return parts.join(",");
         } else {
             var parts = value.toString().split(",");
             parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
             return parts.join(".");
        }
        return value
    }

    $(function () {
        $('#bulkEdit').click(function () {
            var selectedIds = $('.snipe-table').bootstrapTable('getSelections');
            $.each(selectedIds, function(key,value) {
                $( "#bulkForm" ).append($('<input type="hidden" name="ids[' + value.id + ']" value="' + value.id + '">' ));
            });

        });
    });



    $(function() {

        // This handles the search box highlighting on both ajax and client-side
        // bootstrap tables
        var searchboxHighlighter = function (event) {

            $('.search-input').each(function (index, element) {

                if ($(element).val() != '') {
                    $(element).addClass('search-highlight');
                    $(element).next().children().addClass('search-highlight');
                } else {
                    $(element).removeClass('search-highlight');
                    $(element).next().children().removeClass('search-highlight');
                }
            });
        };

        $('.search button[name=clearSearch]').click(searchboxHighlighter);
        searchboxHighlighter({ name:'pageload'});
        $('.search-input').keyup(searchboxHighlighter);

        //  This is necessary to make the bootstrap tooltips work inside of the
        // wenzhixin/bootstrap-table formatters
        $('#table').on('post-body.bs.table', function () {
            $('[data-tooltip="true"]').tooltip({
                container: 'body'
            });


        });
    });

</script>
    
@endpush
