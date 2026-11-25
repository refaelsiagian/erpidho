@props(['color' => 'slate'])

@php
    $classes = match ($color) {
        'emerald', 'green' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'amber', 'yellow' => 'bg-amber-100 text-amber-800 border-amber-200',
        'rose', 'red' => 'bg-rose-100 text-rose-800 border-rose-200',
        'sky', 'blue' => 'bg-sky-100 text-sky-800 border-sky-200',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border '.$classes]) }}>
    {{ $slot }}
</span>

