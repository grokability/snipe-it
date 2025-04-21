@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.audit') }}{{ $location->name }}
    @parent
@stop

{{-- Page content --}}
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>{{ trans('general.assets_checked_out_count') }}</h1>
            <div class="panel panel-default">

                <div class="panel-body">
                    <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans('general.type') }}</th>
                            <th>{{ trans('general.manufacturer') }}</th>
                            <th>{{ trans('general.serial_number') }}</th>
                            <th>{{ trans('general.status') }}</th>
                            <th>{{ trans('general.checked_out') }}?</th>
                        </tr>
                    </thead>
                        <tbody>
                            @forelse($assets as $asset)
                            <td>
                            <tr>
                                <td>{{ $asset->model->category->name ?? 'N/A' }}</td>
                                <td>{{($asset->model->manufacturer) ? $asset->model->manufacturer->name : ''}} {{($asset->model) ? $asset->model->model_number : ''}}</td>
                                <td>{{ $asset->serial ?? 'N/A' }}</td>
                                <td>{{ $asset->assetstatus->name ?? 'N/A' }}</td>
                                <td class="text-center">
                            <!-- Clickable checkbox; default is checked -->
                            <input type="checkbox" name="asset_ids[]" value="{{ $asset->id }}" checked>
                            </td>
                            <!-- Clickable checkbox; default is checked -->
                                <td>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{ trans('general.bad_data') }}</td>
                                    </tr>
                                @endforelse
                            </td>
                        </tbody>
                    </table>
                            <!-- Email Body Section -->
        <div class="form-group">
            <label for="email-body">{{ trans('general.notes') }}</label>
            
            <label for="audit_user_email">{{ $auditUser->email }}</label>
            <textarea id="email-body" name="email_body" class="form-control" rows="6"></textarea>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">{{ trans('general.send_email') }}</button>
    </form>
</div>
                </div><!-- /.panel-body -->
            </div><!-- /.panel -->
        </div><!-- /.col-md-12 -->
    </div><!-- /.row -->
</div><!-- /.container -->
@stop


@section('scripts')
    <!-- Include Summernote CSS and JS (after Bootstrap JS and jQuery) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Summernote
            $('#email-body').summernote({
                placeholder: '{{ trans("general.email_body_placeholder") }}',
                tabsize: 2,
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
            
            // On form submit, gather details from the checked asset rows and append them to the email body.
            $('form').submit(function(e) {
                var assetDetails = "<ul>";
                // Iterate over each checked checkbox
                $('input[name="asset_ids[]"]:checked').each(function() {
                    var $row = $(this).closest('tr');
                    var type           = $row.find('td:nth-child(1)').text().trim();
                    var manufacturer   = $row.find('td:nth-child(2)').text().trim();
                    var modelNumber    = $row.find('td:nth-child(3)').text().trim();
                    var serialNumber   = $row.find('td:nth-child(4)').text().trim();
                    var status         = $row.find('td:nth-child(5)').text().trim();
                    
                    assetDetails += "<li>" + 
                        "<strong>" + type + "</strong> - " + 
                        manufacturer + " - " + 
                        modelNumber + " - " + 
                        serialNumber + " - " + 
                        status + 
                        "</li>";
                });
                assetDetails += "</ul>";
                
                // Append the asset details to the content of Summernote editor.
                // This ensures that the e-mail body includes the list of assets that are present.
                var currentContent = $('#email-body').summernote('code');
                $('#email-body').summernote('code', currentContent + assetDetails);
                
                return true; // Continue with form submission
            });
        });
    </script>
@endsection