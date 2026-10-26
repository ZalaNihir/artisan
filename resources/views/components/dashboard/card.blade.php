@props(['label', 'count', 'description', 'href', 'icon'])

<a href="{{ $href }}" class="group flex min-h-40 flex-col justify-between rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-blue-700 dark:focus:ring-offset-zinc-900 sm:p-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-300">{{ $label }}</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $count }}</p>
        </div>
        <span class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
            <flux:icon :icon="$icon" class="size-5" />
        </span>
    </div>
    <p class="mt-4 text-sm text-zinc-500 transition group-hover:text-zinc-700 dark:text-zinc-400 dark:group-hover:text-zinc-200">{{ $description }} <span aria-hidden="true">&rarr;</span></p>
</a>
