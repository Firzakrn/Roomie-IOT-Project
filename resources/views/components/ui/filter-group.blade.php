@props([
    'legend',
    'name',
    'options',
    'value' => 'all',
])

{{--
    Segmented filter control.

    Built from real <input type="radio"> + <label>, not styled divs, so it is
    keyboard reachable and announces its selected state.

    The checked look is driven by `data-checked` in ui/tokens rather than
    Tailwind's `peer-checked`: the peer variant matches every LATER sibling,
    which in a group of several radios paints all options as selected.

    Every group participates in the same AND-combined filter the dashboard
    applies, and the script keeps `data-checked` in sync with the radio.
--}}

@php
    $id = 'filter-'.$name;
@endphp

<fieldset class="min-w-0">
    <legend class="sr-only">{{ $legend }}</legend>

    <div class="inline-flex max-w-full flex-wrap gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-tile dark:border-slate-700 dark:bg-roomie-surface"
         data-filter-group data-filter-name="{{ $name }}">
        @foreach ($options as $option)
            @php
                $optionValue = $option[0];
                $optionLabel = $option[1];
                $optionId = $id.'-'.($optionValue === 'all' ? 'all' : \Illuminate\Support\Str::slug($optionValue));
                $isChecked = $value === $optionValue;
            @endphp

            <input type="radio" id="{{ $optionId }}" name="{{ $name }}" value="{{ $optionValue }}"
                   class="filter-input sr-only" data-filter-input
                   @checked($isChecked)>

            <label for="{{ $optionId }}"
                   class="filter-option"
                   data-filter-option data-checked="{{ $isChecked ? 'true' : 'false' }}">{{ $optionLabel }}</label>
        @endforeach
    </div>
</fieldset>