<!doctype html>
<html lang="en">


<head>
    @include('includes.adminHeader')
</head>

<body data-layout="horizontal">


    <div id="layout-wrapper">


        @include('includes.topbar')
        @include('includes.topnav')


        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            {{ $slot }}

            @include('includes.Footer')

        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/pages/form-advanced.init.js') }}"></script>
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
    <!-- pace js -->
    <script src="{{ asset('assets/libs/pace-js/pace.min.js') }}"></script>

    <!-- apexcharts -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>


    <!-- Datatable init js -->
    <script src="{{ asset('assets/js/pages/datatables.init.js') }}"></script>

    <!-- Required datatable js -->
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

    <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>


    <!-- Plugins js-->
    <script src="{{ asset('assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js') }}"></script>
    <script src="{{ asset('assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js') }}">
    </script>
    <!-- dashboard init -->
    <script src="{{ asset('assets/js/pages/dashboard.init.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.responsive.fix.js') }}"></script>

    <script>
        // Apply persisted theme before main app script
        (function () {
            try {
                var mode = localStorage.getItem('fixitzed-layout-mode');
                if (mode === 'dark' || mode === 'light') {
                    document.body.setAttribute('data-layout-mode', mode);
                    document.body.setAttribute('data-topbar', mode);
                    document.body.setAttribute('data-sidebar', mode);
                }
            } catch (e) {}
        })();
    </script>

    <script src="{{ asset('assets/js/app.js') }}"></script>

    <script>
        (function () {
            const flash = {
                success: @json(session('success')),
                error: @json(session('error')),
                warning: @json(session('warning')),
            };

            const titles = {
                success: 'Success',
                error: 'Error',
                warning: 'Notice',
            };

            function showAlert(type, message, options = {}) {
                if (!message) return Promise.resolve();
                return Swal.fire({
                    icon: type,
                    title: titles[type] ?? 'Notice',
                    text: message,
                    confirmButtonColor: '#F1592A',
                    confirmButtonText: 'OK',
                    ...options,
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                if (flash.success) showAlert('success', flash.success);
                if (flash.error) showAlert('error', flash.error);
                if (flash.warning) showAlert('warning', flash.warning);
            });

            window.addEventListener('flash-message', function (event) {
                const detail = event.detail || {};
                const type = detail.type || 'info';
                const message = detail.message;
                const redirect = detail.redirect;
                const swalOptions = {};
                if (detail.timer) {
                    swalOptions.timer = detail.timer;
                    swalOptions.timerProgressBar = true;
                    swalOptions.showConfirmButton = false;
                }

                showAlert(type, message, swalOptions).then(() => {
                    if (redirect) {
                        window.location.assign(redirect);
                    }
                });
            });
        })();
    </script>

    <script>
        // Persist theme on toggle and re-apply after Livewire navigation
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#mode-setting-btn')) return;
            setTimeout(function () {
                var mode = document.body.getAttribute('data-layout-mode') === 'dark' ? 'dark' : 'light';
                try { localStorage.setItem('fixitzed-layout-mode', mode); } catch (e) {}
            }, 0);
        });
        window.addEventListener('livewire:navigated', function () {
            var mode = localStorage.getItem('fixitzed-layout-mode');
            if (mode === 'dark' || mode === 'light') {
                document.body.setAttribute('data-layout-mode', mode);
                document.body.setAttribute('data-topbar', mode);
                document.body.setAttribute('data-sidebar', mode);
            }
        });
    </script>

    <script>
        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-confirm-event]');
            if (!trigger) {
                return;
            }

            event.preventDefault();

            const eventName = trigger.getAttribute('data-confirm-event');
            if (!eventName) {
                return;
            }

            const title = trigger.getAttribute('data-confirm-title') || 'Are you sure?';
            const message = trigger.getAttribute('data-confirm-message') || 'This action cannot be undone.';
            const icon = trigger.getAttribute('data-confirm-icon') || 'warning';

            let payload = {};
            const rawPayload = trigger.getAttribute('data-confirm-payload');
            if (rawPayload) {
                try {
                    payload = JSON.parse(rawPayload);
                } catch (e) {
                    console.warn('Unable to parse data-confirm-payload', e);
                }
            }

            const id = trigger.getAttribute('data-confirm-id');
            if (id !== null) {
                payload.id = payload.id ?? (isNaN(id) ? id : Number(id));
            }

            Swal.fire({
                title,
                text: message,
                icon,
                showCancelButton: true,
                confirmButtonColor: '#F1592A',
                cancelButtonColor: '#6c757d',
                confirmButtonText: trigger.getAttribute('data-confirm-button') || 'Yes, proceed',
                cancelButtonText: trigger.getAttribute('data-cancel-button') || 'Cancel',
            }).then(result => {
                if (result.isConfirmed) {
                    Livewire.dispatch(eventName, payload);
                }
            });
        });
    </script>

    <script>
        (function () {
            const permissions = new Set(@json(auth()->user()?->getAllPermissions()->pluck('name') ?? []));

            function applyPermissionGuards(root) {
                if (!root) return;
                root.querySelectorAll('[data-permission]').forEach((el) => {
                    const required = (el.dataset.permission || '')
                        .split('|')
                        .map((value) => value.trim())
                        .filter(Boolean);

                    if (required.length && !required.every((permission) => permissions.has(permission))) {
                        el.remove();
                    }
                });

                root.querySelectorAll('[data-permission-any]').forEach((el) => {
                    const options = (el.dataset.permissionAny || '')
                        .split('|')
                        .map((value) => value.trim())
                        .filter(Boolean);

                    if (options.length && !options.some((permission) => permissions.has(permission))) {
                        el.remove();
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                applyPermissionGuards(document);
            });

            window.addEventListener('livewire:navigated', function () {
                applyPermissionGuards(document);
            });
        })();
    </script>
      <!-- choices js -->
        <script src="assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>

        <!-- color picker js -->
        <script src="{{ asset('assets/libs/%40simonwep/pickr/pickr.min.js') }}"></script>
        <script src="{{ asset('assets/libs/%40simonwep/pickr/pickr.es5.min.js') }}"></script>

        <!-- datepicker js -->
        <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>

        <!-- init js -->
        <script src="{{ asset('assets/js/pages/form-advanced.init.js') }}"></script>

</body>

</html>
{{-- 
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Platform')" class="grid">
                <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                <flux:navlist.item icon="users" :href="route('users.index')"
                    :current="request()->routeIs('users.index')" wire:navigate>{{ __('Users') }}</flux:navlist.item>
                <flux:navlist.item icon="link" :href="route('role.index')"
                    :current="request()->routeIs('role.index')" wire:navigate>{{ __('Role') }}</flux:navlist.item>
                @if (auth()->user()->can('view.services') || auth()->user()->can('create.services') || auth()->user()->can('edit.services') || auth()->user()->can('show.services') || auth()->user()->can('delete.services'))
                    
                <flux:navlist.item icon="list-bullet" :href="route('services.index')"
                    :current="request()->routeIs('services.index')" wire:navigate>{{ __('Services') }}</flux:navlist.item>
                @endif

                <flux:navlist.item icon="banknotes" :href="route('subscriptions.plans')"
                    :current="request()->routeIs('subscriptions.plans')" wire:navigate>{{ __('Subscription Plans') }}</flux:navlist.item>
                <flux:navlist.item icon="receipt-percent" :href="route('subscriptions.purchases')"
                    :current="request()->routeIs('subscriptions.purchases')" wire:navigate>{{ __('Purchases') }}</flux:navlist.item>
                <flux:navlist.item icon="wallet" :href="route('wallet.index')"
                    :current="request()->routeIs('wallet.index')" wire:navigate>{{ __('Wallets') }}</flux:navlist.item>

            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        <flux:navlist variant="outline">
            <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit"
                target="_blank">
                {{ __('Repository') }}
            </flux:navlist.item>

            <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire"
                target="_blank">
                {{ __('Documentation') }}
            </flux:navlist.item>
        </flux:navlist>

        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                icon:trailing="chevrons-up-down" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                        {{ __('Settings') }}</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html> --}}
