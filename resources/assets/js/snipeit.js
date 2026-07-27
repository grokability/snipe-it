var jQuery = require('jquery');
window.jQuery = jQuery
window.$ = jQuery

// window._ = require('lodash'); //the only place I saw this used was vue.js, and we don't use that anymore

/****************************************
 Much of what you'll see below is just plain require()'ed, this is because
 it is mostly jQuery stuff, which attaches itself to the $() function/object
 So we don't have to assign it to anything, it will just automagically attach
 itself
 *****************************************/

require("jquery-ui/dist/jquery-ui")
jQuery.fn.uitooltip = jQuery.fn.tooltip;
require('bootstrap-less');
require('select2');
require('admin-lte');
require('tether');
require('jquery-slimscroll');
require('jquery.iframe-transport'); //probably not needed anymore, if I'm honest
require('blueimp-file-upload')
require('bootstrap-colorpicker')
// eonasdan-bootstrap-datetimepicker (BS3) needs moment on window before it loads
window.moment = require('moment')
require('eonasdan-bootstrap-datetimepicker')
require('ekko-lightbox') //TODO - this doesn't seem jquery-ish, we might need to do something weird here
                         // it *does* require Bootstrap, which requires jquery, so maybe that's OK
                         // it seems to work...
require('./extensions/pGenerator.jquery'); //WEIRD, but works
//require('chart.js') // Weirdly, this seems to "just work." Without this line, the dashboard blows up
// but it's *HUGE* - and we only use it one place. So we're taking it out of the bundle
window.SignaturePad = require('./signature_pad'); //ALSO WEIRD - but works
require('jquery-validation')
window.List = require('list.js')
window.ClipboardJS = require('clipboard')
// TODO - find everything using moment.js and kill it or upgrade it? It's huge
// - adminLTE (UGH)
// - bootstrap-daterangepicker
// - fullcalendar (what's that? it's used by AdminLTE)

/**
 * Module containing core application logic.
 * @param  {jQuery} $        Insulated jQuery object
 * @param  {JSON} settings Insulated `window.snipeit.settings` object.
 * @return {IIFE}          Immediately invoked. Returns self.
 */

lineOptions = {

        legend: {
            position: "bottom"
        },
        scales: {
            yAxes: [{
                ticks: {
                    fontColor: "rgba(0,0,0,0.5)",
                    fontStyle: "bold",
                    beginAtZero: true,
                    maxTicksLimit: 5,
                    padding: 20
                },
                gridLines: {
                    drawTicks: false,
                    display: false
                }
            }],
            xAxes: [{
                gridLines: {
                    zeroLineColor: "transparent"
                },
                ticks: {
                    padding: 20,
                    fontColor: "rgba(0,0,0,0.5)",
                    fontStyle: "bold"
                }
            }]
        }

};

pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,

    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li>" +
    "<i class='fas fa-circle-o' style='color: <%=segments[i].fillColor%>'></i>" +
    "<%if(segments[i].label){%><%=segments[i].label%><%}%> foo</li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> "
};

//-----------------
//- END PIE CHART -
//-----------------

var baseUrl = $('meta[name="baseUrl"]').attr('content');



