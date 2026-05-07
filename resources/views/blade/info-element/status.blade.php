@props([
    'infoObject',
])

@if (($infoObject) && ($infoObject->status))

    @if ($infoObject->assignedTo)
        <x-icon type="circle-solid" class="text-blue"/>
        {{ $infoObject->status->name }}
        <label class="label label-default">{{ trans('general.deployed') }}</label>
        <x-icon type="long-arrow-right"/>
        <x-icon type="{{ $infoObject->assignedType() }}" class="fa-fw"/>
        {!!  $infoObject->assignedTo->present()->nameUrl() !!}
    @elseif ($infoObject->hasOrphanedAssignment())
        <x-icon type="circle-solid" class="text-blue"/>
        {{ $infoObject->status->name }}
        <label class="label label-default">{{ trans('general.deployed') }}</label>
        <x-icon type="long-arrow-right"/>
        <span class="text-danger">
            <x-icon type="{{ $infoObject->assignedType() }}"/>
            <x-icon type="x"/>
            {{ trans('general.deleted') }}
        </span>
    @else
        @if (($infoObject->status) && ($infoObject->status->deployable=='1'))
            <x-icon type="circle-solid" class="text-green"/>
        @elseif (($infoObject->status) && ($infoObject->status->pending=='1'))
            <x-icon type="circle-solid" class="text-orange"/>
        @else
            <x-icon type="x" class="text-red"/>
        @endif
        <a href="{{ route('statuslabels.show', $infoObject->status->id) }}">
            {{ $infoObject->status->name }}</a>
        <label class="label label-default">{{ $infoObject->present()->statusMeta }}</label>

    @endif
@endif
