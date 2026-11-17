@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.merge_models') }}
    @parent
@stop

@section('header_right')
    <a href="{{ URL::previous() }}" class="btn btn-primary pull-right">
        {{ trans('general.back') }}</a>
@stop

{{-- Page content --}}
@section('content')
    <x-container class="col-md-8 col-md-offset-2">
        <p>{{ trans('admin/models/message.merge.information', ['count' => count($models)]) }}</p>

        <x-callout type="danger" icon="warning" live="assertive">
            {{ trans('admin/models/message.merge.warning') }}
        </x-callout>

        <x-demo-callout />

        <x-form route="{{ route('models.merge.save') }}">
            <x-box>
                <x-slot:header>
                    {{ trans('admin/models/message.merge.pick_target') }}
                </x-slot:header>

                <table class="table table-striped table-condensed">
                    <thead>
                        <tr>
                            <th scope="col" class="col-md-3">{{ trans('general.name') }}</th>
                            <th scope="col" class="col-md-2">{{ trans('general.manufacturer') }}</th>
                            <th scope="col" class="col-md-2">{{ trans('general.category') }}</th>
                            <th scope="col" class="col-md-3">{{ trans('admin/custom_fields/general.fieldset') }}</th>
                            <th scope="col" class="col-md-2 text-right">
                                <i class="fas fa-barcode fa-fw" aria-hidden="true" style="font-size: 17px;"></i>
                                <span class="sr-only">{{ trans('general.assets') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($models as $model)
                            <tr>
                                <td>
                                    <label class="form-control" for="model_{{ $model->id }}">
                                        <input type="radio" name="merge_into_id" id="model_{{ $model->id }}" value="{{ $model->id }}">
                                        {{ $model->name }}
                                    </label>
                                </td>
                                <td>{{ $model->manufacturer?->name }}</td>
                                <td>{{ $model->category?->name }}</td>
                                <td>
                                    @if ($fieldsets->has($model->fieldset_id))
                                        <a href="{{ route('fieldsets.show', $model->fieldset_id) }}">{{ $fieldsets->get($model->fieldset_id)->name }}</a>
                                    @endif
                                </td>
                                <td class="text-right">{{ number_format($model->assets_count) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @foreach ($models as $model)
                    <input type="hidden" name="ids_to_merge[]" value="{{ $model->id }}">
                @endforeach

                <x-slot:customfooter>
                    <div class="box-footer text-right">
                        <a class="btn btn-link pull-left" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
                        <button type="submit" class="btn btn-success" id="submit-button" disabled>
                            <x-icon type="merge" /> {{ trans('general.merge_models') }}
                        </button>
                    </div>
                </x-slot:customfooter>
            </x-box>
        </x-form>
    </x-container>
@stop

@section('moar_scripts')
    <script>
        $("input[type='radio']").on('change', function () {
            $('#submit-button').prop('disabled', false).removeAttr('disabled');
        });
    </script>
@stop
