<script nonce="{{ csrf_token() }}">

    // create the assigned assets listing box for the right side of the screen
    $(function() {
        $('#assigned_user').on("change",function () {
            var userid = $('#assigned_user option:selected').val();

            if(userid=='') {
                console.warn('no user selected');
                $('#current_assets_box').fadeOut();
                $('#current_assets_content').html("");
            } else {

                $.ajax({
                    type: 'GET',
                    url: '{{ config('app.url') }}/api/v1/users/' + userid + '/assets',
                    headers: {
                        "X-Requested-With": 'XMLHttpRequest',
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },

                    dataType: 'json',
                    success: function (data) {
                        $('#current_assets_box').fadeIn();

                        var table_html = '<div class="row">';
                        table_html += '<div class="col-md-12">';
                        table_html += '<table class="table table-striped">';
                        table_html += '<thead><tr>';
                        table_html += '<th></th>';
                        table_html += '<th>{{ trans('admin/hardware/form.name') }}</th>';
                        table_html += '<th>{{ trans('admin/hardware/form.tag') }}</th>';
                        table_html += '<th>{{ trans('admin/hardware/form.serial') }}</th>';
                        table_html += '</tr></thead><tbody>';

                        $('#current_assets_content').append('');

                        if (data.rows.length > 0) {

                            for (var i in data.rows) {
                                var asset = data.rows[i];
                                table_html += '<tr>';
                                if (asset.image != null) {
                                    table_html += '<td class="col-md-1"><a href="' + asset.image + '" data-toggle="lightbox" data-type="image"><img src="' + asset.image + '" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;"></a></td>';
                                } else {
                                    table_html += "<td></td> ";
                                }
                                table_html += '<td><a href="{{ config('app.url') }}/hardware/' + asset.id + '">';

                                if ((asset.name == '') && (asset.name != null)) {
                                    table_html += " " + asset.model.name;
                                } else {
                                    table_html += asset.name;
                                    table_html += " (" + asset.model.name + ")";
                                }

                                table_html += '</a></td>';
                                table_html += '<td class="col-md-4">' + asset.asset_tag + '</td>';
                                table_html += '<td class="col-md-4">' + asset.serial + '</td>';
                                table_html += "</tr>";
                            }
                        } else {
                            table_html += '<tr><td colspan="4">{{ trans('admin/users/message.user_has_no_assets_assigned') }}</td></tr>';
                        }
                        $('#current_assets_content').html(table_html + '</tbody></table></div></div>');

                    },
                    error: function (data) {
                        $('#current_assets_box').fadeOut();
                    }
                });
            }
        });
    });
</script>
