@props(['orders', 'selectedDate'])

<aside class="sidebar" id="sidebar" aria-label="Orders sidebar">
    <div class="sidebar-header">
        <div class="sidebar-date-heading">
            <button type="button" class="sidebar-date-arrow" data-order-date-step="-1" aria-label="Show previous day">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
            </button>
            <label class="sidebar-date-picker">
                <span class="sr-only">Orders date</span>
                <input type="date" value="{{ $selectedDate }}" data-order-date>
            </label>
            <button type="button" class="sidebar-date-arrow" data-order-date-step="1" aria-label="Show next day">
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </button>
        </div>
        <button class="sidebar-close-toggle" type="button" data-sidebar-toggle aria-expanded="true" aria-controls="sidebar" aria-label="Close orders sidebar">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            <span class="sr-only">Close orders sidebar</span>
        </button>
    </div>

    <div class="order-list" id="orderList">
        @forelse($orders as $orderItem)
            <div class="order-item" data-order-id="{{ $orderItem->id }}">
                <div class="order-item-heading">
                    <div class="order-number">{{ $orderItem->order_number }}</div>
                    <button type="button" class="order-delete" data-order-delete="{{ $orderItem->id }}" aria-label="Delete order {{ $orderItem->order_number }}">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <h4 class="order-name">{{ Str::limit($orderItem->customer_name, 60) }}</h4>
                <div class="order-price">€{{ number_format((float) $orderItem->price, 2) }}</div>
            </div>
        @empty
            <p class="no-orders">No orders for this day.</p>
        @endforelse
    </div>

    @auth
        <div class="sidebar-footer">
            <a href="{{ route('orders.create') }}" class="sidebar-new-order" data-dashboard-action="new-order">
                <span>New order</span>
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
            </a>
        </div>
    @endauth
</aside>
