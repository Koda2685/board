<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin User Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold">{{ __('Create Admin User') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('Only authorized admins can create another admin account.') }}</p>
                        </div>
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            {{ __('Back to Dashboard') }}
                        </a>
                    </div>

                    <form method="POST" action="{{ route('admin.users.store') }}" class="mt-6 space-y-4">
                        @csrf

                        <div>
                            <label for="admin-user-name" class="block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
                            <input id="admin-user-name" name="name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>

                        <div>
                            <label for="admin-user-email" class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                            <input id="admin-user-email" name="email" type="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>

                        <div>
                            <label for="admin-user-password" class="block text-sm font-medium text-gray-700">{{ __('Password') }}</label>
                            <input id="admin-user-password" name="password" type="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>

                        <div>
                            <label for="admin-user-password-confirmation" class="block text-sm font-medium text-gray-700">{{ __('Confirm Password') }}</label>
                            <input id="admin-user-password-confirmation" name="password_confirmation" type="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>

                        <button type="submit" class="inline-flex items-center rounded-lg border border-indigo-700 bg-indigo-600 px-5 py-3 text-sm font-bold text-black shadow-md shadow-indigo-200 transition hover:bg-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-200">
                            {{ __('Create Admin User') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold">{{ __('Existing Admin Users') }}</h3>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-gray-300 bg-slate-50 text-gray-700">
                                    <th class="py-2 pr-4 pl-2">{{ __('Name') }}</th>
                                    <th class="py-2 pr-4">{{ __('Email') }}</th>
                                    <th class="py-2 pr-4">{{ __('Role') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr class="border-b border-gray-200 odd:bg-white even:bg-slate-50">
                                        <td class="py-2 pr-4 pl-2">{{ $user->name }}</td>
                                        <td class="py-2 pr-4">{{ $user->email }}</td>
                                        <td class="py-2 pr-4">{{ $user->is_admin ? __('Admin') : __('User') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-gray-500">{{ __('No users found.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