$(function () {

    var $el = $('body');

    // confirm restore modal

    $el.on('click', '.restore-asset', function (evnt) {
        var $context = $(this);
        var $restoreConfirmModal = $('#restoreConfirmModal');
        var href = $context.attr('href');
        var message = $context.attr('data-content');
        var title = $context.attr('data-title');

        $('#confirmModalLabel').text(title);
        $restoreConfirmModal.find('.modal-body').text(message);
        $('#restoreForm').attr('action', href);
        $restoreConfirmModal.modal({
            show: true
        });
        return false;
    });

    // Mark-a-maintenance-complete modal (green checkmark button in the
    // maintenances table actions column). Sets the modal form's action to
    // the row's completion URL and opens it.
    $el.on('click', '.complete-maintenance', function () {
        var url = $(this).data('url');
        $('#completeMaintenanceForm').attr('action', url);
        $('#completionNote').val('');
        $('#completeMaintenanceModal').modal('show');
    });

    // confirm delete modal
    $el.on('click', '.delete-asset', function (evnt) {
        var $context = $(this);
        var $dataConfirmModal = $('#dataConfirmModal');
        // Anchors keep the URL in href; buttons keep it in data-href
        // (buttons don't semantically support href per HTML5).
        var href = $context.attr('data-href') || $context.attr('href');
        var message = $context.attr('data-content');
        var headericon = $context.attr('data-icon');
        var title = $context.attr('data-title');

        // deleteForm is the ID of the modal form itself
        $('#deleteForm').attr('action', href);
        $dataConfirmModal.find('.modal-header-icon').addClass(headericon);
        $dataConfirmModal.find('.modal-title').text('').text(title).prepend('<i class="fa ' + headericon + '"></i> ');
        $dataConfirmModal.find('.modal-body').text('').text(message);
        $dataConfirmModal.attr('action', href);

        // Fire the modal
        $dataConfirmModal.modal({
            show: true
        });
        return false;
    });



     /*
     * Select2
     */

        $('select.select2:not(".select2-hidden-accessible")').each(function (i,obj) {
            {
                $(obj).select2();
            }
        });


    // $('.datepicker').datepicker();
    // var datepicker = $.fn.datepicker.noConflict(); // return $.fn.datepicker to previously assigned value
    // $.fn.bootstrapDP = datepicker;
    // $('.datepicker').datepicker();

    // Crazy select2 rich dropdowns with images!
    $('.js-data-ajax').each( function (i,item) {
        var link = $(item);
        var endpoint = link.data("endpoint");
        var select = link.data("select");

        link.select2({

            /**
             * Adds an empty placeholder, allowing every select2 instance to be cleared.
             * This placeholder can be overridden with the "data-placeholder" attribute.
             */
            placeholder: '',
            allowClear: true,
            language: $('meta[name="language"]').attr('content'),
            dir: $('meta[name="language-direction"]').attr('content'),
            
            ajax: {

                // the baseUrl includes a trailing slash
                url: baseUrl + 'api/v1/' + endpoint + '/selectlist',
                dataType: 'json',
                delay: 250,
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                data: function (params) {
                    var data = {
                        search: params.term,
                        page: params.page || 1,
                        statusType: link.data("asset-status-type"),
                        companyId: link.data("company-ids") || link.data("company-id"),
                        excludeId: link.data("exclude-id"),
                        // When true, the companies selectlist marks child companies
                        // (those with a parent of their own) as disabled — used by
                        // the parent-company picker so users can't choose options
                        // that would fail the parent_must_be_top_level validator.
                        onlyTopLevel: link.data("only-top-level"),
                    };
                    return data;
                },
                /* processResults: function (data, params) {

                    params.page = params.page || 1;

                    var answer =  {
                        results: data.items,
                        pagination: {
                            more: data.pagination.more
                        }
                    };

                    return answer;
                }, */
                cache: true
            },
            //escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
            templateResult: formatDatalistSafe,
            //templateSelection: formatDataSelection
        });

    });

	function getSelect2Value(element) {
		
		// if the passed object is not a jquery object, assuming 'element' is a selector
		if (!(element instanceof jQuery)) element = $(element);

		var select = element.data("select2");

		// There's two different locations where the select2-generated input element can be. 
		searchElement = select.dropdown.$search || select.$container.find(".select2-search__field");

		var value = searchElement.val();
		return value;
	}
	
	$(".select2-hidden-accessible").on('select2:selecting', function (e) {
		var data = e.params.args.data;
		var isMouseUp = false;
		var element = $(this);
		var value = getSelect2Value(element);
		
		if(e.params.args.originalEvent) isMouseUp = e.params.args.originalEvent.type == "mouseup";
		
		// if selected item does not match typed text, do not allow it to pass - force close for ajax.
		if(!isMouseUp) {
			if(value.toLowerCase() && data.text.toLowerCase().indexOf(value) < 0) {
				e.preventDefault();

				element.select2('close');
				
			// if it does match, we set a flag in the event (which gets passed to subsequent events), telling it not to worry about the ajax
			} else if(value.toLowerCase() && data.text.toLowerCase().indexOf(value) > -1) {
				e.params.args.noForceAjax = true;
			}
		}
	});
	
	$(".select2-hidden-accessible").on('select2:closing', function (e) {
		var element = $(this);
		var value = getSelect2Value(element);
		var noForceAjax = false;
		var isMouseUp = false;
		if(e.params.args.originalSelect2Event) noForceAjax = e.params.args.originalSelect2Event.noForceAjax;
		if(e.params.args.originalEvent) isMouseUp = e.params.args.originalEvent.type == "mouseup";
		
		if(value && !noForceAjax && !isMouseUp) {
			var endpoint = element.data("endpoint");
            var statusType = element.data("asset-status-type");
			$.ajax({
                url: baseUrl + 'api/v1/' + endpoint + '/selectlist?search=' + value + '&page=1' + (statusType ? '&statusType=' + statusType : ''),
				dataType: 'json',
				headers: {
					"X-Requested-With": 'XMLHttpRequest',
					"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
				},
			}).done(function(response) {
				var currentlySelected = element.select2('data').map(function (x){ 
                    return +x.id;
                }).filter(function (x) {
                    return x !== 0;
                });
				
				// makes sure we're not selecting the same thing twice for multiples
				var filteredResponse = response.results.filter(function(item) {
					return currentlySelected.indexOf(+item.id) < 0;
				});

				var first = (currentlySelected.length > 0) ? filteredResponse[0] : response.results[0];
				
				if(first && first.id) {
					first.selected = true;
					
					if($("option[value='" + first.id + "']", element).length < 1) {
						var option = new Option(first.text, first.id, true, true);
						element.append(option);
					} else {
						var isMultiple = element.attr("multiple") == "multiple";
						element.val(isMultiple? element.val().concat(first.id) : element.val(first.id));
					}
					element.trigger('change');

					element.trigger({
						type: 'select2:select',
						params: {
							data: first
						}
					});
				}
			});
		}
	});

    function formatDatalist (datalist) {
        var loading_markup = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Loading...';
        if (datalist.loading) {
            return loading_markup;
        }

        var markup = '<div class="clearfix">' ;
        markup += '<div class="pull-left" style="padding-right: 10px;">';
        if (datalist.image) {
            markup += "<div style='width: 30px;'><img src='" + datalist.image + "' style='max-height: 20px; max-width: 30px;' alt='" +  datalist.text + "'></div>";
        } else {
            markup += '<div style="height: 20px; width: 30px;"></div>';
        }

        markup += "</div><div>" + datalist.text + "</div>";
        markup += "</div>";
        return markup;
    }

    function formatDatalistSafe(datalist) {

        if (datalist.loading) {
            return $('<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Loading...');
        }

        var root_div = $("<div class='clearfix'>") ;
        var left_pull = $("<div class='pull-left' style='padding-right: 10px;'>");
        if (datalist.image) {
            var inner_div = $("<div style='width: 20px;'>");
            /******************************************************************
             *
             * We are specifically chosing empty alt-text below, because this
             * image conveys no additional information, relative to the text
             * that will *always* be there in any select2 list that is in use
             * in Snipe-IT. If that changes, we would probably want to change
             * some signatures of some functions, but right now, we don't want
             * screen readers to say "HP SuperJet 5000, .... picture of HP
             * SuperJet 5000..." and so on, for every single row in a list of
             * assets or models or whatever.
             *
             *******************************************************************/
            var img = $("<img src='' style='max-height: 20px; max-width: 20px;' alt=''>");
            img.attr("src", datalist.image);
            inner_div.append(img)
        } else if (datalist.tag_color) {
            var inner_div = $("<div style='width: 20px;'>");
            var icon = $('<i class="fa-solid fa-square" style="font-size: 20px;" aria-hidden="true"></i>');
            icon.css("color", datalist.tag_color );
            inner_div.append(icon)
        } else {
            var inner_div=$("<div style='height: 20px; width: 20px;'></div>");
        }
        left_pull.append(inner_div);
        root_div.append(left_pull);
        var name_div = $("<div>");
        name_div.text(datalist.text);
        root_div.append(name_div)
        var safe_html = root_div.get(0).outerHTML;
        var old_html = formatDatalist(datalist);
        if(safe_html != old_html) {
            //console.log("HTML MISMATCH: ");
            //console.log("FormatDatalistSafe: ");
            // console.dir(root_div.get(0));
            //console.log(safe_html);
            //console.log("FormatDataList: ");
            //console.log(old_html);
        }
        return root_div;

    }

    function formatDataSelection (datalist) {
        // This a heinous workaround for a known bug in Select2.
        // Without this, the rich selectlists are vulnerable to XSS.
        // Many thanks to @uberbrady for this fix. It ain't pretty,
        // but it resolves the issue until Select2 addresses it on their end.
        //
        // Bug was reported in 2016 :{
        // https://github.com/select2/select2/issues/4587

        return datalist.text.replace(/>/g, '&gt;')
            .replace(/</g, '&lt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // This handles the radio button selectors for the checkout-to-foo options
    // on asset checkout and also on asset edit
    $(function() {
        var checkoutToTypeInputs = $('input[name=checkout_to_type]');

        if (!checkoutToTypeInputs.length) {
            return;
        }

        function syncCheckoutToTypeUi(resetSelections) {
            var assignto_type = $('input[name=checkout_to_type]:checked').val();
            var userid = $('#assigned_user option:selected').val();

            if (assignto_type == 'asset') {
                $('#current_assets_box').fadeOut();
                $('#assigned_asset').show();
                $('#assigned_user').hide();
                $('#assigned_location').hide();
                $('.notification-callout').fadeOut();

                if (resetSelections) {
                    $('[name="assigned_location"]').val('').trigger('change.select2');
                    $('[name="assigned_user"]').val('').trigger('change.select2');
                }
            } else if (assignto_type == 'location') {
                $('#current_assets_box').fadeOut();
                $('#assigned_asset').hide();
                $('#assigned_user').hide();
                $('#assigned_location').show();
                $('.notification-callout').fadeOut();

                if (resetSelections) {
                    $('[name="assigned_asset"]').val('').trigger('change.select2');
                    $('[name="assigned_user"]').val('').trigger('change.select2');
                }
            } else {
                $('#assigned_asset').hide();
                $('#assigned_user').show();
                $('#assigned_location').hide();
                if (userid) {
                    $('#current_assets_box').fadeIn();
                }
                $('.notification-callout').fadeIn();

                if (resetSelections) {
                    $('[name="assigned_asset"]').val('').trigger('change.select2');
                    $('[name="assigned_location"]').val('').trigger('change.select2');
                }
            }
        }

        checkoutToTypeInputs.on('change', function () {
            syncCheckoutToTypeUi(true);
        });

        // Expose so pages that reveal #assignto_selector later (asset edit's
        // user_add() flow, etc.) can trigger the sync once the selector is
        // visible. Standalone checkout pages don't need to call this — the
        // initial-render block below handles them.
        window.snipeitSyncCheckoutToTypeUi = syncCheckoutToTypeUi;

        // Apply the current radio selection on initial render unless the page
        // has explicitly hidden the selector via an inline style="display:none"
        // (asset create/edit start that way and reveal it from user_add() after
        // a deployability AJAX call). Using getAttribute('style') instead of
        // jQuery's :visible avoids false negatives on pages like the standalone
        // /hardware/{id}/checkout, where the selector is visible from the start
        // but :visible can transiently return false during select2 boot — that
        // was what hid the acceptance-options callout until a radio was toggled.
        var selectorStyle = ($('#assignto_selector').attr('style') || '').toLowerCase();
        if (selectorStyle.indexOf('display:none') === -1 && selectorStyle.indexOf('display: none') === -1) {
            syncCheckoutToTypeUi(false);
        }
    });


    // ------------------------------------------------
    // Deep linking for Bootstrap tabs
    // ------------------------------------------------
    var taburl = document.location.toString();

    // Allow full page URL to activate a tab's ID
    // ------------------------------------------------
    // This allows linking to a tab on page load via the address bar.
    // So a URL such as, http://snipe-it.local/hardware/2/#my_tab will
    // cause the tab on that page with an ID of “my_tab” to be active.
    if (taburl.match('#') ) {
        $('.nav-tabs a[href="#'+taburl.split('#')[1]+'"]').tab('show');
    }

    // Allow internal page links to activate a tab's ID.
    // ------------------------------------------------
    // This allows you to link to a tab from anywhere on the page
    // including from within another tab. Also note that internal page
    // links either inside or out of the tabs need to include data-toggle="tab"
    // Ex: <a href="#my_tab" data-toggle="tab">Click me</a>
    $('a[data-toggle="tab"]').click(function (e) {
        var href = $(this).attr("href");
        history.pushState(null, null, href);
        e.preventDefault();
        $('a[href="' + $(this).attr('href') + '"]').tab('show');
    });

    // Bootstrap-table's fixed-columns extension computes the overlay widths
    // at init time. Tables inside a hidden tab pane initialize with a
    // zero-width container and the fixed left/right columns never recover
    // on their own once the pane becomes visible. Force a resetView on any
    // snipe-tables inside the newly-shown pane so fixed columns line up.
    $('body').on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
        var pane = $(e.target).attr('href');
        if (!pane) return;
        $(pane).find('.snipe-table').each(function () {
            if ($(this).data('bootstrap.table')) {
                $(this).bootstrapTable('resetView');
            }
        });
    });

    // Same story for viewport resizes: the fixed-columns overlay caches
    // widths from the initial layout and doesn't recompute when the window
    // width changes. Debounce so a drag-resize doesn't fire resetView on
    // every intermediate pixel.
    var snipeTableResizeTimer;
    $(window).on('resize', function () {
        clearTimeout(snipeTableResizeTimer);
        snipeTableResizeTimer = setTimeout(function () {
            $('.snipe-table').each(function () {
                if ($(this).data('bootstrap.table')) {
                    $(this).bootstrapTable('resetView');
                }
            });
        }, 150);
    });

    // ------------------------------------------------
    // End Deep Linking for Bootstrap tabs
    // ------------------------------------------------



    // Image preview
    function readURL(input, $preview) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $preview.attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function formatBytes(bytes) {
        if(bytes < 1024) return bytes + " Bytes";
        else if(bytes < 1048576) return(bytes / 1024).toFixed(2) + " KB";
        else if(bytes < 1073741824) return(bytes / 1048576).toFixed(2) + " MB";
        else return(bytes / 1073741824).toFixed(2) + " GB";
    }

     // File size validation
    $('.js-uploadFile').bind('change', function() {
        var $this = $(this);
        var id = '#' + $this.attr('id');
        var status = id + '-status';
        var $status = $(status);
        var delete_id = $(id + '-deleteCheckbox');
        var preview_container = $(id + '-previewContainer');



        $status.removeClass('text-success').removeClass('text-danger');
        $(status + ' .goodfile').remove();
        $(status + ' .badfile').remove();
        $(status + ' .previewSize').hide();
        preview_container.hide();
        $(id + '-info').html('');

        var max_size = $this.data('maxsize');
        var total_size = 0;

        for (var i = 0; i < this.files.length; i++) {
            total_size += this.files[i].size;
            $(id + '-info').append('<span class="label label-default">' + htmlEntities(this.files[i].name) + ' (' + formatBytes(this.files[i].size) + ')</span> ');
        }

        if (total_size > max_size) {
            $status.addClass('text-danger').removeClass('help-block').prepend('<i class="badfile fas fa-times"></i> ').append('<span class="previewSize"> Upload is ' + formatBytes(total_size) + '.</span>');
        } else {
            $status.addClass('text-success').removeClass('help-block').prepend('<i class="goodfile fas fa-check"></i> ');
            var $preview =  $(id + '-imagePreview');
            readURL(this, $preview);
            $preview.fadeIn();
            preview_container.fadeIn();
            delete_id.hide();
        }


    });

});

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}



