<div>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Show Users') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('form for Showing User') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>
    <div>
       

        <a href="{{ route('users.index') }}"
            class="cursor-pointer px-3 py-2 text-xs font-medium text-black bg-green-700 rounded-lg hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300">
            Back
        </a>
        <div wire:submit="submit" class="w-50">
           <p><strong>Name:</strong>{{ $user->name }}</p>
           <p><strong>Email:</strong>{{ $user->email }}</p>
        </div>
    </div>

</div>
