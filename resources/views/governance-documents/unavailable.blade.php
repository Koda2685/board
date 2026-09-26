<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attachment unavailable') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
                <h3 class="text-xl font-semibold text-amber-900">{{ __('This attachment is currently unavailable') }}</h3>
                <p class="mt-3 text-sm text-amber-800">
                    {{ $message ?? __('The linked file is missing or has not been uploaded to storage yet.') }}
                </p>

                @if ($document && $document->file_path)
                    <p class="mt-4 text-sm text-amber-700">
                        <span class="font-medium">{{ __('File reference:') }}</span>
                        {{ basename($document->file_path) }}
                    </p>
                @endif

                <div class="mt-6">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center rounded-md border border-amber-300 bg-white px-4 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100">
                        {{ __('Return to dashboard') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