/**
 * Toggle disabled
 */
(function($){
		
    $.fn.toggleDisabled = function(callback){
        return this.each(function(){
            var disabled, $this = $(this);
            if($this.attr('disabled')){
                $this.removeAttr('disabled');
                disabled = false;
            } else {
                $this.attr('disabled', 'disabled');
                disabled = true;
            }

            if(callback && typeof callback === 'function'){
                callback(this, disabled);
            }
        });
    };
    
})(jQuery);

$(document).ready(function () {
    // Password-reveal eye. data-toggle is a jQuery selector — usually one
    // input id, but a multi-selector like "#password, #password_confirm"
    // lets a single click flip every matched input at once (the confirm
    // field on the user create/edit form uses this so revealing the
    // password reveals its confirmation too). Every .toggle-password
    // sharing the same data-toggle string flips its icon together so the
    // eye state doesn't visually drift between the two addons.
    $(document).on('click', '.toggle-password', function () {
        var toggleTarget = $(this).attr('data-toggle');
        var $inputs = $(toggleTarget);
        var reveal = $inputs.first().attr('type') === 'password';
        $inputs.attr('type', reveal ? 'text' : 'password');
        var $eyes = $('.toggle-password[data-toggle="' + toggleTarget + '"]');
        $eyes.toggleClass('fa-eye', ! reveal);
        $eyes.toggleClass('fa-eye-slash', reveal);
    });

    // Auto-init eonasdan datetimepickers. bootstrap-datepicker has a native
    // data-provide auto-init; eonasdan does not, so we do it ourselves.
    // Options are read from data-attributes on the wrapper so blade components
    // can tune format/side-by-side without touching this JS.
    //
    // Icon set is overridden to Font Awesome — the picker defaults to
    // Glyphicon classes, which we do not ship, so up/down arrows and clock
    // glyphs would otherwise render as empty boxes.
    // Exposed so callers who insert new [data-provide="datetimepicker"]
    // wrappers into the DOM post-load (e.g., AJAX-loaded custom fields on
    // asset create/edit when the model changes) can re-run the init on the
    // freshly-inserted elements. Pass a jQuery scope to narrow the search;
    // omit to init every uninitialised picker on the page.
    window.snipeitInitDatetimepickers = function (scope) {
        var $targets = scope ? $(scope).find('[data-provide="datetimepicker"]') : $('[data-provide="datetimepicker"]');
        $targets.each(initDatetimepicker);
    };

    function initDatetimepicker() {
        var $wrapper = $(this);
        // Skip if this wrapper already has an eonasdan instance attached
        // (data('DateTimePicker') is set by the library on init).
        if ($wrapper.data('DateTimePicker')) {
            return;
        }
        var $input = $wrapper.find('input');
        var existingValue = ($input.val() || '').trim();

        var options = {
            format: $wrapper.data('format') || 'YYYY-MM-DD HH:mm:ss',
            // Default to the compact (collapsed) view — calendar shows first
            // and a small clock icon toggles the time view. Callers that want
            // date + time visible side by side can set data-side-by-side="true".
            sideBySide: $wrapper.data('side-by-side') === true,
            showClear: true,
            showClose: true,
            showTodayButton: true,
            // In sideBySide mode the toolbar row (Today/Clear/Close) is only
            // rendered when placement is explicitly 'top' or 'bottom'; the
            // library drops it entirely on the default 'default' placement.
            toolbarPlacement: 'bottom',
            // Open the popup on any focus/click of the input (not just the
            // calendar addon icon), matching the behavior of the bootstrap
            // datepicker used elsewhere in the app.
            allowInputToggle: true,
            locale: $wrapper.data('locale') || 'en',
            icons: {
                time: 'fa-regular fa-clock',
                date: 'fa-regular fa-calendar',
                up: 'fa-solid fa-chevron-up',
                down: 'fa-solid fa-chevron-down',
                previous: 'fa-solid fa-chevron-left',
                next: 'fa-solid fa-chevron-right',
                today: 'fa-solid fa-calendar-day',
                clear: 'fa-solid fa-trash',
                close: 'fa-solid fa-xmark',
            },
        };

        // Pre-fill empty inputs with the user's current local datetime by
        // default. Callers that render a picker where "now" is NOT a safe
        // default (e.g., user-defined custom fields) can opt out by setting
        // data-default-now="false" on the wrapper.
        var wantsDefaultNow = $wrapper.data('default-now') !== false;
        if (existingValue === '' && wantsDefaultNow) {
            options.defaultDate = moment();
        }

        // data-max-date="today" caps the picker at today (replaces the
        // bootstrap-datepicker era's data-date-end-date="0d"); any other
        // value is parsed as a moment-compatible date string.
        var maxDate = $wrapper.data('max-date');
        if (maxDate) {
            options.maxDate = maxDate === 'today' ? moment().endOf('day') : moment(maxDate);
        }

        $wrapper.datetimepicker(options);
    }

    // Wires up the linked-pickers pattern for <x-input.date-range>. Each
    // .js-date-range wrapper holds a .js-date-range-start and .js-date-range-end
    // datetimepicker; changing one bounds the other so a user can't pick an
    // end date before the start (or vice versa). Runs after the plain
    // datetimepicker init above so both instances already exist.
    function initDateRangeLinking() {
        $('.js-date-range').each(function () {
            var $start = $(this).find('.js-date-range-start');
            var $end = $(this).find('.js-date-range-end');
            if (!$start.length || !$end.length) {
                return;
            }
            $start.off('dp.change.snipeitDateRange').on('dp.change.snipeitDateRange', function (e) {
                var picker = $end.data('DateTimePicker');
                if (picker) {
                    picker.minDate(e.date);
                }
            });
            $end.off('dp.change.snipeitDateRange').on('dp.change.snipeitDateRange', function (e) {
                var picker = $start.data('DateTimePicker');
                if (picker) {
                    picker.maxDate(e.date);
                }
            });
        });
    }

    // Push the app's "week starts on" setting into moment's active locale so
    // the eonasdan datetimepicker (which reads firstDayOfWeek from moment
    // locale data, not from its own options) opens with the calendar column
    // order the admin picked in Localization settings. Runs once before any
    // picker is initialized; downstream code that formats using moment's w/W
    // tokens will pick up the same value.
    if (window.snipeit && window.snipeit.settings && typeof window.snipeit.settings.first_day_of_week === 'number') {
        moment.updateLocale(moment.locale(), { week: { dow: window.snipeit.settings.first_day_of_week } });
    }

    window.snipeitInitDatetimepickers();
    initDateRangeLinking();
});



