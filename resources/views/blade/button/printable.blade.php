@props([
    'item'  => null,
    'wide'  => false,
])

@if ($item && $item->deleted_at == '' && $item->model && $item->model->category)
    @php
        $printables = $item->model->category->printables ?? collect();
    @endphp

    @if ($printables->isNotEmpty())
        <div class="btn-group{{ $wide == 'true' ? ' btn-block' : '' }}" style="display: inline-block; margin-right: 2px;">
            <button type="button"
                    class="btn btn-sm btn-default dropdown-toggle hidden-print{{ $wide == 'true' ? ' btn-block btn-social' : '' }}"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                    data-tooltip="true"
                    title="{{ trans_choice('button.generate_printable', 1) }}">
                <x-icon type="assets" class="fa-fw"/>
                @if ($wide == 'true')
                    {{ trans_choice('button.generate_printable', 1) }}
                @endif
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
                @foreach ($printables as $printable)
                    <li>
                        <a href="{{ route('hardware.printable.show', ['asset' => $item->id, 'printable' => $printable->id]) }}" target="_blank">
                            {{ $printable->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endif
