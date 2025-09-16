<div class="topnav">
    <div class="container-fluid">
        <nav class="navbar navbar-light navbar-expand-lg topnav-menu">

            <div class="collapse navbar-collapse" id="topnav-menu-content">
                <ul class="navbar-nav">

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="{{ route('dashboard') }}"
                            id="topnav-dashboard" role="button">
                            {{-- <i data-feather="home"></i> --}}
                            <span data-key="t-dashboards">Dashboard</span>
                        </a>
                    </li>


                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="{{ asset('#') }}" id="topnav-pages"
                            role="button">
                            {{-- <i data-feather="users"></i> --}}
                            <span data-key="t-elements">Users</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">
                            <a href="{{ route('users.index') }}" class="dropdown-item" data-key="t-chat">Users</a>
                            <a href="{{ route('fixer.index') }}" class="dropdown-item" data-key="t-chat">Fixer</a>
                            {{-- <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="{{ asset('#') }}"
                                    id="topnav-email" role="button">
                                    <span data-key="t-email">Fixer</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-email">
                                    <a href="{{ route('dashboard') }}" class="dropdown-item"
                                        data-key="t-inbox">Fixer List</a>
                                    <a href="{{ route('dashboard') }}" class="dropdown-item"
                                        data-key="t-read-email">Fixer Request List</a>
                                </div>
                            </div> --}}
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="{{ asset('#') }}" id="topnav-pages"
                            role="button">
                            {{-- <i data-feather="briefcase"></i> --}}
                            <span data-key="t-elements">Services</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">

                            <a href="{{ route('services.index') }}" class="dropdown-item" data-key="t-chat">Services</a>
                            <a href="{{ route('serviceRequest.index') }}" class="dropdown-item"
                                data-key="t-chat">Services
                                Requests</a>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="{{ asset('#') }}" id="topnav-pages"
                            role="button">
                            {{-- <i data-feather="briefcase"></i> --}}
                            <span data-key="t-elements">Category</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">

                            <a href="{{ route('category.index') }}" class="dropdown-item"
                                data-key="t-calendar">Categories</a>
                            <a href="{{ route('subcategory.index') }}" class="dropdown-item" data-key="t-chat">Sub
                                Categories</a>

                        </div>
                    </li>


                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="{{ asset('#') }}" id="topnav-pages"
                            role="button">
                            {{-- <i data-feather="payments"></i> --}}
                            <span data-key="t-elements">Transactions</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">

                            <a href="{{ route('payment.index') }}" class="dropdown-item"
                                data-key="t-calendar">Payments</a>
                            <a href="{{ route('earning.index') }}" class="dropdown-item" data-key="t-chat">Earnings</a>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="{{ asset('#') }}" id="topnav-pages"
                            role="button">
                            {{-- <i data-feather="payments"></i> --}}
                            <span data-key="t-elements">Promotional</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">

                            <a href="{{ route('coupon.index') }}" class="dropdown-item"
                                data-key="t-calendar">Coupon</a>
                            <a href="{{ route('notification.index') }}" class="dropdown-item"
                                data-key="t-calendar">Notifications</a>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="{{ asset('#') }}" id="topnav-pages"
                            role="button">
                            {{-- <i data-feather="payments"></i> --}}
                            <span data-key="t-elements">Rating</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">
                            <a href="{{ route('rating.index') }}" class="dropdown-item" data-key="t-chat">Fixer
                                Ratings List</a>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="{{ asset('#') }}" id="topnav-pages"
                            role="button">
                            {{-- <i data-feather="payments"></i> --}}
                            <span data-key="t-elements">Settings</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">

                            <a href="{{ route('role.index') }}" class="dropdown-item"
                                data-key="t-calendar">Role & Permission</a>
                            <a href="{{ route('logs.index') }}" class="dropdown-item"
                                data-key="t-calendar">System Logs</a>
                            <a href="{{ route('location-options.index') }}" class="dropdown-item"
                                data-key="t-calendar">Locations</a>
                        </div>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="{{ route('reportd.index') }}">
                            <span data-key="t-reports">Reports</span>
                        </a>
                    </li>


                </ul>
            </div>
        </nav>
    </div>
</div>
