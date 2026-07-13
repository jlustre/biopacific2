@php
    use App\Support\ContentVisibility;

    $visibilityField = $visibilityField ?? 'visibility';
    $visibilityValue = old($visibilityField, $visibilityValue ?? ContentVisibility::BOTH);
    $visibilityId = $visibilityId ?? $visibilityField;
    $visibilityHelp = $visibilityHelp ?? 'Choose where this item should appear.';
@endphp
<div class="{{ $visibilityWrapperClass ?? 'mb-6' }}">
    <label for="{{ $visibilityId }}" class="{{ $visibilityLabelClass ?? 'block text-sm font-medium mb-1' }}">
        Show on
    </label>
    <select name="{{ $visibilityField }}"
            id="{{ $visibilityId }}"
            class="{{ $visibilitySelectClass ?? 'w-full border border-gray-700 rounded px-2 py-1' }}"
            required>
        @foreach(ContentVisibility::options() as $value => $label)
            <option value="{{ $value }}" @selected($visibilityValue === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <p class="{{ $visibilityHelpClass ?? 'mt-1 text-xs text-gray-500' }}">{{ $visibilityHelp }}</p>
    @error($visibilityField)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
