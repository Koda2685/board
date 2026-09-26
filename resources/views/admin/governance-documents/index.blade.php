<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Governance Documents') }}
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-600 hover:underline">{{ __('Back to Admin Dashboard') }}</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-blue-50 border border-blue-200 text-blue-900 rounded-lg p-4 text-sm">
                {{ __('Read-only view: create, update, and delete actions are intentionally disabled.') }}
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 space-y-4">
                    <form method="GET" action="{{ route('admin.governance-documents.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-5">
                        <div class="md:col-span-2">
                            <label for="admin-search" class="block text-sm font-medium text-gray-700">{{ __('Search') }}</label>
                            <input id="admin-search" name="q" type="text" value="{{ request('q') }}" placeholder="Search title, reference, notes" class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>

                        <div>
                            <label for="admin-category" class="block text-sm font-medium text-gray-700">{{ __('Category') }}</label>
                            <select id="admin-category" name="category" class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('All categories') }}</option>
                                @foreach (App\Models\GovernanceDocument::allowedCategories() as $category)
                                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="admin-status" class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                            <select id="admin-status" name="status" class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('All statuses') }}</option>
                                @foreach (['draft', 'active', 'archived', 'expired'] as $status)
                                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="admin-type" class="block text-sm font-medium text-gray-700">{{ __('Type') }}</label>
                            <select id="admin-type" name="document_type" class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('All types') }}</option>
                                @foreach (['policy', 'regulation', 'minutes', 'resolution', 'communication', 'special_resolution', 'board_reviewed'] as $documentType)
                                    <option value="{{ $documentType }}" @selected(request('document_type') === $documentType)>{{ $documentType === 'board_reviewed' ? 'Board Reviewed' : $documentType }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-5 flex gap-2">
                            <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                                {{ __('Apply Filters') }}
                            </button>
                            <a href="{{ route('admin.governance-documents.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                {{ __('Clear') }}
                            </a>
                        </div>
                    </form>
                </div>

                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead>
                            <tr class="border-b text-gray-600">
                                <th class="py-2 pr-4 text-center">{{ __('Title') }}</th>
                                <th class="py-2 pr-4 text-center">{{ __('Reference') }}</th>
                                <th class="py-2 pr-4 text-center">{{ __('Type') }}</th>
                                <th class="py-2 pr-4 text-center">{{ __('Category') }}</th>
                                <th class="py-2 pr-4 text-center">{{ __('Status') }}</th>
                                <th class="py-2 pr-4 text-center">{{ __('Version') }}</th>
                                <th class="py-2 pr-4 text-center">{{ __('Records') }}</th>
                                <th class="py-2 pr-4 text-center">{{ __('Updated') }}</th>
                                <th class="py-2 text-center">{{ __('View') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($documents as $document)
                                <tr class="border-b last:border-b-0 text-gray-900">
                                    <td class="py-2 pr-4 font-medium text-center">{{ $document->title }}</td>
                                    <td class="py-2 pr-4 text-center">{{ $document->reference_no ?? '-' }}</td>
                                    <td class="py-2 pr-4 text-center">{{ $document->document_type ?? '-' }}</td>
                                    <td class="py-2 pr-4 text-center">{{ $document->category ?? '-' }}</td>
                                    <td class="py-2 pr-4 text-center">{{ $document->status ?? '-' }}</td>
                                    <td class="py-2 pr-4 text-center">{{ $document->version ?? '-' }}</td>
                                    <td class="py-2 pr-4 text-center">{{ $document->records_count }}</td>
                                    <td class="py-2 pr-4 text-center">{{ optional($document->updated_at)?->diffForHumans() }}</td>
                                    <td class="py-2 text-center">
                                        <a href="{{ route('admin.governance-documents.show', $document) }}" class="text-blue-600 hover:underline">{{ __('Open') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-6 text-center text-gray-500">{{ __('No governance documents found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 pb-6">
                    {{ $documents->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
