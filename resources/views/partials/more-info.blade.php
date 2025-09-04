<a style="padding-left: 5px; font-size: 15px;" class="text-dark-gray hidden-print" data-trigger="focus" tabindex="0" role="button" data-toggle="popover"  data-container="body"
   data-template="<div class='popover help-popover' role='tooltip'><div class='arrow'></div><h3 class='popover-title'></h3><div class='popover-content popover-body'></div></div>"
   title="{{ trans('general.more_info') }}" data-placement="right" data-html="true" data-content="{{ (isset($helpText)) ? $helpText : 'Help Info Missing'  }}">
    <x-icon type="more-info" style="padding-top: 9px;" />
    <span class="sr-only">{{ trans('general.moreinfo') }}</span>
</a>
