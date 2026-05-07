@props([
    'manufacturer' => null,
    'asset' => null,
])



@if ($manufacturer)
    <x-info-element icon_type="manufacturer" title="{{ trans('general.manufacturer') }}" icon_color="{{ $manufacturer->tag_color }}">
        {!!  $manufacturer->present()->nameUrl !!}
        <a class="pull-right js-copy-link" style="font-size: 16px; margin-right: 3px;" type="button" data-toggle="collapse" data-target="#manufacturerContact" aria-expanded="false" aria-controls="manufacturerContact">
            <x-icon type="plus" class="fa-fw"/>
        </a>
    </x-info-element>

    <span class="collapse" id="manufacturerContact">

        <x-info-element class="subitem well well-sm">
            <p style="line-height: 25px;">

                @if($manufacturer->support_phone)
                    <x-icon type="phone" class="fa-fw"/>
                    <x-info-element.phone>
                    {{ $manufacturer->support_phone }}
                    </x-info-element.phone>
                    <br>
                @endif

                @if($manufacturer->support_email)
                    <x-icon type="email" class="fa-fw"/>
                    <x-info-element.email>
                    {{ $manufacturer->support_email }}
                    </x-info-element.email>
                    <br>
                @endif


                @if(($asset) && ($manufacturer->warranty_lookup_url))
                    <x-icon type="external-link" class="fa-fw"/>
                    <x-info-element.url>
                        {{ $asset->present()->dynamicUrl($asset->manufacturer->warranty_lookup_url) }}
                    </x-info-element.url>
                    <br>
                @endif

                @if($manufacturer->url)
                    <x-icon type="external-link" class="fa-fw"/>
                    <x-info-element.url>
                        {{ $manufacturer->url }}
                    </x-info-element.url>
                    <br>
                @endif

                @if($manufacturer->support_url)
                    <x-icon type="external-link" class="fa-fw"/>
                    <x-info-element.url>
                        {{ $manufacturer->support_url }}
                    </x-info-element.url>
                    <br>
                @endif


                {!! nl2br($manufacturer->present()->displayAddress) !!}
            </p>
        </x-info-element>
            </span>

@endif