/**
 * Universal Livewire Select2 integration
 *
 * How to use:
 *
 * 1. Set the class of your select2 elements to 'livewire-select2').
 * 2. Name your element to match a property in your Livewire component
 * 3. Add an attribute called 'data-livewire-component' that points to $this->getId() (via `{{ }}` if you're in a blade,
 *    or just $this->getId() if not).
 */
document.addEventListener('livewire:init', () => {
    $('.livewire-select2').select2()

    $(document).on('select2:select', '.livewire-select2', function (event) {
        var target = $(event.target)
        if(!event.target.name || !target.data('livewire-component')) {
            console.error("You need to set both name (which should match a Livewire property) and data-livewire-component on your Livewire-ed select2 elements!")
            console.error("For data-livewire-component, you probably want to use $this->getId() or {{ $this->getId() }}, as appropriate")
            return false
        }
        // PHP property names cannot start with a digit — skip bare numeric names (e.g. "0") that would cause a 500
        if (/^\d+$/.test(event.target.name)) {
            console.error("Livewire select2: name attribute '" + event.target.name + "' is not a valid Livewire property name — skipping")
            return false
        }
        Livewire.find(target.data('livewire-component')).set(event.target.name, this.options[this.selectedIndex].value)
    });

  Livewire.interceptMessage(({ onFinish }) => {
    onFinish(() => {
      // Runs after DOM morph completes (or on error/cancel)
        queueMicrotask(() => {
          $(".livewire-select2").select2();
        });
      });
    }
  );
});




