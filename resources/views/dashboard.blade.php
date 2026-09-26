<x-app-layout>
    @php
        $hasStoredFile = fn ($document) => $document && filled($document->file_path) && Storage::disk('private')->exists($document->file_path);
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Governance Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-blue-700">{{ __('Overview guide') }}</p>
                <p>{{ __('This overview is read-only. Use the search and filters to narrow documents by title, reference, category, document type, or status. The consolidated report brings related resolutions, minutes, and supporting documents together in one place.') }}</p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-3 sm:p-6 space-y-3 sm:space-y-4">
                    <form method="GET" action="{{ route('dashboard') }}" class="grid grid-cols-1 gap-3 sm:gap-4 md:grid-cols-5">
                        <div class="md:col-span-2">
                            <label for="dashboard-search" class="block text-xs font-medium uppercase tracking-wide text-gray-600 sm:text-sm sm:normal-case sm:tracking-normal">{{ __('Search') }}</label>
                            <input id="dashboard-search" name="q" type="text" value="{{ request('q') }}" placeholder="Search title, reference, notes" class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>

                        <div>
                            <label for="dashboard-category" class="block text-xs font-medium uppercase tracking-wide text-gray-600 sm:text-sm sm:normal-case sm:tracking-normal">{{ __('Category') }}</label>
                            <select id="dashboard-category" name="category" class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('All categories') }}</option>
                                @foreach (App\Models\GovernanceDocument::allowedCategories() as $category)
                                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="dashboard-type" class="block text-xs font-medium uppercase tracking-wide text-gray-600 sm:text-sm sm:normal-case sm:tracking-normal">{{ __('Document type') }}</label>
                            <select id="dashboard-type" name="document_type" class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('All types') }}</option>
                                @foreach (['resolution' => 'Resolutions', 'minutes' => 'Minutes', 'communication' => 'Memos', 'policy' => 'Policies', 'regulation' => 'Regulations', 'correspondence' => 'Correspondence'] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('document_type') === $value)>{{ __($label) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="dashboard-status" class="block text-xs font-medium uppercase tracking-wide text-gray-600 sm:text-sm sm:normal-case sm:tracking-normal">{{ __('Status') }}</label>
                            <select id="dashboard-status" name="status" class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('All statuses') }}</option>
                                @foreach (['draft', 'active', 'archived', 'expired'] as $status)
                                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-5 flex flex-col gap-2 sm:flex-row sm:items-end">
                            <button type="submit" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                                {{ __('Apply Filters') }}
                            </button>
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                {{ __('Clear') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-3 sm:p-6">
                    <div class="hidden sm:block">
                        <div class="overflow-x-auto">
                            <table class="min-w-[760px] w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b text-gray-600">
                                        <th class="py-3 pr-6 text-center w-[28%]">{{ __('Title') }}</th>
                                        <th class="py-3 pr-6 text-center w-[15%]">{{ __('Ref') }}</th>
                                        <th class="py-3 pr-6 text-center w-[12%]">{{ __('Type') }}</th>
                                        <th class="py-3 pr-6 text-center w-[13%]">{{ __('Cat') }}</th>
                                        <th class="py-3 pr-6 text-center w-[12%]">{{ __('Status') }}</th>
                                        <th class="py-3 pr-6 text-center w-[8%]">{{ __('#') }}</th>
                                        <th class="py-3 pr-6 text-center w-[12%]">{{ __('User') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($governanceDocuments as $document)
                                        <tr class="border-b last:border-b-0 text-gray-900 align-top">
                                            <td class="py-3 pr-6 font-medium text-gray-900">{{ $document->title }}</td>
                                            <td class="py-3 pr-6 text-gray-700">{{ $document->reference_no ?? '-' }}</td>
                                            <td class="py-3 pr-6 text-gray-700">{{ $document->document_type ?? '-' }}</td>
                                            <td class="py-3 pr-6 text-gray-700">{{ $document->category ?? '-' }}</td>
                                            <td class="py-3 pr-6 text-gray-700">{{ $document->status ?? '-' }}</td>
                                            <td class="py-3 pr-6 text-center text-gray-700">{{ $document->records_count }}</td>
                                            <td class="py-3 pr-6 text-gray-700">{{ $document->uploader?->name ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-6 text-center text-gray-500">{{ __('No governance documents found.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="space-y-3 sm:hidden">
                        @forelse ($governanceDocuments as $document)
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="mb-2 flex items-start justify-between gap-3">
                                    <div class="font-semibold text-gray-900">{{ $document->title }}</div>
                                    <span class="rounded-full bg-indigo-100 px-2 py-1 text-[10px] font-medium text-indigo-700">{{ $document->status ?? 'draft' }}</span>
                                </div>

                                <dl class="space-y-1 text-xs text-gray-700">
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-gray-500">Reference</dt>
                                        <dd class="font-medium text-right">{{ $document->reference_no ?? '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-gray-500">Type</dt>
                                        <dd class="font-medium text-right">{{ $document->document_type ?? '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-gray-500">Category</dt>
                                        <dd class="font-medium text-right">{{ $document->category ?? '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-gray-500">Records</dt>
                                        <dd class="font-medium text-right">{{ $document->records_count }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-gray-500">Uploader</dt>
                                        <dd class="font-medium text-right">{{ $document->uploader?->name ?? '-' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        @empty
                            <div class="py-6 text-center text-sm text-gray-500">{{ __('No governance documents found.') }}</div>
                        @endforelse
                    </div>
                </div>

                <div class="px-3 pb-3 sm:px-6 sm:pb-6">
                    {{ $governanceDocuments->links() }}
                </div>
            </div>

            @php
                $selectedDocumentType = request('document_type');
                $showConsolidated = $selectedDocumentType === null || $selectedDocumentType === '' || in_array($selectedDocumentType, ['resolution', 'minutes', 'communication'], true);
            @endphp

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-3 sm:p-6 text-gray-900">
                    <h3 class="text-lg font-semibold">{{ __('Governance Reports') }}</h3>

                    @if ($showConsolidated)
                        <div class="mt-5 overflow-x-auto">
                            <h4 class="mb-2 text-base font-semibold text-gray-800">{{ __('Consolidated Resolution Report') }}</h4>
                            <table class="w-full table-fixed text-left text-sm">
                                <colgroup>
                                    <col class="w-[28%]" />
                                    <col class="w-[18%]" />
                                    <col class="w-[18%]" />
                                    <col class="w-[18%]" />
                                    <col class="w-[18%]" />
                                </colgroup>
                                <thead>
                                    <tr class="border-b-2 border-gray-300 bg-slate-50 text-gray-700">
                                        <th class="border-r border-gray-400 py-2 pr-4 pl-2">{{ __('Title') }}</th>
                                        <th class="border-r border-gray-400 py-2 pr-4">{{ __('Issued Date') }}</th>
                                        <th class="border-r border-gray-400 py-2 pr-4">{{ __('Resolution') }}</th>
                                        <th class="border-r border-gray-400 py-2 pr-4">{{ __('Minute') }}</th>
                                        <th class="py-2 pr-4">{{ __('Documents Reviewed') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($consolidatedResolutionRegister as $entry)
                                        <tr class="border-b-2 border-gray-300 last:border-b-0 text-gray-900 align-top odd:bg-white even:bg-slate-50">
                                            <td class="border-r border-gray-400 py-2 pr-4 pl-2 font-medium align-top">{{ $entry['title'] ?? '-' }}</td>
                                            <td class="border-r border-gray-400 py-2 pr-4 align-top text-gray-700">
                                                {{ $entry['issued_at'] ?? '-' }}
                                            </td>
                                            <td class="border-r border-gray-400 py-2 pr-4 align-top">
                                                @if ($entry['resolution'])
                                                    @php $resolutionHasFile = $hasStoredFile($entry['resolution']); @endphp
                                                    <a href="{{ $resolutionHasFile ? route('admin.governance-documents.viewer', $entry['resolution']->id) : '#' }}" class="inline-flex w-full min-w-[8rem] items-center justify-center rounded-md border px-3 py-2 text-xs font-semibold shadow-md transition focus:outline-none focus:ring-2 focus:ring-offset-1 {{ $resolutionHasFile ? 'border-orange-400 bg-orange-50 text-orange-600 hover:bg-orange-100 focus:ring-orange-400' : 'cursor-not-allowed border-gray-300 bg-gray-200 text-gray-500 hover:bg-gray-200 focus:ring-gray-400' }}" @if (! $resolutionHasFile) aria-disabled="true" onclick="return false;" @else target="_blank" rel="noopener noreferrer" @endif>
                                                        {{ $entry['resolution']->reference_no ?? __('Resolution') }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="border-r border-gray-400 py-2 pr-4 align-top">
                                                @if ($entry['minute'])
                                                    @php $minuteHasFile = $hasStoredFile($entry['minute']); @endphp
                                                    <a href="{{ $minuteHasFile ? route('admin.governance-documents.viewer', $entry['minute']->id) : '#' }}" class="inline-flex w-full min-w-[8rem] items-center justify-center rounded-md border px-3 py-2 text-xs font-semibold shadow-md transition focus:outline-none focus:ring-2 focus:ring-offset-1 {{ $minuteHasFile ? 'border-orange-400 bg-orange-50 text-orange-600 hover:bg-orange-100 focus:ring-orange-400' : 'cursor-not-allowed border-gray-300 bg-gray-200 text-gray-500 hover:bg-gray-200 focus:ring-gray-400' }}" @if (! $minuteHasFile) aria-disabled="true" onclick="return false;" @else target="_blank" rel="noopener noreferrer" @endif>
                                                        {{ $entry['minute']->reference_no ?? __('Minute') }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="py-2 pr-4 align-top">
                                                @if ($entry['supporting_documents']->isNotEmpty())
                                                    <ul class="list-none space-y-2 pl-0">
                                                        @foreach ($entry['supporting_documents'] as $supportingDocument)
                                                            @php $supportingHasFile = $hasStoredFile($supportingDocument); @endphp
                                                            <li>
                                                                <a href="{{ $supportingHasFile ? route('admin.governance-documents.viewer', $supportingDocument->id) : '#' }}" class="inline-flex w-full min-w-[8rem] items-center justify-center rounded-md border px-2.5 py-1.5 text-[11px] font-semibold shadow-md transition focus:outline-none focus:ring-2 focus:ring-offset-1 {{ $supportingHasFile ? 'border-orange-400 bg-orange-50 text-orange-600 hover:bg-orange-100 focus:ring-orange-400' : 'cursor-not-allowed border-gray-300 bg-gray-200 text-gray-500 hover:bg-gray-200 focus:ring-gray-400' }}" @if (! $supportingHasFile) aria-disabled="true" onclick="return false;" @else target="_blank" rel="noopener noreferrer" @endif>
                                                                    {{ $supportingDocument->reference_no ?? __('Support') }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-6 text-center text-gray-500">{{ __('No board resolutions found.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
