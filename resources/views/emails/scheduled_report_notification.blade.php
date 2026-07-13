@component('mail::message')
# Scheduled Report Executed

**Report:** {{ $reportName }} (ID: {{ $reportId }})

**Run At:** {{ $runAt }}

@if(!empty($parameters))
**Parameters:**
@foreach($parameters as $key => $value)
- {{ $key }}: {{ is_array($value) ? json_encode($value) : $value }}
@endforeach
@endif

@if(!empty($resultSummary))
**Result Summary:**
{{ $resultSummary }}
@endif

The generated report file is attached when available.

Thank you,
BioPacific Admin
@endcomponent
