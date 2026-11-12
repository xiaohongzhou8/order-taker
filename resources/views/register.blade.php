<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }} - Register</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-950">
        <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8 sm:px-6">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(37,99,235,0.28),transparent_38%),radial-gradient(circle_at_bottom_right,rgba(124,58,237,0.24),transparent_35%)]"></div>
            <div class="relative grid w-full max-w-5xl overflow-hidden rounded-3xl border border-white/10 bg-white shadow-2xl shadow-blue-950/30 dark:bg-slate-900 lg:grid-cols-[0.85fr_1.15fr]">
                <aside class="hidden flex-col justify-between bg-linear-to-br from-blue-700 via-indigo-700 to-violet-800 p-10 text-white lg:flex">
                    <div>
                        <div class="mb-12 flex items-center gap-3">
                            <span class="grid h-11 w-11 place-items-center rounded-2xl bg-white/15 text-xl shadow-lg shadow-blue-950/20">
                                <i class="fa-solid fa-basket-shopping" aria-hidden="true"></i>
                            </span>
                            <span class="text-xl font-extrabold tracking-tight">OrderTaker</span>
                        </div>
                        <p class="mb-3 text-sm font-semibold uppercase tracking-[0.2em] text-blue-200">Start serving smarter</p>
                        <h1 class="max-w-sm text-4xl font-black leading-tight tracking-tight">Your next great order starts here.</h1>
                        <p class="mt-5 max-w-sm text-sm leading-6 text-blue-100">
                            Create your workspace and keep every food order organized from first click to final receipt.
                        </p>
                    </div>
                    <div class="space-y-4 text-sm text-blue-100">
                        <div class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-emerald-300" aria-hidden="true"></i><span>Fast order entry</span></div>
                        <div class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-emerald-300" aria-hidden="true"></i><span>Clear item and quantity tracking</span></div>
                        <div class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-emerald-300" aria-hidden="true"></i><span>Print-ready receipts</span></div>
                    </div>
                </aside>

                <main class="p-6 sm:p-10">
                    <div class="mb-8 lg:hidden">
                        <div class="flex items-center gap-3 text-slate-900 dark:text-white">
                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-linear-to-br from-blue-600 to-violet-600 text-white shadow-lg shadow-blue-500/25">
                                <i class="fa-solid fa-basket-shopping" aria-hidden="true"></i>
                            </span>
                            <span class="text-xl font-extrabold tracking-tight">OrderTaker</span>
                        </div>
                    </div>

                    <div class="mb-8">
                        <p class="mb-2 text-sm font-semibold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400">Welcome</p>
                        <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Create your account</h2>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Set up your order workspace in less than a minute.</p>
                    </div>

                    <form method="POST" action="{{ route('register.post') }}" class="space-y-5">
                        @csrf
                        <div>
                            <label for="name" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">Full name</label>
                            <div class="relative">
                                <i class="fa-regular fa-user pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true"></i>
                                <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Alex Morgan"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-blue-400 dark:focus:bg-slate-800 dark:focus:ring-blue-900/40">
                            </div>
                            @error('name') <span class="mt-1 block text-xs font-medium text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">Email address</label>
                            <div class="relative">
                                <i class="fa-regular fa-envelope pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true"></i>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-blue-400 dark:focus:bg-slate-800 dark:focus:ring-blue-900/40">
                            </div>
                            @error('email') <span class="mt-1 block text-xs font-medium text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="password" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">Password</label>
                                <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-blue-400 dark:focus:bg-slate-800 dark:focus:ring-blue-900/40">
                                @error('password') <span class="mt-1 block text-xs font-medium text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">Confirm password</label>
                                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-blue-400 dark:focus:bg-slate-800 dark:focus:ring-blue-900/40">
                            </div>
                        </div>
                        <button type="submit" class="w-full rounded-xl bg-linear-to-r from-blue-600 to-indigo-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-500/25 transition hover:-translate-y-0.5 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-900/50">
                            Create account <i class="fa-solid fa-arrow-right ml-2" aria-hidden="true"></i>
                        </button>
                    </form>

                    <p class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-bold text-blue-600 transition hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">Sign in</a>
                    </p>
                </main>
            </div>
        </div>
    </body>
</html>
