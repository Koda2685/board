<x-app-layout>
    @php
        $hasStoredFile = fn ($document) => $document && filled($document->file_path) && Storage::disk('private')->exists($document->file_path);
    @endphp

    <x-slot name="header">
        <div class="rounded-[1.25rem] bg-[#0f172a] px-4 py-4 shadow-sm">
            <h2 class="text-[1.6rem] font-black tracking-wide text-white leading-tight">
                {{ __('Admin Dashboard') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __('Welcome, admin. Governance and platform controls are available.') }}
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-3 text-gray-900">
                    <h3 class="font-semibold text-lg">{{ __('Governance Console') }}</h3>
                    <p class="text-sm text-gray-600">{{ __('Use the read-only governance UI to review documents and their audit records.') }}</p>
                    <a href="{{ route('admin.governance-documents.index') }}" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">
                        {{ __('Open Read-Only Governance View') }}
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="mb-6 border-b border-gray-200">
                        <nav class="flex flex-wrap gap-2" aria-label="Admin dashboard tabs">
                            <button type="button" data-tab-target="overview" class="tab-button rounded-t-md border border-b-2 border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                {{ __('Overview') }}
                            </button>
                            <button type="button" data-tab-target="records-entry" class="tab-button rounded-t-md border border-b-2 border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                {{ __('Records Entry') }}
                            </button>
                        </nav>
                    </div>

                    <div data-tab-panel="overview" class="tab-panel">
                        <div class="text-gray-900">
                            <h3 class="text-lg font-semibold">{{ __('Resolution Report') }}</h3>

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
                                        <td class="border-r border-gray-400 py-2 pr-4 pl-2 font-medium align-top">
                                            {{ $entry['title'] ?? '-' }}
                                        </td>
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

                    <div class="mt-8 space-y-4">
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                            <h4 class="mb-3 text-base font-semibold text-gray-800">{{ __('Record of Resolutions') }}</h4>

                            @forelse ($resolutionRegister as $entry)
                                <div class="rounded-xl border border-gray-200 bg-slate-50 p-3 shadow-sm first:mt-0 mt-3">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="font-medium text-gray-900">{{ $entry['title'] ?? '-' }}</div>
                                        @if ($entry['resolution'])
                                            @php $resolutionHasFile = $hasStoredFile($entry['resolution']); @endphp
                                            <a href="{{ $resolutionHasFile ? route('admin.governance-documents.viewer', $entry['resolution']->id) : '#' }}" class="inline-flex items-center rounded-md border px-3 py-2 text-xs font-semibold shadow-md transition focus:outline-none focus:ring-2 focus:ring-offset-1 {{ $resolutionHasFile ? 'border-orange-400 bg-orange-50 text-orange-600 hover:bg-orange-100 focus:ring-orange-400' : 'cursor-not-allowed border-gray-300 bg-gray-200 text-gray-500 hover:bg-gray-200 focus:ring-gray-400' }}" @if (! $resolutionHasFile) aria-disabled="true" onclick="return false;" @else target="_blank" rel="noopener noreferrer" @endif>
                                                {{ $entry['resolution']->reference_no ?? __('Resolution') }}
                                            </a>
                                        @endif
                                    </div>

                                    @if ($entry['supporting_documents']->isNotEmpty())
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach ($entry['supporting_documents'] as $supportingDocument)
                                                @php $supportingHasFile = $hasStoredFile($supportingDocument); @endphp
                                                <a href="{{ $supportingHasFile ? route('admin.governance-documents.viewer', $supportingDocument->id) : '#' }}" class="inline-flex items-center rounded-md border px-2.5 py-1.5 text-[11px] font-semibold shadow-md transition focus:outline-none focus:ring-2 focus:ring-offset-1 {{ $supportingHasFile ? 'border-orange-400 bg-orange-50 text-orange-600 hover:bg-orange-100 focus:ring-orange-400' : 'cursor-not-allowed border-gray-300 bg-gray-200 text-gray-500 hover:bg-gray-200 focus:ring-gray-400' }}" @if (! $supportingHasFile) aria-disabled="true" onclick="return false;" @else target="_blank" rel="noopener noreferrer" @endif>
                                                    {{ $supportingDocument->reference_no ?? __('Support') }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 text-center text-sm text-gray-500">
                                    {{ __('No board resolutions found.') }}
                                </div>
                            @endforelse
                        </div>

                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                            <h4 class="mb-3 text-base font-semibold text-gray-800">{{ __('Minutes Register') }}</h4>

                            @forelse ($minutesRegister as $entry)
                                <div class="rounded-xl border border-gray-200 bg-slate-50 p-3 shadow-sm first:mt-0 mt-3">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="font-medium text-gray-900">{{ $entry['title'] ?? '-' }}</div>
                                        @if ($entry['minutes'])
                                            @php $minutesHasFile = $hasStoredFile($entry['minutes']); @endphp
                                            <a href="{{ $minutesHasFile ? route('admin.governance-documents.viewer', $entry['minutes']->id) : '#' }}" class="inline-flex items-center rounded-md border px-3 py-2 text-xs font-semibold shadow-md transition focus:outline-none focus:ring-2 focus:ring-offset-1 {{ $minutesHasFile ? 'border-orange-400 bg-orange-50 text-orange-600 hover:bg-orange-100 focus:ring-orange-400' : 'cursor-not-allowed border-gray-300 bg-gray-200 text-gray-500 hover:bg-gray-200 focus:ring-gray-400' }}" @if (! $minutesHasFile) aria-disabled="true" onclick="return false;" @else target="_blank" rel="noopener noreferrer" @endif>
                                                {{ $entry['minutes']->reference_no ?? __('Minutes') }}
                                            </a>
                                        @endif
                                    </div>

                                    @if ($entry['supporting_documents']->isNotEmpty())
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach ($entry['supporting_documents'] as $supportingDocument)
                                                @php $supportingHasFile = $hasStoredFile($supportingDocument); @endphp
                                                <a href="{{ $supportingHasFile ? route('admin.governance-documents.viewer', $supportingDocument->id) : '#' }}" class="inline-flex items-center rounded-md border px-2.5 py-1.5 text-[11px] font-semibold shadow-md transition focus:outline-none focus:ring-2 focus:ring-offset-1 {{ $supportingHasFile ? 'border-orange-400 bg-orange-50 text-orange-600 hover:bg-orange-100 focus:ring-orange-400' : 'cursor-not-allowed border-gray-300 bg-gray-200 text-gray-500 hover:bg-gray-200 focus:ring-gray-400' }}" @if (! $supportingHasFile) aria-disabled="true" onclick="return false;" @else target="_blank" rel="noopener noreferrer" @endif>
                                                    {{ $supportingDocument->reference_no ?? __('Support') }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 text-center text-sm text-gray-500">
                                    {{ __('No board minutes found.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>

                    @php
                        $registers = [
                            ['title' => 'Policies Register', 'label' => 'Policy', 'items' => $policiesRegister],
                            ['title' => 'Regulations Register', 'label' => 'Regulation', 'items' => $regulationsRegister],
                            ['title' => 'Correspondence Register', 'label' => 'Correspondence', 'items' => $correspondenceRegister],
                            ['title' => 'HR Agreements Register', 'label' => 'HR Agreement', 'items' => $hrAgreementsRegister],
                            ['title' => 'External Agreements Register', 'label' => 'External Agreement', 'items' => $externalAgreementsRegister],
                            ['title' => 'Commercial Contracts Register', 'label' => 'Commercial Contract', 'items' => $commercialContractsRegister],
                        ];
                    @endphp

                    @foreach ($registers as $register)
                        <div class="mt-8 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                            <h4 class="mb-3 text-base font-semibold text-gray-800">{{ __($register['title']) }}</h4>

                            @forelse ($register['items'] as $entry)
                                <div class="rounded-xl border border-gray-200 bg-slate-50 p-3 shadow-sm first:mt-0 mt-3">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="font-medium text-gray-900">{{ $entry['title'] ?? '-' }}</div>
                                        @if ($entry['document'])
                                            @php $documentHasFile = $hasStoredFile($entry['document']); @endphp
                                            <a href="{{ $documentHasFile ? route('admin.governance-documents.viewer', $entry['document']->id) : '#' }}" class="inline-flex items-center rounded-md border px-3 py-2 text-xs font-semibold shadow-md transition focus:outline-none focus:ring-2 focus:ring-offset-1 {{ $documentHasFile ? 'border-orange-400 bg-orange-50 text-orange-600 hover:bg-orange-100 focus:ring-orange-400' : 'cursor-not-allowed border-gray-300 bg-gray-200 text-gray-500 hover:bg-gray-200 focus:ring-gray-400' }}" @if (! $documentHasFile) aria-disabled="true" onclick="return false;" @else target="_blank" rel="noopener noreferrer" @endif>
                                                {{ $entry['document']->reference_no ?? $entry['title'] ?? __($register['label']) }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 text-center text-sm text-gray-500">
                                    {{ __('No records found.') }}
                                </div>
                            @endforelse
                        </div>
                    @endforeach
                        </div>
                    </div>

                    <div data-tab-panel="records-entry" class="tab-panel hidden">
                        <div class="grid gap-6 lg:grid-cols-2">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <h4 class="mb-4 text-lg font-semibold text-gray-900">{{ __('Add Governance Document') }}</h4>

                                <form method="POST" action="{{ route('dashboard.governance-documents.store') }}" enctype="multipart/form-data" class="space-y-4">
                                    @csrf

                                    <div>
                                        <label for="admin-document-title" class="block text-sm font-medium text-gray-700">{{ __('Title') }}</label>
                                        <input id="admin-document-title" name="title" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    </div>

                                    <div>
                                        <label for="admin-document-reference" class="block text-sm font-medium text-gray-700">{{ __('Reference No.') }}</label>
                                        <input id="admin-document-reference" name="reference_no" type="text" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm text-gray-700 focus:border-indigo-500 focus:ring-indigo-500" />
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="admin-document-type" class="block text-sm font-medium text-gray-700">{{ __('Document Type') }}</label>
                                            <select id="admin-document-type" name="document_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="">{{ __('Select type') }}</option>
                                                @foreach (['policy', 'regulation', 'minutes', 'resolution', 'communication', 'special_resolution', 'board_reviewed'] as $type)
                                                    <option value="{{ $type }}">{{ $type === 'board_reviewed' ? 'Board Reviewed' : $type }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label for="admin-document-category" class="block text-sm font-medium text-gray-700">{{ __('Category') }}</label>
                                            <select id="admin-document-category" name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="">{{ __('Select category') }}</option>
                                                @foreach (App\Models\GovernanceDocument::allowedCategories() as $category)
                                                    <option value="{{ $category }}">{{ $category }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="admin-document-status" class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                                            <select id="admin-document-status" name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                @foreach (['draft', 'active', 'archived', 'expired'] as $status)
                                                    <option value="{{ $status }}" @selected($status === 'draft')>{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label for="admin-document-version" class="block text-sm font-medium text-gray-700">{{ __('Version') }}</label>
                                            <input id="admin-document-version" name="version" type="number" min="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                        </div>
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="admin-document-issued-at" class="block text-sm font-medium text-gray-700">{{ __('Issued At') }}</label>
                                            <input id="admin-document-issued-at" name="issued_at" type="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                        </div>

                                        <div>
                                            <label for="admin-document-effective-at" class="block text-sm font-medium text-gray-700">{{ __('Effective At') }}</label>
                                            <input id="admin-document-effective-at" name="effective_at" type="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                        </div>
                                    </div>

                                    <div>
                                        <label for="admin-document-file" class="block text-sm font-medium text-gray-700">{{ __('Document File') }}</label>
                                        <input id="admin-document-file" name="document" type="file" required class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-500" />
                                    </div>

                                    <div>
                                        <label for="admin-document-notes" class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
                                        <textarea id="admin-document-notes" name="notes" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    </div>

                                    <button type="submit" class="inline-flex items-center rounded-lg border border-indigo-700 bg-indigo-600 px-5 py-3 text-sm font-bold text-black shadow-md shadow-indigo-200 transition hover:bg-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-200">
                                        {{ __('Submit Document') }}
                                    </button>
                                </form>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <h4 class="mb-4 text-lg font-semibold text-gray-900">{{ __('Add Governance Record') }}</h4>

                                <form method="POST" action="{{ route('dashboard.governance-records.store') }}" class="space-y-4">
                                    @csrf

                                    <div>
                                        <label for="admin-record-document" class="block text-sm font-medium text-gray-700">{{ __('Governance Document') }}</label>
                                        <select id="admin-record-document" name="governance_document_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">{{ __('Select document') }}</option>
                                            @foreach (App\Models\GovernanceDocument::query()->orderBy('title')->get() as $document)
                                                <option value="{{ $document->id }}">{{ $document->title }} ({{ $document->reference_no ?? 'No ref' }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="admin-record-action" class="block text-sm font-medium text-gray-700">{{ __('Action') }}</label>
                                        <select id="admin-record-action" name="action" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            @foreach (App\Models\GovernanceRecord::allowedActions() as $action)
                                                <option value="{{ $action }}">{{ $action }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="admin-record-remarks" class="block text-sm font-medium text-gray-700">{{ __('Remarks') }}</label>
                                        <textarea id="admin-record-remarks" name="remarks" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    </div>

                                    <div>
                                        <label for="admin-record-date" class="block text-sm font-medium text-gray-700">{{ __('Recorded At') }}</label>
                                        <input id="admin-record-date" name="recorded_at" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    </div>

                                    <button type="submit" class="inline-flex items-center rounded-lg border border-emerald-700 bg-emerald-600 px-5 py-3 text-sm font-bold text-black shadow-md shadow-emerald-200 transition hover:bg-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-200">
                                        {{ __('Submit Record') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const initializeDashboardControls = () => {
            const buttons = document.querySelectorAll('.tab-button');
            const panels = document.querySelectorAll('.tab-panel');
            const documentTypeInput = document.getElementById('admin-document-type');
            const referenceInput = document.getElementById('admin-document-reference');

            const fallbackReferenceNumber = (selectedType) => {
                const codeMap = {
                    policy: 'POL',
                    regulation: 'REG',
                    minutes: 'MIN',
                    resolution: 'RES',
                    communication: 'COM',
                    special_resolution: 'SPR',
                    board_reviewed: 'BRD',
                };

                const code = codeMap[selectedType] || 'GEN';
                const year = new Date().getFullYear();

                return `${code}-${year}-001`;
            };

            const updateReferencePreview = async () => {
                if (!documentTypeInput || !referenceInput) {
                    return;
                }

                const selectedType = documentTypeInput.value;

                if (!selectedType) {
                    referenceInput.value = '';
                    return;
                }

                try {
                    const response = await fetch(`/dashboard/governance-documents/reference?document_type=${encodeURIComponent(selectedType)}`);

                    if (!response.ok) {
                        referenceInput.value = fallbackReferenceNumber(selectedType);
                        return;
                    }

                    const data = await response.json();
                    referenceInput.value = data.reference_no || fallbackReferenceNumber(selectedType);
                } catch (error) {
                    referenceInput.value = fallbackReferenceNumber(selectedType);
                }
            };

            const activateTab = (target) => {
                buttons.forEach((button) => {
                    const isActive = button.dataset.tabTarget === target;
                    button.classList.toggle('bg-indigo-50', isActive);
                    button.classList.toggle('border-indigo-600', isActive);
                    button.classList.toggle('text-indigo-700', isActive);
                    button.classList.toggle('shadow-sm', isActive);
                    button.classList.toggle('bg-white', !isActive);
                    button.classList.toggle('border-gray-200', !isActive);
                    button.classList.toggle('text-gray-700', !isActive);
                    button.setAttribute('aria-selected', String(isActive));
                });

                panels.forEach((panel) => {
                    const isVisible = panel.dataset.tabPanel === target;
                    panel.classList.toggle('hidden', !isVisible);
                });
            };

            document.querySelectorAll('form').forEach((form) => {
                form.addEventListener('submit', function () {
                    if (form.dataset.submitted === 'true') {
                        return false;
                    }

                    form.dataset.submitted = 'true';

                    const submitButton = form.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.setAttribute('aria-disabled', 'true');
                        submitButton.classList.add('opacity-60', 'cursor-not-allowed');
                    }
                });
            });

            if (documentTypeInput) {
                documentTypeInput.addEventListener('change', updateReferencePreview);
            }

            buttons.forEach((button) => {
                button.addEventListener('click', function () {
                    activateTab(this.dataset.tabTarget);
                });
            });

            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', window.location.href);
            }

            activateTab('overview');
            updateReferencePreview();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeDashboardControls);
        } else {
            initializeDashboardControls();
        }
    </script>
</x-app-layout>
