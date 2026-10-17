@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'options' => null,
    'rows' => 3,
    'required' => false,
    'autocomplete' => null,
    'autofocus' => false,
    'maxlength' => null,
    'minlength' => null,
    'inputmode' => null,
    'id' => null,
])

{{--
    Form field: label, control, hint, error — in that order, so the visual and
    DOM reading order agree.

    Validation errors come from the shared $errors bag, are wired with
    aria-invalid + aria-describedby, and are never signalled by colour alone.
--}}

@php
    $fieldId = $id ?? $name;
    $error = $errors->first($name);
    $current = $value ?? old($name);

    $control = 'block w-full rounded-xl border px-3.5 text-sm text-slate-900 shadow-tile outline-none transition-colors duration-200 placeholder:text-slate-400 focus:ring-4 disabled:cursor-not-allowed disabled:bg-slate-100'
        .' dark:text-slate-100 dark:placeholder:text-slate-500 dark:disabled:bg-slate-800';

    $control .= $error
        ? ' border-rose-400 bg-rose-50/40 focus:border-rose-500 focus:ring-rose-500/10 dark:border-rose-500 dark:bg-rose-500/5'
        : ' border-slate-300 bg-white focus:border-indigo-500 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-roomie-night dark:focus:border-indigo-400';

    $describedBy = trim(($hint ? $fieldId.'-hint ' : '').($error ? $fieldId.'-error' : ''));
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    <label for="{{ $fieldId }}" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
        {{ $label }}
        @if ($required)
            <span class="text-rose-600" aria-hidden="true">*</span>
            <span class="sr-only">(wajib diisi)</span>
        @endif
    </label>

    <div class="relative mt-1.5">
        @if ($type === 'select')
            <select id="{{ $fieldId }}" name="{{ $name }}"
                    @if ($required) required @endif
                    @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                    @if ($error) aria-invalid="true" @endif
                    class="{{ $control }} h-11 cursor-pointer appearance-none pr-10">
                {{ $slot }}
            </select>
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true">
                <x-ui.icon name="chevron-down" :size="16" />
            </span>

        @elseif ($type === 'textarea')
            <textarea id="{{ $fieldId }}" name="{{ $name }}" rows="{{ $rows }}"
                      @if ($placeholder) placeholder="{{ $placeholder }}" @endif
                      @if ($required) required @endif
                      @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                      @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                      @if ($error) aria-invalid="true" @endif
                      class="{{ $control }} py-3">{{ $current }}</textarea>

        @else
            <input id="{{ $fieldId }}" name="{{ $name }}" type="{{ $type }}"
                   value="{{ $current }}"
                   @if ($placeholder) placeholder="{{ $placeholder }}" @endif
                   @if ($required) required @endif
                   @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
                   @if ($autofocus) autofocus @endif
                   @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                   @if ($minlength) minlength="{{ $minlength }}" @endif
                   @if ($inputmode) inputmode="{{ $inputmode }}" @endif
                   @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                   @if ($error) aria-invalid="true" @endif
                   class="{{ $control }} h-11 {{ $type === 'password' ? 'pr-11' : '' }}">
            @if ($type === 'password')
                <button type="button" data-password-toggle="{{ $fieldId }}"
                        class="absolute right-1 top-1/2 flex size-9 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                    <span class="sr-only">Tampilkan kata sandi</span>
                    <span data-password-icon="eye"><x-ui.icon name="eye" :size="18" /></span>
                    <span data-password-icon="eye-off" class="hidden"><x-ui.icon name="eye-off" :size="18" /></span>
                </button>
            @endif
        @endif
    </div>

    @if ($hint)
        <p id="{{ $fieldId }}-hint" class="mt-1.5 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif

    @if ($error)
        <p id="{{ $fieldId }}-error" class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $error }}</p>
    @endif
</div>