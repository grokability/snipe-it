@extends('custom::layouts.fom')

@section('title0')
    {{ __('custom::fom.si_title') }} — {{ $part->name }}
@stop

@section('title')
    @yield('title0') @parent
@stop

@section('header_right')
    <a href="{{ route('fom.spare-parts.show', $part->id) }}" class="btn btn-default pull-right">
        <i class="fas fa-arrow-left"></i> {{ __('custom::fom.sp_btn_new') }}
    </a>
@stop

@section('content')

    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-plus-circle"></i> {{ __('custom::fom.si_heading') }}</h3>
                </div>
                <form method="POST" action="{{ route('fom.spare-parts.stock-in.store', $part->id) }}">
                    @csrf
                    <div class="box-body">
                        <div class="well">
                            <strong>{{ $part->name }}</strong> — <code>{{ $part->part_number }}</code>
                            <br>{{ __('custom::fom.si_current') }}: <strong>{{ $part->quantity_on_hand }}</strong> | {{ __('custom::fom.si_minimum') }}: {{ $part->minimum_quantity }}
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul style="margin-bottom: 0;">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="quantity">{{ __('custom::fom.si_qty_label') }} <span class="text-red">*</span></label>
                            <input type="number" name="quantity" id="quantity" class="form-control" min="1" required
                                   value="{{ old('quantity') }}" style="max-width: 200px; font-size: 18px;">
                        </div>

                        <div class="form-group">
                            <label for="notes">{{ __('custom::fom.si_notes_label') }}</label>
                            <input type="text" name="notes" id="notes" class="form-control"
                                   placeholder="{{ __('custom::fom.si_notes_placeholder') }}" value="{{ old('notes') }}">
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check"></i> {{ __('custom::fom.si_btn_submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop
