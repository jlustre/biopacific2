@component('mail::message')
# Scheduled Report Executed

**Report:** {{ $reportName }} (ID: {{ $reportId }})

**Run At:** {{ $runAt }}

@if(!empty($parameters))
**Parameters:**
@foreach($parameters as $key => $value)
- {{ $key }}: {{ $value }}
@endforeach
@endif

@if(!empty($resultSummary))
**Result Summary:**
{{ $resultSummary }}
@endif

Thank you,
BioPacific Admin
@endcomponent
