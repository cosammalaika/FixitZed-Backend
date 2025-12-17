<div class="topnav">
    <div class="container-fluid">
        <nav class="navbar navbar-light navbar-expand-lg topnav-menu">
            <div class="collapse navbar-collapse" id="topnav-menu-content">
                <ul class="navbar-nav">
                    @can('view.dashboard')
                        <li class="nav-item dropdown" data-permission="view.dashboard">
                            <a class="nav-link dropdown-toggle arrow-none" href="{{ route('dashboard') }}"
                                id="topnav-dashboard" role="button">
                                <span data-key="t-dashboards">Dashboard</span>
                            </a>
                        </li>
                    @endcan

                    @canany(['view.users', 'view.fixers'])
                        <li class="nav-item dropdown" data-permission-any="view.users|view.fixers">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-users" role="button">
                                <span data-key="t-elements">Users</span>
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-users">
                                @can('view.users')
                                    <a href="{{ route('users.index') }}" class="dropdown-item" data-permission="view.users"
                                        data-key="t-chat">Users</a>
                                @endcan
                                @can('view.fixers')
                                    <a href="{{ route('fixer.index') }}" class="dropdown-item" data-permission="view.fixers"
                                        data-key="t-chat">Fixer</a>
                                @endcan
                            </div>
                        </li>
                    @endcanany

                    @canany(['view.services', 'view.service_requests'])
                        <li class="nav-item dropdown" data-permission-any="view.services|view.service_requests">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-services" role="button">
                                <span data-key="t-elements">Services</span>
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-services">
                                @can('view.services')
                                    <a href="{{ route('services.index') }}" class="dropdown-item" data-permission="view.services"
                                        data-key="t-chat">Services</a>
                                @endcan
                                @can('view.service_requests')
                                    <a href="{{ route('serviceRequest.index') }}" class="dropdown-item"
                                        data-permission="view.service_requests" data-key="t-chat">Services Requests</a>
                                @endcan
                            </div>
                        </li>
                    @endcanany

                    @canany(['view.categories', 'view.subcategories'])
                        <li class="nav-item dropdown" data-permission-any="view.categories|view.subcategories">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-categories" role="button">
                                <span data-key="t-elements">Category</span>
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-categories">
                                @can('view.categories')
                                    <a href="{{ route('category.index') }}" class="dropdown-item" data-permission="view.categories"
                                        data-key="t-calendar">Categories</a>
                                @endcan
                                @can('view.subcategories')
                                    <a href="{{ route('subcategory.index') }}" class="dropdown-item"
                                        data-permission="view.subcategories" data-key="t-chat">Sub Categories</a>
                                @endcan
                            </div>
                        </li>
                    @endcanany

                    @canany(['view.payments', 'view.payment_methods', 'view.earnings'])
                        <li class="nav-item dropdown"
                            data-permission-any="view.payments|view.payment_methods|view.earnings">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-transactions"
                                role="button">
                                <span data-key="t-elements">Transactions</span>
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-transactions">
                                @can('view.payments')
                                    <a href="{{ route('payment.index') }}" class="dropdown-item" data-permission="view.payments"
                                        data-key="t-calendar">Payments</a>
                                @endcan
                                @can('view.payment_methods')
                                    <a href="{{ route('payment-methods.index') }}" class="dropdown-item"
                                        data-permission="view.payment_methods" data-key="t-chat">Payment Methods</a>
                                @endcan
                                @can('view.earnings')
                                    <a href="{{ route('earning.index') }}" class="dropdown-item" data-permission="view.earnings"
                                        data-key="t-chat">Earnings</a>
                                @endcan
                            </div>
                        </li>
                    @endcanany

                    @canany(['view.coupons', 'view.notifications'])
                        <li class="nav-item dropdown" data-permission-any="view.coupons|view.notifications">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-promotional" role="button">
                                <span data-key="t-elements">Promotional</span>
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-promotional">
                                @can('view.coupons')
                                    <a href="{{ route('coupon.index') }}" class="dropdown-item" data-permission="view.coupons"
                                        data-key="t-calendar">Coupon</a>
                                @endcan
                                @can('view.notifications')
                                    <a href="{{ route('notification.index') }}" class="dropdown-item"
                                        data-permission="view.notifications" data-key="t-calendar">Notifications</a>
                                @endcan
                            </div>
                        </li>
                    @endcanany

                    @can('view.ratings')
                        <li class="nav-item dropdown" data-permission="view.ratings">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-ratings" role="button">
                                <span data-key="t-elements">Rating</span>
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-ratings">
                                <a href="{{ route('rating.index') }}" class="dropdown-item" data-permission="view.ratings"
                                    data-key="t-chat">Fixer Ratings List</a>
                            </div>
                        </li>
                    @endcan

                    @canany(['view.subscriptions', 'view.wallet'])
                        <li class="nav-item dropdown" data-permission-any="view.subscriptions|view.wallet">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-subscriptions"
                                role="button">
                                <span data-key="t-elements">Subscriptions</span>
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-subscriptions">
                                @can('view.subscriptions')
                                    <a href="{{ route('subscriptions.plans') }}" class="dropdown-item"
                                        data-permission="view.subscriptions" data-key="t-chat">Plans</a>
                                    <a href="{{ route('subscriptions.purchases') }}" class="dropdown-item"
                                        data-permission="view.subscriptions" data-key="t-chat">Purchases</a>
                                @endcan
                                @can('view.wallet')
                                    <a href="{{ route('wallet.index') }}" class="dropdown-item" data-permission="view.wallet"
                                        data-key="t-chat">Wallets</a>
                                @endcan
                            </div>
                        </li>
                    @endcanany


                    @can('view.reports')
                        <li class="nav-item" data-permission="view.reports">
                            <a class="nav-link" href="{{ route('reportd.index') }}">
                                <span data-key="t-reports">Reports</span>
                            </a>
                        </li>
                        <li class="nav-item" data-permission="view.reports">
                            <a class="nav-link" href="{{ route('issues.index') }}">
                                <span data-key="t-issues">Issues</span>
                            </a>
                        </li>
                    @endcan

                    @canany(['edit.settings', 'view.roles', 'view.logs', 'view.location_options'])
                        <li class="nav-item dropdown"
                            data-permission-any="edit.settings|view.roles|view.logs|view.location_options">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-settings" role="button">
                                <span data-key="t-elements">Settings</span>
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-settings">
                                @can('edit.settings')
                                    <a href="{{ route('settings.general') }}" class="dropdown-item"
                                        data-permission="edit.settings" data-key="t-calendar">General</a>
                                @endcan
                                @can('view.roles')
                                    <a href="{{ route('role.index') }}" class="dropdown-item" data-permission="view.roles"
                                        data-key="t-calendar">Role &amp; Permission</a>
                                @endcan
                                @can('view.logs')
                                    <a href="{{ route('logs.index') }}" class="dropdown-item" data-permission="view.logs"
                                        data-key="t-calendar">System Logs</a>
                                @endcan
                                @can('view.location_options')
                                    <a href="{{ route('location-options.index') }}" class="dropdown-item"
                                        data-permission="view.location_options" data-key="t-calendar">Locations</a>
                                @endcan
                            </div>
                        </li>
                    @endcanany
                </ul>
            </div>
        </nav>
    </div>
</div>
