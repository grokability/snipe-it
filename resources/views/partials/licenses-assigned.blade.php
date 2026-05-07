<script nonce="{{ csrf_token() }}">
    let canViewKeys = @json(Gate::check('viewKeys', $license));
    // create the assigned licenses listing box for the right side of the screen
    $(function() {
        $('#assigned_user').on("change",function () {
            var userid = $('#assigned_user option:selected').val();

            if(userid=='') {
                console.warn('no user selected');
                $('#current_license_box').fadeOut();
                $('#current_license_content').html("");
            } else {

                $.ajax({
                    type: 'GET',
                    url: '{{ config('app.url') }}/api/v1/users/' + userid + '/licenses',
                    headers: {
                        "X-Requested-With": 'XMLHttpRequest',
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },

                    dataType: 'json',
                    success: function (data) {
                        $('#current_license_box').fadeIn();

                        var table_html = '<div class="row">';
                        table_html += '<div class="col-md-12">';
                        table_html += '<table class="table table-striped">';
                        table_html += '<thead><tr>';
                        table_html += '<th>{{ trans('admin/licenses/form.name') }}</th>';
                        table_html += '<th>{{ trans('admin/licenses/form.license_key') }}</th>';
                        table_html += '</tr></thead><tbody>';

                        $('#current_license_content').append('');

                        if (data.rows.length > 0) {

                            for (var i in data.rows) {
                                var license = data.rows[i];
                                table_html += '<tr>';
                                table_html += '<td><a href="{{ config('app.url') }}/licenses/' + license.id + '">';

                                if ((license.name == '') && (license.name != null)) {
                                    table_html += " " + license.name;
                                } else {
                                    table_html += license.name;
                                }
                                    table_html += '</a></td>';
                                if (canViewKeys) {
                                    table_html += '<td>' + license.product_key + '</td>';
                                }
                                    table_html += "</tr>";
                            }
                        } else {
                            table_html += '<tr><td colspan="4">{{ trans('admin/users/message.user_has_no_assets_assigned') }}</td></tr>';
                        }
                        $('#current_license_content').html(table_html + '</tbody></table></div></div>');

                    },
                    error: function (data) {
                        $('#current_license_box').fadeOut();
                    }
                });
            }
        });
    });
</script>
<?php
