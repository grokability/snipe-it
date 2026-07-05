@component('mail::message')
# {{ $dryRun ? '[Dry Run] ' : '' }}Bulk Check-in/Delete Report

**Run by:** {{ $admin->first_name }} {{ $admin->last_name }} ({{ $admin->username }})<br>
**Date:** {{ $runAt->format('Y-m-d H:i:s') }}<br>
**Mode:** {{ $dryRun ? 'Dry run (no changes made)' : 'Live run' }}<br>
**Delete type:** {{ ucfirst($deleteType) }}<br>
**Companies:** {{ implode(', ', $companyNames) }}<br>
**Item types:** {{ implode(', ', $selectedTypes) }}

---

@if(count($reportLines) > 0)
## Actions {{ $dryRun ? 'That Would Have Been ' : '' }}Taken

@foreach($reportLines as $line)
- {{ $line }}
@endforeach
@else
No actions were {{ $dryRun ? 'identified' : 'taken' }}.
@endif

@endcomponent
