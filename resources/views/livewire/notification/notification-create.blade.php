<div class="container mt-4">

    <form wire:submit.prevent="submit">
        {{-- Recipient Type --}}
        <div class="mb-3">
            <select wire:model="recipient_type" class="form-control" required data-notification-recipient>
                <option value="">-- Select Recipient Type --</option>
                <option value="Customer">All Customers</option>
                <option value="Fixer">All Fixers</option>
                <option value="Admin">All Admins</option>
                <option value="Support">All Support</option>
                <option value="Individual">Individual User</option>
            </select>

            @error('recipient_type')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        @php
            $showIndividualSelector = is_string($recipient_type) && strcasecmp($recipient_type, 'Individual') === 0;
        @endphp

        {{-- User Selection (only if recipient_type is Individual) --}}
        <div class="mb-3" id="notificationIndividualUser" @unless($showIndividualSelector) style="display: none;" @endunless>
            <label for="userId" class="form-label">Select User</label>
            <select wire:model="user_id" id="userId" class="form-control" @unless($showIndividualSelector) disabled @endunless>
                <option value="">-- Select User --</option>
                @foreach ($users as $user)
                    @php
                        $display = $user->display_name ?? '';
                        $email = $user->email ?? '';
                    @endphp
                    <option value="{{ $user->id }}">
                        {{ $display ?: $email }}
                        @if ($email && $display && strcasecmp($display, $email) !== 0)
                            ({{ $email }})
                        @endif
                    </option>
                @endforeach
            </select>
            @error('user_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        {{-- Title --}}
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" wire:model="title" id="title" class="form-control"
                placeholder="Enter notification title">
            @error('title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        {{-- Message --}}
        <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea wire:model="message" id="message" class="form-control" rows="4"
                placeholder="Enter notification message"></textarea>
            @error('message')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        {{-- Submit Button --}}
        <button type="submit" class="btn btn-primary">Send Notification</button>
    </form>
</div>

@once
    <script>
        document.addEventListener('livewire:initialized', () => {
            const toggleIndividualSelector = () => {
                const typeSelect = document.querySelector('[data-notification-recipient]');
                const container = document.getElementById('notificationIndividualUser');
                if (!typeSelect || !container) {
                    return;
                }

                const value = (typeSelect.value || '').trim().toLowerCase();
                const shouldShow = value === 'individual';

                container.style.display = shouldShow ? '' : 'none';

                const userSelect = container.querySelector('select');
                if (userSelect) {
                    userSelect.disabled = !shouldShow;
                }
            };

            toggleIndividualSelector();

            document.addEventListener('change', (event) => {
                if (event.target && event.target.matches('[data-notification-recipient]')) {
                    toggleIndividualSelector();
                }
            });

            Livewire.hook('message.processed', () => {
                toggleIndividualSelector();
            });
        });
    </script>
@endonce
