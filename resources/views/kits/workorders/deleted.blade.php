@extends('layouts.default')

@section('title') Deleted Work Orders @endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-danger">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fas fa-trash"></i> Deleted Work Orders (Soft-Deleted)</h3>
        <div class="box-tools pull-right">
          <a href="{{ route('maintenance.workorders.index') }}" class="btn btn-sm btn-default"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
      </div>
      <div class="box-body">
        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i> These work orders are soft-deleted and can be restored. Use the restore button to bring them back.
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>WO #</th>
                <th>Title</th>
                <th>Asset</th>
                <th>Status Before Delete</th>
                <th>Deleted At</th>
                <th>Assigned To</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($workOrders as $wo)
                <tr>
                  <td>{{ $wo->work_order_number }}</td>
                  <td>{{ $wo->title }}</td>
                  <td>{{ $wo->asset->name ?? 'N/A' }}</td>
                  <td><span class="label label-default">{{ ucfirst(str_replace('_',' ',$wo->status)) }}</span></td>
                  <td>{{ $wo->deleted_at? $wo->deleted_at->format('Y-m-d H:i') : '' }}</td>
                  <td>{{ $wo->assignedUser? $wo->assignedUser->first_name.' '.$wo->assignedUser->last_name : 'Unassigned' }}</td>
                  <td>
                    <form method="POST" action="{{ route('maintenance.workorders.restore',$wo->id) }}" style="display:inline;">
                      @csrf
                      <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Restore this work order?');">
                        <i class="fas fa-undo"></i> Restore
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td colspan="7" class="text-center">No deleted work orders.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="text-center">{{ $workOrders->links() }}</div>
      </div>
    </div>
  </div>
</div>
@endsection