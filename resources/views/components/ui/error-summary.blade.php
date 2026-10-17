@props([
    'title' => 'Periksa kembali isian berikut',
    // field name => element id, so each message can link to the input it is about
    'fields' => [],
])

{{--
    Form error summary.

    Focusable, and the page script moves focus to it, so a screen reader hears
    every problem at once instead of only the first. Each message that belongs
    to a known field links straight to it — the alternative is making the user
    hunt for which of four inputs is at fault.
--}}
<div tabindex="-1" data-error-summary
     class="rounded-xl border border-rose-200 bg-rose-50 p-4 focus-visible:outline-none dark:border-rose-500/40 dark:bg-rose-500/10">
    <p class="flex items-center gap-2 text-sm font-semibold text-rose-800 dark:text-rose-200">
        <x-ui.icon name="alert-triangle" :size="16" />
        {{ $title }}
    </p>

    <ul class="mt-2 list-disc space-y-1 pl-6 text-sm leading-relaxed text-rose-700 dark:text-rose-300">
        @foreach ($errors->getMessages() as $field => $messages)
            @foreach ($messages as $message)
                <li>
                    @isset($fields[$field])
                        <a href="#{{ $fields[$field] }}"
                           class="rounded font-semibold underline underline-offset-2 hover:text-rose-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 dark:hover:text-rose-100">
                            {{ $message }}
                        </a>
                    @else
                        {{ $message }}
                    @endif
                </li>
            @endforeach
        @endforeach
    </ul>
</div>