// Check/Uncheck all radio buttons in the permissions group
$('.header-row input:radio').change(function() {
    value = $(this).attr('value');
    area = $(this).data('checker-group');
    $('.radiochecker-'+area+'[value='+value+']').prop('checked', true);
});

// Generic toggleable callouts with remember state
$(".remember-toggle").on("click",function(){

    var toggleable_callout_id = $(this).attr('id');
    var toggle_content_class = 'toggle-content-'+$(this).attr('id');
    var toggle_arrow = '#toggle-arrow-' + toggleable_callout_id;
    var toggle_cookie_name='toggle_state_'+toggleable_callout_id;

    $('.'+toggle_content_class).fadeToggle(100);
    $(toggle_arrow).toggleClass('fa-caret-right fa-caret-down');
    var toggle_open = $(toggle_arrow).hasClass('fa-caret-down');
    document.cookie=toggle_cookie_name+"="+toggle_open+';path=/';
});

var all_cookies = document.cookie.split(';')
for (var i in all_cookies) {
    var trimmed_cookie = all_cookies[i].trim(' ')
    elems = trimmed_cookie.split('=', 2);

    // We have to do more here since we don't know the name of the selector
    if (trimmed_cookie.startsWith('toggle_state_')) {

        var toggle_selector_name = elems[0].replace('toggle_state_','');

        if (elems[1] != "true") {
            $('#'+toggle_selector_name+'.remember-toggle').trigger('click')
        }
    }

}


