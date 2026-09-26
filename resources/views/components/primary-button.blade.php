<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#f97316] to-[#ea580c] px-5 py-2.5 text-sm font-bold text-white shadow-[0_12px_26px_rgba(249,115,22,0.28)] transition hover:-translate-y-0.5 hover:shadow-[0_16px_30px_rgba(249,115,22,0.32)] focus:outline-none focus:ring-2 focus:ring-orange-300 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
