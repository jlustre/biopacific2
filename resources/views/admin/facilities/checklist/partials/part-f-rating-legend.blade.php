@php
    use App\Support\PartFPerformanceScoring;

    $legendVariant = $legendVariant ?? 'screen';
    $showOverallFooter = ! empty($showOverallFooter);
    $legendRows = PartFPerformanceScoring::ratingDescriptionLegendRows();
    $overallRatingCode = $overallRatingCode
        ?? PartFPerformanceScoring::overallRatingCode(
            $overallRatingLabel ?? null,
            isset($overallAverage) ? (float) $overallAverage : null,
        );
@endphp

@if($legendVariant === 'pdf')
<table class="expectations-legend-table" style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
    <thead>
        <tr>
            <th style="border: 1px solid #000; background: #fff; padding: 4px 6px; text-align: left; font-size: 9px; font-weight: bold;">RATING DESCRIPTION</th>
            <th style="border: 1px solid #000; background: #fff; padding: 4px 6px; text-align: center; font-size: 9px; font-weight: bold;">EXPECTATIONS</th>
            <th style="border: 1px solid #000; background: #fff; padding: 4px 6px; text-align: center; font-size: 9px; font-weight: bold; text-decoration: underline;">RATING GUIDELINES</th>
        </tr>
    </thead>
    <tbody>
        @foreach($legendRows as $row)
        <tr>
            <td style="border: 1px solid #000; padding: 4px 6px; font-size: 8px; vertical-align: top;">{{ $row['description'] }}</td>
            <td style="border: 1px solid #000; padding: 4px 6px; font-size: 9px; font-weight: bold; text-align: center; vertical-align: middle;">{{ $row['expectation'] }}</td>
            <td style="border: 1px solid #000; padding: 4px 6px; font-size: 9px; font-weight: bold; text-align: center; vertical-align: middle;">{{ $row['range'] }}</td>
        </tr>
        @endforeach
        @if($showOverallFooter)
        <tr>
            <td colspan="2" style="border: 1px solid #000; padding: 4px 6px; font-size: 8px; font-weight: bold; vertical-align: middle;">
                Overall Performance Rating. Write an E, M, or B in the shaded box &rarr;
            </td>
            <td style="border: 1px solid #000; padding: 8px 6px; font-size: 14px; font-weight: bold; text-align: center; vertical-align: middle; background: #e2e8f0;">
                {{ $overallRatingCode }}
            </td>
        </tr>
        @endif
    </tbody>
</table>
@if(! $showOverallFooter)
<p class="muted" style="margin: 0 0 8px; font-size: 8px;">
    <strong>Item ratings:</strong> E = 3 points &nbsp;|&nbsp; M = 2 points &nbsp;|&nbsp; B = 1 point
</p>
@endif
@else
<div class="overflow-hidden rounded-md border border-slate-400 bg-white text-[11px] text-slate-800 shadow-sm {{ $showOverallFooter ? '' : 'mb-4' }}">
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-white text-slate-900">
                <th class="border border-slate-900 px-3 py-1.5 text-left text-[10px] font-bold uppercase tracking-wide">Rating Description</th>
                <th class="border border-slate-900 px-3 py-1.5 text-center text-[10px] font-bold uppercase tracking-wide">Expectations</th>
                <th class="border border-slate-900 px-3 py-1.5 text-center text-[10px] font-bold uppercase tracking-wide underline">Rating Guidelines</th>
            </tr>
        </thead>
        <tbody>
            @foreach($legendRows as $row)
            <tr class="bg-white">
                <td class="border border-slate-900 px-3 py-2 align-top">{{ $row['description'] }}</td>
                <td class="border border-slate-900 px-3 py-2 text-center align-middle font-bold">{{ $row['expectation'] }}</td>
                <td class="border border-slate-900 px-3 py-2 text-center align-middle font-bold">{{ $row['range'] }}</td>
            </tr>
            @endforeach
            @if($showOverallFooter)
            <tr class="bg-white">
                <td colspan="2" class="border border-slate-900 px-3 py-2 align-middle font-bold">
                    Overall Performance Rating. Write an E, M, or B in the shaded box &rarr;
                </td>
                <td class="border border-slate-900 bg-slate-200 px-3 py-3 text-center align-middle">
                    <span id="partFOverallRatingCode" class="text-2xl font-bold leading-none text-slate-900">{{ $overallRatingCode }}</span>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
    @if(! $showOverallFooter)
    <p class="border-t border-slate-300 bg-slate-50 px-3 py-2 text-[10px] text-slate-700">
        <span class="font-semibold text-slate-900">Item ratings:</span>
        E = 3 points &nbsp;&nbsp; M = 2 points &nbsp;&nbsp; B = 1 point
    </p>
    @endif
</div>
@endif
