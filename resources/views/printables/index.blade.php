@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.printables') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-box>
            <x-slot:header>
                @can('create', App\Models\Printable::class)
                    <a href="{{ route('printables.create') }}" class="btn btn-primary btn-sm pull-right">
                        <x-icon type="add" class="fa-fw"/>
                        {{ trans('admin/printables/general.create') }}
                    </a>
                @endcan
            </x-slot:header>

            @if ($printables->isEmpty())
                <p class="text-muted">{{ trans('admin/printables/general.no_printables') }}</p>
            @else
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ trans('admin/printables/general.printable_name') }}</th>
                            <th>{{ trans('admin/printables/general.assigned_categories') }}</th>
                            <th>{{ trans('admin/printables/general.created_by') }}</th>
                            <th>{{ trans('general.date') }}</th>
                            <th class="text-right">{{ trans('table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($printables as $printable)
                            <tr>
                                <td>{{ $printable->name }}</td>
                                <td>
                                    @forelse ($printable->categories as $category)
                                        <span class="label label-default">{{ $category->name }}</span>
                                    @empty
                                        <span class="text-muted">{{ trans('general.none') }}</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if ($printable->creator)
                                        {!! $printable->creator->present()->formattedNameLink() !!}
                                    @else
                                        <span class="text-muted">{{ trans('general.na') }}</span>
                                    @endif
                                </td>
                                <td>{{ Helper::getFormattedDateObject($printable->created_at, 'datetime', false) }}</td>
                                <td class="text-right" style="white-space: nowrap;">
                                    @can('update', App\Models\Printable::class)
                                        <a href="{{ route('printables.edit', $printable->id) }}" class="btn btn-sm btn-warning">
                                            <x-icon type="edit" class="fa-fw"/>
                                            {{ trans('button.edit') }}
                                        </a>
                                    @endcan
                                    @can('delete', App\Models\Printable::class)
                                        <form method="POST" action="{{ route('printables.destroy', $printable->id) }}" style="display: inline;" onsubmit="return confirm('{{ trans('admin/printables/message.delete.confirm') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <x-icon type="delete" class="fa-fw"/>
                                                {{ trans('button.delete') }}
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="text-center">
                    {{ $printables->links() }}
                </div>
            @endif
        </x-box>
    </x-container>
@stop
