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
                        <a class="nav-link dropdown-toggle arrow-none" href="{{ asset('index.html') }}"
                            id="topnav-dashboard" role="button">
                            {{-- <i data-feather="home"></i> --}}
                            <span data-key="t-dashboards">Booking</span>
                        </a>
                    </li>


                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="{{ asset('#') }}" id="topnav-pages"
                            role="button">
                            {{-- <i data-feather="briefcase"></i> --}}
                            <span data-key="t-elements">Services</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">

                            <a href="{{ asset('apps-calendar.html') }}" class="dropdown-item"
                                data-key="t-calendar">Category</a>
                            <a href="{{ asset('apps-chat.html') }}" class="dropdown-item" data-key="t-chat">Sub
                                Category</a>
                            <a href="{{ asset('apps-chat.html') }}" class="dropdown-item" data-key="t-chat">All
                                Services</a>
                            <a href="{{ asset('apps-chat.html') }}" class="dropdown-item" data-key="t-chat">Services
                                Request List</a>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="{{ asset('#') }}" id="topnav-pages"
                            role="button">
                            {{-- <i data-feather="users"></i> --}}
                            <span data-key="t-elements">Users</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">
                            <a href="{{ route('users.index') }}" class="dropdown-item" data-key="t-chat">All
                                Users</a>
                            <a href="{{ asset('apps-calendar.html') }}" class="dropdown-item"
                                data-key="t-calendar">Unverified Users</a>
                            <a href="{{ asset('apps-chat.html') }}" class="dropdown-item"
                            data-key="t-chat">Customers</a>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="{{ asset('#') }}"
                                    id="topnav-email" role="button">
                                    <span data-key="t-email">Fixer</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-email">
                                    <a href="{{ asset('apps-email-inbox.html') }}"" class="dropdown-item"
                                        data-key="t-inbox">Fixer List</a>
                                    <a href="{{ asset('apps-email-read.html') }}"" class="dropdown-item"
                                        data-key="t-read-email">Fixer Request List</a>
                                </div>
                            </div>
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

                            <a href="{{ asset('apps-calendar.html') }}" class="dropdown-item"
                                data-key="t-calendar">Payments</a>
                            <a href="{{ asset('apps-chat.html') }}" class="dropdown-item"
                                data-key="t-chat">Earnings</a>
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

                            <a href="{{ asset('apps-calendar.html') }}" class="dropdown-item"
                                data-key="t-calendar">Coupon List</a>
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

                            <a href="{{ asset('apps-calendar.html') }}" class="dropdown-item"
                                data-key="t-calendar">User Ratings List</a>
                            <a href="{{ asset('apps-chat.html') }}" class="dropdown-item" data-key="t-chat">Fixer
                                Ratings List</a>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="{{ asset('#') }}" id="topnav-pages"
                            role="button">
                            {{-- <i data-feather="payments"></i> --}}
                            <span data-key="t-elements"></span>
                            <div class="arrow-down">Settings</div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">

                              <div class="dropdown">

                                <a href="{{ route('role.index') }}" class="dropdown-item" data-key="t-inbox">
                                    Role & Permission
                                </a>

                            </div>
                        </div>
                    </li>

                </ul>
            </div>
        </nav>
    </div>
</div>