/**
 * This handles the show/hide of superuser and admin specific permissions
 * on the group edit and user edit pages
 */
if ($("#superuser_allow").is(':checked')) {

    // Hide here instead of fadeout on pageload to prevent what looks like Flash Of Unstyled Content (FOUC)
    $(".nonsuperuser").hide();
    $(".nonsuperuser").attr('display','none');
}


$(".superuser").change(function() {
    if ($(this).val() == '1') {
        $(".nonsuperuser").fadeOut();
        $(".nonsuperuser").attr('display','none');
        $(".nonadmin").fadeOut();
        $(".nonadmin").attr('display','none');
    } else if ($(this).val() != '1') {
        $(".nonsuperuser").fadeIn();
        $(".nonsuperuser").attr('display','block');

        // If the superuser button has been set to deny, we need to
        // check that the admin button isn't set to allow, before we show non-admin stuff
        if ($("#admin_allow").is(':checked')) {

            // Hide here instead of fadeout on pageload to prevent what looks like Flash Of Unstyled Content (FOUC)
            $(".nonadmin").hide();
            $(".nonadmin").attr('display','none');
        }

    }
});



if ($("#admin_allow").is(':checked')) {

    // Hide here instead of fadeout on pageload to prevent what looks like Flash Of Unstyled Content (FOUC)
    $(".nonadmin").hide();
    $(".nonadmin").attr('display','none');
}

$(".admin").change(function() {
    if ($(this).val() == '1') {
        $(".nonadmin").fadeOut();
        $(".nonadmin").attr('display','none');
    } else if ($(this).val() != '1') {
        $(".nonadmin").fadeIn();
        $(".nonadmin").attr('display','block');
    }
});

// Handle the select/deselect of the select boxes with the button from right to left

