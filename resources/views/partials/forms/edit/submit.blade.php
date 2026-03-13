<!-- partials/forms/edit/submit.blade.php -->

<div class="box-footer text-right" style="padding-bottom: 0px;">
    <a class="btn btn-link pull-left" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
    <button type="submit" {{$snipeSettings->shortcuts_enabled == 1 ? "accesskey=s" : ''}} class="btn btn-primary"><x-icon type="checkmark" /> {{ trans('general.save') }}</button>
</div>
<!-- / partials/forms/edit/submit.blade.php -->
