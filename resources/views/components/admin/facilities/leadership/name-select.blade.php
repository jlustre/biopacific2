@props([
    'id' => null,
    'name' => null,
    'value' => '',
    'employeeOptions' => collect(),
])

@php
    use App\Services\FacilityLeadershipService;

    $options = collect($employeeOptions);
    $selected = old(str_replace(['[', ']'], ['.', ''], (string) $name), $value);
    $hasSelected = $selected !== '' && $selected !== null;
    $leadership = app(FacilityLeadershipService::class);
    $matchedValue = $hasSelected ? ($leadership->resolveEmployeeOptionValue((string) $selected, $options) ?? $selected) : '';
    $orphaned = $hasSelected && $leadership->resolveEmployeeOptionValue((string) $selected, $options) === null;
@endphp

<select @if($id) id="{{ $id }}" @endif
        @if($name) name="{{ $name }}" @endif
        {{ $attributes->merge(['class' => 'w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500']) }}>
    <option value="" @selected(! $hasSelected)>— Vacant —</option>
    @foreach($options as $option)
        @php
            $optionValue = (string) ($option['value'] ?? '');
            $optionLabel = (string) ($option['label'] ?? $optionValue);
        @endphp
        <option value="{{ $optionValue }}" @selected($hasSelected && $matchedValue === $optionValue)>{{ $optionLabel }}</option>
    @endforeach
    @if($orphaned)
        <option value="{{ $selected }}" selected>{{ $selected }} (not in roster)</option>
    @endif
</select>
