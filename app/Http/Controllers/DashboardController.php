<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $selectedDate = $request->date('date')?->toDateString() ?? now()->toDateString();
        $orders = $user->orders()
            ->select(['id', 'order_number', 'customer_name', 'price'])
            ->whereDate('created_at', $selectedDate)
            ->latest('created_at')
            ->get();

        return view('layouts.dashboard', [
            'title' => 'OrderTaker - Dashboard',
            'orders' => $orders,
            'user' => $user,
            'data' => [
                'selectedOrder' => null,
                'selectedOrderId' => null,
            ],
            'menuItems' => MenuItem::query()
                ->select(['item_number', 'name', 'price'])
                ->orderBy('item_number')
                ->get(),
            'selectedDate' => $selectedDate,
        ]);
    }
}
