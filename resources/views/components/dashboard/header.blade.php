<header class="main-header">
    <div class="header-inner">
        <a href="{{ route('dashboard') }}" class="logo">
            <span class="logo-mark"><i class="fa-solid fa-basket-shopping" aria-hidden="true"></i></span>
            <span class="logo-label">OrderTaker</span>
        </a>

        <div class="nav-container">
            @auth
                <nav class="header-nav">
                    <button type="button" class="header-action-button" data-dashboard-action="print" aria-label="Print workspace">
                        <i class="fa-solid fa-print" aria-hidden="true"></i>
                        <span class="header-action-label">Print</span>
                    </button>
                    <button type="button" class="header-action-button" data-dashboard-action="kitchen" aria-label="Print kitchen ticket">
                        <i class="fa-solid fa-utensils" aria-hidden="true"></i>
                        <span class="header-action-label">Kitchen</span>
                    </button>
                    <a href="{{ route('orders.create') }}" class="new-order-button" data-dashboard-action="new-order" aria-label="New order">
                        <span class="new-order-label">New order</span>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="header-signout" aria-label="Sign out">
                            <span>Sign out</span>
                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                        </button>
                    </form>
                </nav>
            @else
                <nav class="header-nav">
                    <a href="{{ route('login') }}" class="btn-login">Sign In</a>
                    <a href="{{ route('register') }}" class="nav-link">Register</a>
                </nav>
            @endauth
        </div>

        <button class="sidebar-open-toggle" type="button" data-sidebar-toggle aria-expanded="true" aria-controls="sidebar" aria-label="Open orders sidebar">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>
    </div>
</header>
