<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Document Details') }}
            </h2>
            <a href="{{ route('admin.governance-documents.index') }}" class="text-sm text-blue-600 hover:underline">{{ __('Back to Documents') }}</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-3 text-sm text-gray-900">
                    <h3 class="text-lg font-semibold">{{ $document->title }}</h3>
                    <p><span class="font-medium">{{ __('Reference:') }}</span> {{ $document->reference_no ?? '-' }}</p>
                    <p><span class="font-medium">{{ __('Type:') }}</span> {{ $document->document_type ?? '-' }}</p>
                    <p><span class="font-medium">{{ __('Category:') }}</span> {{ $document->category ?? '-' }}</p>
                    <p><span class="font-medium">{{ __('Status:') }}</span> {{ $document->status ?? '-' }}</p>
                    <p><span class="font-medium">{{ __('Version:') }}</span> {{ $document->version ?? '-' }}</p>
                    <p><span class="font-medium">{{ __('Uploader:') }}</span> {{ $document->uploader?->name ?? '-' }}</p>
                    <p><span class="font-medium">{{ __('Issued:') }}</span> {{ optional($document->issued_at)?->format('Y-m-d H:i') ?? '-' }}</p>
                    <p><span class="font-medium">{{ __('Effective:') }}</span> {{ optional($document->effective_at)?->format('Y-m-d H:i') ?? '-' }}</p>
                    <p><span class="font-medium">{{ __('Expires:') }}</span> {{ optional($document->expires_at)?->format('Y-m-d H:i') ?? '-' }}</p>
                    <p><span class="font-medium">{{ __('Attachment:') }}</span>
                        @if ($document->file_path && Storage::disk('private')->exists($document->file_path))
                            <a href="{{ route('admin.governance-documents.viewer', $document) }}" class="text-blue-600 hover:underline">{{ basename($document->file_path) }}</a>
                        @else
                            {{ __('No attachment uploaded') }}
                        @endif
                    </p>
                    <p><span class="font-medium">{{ __('Notes:') }}</span> {{ $document->notes ?? '-' }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Update Document') }}</h3>
                    <form method="POST" action="{{ route('admin.governance-documents.update', $document) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="admin-edit-title" class="block text-sm font-medium text-gray-700">{{ __('Title') }}</label>
                                <input id="admin-edit-title" name="title" type="text" value="{{ old('title', $document->title) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="admin-edit-document-type" class="block text-sm font-medium text-gray-700">{{ __('Document Type') }}</label>
                                <select id="admin-edit-document-type" name="document_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach (['policy', 'regulation', 'minutes', 'resolution', 'communication', 'special_resolution', 'board_reviewed'] as $type)
                                        <option value="{{ $type }}" @selected(old('document_type', $document->document_type) === $type)>{{ $type === 'board_reviewed' ? 'Board Reviewed' : $type }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="admin-edit-category" class="block text-sm font-medium text-gray-700">{{ __('Category') }}</label>
                                <select id="admin-edit-category" name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach (App\Models\GovernanceDocument::allowedCategories() as $category)
                                        <option value="{{ $category }}" @selected(old('category', $document->category) === $category)>{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="admin-edit-status" class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                                <select id="admin-edit-status" name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach (['draft', 'active', 'archived', 'expired'] as $status)
                                        <option value="{{ $status }}" @selected(old('status', $document->status) === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="admin-edit-version" class="block text-sm font-medium text-gray-700">{{ __('Version') }}</label>
                                <input id="admin-edit-version" name="version" type="number" min="1" value="{{ old('version', $document->version) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="admin-edit-document" class="block text-sm font-medium text-gray-700">{{ __('Replace file') }}</label>
                                <input id="admin-edit-document" name="document" type="file" class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label for="admin-edit-notes" class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
                            <textarea id="admin-edit-notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $document->notes) }}</textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center rounded-lg border border-indigo-700 bg-indigo-600 px-5 py-3 text-sm font-bold text-black shadow-md shadow-indigo-200 transition hover:bg-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-200">
                                {{ __('Save changes') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-x-auto">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Record History') }}</h3>
                    <table class="min-w-full text-sm text-left">
                        <thead>
                            <tr class="border-b text-gray-600">
                                <th class="py-2 pr-4">{{ __('When') }}</th>
                                <th class="py-2 pr-4">{{ __('Action') }}</th>
                                <th class="py-2 pr-4">{{ __('Recorder') }}</th>
                                <th class="py-2 pr-4">{{ __('Remarks') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($document->records as $record)
                                <tr class="border-b last:border-b-0 text-gray-900">
                                    <td class="py-2 pr-4">{{ optional($record->recorded_at)?->format('Y-m-d H:i') ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $record->action }}</td>
                                    <td class="py-2 pr-4">{{ $record->recorder?->name ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $record->remarks ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-gray-500">{{ __('No records found for this document.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
