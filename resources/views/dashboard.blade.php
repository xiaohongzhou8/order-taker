<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }} - Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Additional custom styles for dashboard */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            text-align: center;
        }

        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -80px auto 1.5rem;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }

        .avatar svg {
            width: 50px;
            height: 50px;
            color: white;
        }

        .welcome-message h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-message p {
            color: #6b7280;
            font-size: 1.1rem;
            margin-top: 0.5rem;
        }

        .user-info-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }

        .info-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }

        .info-card:hover {
            transform: translateY(-4px);
        }

        .info-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .info-value {
            color: #1f2937;
            font-weight: 500;
            font-size: 1.1rem;
        }

        .btn-logout {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: linear-gradient(45deg, #ef4444, #dc2626);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            box-shadow: 0 8px 32px rgba(239, 68, 68, 0.3);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-logout:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 40px rgba(239, 68, 68, 0.4);
        }

        .welcome-message svg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100px;
            height: 100px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="avatar">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
            </div>

            <div class="welcome-message">
                <h1>Welcome back, {{ $user->name }}!</h1>
                <p>You're logged in as <strong>{{ $user->email }}</strong></p>
            </div>

            <!-- Welcome Message Icon -->
            <svg class="welcome-message__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.25-.749L3 20l1.395-3.727A3.5 3.5 0 1013.858 9.166 3.5 3.5 0 0010.9 3c0 .47-.07.92-.195 1.361H6a8 8 0 017.556 7zM14.552 4A4.485 4.485 0 0014 6.5c-1.656 0-3.236.956-4.134 2.447l-.48 1.04a3.97 3.97 0 00-2.15 2.52H4V6h4.985a3.981 3.981 0 013.705-2.43zM7.5 4c.856 0 1.65.309 2.248.808C9.606 4.31 10.295 4.45 11 4.5V8H7z" />
            </svg>
        </div>

        <!-- User Information Section -->
        <section class="user-info-section">
            <div class="info-card">
                <div class="info-label">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profile Status</span>
                </div>
                <div class="info-value">Active & Verified</div>
            </div>

            <div class="info-card">
                <div class="info-label">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Account Created</span>
                </div>
                <div class="info-value">{{ \Carbon\Carbon::parse($user->created_at)->format('F Y') }}</div>
            </div>

            <div class="info-card">
                <div class="info-label">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Last Login</span>
                </div>
                <div class="info-value">{{ $user->last_login_at?->format('M d, Y') ?? 'Never' }}</div>
            </div>
        </section>
    </div>

    <!-- Logout Button -->
    <form method="POST" action="{{ route('logout') }}" class="btn-logout">
        @csrf
        <button type="submit" style="border: 0; background: transparent; color: inherit; cursor: pointer;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        Sign Out
        </button>
    </form>

    <script>
        // Prevent logout button from being scrolled with mouse wheel
        document.querySelectorAll('.btn-logout').forEach(btn => {
            btn.onwheel = (e) => e.preventDefault();
            btn.ondrag = () => false;
        });
    </script>
</body>
</html>
