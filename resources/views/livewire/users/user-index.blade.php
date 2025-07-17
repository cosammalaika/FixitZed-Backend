<div>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Users') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Manage your Users') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>
    <div>
        @if (session('success'))
            <div class="flex items-center p-2 mb-4 text-sm text-green-800 border border-green-300 rounded-lg bg-green-50 dark:bg-green-900 dark:text-green-300 dark:border-green-800"
                role="alert">
                <svg class="flex-shrink-0 w-8 h-8 mr-1 text-green-700 dark:text-green-300"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                </svg>
                <span class="font-medium"> {{ session('success') }} </span>
            </div>
        @endif

        @can('create.users')
            <a href="{{ route('users.create') }}"
                class="cursor-pointer px-3 py-2 text-xs font-medium text-black bg-green-700 rounded-lg hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300">
                Create user
            </a>
        @endcan
        <div class="overflow-x-auto mt-4">
            <table class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs uppercase bg-gray-50 text-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3">ID</th>
                        <th scope="col" class="px-6 py-3">Name</th>
                        <th scope="col" class="px-6 py-3">Email</th>
                        <th scope="col" class="px-6 py-3">Roles</th>
                        <th scope="col" class="px-6 py-3 w-70">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="odd:bg-white even:bg-gray-50 border-b border-gray-200">
                            <td class="px-6 py-2 font-medium text-gray-900">{{ $user->id }}</td>
                            <td class="px-6 py-2 text-gray-700">{{ $user->name }}</td>
                            <td class="px-6 py-2 text-gray-700">{{ $user->email }}</td>
                            <td class="px-6 py-2 text-gray-700">
                                @if ($user->roles)
                                    @foreach ($user->roles as $role)
                                        <flux:badge>{{ $role->name }}</flux:badge>
                                    @endforeach
                                @endif
                            </td>
                            <td class="px-6 py-2">
                                <div class="flex gap-2">
                                    @can('show.users')
                                        <a href="{{ route('users.show', $user->id) }}" variant="primary">
                                            Show
                                        </a>
                                    @endcan
                                    @can('edit.users')
                                        <a href="{{ route('users.edit', $user->id) }}" variant="primary">
                                            Edit
                                        </a>
                                    @endcan

                                    @can('delete.users')
                                        <button wire:click="delete({{ $user->id }})"
                                            wire:confirm="Are you Sure you want to delete user" variant="primary">
                                            Delete
                                        </button>
                                    @endcan
                                </div>
                            </td>

                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>

</div>
