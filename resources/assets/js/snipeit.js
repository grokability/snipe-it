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
require('bootstrap-datepicker')
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

    // confirm delete modal
    $el.on('click', '.delete-asset', function (evnt) {
        var $context = $(this);
        var $dataConfirmModal = $('#dataConfirmModal');
        var href = $context.attr('href');
        var message = $context.attr('data-content');
        var headericon = $context.attr('data-icon');
        var title = $context.attr('data-title');

        // deleteForm is the ID of the modal form itself
        $('#deleteForm').attr('action', href);
        $dataConfirmModal.find('.modal-header-icon').addClass(headericon);
        $dataConfirmModal.find('.modal-title').text(title).prepend('<i class="fa ' + headericon + '"></i> ');
        $dataConfirmModal.find('.modal-body').text(message);
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
                        assetStatusType: link.data("asset-status-type"),
                        companyId: link.data("company-id"),
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
			var assetStatusType = element.data("asset-status-type");
			$.ajax({
				url: baseUrl + 'api/v1/' + endpoint + '/selectlist?search='+value+'&page=1' + (assetStatusType ? '&assetStatusType='+assetStatusType : ''),
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
        // console.warn("What in the hell is going on with Select2?!?!!?!?");
        // console.warn($.select2);
        if (datalist.loading) {
            return $('<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Loading...');
        }

        var root_div = $("<div class='clearfix'>") ;
        var left_pull = $("<div class='pull-left' style='padding-right: 10px;'>");
        if (datalist.image) {
            var inner_div = $("<div style='width: 30px;'>");
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
            var img = $("<img src='' style='max-height: 20px; max-width: 30px;' alt=''>");
            // console.warn("Img is: ");
            // console.dir(img);
            // console.warn("Strigularly, that's: ");
            // console.log(img);
            img.attr("src", datalist.image );
            inner_div.append(img)
        } else {
            var inner_div=$("<div style='height: 20px; width: 30px;'></div>");
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
        $('input[name=checkout_to_type]').on("change",function () {
            var assignto_type = $('input[name=checkout_to_type]:checked').val();
            var userid = $('#assigned_user option:selected').val();

            if (assignto_type == 'asset') {
                $('#current_assets_box').fadeOut();
                $('#assigned_asset').show();
                $('#assigned_user').hide();
                $('#assigned_location').hide();
                $('.notification-callout').fadeOut();

                $('[name="assigned_location"]').val('').trigger('change.select2');
                $('[name="assigned_user"]').val('').trigger('change.select2');

            } else if (assignto_type == 'location') {
                $('#current_assets_box').fadeOut();
                $('#assigned_asset').hide();
                $('#assigned_user').hide();
                $('#assigned_location').show();
                $('.notification-callout').fadeOut();

                $('[name="assigned_asset"]').val('').trigger('change.select2');
                $('[name="assigned_user"]').val('').trigger('change.select2');
            } else  {

                $('#assigned_asset').hide();
                $('#assigned_user').show();
                $('#assigned_location').hide();
                if (userid) {
                    $('#current_assets_box').fadeIn();
                }
                $('.notification-callout').fadeIn();

                $('[name="assigned_asset"]').val('').trigger('change.select2');
                $('[name="assigned_location"]').val('').trigger('change.select2');
            }
        });
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
        Livewire.find(target.data('livewire-component')).set(event.target.name, this.options[this.selectedIndex].value)
    });

    Livewire.hook('request', ({succeed}) => {
        succeed(() => {
            queueMicrotask(() => {
                $('.livewire-select2').select2();
            });
        });
    });
});
