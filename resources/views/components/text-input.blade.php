@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200']) }}>
