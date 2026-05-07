@props([
    'infoPanelObj' => null,
])


@if ($infoPanelObj->supplier)
    <x-info-element icon_type="supplier" icon_color="{{ $infoPanelObj->supplier->tag_color }}" title="{{ trans('general.supplier') }}">
        {!!  $infoPanelObj->supplier->present()->nameUrl !!}
        <a class="pull-right js-copy-link" style="font-size: 16px; margin-right: 3px;" type="button" data-toggle="collapse" data-target="#supplierContact" aria-expanded="false" aria-controls="supplierContact">
            <x-icon type="plus" class="fa-fw"/>
        </a>
    </x-info-element>

    <span class="collapse" id="supplierContact">

        <x-info-element class="subitem well well-sm">
            <p style="line-height: 25px;">

                @if($infoPanelObj->supplier->contact)
                    <x-icon type="contact-card" class="fa-fw"/>
                    {{ $infoPanelObj->supplier->contact }}
                    <br>
                @endif

                    @if($infoPanelObj->supplier->phone)
                        <x-icon type="phone" class="fa-fw"/>
                        <x-info-element.phone>
                    {{ $infoPanelObj->supplier->phone }}
                    </x-info-element.phone>
                        <br>
                    @endif

                    @if($infoPanelObj->supplier->email)
                        <x-icon type="email" class="fa-fw"/>
                        <x-info-element.email>
                    {{ $infoPanelObj->supplier->email }}
                    </x-info-element.email>
                        <br>
                    @endif

                    @if($infoPanelObj->supplier->url)
                        <x-icon type="external-link" class="fa-fw"/>
                        <x-info-element.url>
                    {{ $infoPanelObj->supplier->url }}
                    </x-info-element.url>
                        <br>
                    @endif


                {!! nl2br($infoPanelObj->supplier->present()->displayAddress) !!}
            </p>
        </x-info-element>
            </span>

@endif