<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardLayout extends Controller
{
    public function render(string $view, array $data = []): View
    {
        // Ensure dashboard layout includes the user's data in session
        if (! session()->has('user')) {
            session()->put('user', Auth::guard('web')->user());
        }

        return view('layouts.dashboard', [
            'title' => config('app.name'),
            'data' => $data,
        ]);
    }
}