$(function () {

    function moveItems(origin, dest) {
        $(origin).find(':selected').appendTo(dest);
        $(dest).attr('selected', true);
        $(dest).sort_select_box();
    }

    function moveAllItems(origin, dest) {
        $(origin).children("option:visible").appendTo(dest);
        $(dest).attr('selected', true);
        $(dest).sort_select_box();
    }

    $('.left').on('click', function () {
        var container = $(this).closest('.addremove-multiselect');
        moveItems($(container).find('select.multiselect.selected'), $(container).find('select.multiselect.available'));
    });

    $('.right').on('click', function () {
        var container = $(this).closest('.addremove-multiselect');
        moveItems($(container).find('select.multiselect.available'), $(container).find('select.multiselect.selected'));

    });

    $('.leftall').on('click', function () {
        var container = $(this).closest('.addremove-multiselect');
        moveAllItems($(container).find('select.multiselect.selected'), $(container).find('select.multiselect.available'));
    });

    $('.rightall').on('click', function () {
        var container = $(this).closest('.addremove-multiselect');
        moveAllItems($(container).find('select.multiselect.available'), $(container).find('select.multiselect.selected'));
    });

    $('select.multiselect.selected').on('dblclick keyup',function(e){
        if(e.which == 13 || e.type == 'dblclick') {
            var container = $(this).closest('.addremove-multiselect');
            moveItems($(container).find('select.multiselect.selected'), $(container).find('select.multiselect.available'));
        }
    });

    $('select.multiselect.available').on('dblclick keyup',function(e){
        if(e.which == 13 || e.type == 'dblclick') {
            var container = $(this).closest('.addremove-multiselect');
            moveItems($(container).find('select.multiselect.available'), $(container).find('select.multiselect.selected'));
            $('#hidden_ids_box').val($('#selected-select').val());
        }
    });


});

$.fn.sort_select_box = function(){
    // Get options from select box
    var selected_options = $(this).children('option');
    // sort alphabetically
    selected_options.sort(function(a,b) {
        if (a.text > b.text) return 1;
        else if (a.text < b.text) return -1;
        else return 0
    })
    //replace with sorted my_options;
    $(this).empty().append(selected_options);

    var selected_in_box =  $('#selected-select option').toArray().map(item => item.value).join();

    $('#hidden_ids_box').empty().val(selected_in_box);

    $('#count_selected_box').html($('#selected-select option').length);
    $('#count_unselected_box').html($('#available-select option').length);

    // clearing any selections
    $("#"+this.attr('id')+" option").attr('selected', true);
}


/*
 * Data-attribute driven initializers. Blades attach behavior by adding
 * `data-toggle="..."` (plus supporting data-* attributes) to elements
 * instead of shipping an inline <script> block. Add new handlers here
 * as inline scripts get migrated out of blades.
 */
