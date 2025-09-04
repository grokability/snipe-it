/* 
 * 
 * Snipe-IT Universal Modal support
 * 
 * Enables modal dialogs to create sub-resources throughout Snipe-IT
 * 
 */

 /* 
 HOW TO USE

 Create a Button looking like this:

 <a href='{{ route('modal.show', 'user') }}' data-toggle="modal"  data-target="#createModal" data-select='assigned_to' class="btn btn-sm btn-primary">New</a>

 If you don't have access to Blade commands (like {{ and }}, etc), you can hard-code a URL as the 'href'

 data-toggle="modal" - required for Bootstrap Modals
 data-target="#createModal" - fixed ID for the modal, do not change
 data-select="assigned_to" - What is the *ID* of the select-dropdown that you're going to be adding to, if the modal-create was a success? Be on the lookout for duplicate ID's, it will confuse this library!
 class="btn btn-sm btn-primary" - makes it look button-ey, feel free to change :)
 
 If you want to pass additional variables to the modal (In the Category Create one, for example, you can pass category_id), you can encode them as URL variables in the href
 
 */

$(function () {

  var baseUrl = $('meta[name="baseUrl"]').attr('content');
  //handle modal-add-interstitial calls
  var model, select, refreshSelector;

  if($('#createModal').length == 0) {
    $('body').append('<div class="modal fade" id="createModal"></div><!-- /.modal -->');
  }

  $('#createModal').on("show.bs.modal", function (event) {
      var link = $(event.relatedTarget);
      model = link.data("dependency");
      select = link.data("select");
      refreshSelector = link.data("refresh");

      $('#createModal').load(link.attr('href'),function () {

          // this sets the focus to be the name field
          $('#modal-name').focus();
          
        //do we need to re-select2 this, after load? Probably.
        $('#createModal').find('select.select2').select2();
        // Initialize the ajaxy select2 with images.
        // This is a copy/paste of the code from snipeit.js, would be great to only have this in one place.

        $('.js-data-ajax').each( function (i,item) {
            var link = $(item);
            var endpoint = link.data("endpoint");
            var select = link.data("select");

            link.select2({
                ajax: {

                    // the baseUrl includes a trailing slash
                    url: baseUrl + 'api/v1/' + endpoint + '/selectlist', //WARNING - we're hoping that's defined on the page somewhere...
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
                        };
                        return data;
                    },
                    /*processResults: function (data, params) {

                        params.page = params.page || 1;

                        var answer =  {
                            results: data.items,
                            pagination: {
                                more: data.pagination.more
                            }
                        };

                        return answer;
                    },*/
                    cache: true
                },
                //escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
                templateResult: formatDatalistSafe,
                //templateSelection: formatDataSelection
            });
        });
      });

  });

 

  $('#createModal').on('click','#modal-save', function () {
    $.ajax({
        type: 'POST',
        url: $('.modal-body form').attr('action'),
        headers: {
            "X-Requested-With": 'XMLHttpRequest',
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
        },

        data: $('.modal-body form').serialize(),
        success: function (result) {

            if(result.status == "error") {
                var error_message="";
                for(var field in result.messages) {
                    error_message += "<li>Problem(s) with field <i><strong>" + field + "</strong></i>: " + result.messages[field];

                }
                $('#modal_error_msg').html(error_message).show();
                return false;
            }

            var id = result.payload.id;
            var name = result.payload.name || (result.payload.first_name + " " + result.payload.last_name);
            if (!id || !name) {
                console.error("Could not find resulting name or ID from modal-create. Name: " + name + ", id: " + id);
                return false;
            }
            $('#createModal').modal('hide');
            $('#createModal').html("");

            var refreshTable = $('#' + refreshSelector);

            if(refreshTable.length > 0) {
                refreshTable.bootstrapTable('refresh');
            }

            // "select" is the original drop-down menu that someone
            // clicked 'add' on to add a new 'thing'
            // this code adds the newly created object to that select
            var selector = document.getElementById(select);

            if(!selector) {
                return false;
            }

            selector.options[selector.length] = new Option(name, id);
            selector.selectedIndex = selector.length - 1;
            $(selector).trigger("change");
            if(window.fetchCustomFields) {
                fetchCustomFields();
            }

        },
        error: function (result) {
            msg = result.responseJSON.messages || result.responseJSON.error;
            $('#modal_error_msg').html("Server Error: "+msg).show();
        }



    });
  });
});

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
    if (safe_html != old_html) {
        // console.log("HTML MISMATCH: ");
        // console.log("FormatDatalistSafe: ");
        // console.dir(root_div.get(0));
        // console.log(safe_html);
        // console.log("FormatDataList: ");
        // console.log(old_html);
    }
    return root_div;

}

function formatDatalist (datalist) {
    var loading_markup = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Loading...';
    if (datalist.loading) {
        return loading_markup;
    }

    var markup = "<div class='clearfix'>" ;
    markup +="<div class='pull-left' style='padding-right: 10px;'>";
    if (datalist.image) {
        markup += "<div style='width: 30px;'><img src='" + datalist.image + "' alt='"+ datalist.tex + "' style='max-height: 20px; max-width: 30px;'></div>";
    } else {
        markup += "<div style='height: 20px; width: 30px;'></div>";
    }

    markup += "</div><div>" + datalist.text + "</div>";
    markup += "</div>";
    return markup;
}

function formatDataSelection (datalist) {
    return datalist.text.replace(/>/g, '&gt;')
        .replace(/</g, '&lt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
