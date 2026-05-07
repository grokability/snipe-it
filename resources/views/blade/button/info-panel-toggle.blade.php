@props([
	'hideOnXs' => false,
])

<button
	type="button"
	id="expand-info-panel-button"
	data-tooltip="true"
	title="{{ trans('button.show_hide_info') }}"
	aria-label="{{ trans('button.show_hide_info') }}"
	style="background: none; border: 0; padding: 0;"
	{{ $attributes->class([
		'fa-regular',
		'fa-2x',
		'fa-square-caret-right',
		'pull-right',
		'hidden-xs' => $hideOnXs,
	]) }}
></button>