$(function () {

    // Sound preview on account/profile. Fires the URL in data-sound-url
    // when the user toggles the checkbox on.
    $(document).on('click', '[data-toggle="sound-test"]', function () {
        if (!$(this).is(':checked')) return;
        var url = $(this).data('sound-url');
        if (!url) return;
        new Audio(url).play();
    });

    // Confetti preview on account/profile. Same shape as sound-test.
    $(document).on('click', '[data-toggle="confetti-test"]', function () {
        if (!$(this).is(':checked')) return;

        var duration = 1500;
        var animationEnd = Date.now() + duration;
        var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

        function randomInRange(min, max) {
            return Math.random() * (max - min) + min;
        }

        var interval = setInterval(function () {
            var timeLeft = animationEnd - Date.now();
            if (timeLeft <= 0) {
                return clearInterval(interval);
            }
            var particleCount = 50 * (timeLeft / duration);
            confetti({
                ...defaults,
                particleCount,
                origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 },
            });
            confetti({
                ...defaults,
                particleCount,
                origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 },
            });
        }, 250);
    });

    // Live color preview for the nav-link colorpicker on account/profile.
    // The colorpicker widget itself is initialized by $(".color").colorpicker()
    // in the default layout; this just wires the changeColor listener.
    if ($('#nav-link-color').length) {
        $('#nav-link-color').on('changeColor', function (e) {
            var color = e.color.toString('rgba');
            $('.navbar-nav > li > a:link').attr('style', 'color: ' + color + ' !important');
            $('.btn-theme').attr('style', 'color: ' + color + ' !important');
        });
    }

    // Reset the localStorage theme override when the user clicks the
    // "system default" link (any element carrying data-theme-toggle-clear).
    document.querySelectorAll('[data-theme-toggle-clear]').forEach(function (el) {
        el.addEventListener('click', function () {
            localStorage.removeItem('theme');
        });
    });

    // Master checkbox → target field disabled state. Callers pair a
    // <input type="checkbox" data-toggle="disable-when-unchecked"
    // data-disable-target="#some-field"> with a target rendered
    // server-side with the matching @disabled state (avoids FOUC).
    // Handler keeps them in sync on change.
    $(document).on('change', '[data-toggle="disable-when-unchecked"]', function () {
        var target = $(this).data('disable-target');
        if (target) {
            $(target).prop('disabled', !$(this).is(':checked'));
        }
    });

    // Disable empty REQUIRED inputs on submit so browser HTML5 validation
    // doesn't block the request before Laravel's form-request validator
    // gets a chance to return a nicer error. Non-required empties (like a
    // "Do not change" select with an explicit value="" option) are left
    // enabled so they submit their intentional empty value. Opt in per
    // form with data-disable-empty-on-submit.
    $(document).on('submit', 'form[data-disable-empty-on-submit]', function () {
        $(this).find(':input[required]').filter(function () { return !this.value; }).attr('disabled', 'disabled');
    });

    // Master checkbox → toggle every non-disabled checkbox in the closest
    // form or table (or a caller-specified selector via data-check-scope).
    // Used by bulk-delete confirmation pages to select or deselect the
    // whole list of rows at once.
    $(document).on('change', '[data-toggle="check-all"]', function () {
        var $master = $(this);
        var scope = $master.data('check-scope');
        var $container = scope ? $(scope) : $master.closest('form, table');
        $container.find('input[type="checkbox"]').not($master).not(':disabled').prop('checked', $master.prop('checked'));
    });

    // When the "This user can login" (activated) checkbox is off, the
    // password + confirmation fields are functionally useless because
    // login is gated by the activated flag. Hide the whole form-group
    // (or dynamic-form-row in the modal) so the form doesn't show
    // fields the user can't meaningfully fill in, and also drop the
    // HTML `required` attribute so the browser doesn't block submission.
    // The server side already skips the password rule for this case
    // via SaveUserRequest::rules(), and the controller stores
    // User::noPassword() raw so no Hash::check can ever match.
    // Applies to both the main users/edit create form and the
    // users/modal form since they share the input names.
    var syncPasswordFields = function ($checkbox) {
        var $form = $checkbox.closest('form');
        var $passwords = $form.find(
            'input[name="password"], input[name="password_confirmation"]'
        );
        var visible = $checkbox.is(':checked');
        $passwords.prop('required', visible);
        $passwords.each(function () {
            var $wrap = $(this).closest('.form-group, .dynamic-form-row');
            if (visible) {
                $wrap.show();
            } else {
                $wrap.hide();
            }
        });
    };

    // Sensitive fields (username, email, password) ship with a
    // `readonly` + onfocus-removes-readonly anti-autofill trick to
    // stop password managers from prefilling or overwriting the
    // operator's own login credentials on user-create forms. The
    // side-effect is that HTML5 `required` constraint validation is
    // SILENTLY skipped for readonly inputs, so hitting submit without
    // ever focusing a required field lets the empty form through the
    // browser check entirely.
    //
    // On submit-button click we strip `readonly` from any
    // required+readonly input inside the form. The browser then runs
    // its normal constraint check (all fields participating) and
    // shows the "please fill in this field" popup on empties. Autofill
    // was already prevented at page load, so removing readonly at
    // click time doesn't reopen that hole.
    $(document).on('click', 'button[type="submit"], input[type="submit"]', function () {
        var $form = $(this).closest('form');
        if (! $form.length) {
            return;
        }
        $form.find('input[required][readonly]').each(function () {
            this.removeAttribute('readonly');
        });
    });
    $('input[name="activated"][type="checkbox"]').each(function () {
        syncPasswordFields($(this));
    });
    $(document).on('change', 'input[name="activated"][type="checkbox"]', function () {
        syncPasswordFields($(this));
    });

    // Generic "typing into input A enables checkbox B" pattern. Server
    // marks the input with data-toggles-checkbox="{selector-of-target}".
    // Threshold is 6 chars, which matches the legacy user-create
    // behaviour of only enabling the send-welcome checkbox once the
    // email is plausibly valid. Server omits the data-attribute when
    // the enable-side should never fire (e.g. app.lock_passwords is on)
    // so the target stays permanently disabled.
    $(document).on('keyup', 'input[data-toggles-checkbox]', function () {
        var $target = $($(this).data('toggles-checkbox'));
        if (! $target.length) {
            return;
        }
        if (this.value.length > 5) {
            $target.prop('disabled', false);
            $target.closest('.form-control').removeClass('form-control--disabled');
        } else {
            $target.prop('disabled', true).prop('checked', false);
            $target.closest('.form-control').addClass('form-control--disabled');
        }
    });

    // Bootstrap tooltips on any element carrying .tooltip-base.
    // Attaching to body avoids clipping inside overflow-hidden panels.
    $('.tooltip-base').tooltip({ container: 'body' });

    // Password generator button. Server puts the desired length on the
    // button as data-password-length (typically pwd_secure_min + 9) so
    // this JS doesn't have to know about app settings. Falls back to 16
    // if the attribute is missing.
    $('a[id="genPassword"], button[id="genPassword"]').each(function () {
        var $btn = $(this);
        if (typeof $btn.pGenerator !== 'function' || ! $('#password').length) {
            return;
        }
        $btn.pGenerator({
            bind: 'click',
            passwordElement: '#password',
            passwordLength: parseInt($btn.data('password-length') || '16', 10),
            uppercase: true,
            lowercase: true,
            numbers: true,
            specialChars: true,
            onPasswordGenerated: function () {
                $('#password_confirm').val($('#password').val());
            },
        });
    });

    // A <select data-gates-submit> disables the submit button(s) in its
    // form until a value is chosen. Used by users/confirm-bulk-delete
    // where the operator must pick a status for the deleted users' assets
    // before the form can be submitted. Runs once on load to reflect
    // whatever value was pre-selected (old input after a validation
    // redirect) and re-syncs on change and on select2's own event.
    $('select[data-gates-submit]').each(function () {
        var $select = $(this);
        var $submits = $select.closest('form').find(':submit');
        var sync = function () {
            $submits.prop('disabled', ! $select.val());
        };
        sync();
        $select.on('change select2:select', sync);
    });

    // Auto-focus the first select2 search input on pages that ask for it.
    // Bulk-checkout uses this so the operator lands directly on the
    // assets-to-checkout picker and can start typing immediately. Results
    // are hidden until the first keystroke so the operator doesn't see a
    // full-list flash on open.
    if ($('[data-autofocus-select2-search]').length) {
        setTimeout(function () {
            var $searchField = $('.select2-search__field');
            var $results = $('.select2-results');
            $searchField.focus();
            $results.hide();
            $searchField.on('input', function () {
                $results.show();
            });
        }, 0);
    }
});
