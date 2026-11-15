<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --header-height: 76px;
            --sidebar-width: 340px;
        }

        .dashboard-layout {
            min-height: 100vh;
            background: #f8fafc;
            color: #0f172a;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: rgba(255, 255, 255, 0.94);
            border-left: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            z-index: 40;
            box-shadow: -8px 0 30px rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(14px);
            transition: width 220ms ease, transform 220ms ease;
        }

        .sidebar-header {
            position: relative;
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(135deg, #eff6ff, #eef2ff);
        }

        .sidebar-header h3 {
            margin: 0;
            color: #1e3a8a;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .sidebar-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .sidebar-date-heading { display: flex; align-items: center; justify-content: center; gap: .45rem; padding-right: 3rem; }
        .sidebar-date-picker input { width: 9.5rem; padding: .55rem .65rem; border: 1px solid #bfdbfe; border-radius: .65rem; background: #fff; color: #1e3a8a; font-size: .85rem; font-weight: 750; text-align: center; }
        .sidebar-date-arrow { display: grid; width: 2rem; height: 2rem; place-items: center; border: 1px solid #dbeafe; border-radius: .55rem; background: #fff; color: #2563eb; cursor: pointer; }
        .sidebar-date-arrow:hover { background: #eff6ff; border-color: #93c5fd; }

        .sidebar-close-toggle,
        .sidebar-open-toggle {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: grid;
            width: 2.5rem;
            height: 2.5rem;
            place-items: center;
            border: 1px solid #dbeafe;
            border-radius: 999px;
            background: #fff;
            color: #2563eb;
            box-shadow: 0 5px 15px rgba(15, 23, 42, .12);
            cursor: pointer;
            font-size: 1.1rem;
            transition: background 160ms ease, box-shadow 160ms ease, transform 160ms ease;
        }

        .sidebar-close-toggle { z-index: 50; }
        .sidebar-close-toggle:hover,
        .sidebar-open-toggle:hover {
            background: #eff6ff;
            box-shadow: 0 8px 18px rgba(37, 99, 235, .2);
            transform: translateY(-1px);
        }

        .sidebar-signout {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            padding: 0.75rem 1rem;
            border: 1px solid #fecaca;
            border-radius: 0.75rem;
            background: #fff1f2;
            color: #dc2626;
            font-size: 0.875rem;
            font-weight: 750;
            cursor: pointer;
            transition: background 160ms ease, border-color 160ms ease, transform 160ms ease;
        }

        .sidebar-signout:hover {
            border-color: #fca5a5;
            background: #fee2e2;
            transform: translateY(-1px);
        }
        .sidebar-close-toggle span:not(.sr-only) { font-size: 1.7rem; line-height: 1; }
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }

        .order-count {
            display: inline-grid;
            min-width: 1.75rem;
            height: 1.75rem;
            padding: 0 0.4rem;
            place-items: center;
            border-radius: 999px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 0.75rem;
            font-weight: 800;
        }

        .order-list {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .order-item {
            padding: 1rem;
            margin-bottom: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            background: #fff;
            cursor: pointer;
            transition: transform 180ms ease, border-color 180ms ease, box-shadow 180ms ease;
        }

        .order-item:hover {
            border-color: #93c5fd;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.12);
            transform: translateX(-4px);
        }

        .order-item.active {
            background: #eff6ff;
            border-color: #3b82f6;
            box-shadow: inset 4px 0 #2563eb;
        }

        .order-number {
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
        }

        .order-item-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .order-delete {
            display: grid;
            width: 1.65rem;
            height: 1.65rem;
            place-items: center;
            border: 0;
            border-radius: 0.45rem;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
        }

        .order-delete:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        .order-name {
            margin: 0;
            color: #1e293b;
            font-size: 0.98rem;
            font-weight: 650;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .order-price {
            margin-top: 0.35rem;
            color: #0f766e;
            font-size: 0.9rem;
            font-weight: 800;
        }

        .order-items { display: grid; gap: .65rem; }
        .order-item-detail { display: grid; grid-template-columns: 8rem 5rem minmax(0, 1fr) auto 2rem; align-items: center; gap: 1rem; padding: .8rem 1rem; border: 1px solid #e2e8f0; border-radius: .75rem; background: #f8fafc; }
        .order-item-detail span { min-width: 0; color: #334155; }
        .order-item-detail .item-name-cell { display: block; min-width: 0; color: #0f172a; font-weight: 700; overflow-wrap: anywhere; }
        .order-item-detail .item-price-cell { color: #0f766e; white-space: nowrap; }
        .item-quantity-controls { display: inline-flex; align-items: center; gap: .35rem; }
        .item-quantity-controls button, .item-delete-button { display: grid; width: 1.75rem; height: 1.75rem; place-items: center; border: 1px solid #cbd5e1; border-radius: .5rem; background: #fff; color: #475569; cursor: pointer; }
        .item-quantity-controls button:hover { border-color: #93c5fd; background: #eff6ff; color: #1d4ed8; }
        .item-delete-button { border-color: transparent; background: transparent; color: #94a3b8; }
        .item-delete-button:hover { border-color: #fecaca; background: #fee2e2; color: #dc2626; }
        .order-item-detail-heading { padding: .25rem 1rem; border: 0; background: transparent; color: #64748b; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .order-detail-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 2rem; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; }
        .order-total { display: flex; flex-direction: column; align-items: flex-end; gap: .25rem; color: #0f766e; white-space: nowrap; }
        .order-total-label { color: #64748b; font-size: .75rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .order-total strong { font-size: 2rem; font-weight: 850; }
        .order-total-input { width: 9rem; margin-top: .25rem; text-align: right; font-size: 1.25rem; font-weight: 800; }
        .no-order-items { padding: 1rem; border: 1px dashed #cbd5e1; border-radius: .75rem; background: #f8fafc; color: #64748b; font-size: .95rem; }
        .new-order-item-row { display: grid; grid-template-columns: 5rem minmax(0, 1fr) 5rem auto; align-items: center; gap: 1rem; padding: .8rem 1rem; border: 1px solid #e2e8f0; border-radius: .75rem; background: #f8fafc; }
        .order-edit-input { width: 100%; box-sizing: border-box; padding: .8rem 1rem; border: 1px solid #cbd5e1; border-radius: .75rem; background: #f8fafc; color: #0f172a; outline: none; transition: border-color 160ms ease, box-shadow 160ms ease, background 160ms ease; }
        .order-name-input { margin-top: .5rem; font-size: 1.25rem; font-weight: 700; }
        .order-edit-input:focus { border-color: #3b82f6; background: #fff; box-shadow: 0 0 0 4px #dbeafe; }
        .order-edit-actions { display: flex; align-items: center; justify-content: flex-end; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0; }
        .item-number-modal { position: fixed; inset: 0; z-index: 80; }
        .item-number-modal-backdrop { position: absolute; inset: 0; background: rgba(15, 23, 42, .48); backdrop-filter: blur(3px); }
        .item-number-modal-dialog { position: relative; width: min(28rem, calc(100% - 2rem)); margin: 15vh auto 0; padding: 2rem; border: 1px solid #dbeafe; border-radius: 1rem; background: #fff; box-shadow: 0 24px 60px rgba(15, 23, 42, .24); }
        .item-number-modal-dialog h2 { margin: 0; color: #0f172a; font-size: 1.35rem; font-weight: 800; }
        .item-number-modal-dialog p { color: #64748b; }
        .item-number-modal-close { position: absolute; top: .75rem; right: .75rem; display: grid; width: 2.25rem; height: 2.25rem; place-items: center; border: 0; border-radius: 999px; background: #f1f5f9; color: #475569; cursor: pointer; }
        .item-number-modal-close:hover { background: #fee2e2; color: #dc2626; }
        .item-number-modal-dialog .workspace-close { width: auto; height: auto; margin-top: 1rem; padding: .65rem 1rem; }

        .main-content {
            min-height: 100vh;
            margin-right: var(--sidebar-width);
            padding: calc(var(--header-height) + 2rem) 2.5rem 2.5rem;
            transition: margin-right 220ms ease;
        }

        .workspace-panel { min-height: 20rem; }
        .order-form-card { max-width: 46rem; margin: 0 auto; }
        .order-form-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 2rem; }
        .order-form-heading h1 { margin: .35rem 0 0; color: #0f172a; font-size: 1.8rem; font-weight: 800; letter-spacing: -.04em; }
        .order-form-heading p:not(.eyebrow) { margin: .5rem 0 0; color: #64748b; }
        .workspace-close { display: grid; width: 2.5rem; height: 2.5rem; place-items: center; border: 1px solid #e2e8f0; border-radius: .75rem; background: #fff; color: #64748b; cursor: pointer; }
        .order-form { display: grid; gap: 1.25rem; }
        .order-form label { display: block; margin-bottom: .5rem; color: #334155; font-size: .85rem; font-weight: 700; }
        .order-form label span { color: #94a3b8; font-weight: 400; }
        .order-form input, .order-form textarea { width: 100%; box-sizing: border-box; padding: .8rem 1rem; border: 1px solid #cbd5e1; border-radius: .65rem; background: #fff; color: #0f172a; outline: none; }
        .order-form input:focus, .order-form textarea:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px #dbeafe; }
        .form-error { min-height: 1.25rem; margin: .35rem 0 0; color: #dc2626; font-size: .8rem; }
        .order-form-actions { display: flex; justify-content: flex-end; gap: .75rem; }
        .form-cancel { padding: .65rem 1rem; border: 0; border-radius: .7rem; background: #f1f5f9; color: #475569; font-weight: 700; cursor: pointer; }
        .header-action-button { display: inline-flex; align-items: center; gap: .5rem; padding: .65rem 1rem; border: 1px solid #cbd5e1; border-radius: .7rem; background: #fff; color: #475569; font-size: .85rem; font-weight: 750; cursor: pointer; }
        .header-action-button:hover { border-color: #93c5fd; background: #eff6ff; color: #1d4ed8; }

        .sidebar-collapsed { --sidebar-width: 0px; }
        .sidebar-collapsed .sidebar { width: 0; border-left: 0; box-shadow: none; }
        .sidebar-collapsed .sidebar > * { opacity: 0; pointer-events: none; }
        .sidebar-collapsed .main-content { margin-right: 0; }

        .dashboard-intro {
            max-width: 980px;
            margin: 0 auto 1.5rem;
        }

        .eyebrow {
            margin: 0 0 0.4rem;
            color: #2563eb;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.13em;
            text-transform: uppercase;
        }

        .dashboard-intro h1 {
            margin: 0;
            color: #0f172a;
            font-size: clamp(1.7rem, 3vw, 2.45rem);
            font-weight: 800;
            letter-spacing: -0.05em;
        }

        .dashboard-intro p {
            max-width: 42rem;
            margin: 0.6rem 0 0;
            color: #64748b;
            font-size: 1rem;
        }

        .main-header {
            position: fixed;
            top: 0;
            right: var(--sidebar-width);
            left: 0;
            z-index: 30;
            min-height: var(--header-height);
            padding: 0 2.5rem;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.9);
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 6px 24px rgba(15, 23, 42, 0.07);
            backdrop-filter: blur(16px);
        }

        .main-header > div {
            width: 100%;
            gap: 1.5rem;
        }

        .nav-container {
            flex: 1;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .order-detail-card {
            max-width: 980px;
            margin: 0 auto;
        }

        .order-card {
            padding: 2.5rem;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        }

        .order-detail-section {
            margin-bottom: 2rem;
        }

        .detail-label {
            margin-bottom: 0.5rem;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .detail-content {
            color: #1e293b;
            font-size: 1.125rem;
            line-height: 1.75;
        }

        .no-order-detail,
        .no-orders {
            color: #64748b;
            text-align: center;
        }

        .no-order-detail {
            padding: 5rem 2rem;
            border: 1px dashed #cbd5e1;
            border-radius: 1.25rem;
            background: rgba(255, 255, 255, 0.72);
        }

        .print-heading { display: none; }

        @media (prefers-color-scheme: dark) {
            .dashboard-layout { background: #0f172a; color: #e2e8f0; }
            .dashboard-intro h1 { color: #f8fafc; }
            .dashboard-intro p { color: #94a3b8; }
            .header-welcome strong { color: #f8fafc; }
            .nav-link { color: #94a3b8; }
            .notification-button { background: #1e293b; border-color: #334155; color: #cbd5e1; }
            .no-order-detail { background: rgba(15, 23, 42, 0.45); border-color: #475569; }
            .main-header, .sidebar, .order-card, .order-item { background: #111827; border-color: #334155; }
            .sidebar-header { background: linear-gradient(135deg, #172554, #1e1b4b); border-color: #334155; }
            .sidebar-header h3, .order-name, .detail-content { color: #f8fafc; }
            .order-number, .detail-label, .no-order-detail, .no-orders { color: #94a3b8; }
            .order-item:hover, .order-item.active { background: #172554; }
            .sidebar-close-toggle { border-color: #334155; background: #1e293b; color: #93c5fd; }
            .sidebar-signout { border-color: #7f1d1d; background: #450a0a; color: #fca5a5; }
            .order-detail-header, .order-edit-actions { border-color: #334155; }
            .order-item-detail { background: #1e293b; border-color: #334155; }
            .order-item-detail span, .order-item-detail .item-name-cell { color: #f8fafc; }
            .order-item-detail-heading, .order-total-label { color: #94a3b8; }
            .order-edit-input { background: #1e293b; border-color: #475569; color: #f8fafc; }
            .order-edit-input:focus { background: #0f172a; }
        }

        @media (max-width: 900px) {
            :root { --sidebar-width: 290px; }
            .main-content { padding-left: 1.25rem; padding-right: 1.25rem; }
            .main-header { padding: 0 1.25rem; }
        }

        @media (max-width: 640px) {
            :root { --header-height: 76px; }
            .main-header { right: 0; align-items: center; padding: 0 .75rem; z-index: 45; }
            .main-header > div { align-items: center !important; flex-wrap: nowrap; gap: .5rem; }
            .header-nav { justify-content: flex-end; gap: .35rem; padding-right: 2.75rem; }
            .sidebar { position: fixed; top: 0; right: 0; bottom: 0; left: 0; width: 100vw; margin: 0; border-left: 0; border-radius: 0; z-index: 60; }
            .main-content { margin-right: 0; padding-top: calc(var(--header-height) + 1.25rem); }
            .order-card { padding: 1.5rem; }
            .order-detail-header { align-items: stretch; flex-direction: column; gap: 1rem; }
            .order-total { align-items: flex-start; }
            .order-item-detail { grid-template-columns: 7rem 3rem minmax(0, 1fr) auto 1.75rem; gap: .4rem; padding: .7rem; }
            .sidebar-close-toggle { top: 1rem; right: 1rem; }
            .sidebar-collapsed .sidebar { display: none; }
            .sidebar-collapsed .sidebar-close-toggle { display: none; }
            .sidebar-open-toggle { top: 1rem; right: 1rem; z-index: 70; }
        }

        @media print {
            @page { margin: 12mm; }
            body { background: #fff !important; color: #000 !important; }
            .order-detail-card { max-width: none; }
            .order-card { padding: 0; border: 0; box-shadow: none; }
            .order-edit-input { border: 0; padding: 0; background: transparent; box-shadow: none; resize: none; }
            .order-name-input { font-size: 1.4rem; }
            .print-heading { display: block; margin-bottom: 1rem; color: #000; font-size: 1.25rem; font-weight: 800; }
            .order-edit-actions,
            .item-quantity-controls button,
            .item-delete-button { display: none !important; }
            .order-item-detail { border-color: #000; background: #fff; }
            .order-detail-header { border-color: #000; }
            .order-total-label,
            .detail-label { color: #000; }
            .order-total strong,
            .order-item-detail .item-price-cell,
            .order-item-detail .item-name-cell { color: #000; }
        }

        @media (max-width: 767px) {
            :root { --sidebar-width: 100vw; }
            .main-header { padding-inline: .75rem; }
            .main-content { padding: calc(var(--header-height) + 1rem) .75rem 1rem; }
            .order-card { padding: 1.25rem; border-radius: 1rem; }
            .order-detail-header { gap: 1rem; margin-bottom: 1.25rem; padding-bottom: 1rem; }
            .order-total strong { font-size: 1.6rem; }
            .order-item-detail { grid-template-columns: 6rem 2.5rem minmax(0, 1fr) auto 1.75rem; }
            .item-number-modal-dialog { margin-top: 10vh; padding: 1.5rem; }
        }

        @media (min-width: 768px) and (max-width: 1023px) {
            :root { --sidebar-width: 300px; --header-height: 72px; }
            .main-header { padding-inline: 1.25rem; }
            .main-content { padding: calc(var(--header-height) + 1.5rem) 1.25rem 1.5rem; }
            .order-card { padding: 2rem; }
            .order-detail-card, .dashboard-intro { max-width: 760px; }
        }

        @media (min-width: 1024px) {
            .main-header { padding-inline: 2.5rem; }
            .main-content { padding: calc(var(--header-height) + 2rem) 2.5rem 2.5rem; }
        }
    </style>

    @if(session('message'))
        <style>
            .alert-container {
                position: fixed;
                top: 80px;
                right: 3rem;
                background-color: #10b981;
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 0.5rem;
                z-index: 100;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
        </style>
    @endif

</head>
<body class="dashboard-layout">

<x-dashboard.header />
<x-dashboard.sidebar :orders="$orders" :selected-date="$selectedDate" />

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Order Details Section -->
    <div class="order-detail-card workspace-panel" id="workspacePanel" data-order-id="{{ $data['selectedOrderId'] ?? '' }}" data-menu-items='@json($menuItems)'>
        @if($data['selectedOrder'] ?? null)
            <div class="order-card">
                <!-- Customer Info -->
                <section class="order-detail-section">
                    <h2 class="detail-label">Name</h2>
                    <div class="detail-content">
                        <strong>{{ $data['selectedOrder']->customer_name }}</strong>
                    </div>
                </section>

                <!-- Order Number -->
                <section class="order-detail-section">
                    <h2 class="detail-label">Order Number</h2>
                    <div class="detail-content">{{ $data['selectedOrder']->order_number }}</div>
                </section>

                <!-- Comments -->
                <section class="order-detail-section">
                    <h2 class="detail-label">Comments</h2>
                    <div class="detail-content">
                        @if($data['selectedOrder']->description)
                            <textarea class="order-edit-input" rows="4">{{ $data['selectedOrder']->description }}</textarea>
                        @else
                            <textarea class="order-edit-input" rows="4"></textarea>
                        @endif
                    </div>
                </section>

                <!-- Notes -->
                @if(!empty($data['selectedOrder']->notes))
                <section class="order-detail-section">
                    <h2 class="detail-label">Notes</h2>
                    <div class="detail-content">
                        <ul>
                            @foreach($data['selectedOrder']->notes as $note)
                                <li>{{ $note }}</li>
                            @endforeach
                        </ul>
                    </div>
                </section>
                @endif

            </div>
        @else
            <p class="no-order-detail">No order selected. Click on an order from the sidebar to view its details.</p>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const layout = document.body;
        const sidebarToggles = document.querySelectorAll('[data-sidebar-toggle]');
        const sidebarMediaQuery = window.matchMedia('(max-width: 640px)');

        const setSidebarState = (collapsed, persist = true) => {
            layout.classList.toggle('sidebar-collapsed', collapsed);
            sidebarToggles.forEach((toggle) => {
                toggle.setAttribute('aria-expanded', String(!collapsed));
                toggle.setAttribute(
                    'aria-label',
                    collapsed ? 'Open orders sidebar' : 'Close orders sidebar'
                );
                toggle.querySelector('.sr-only')?.replaceChildren(
                    document.createTextNode(collapsed ? 'Expand orders sidebar' : 'Collapse orders sidebar')
                );
            });
            if (persist) {
                window.localStorage.setItem('orders-sidebar-collapsed', String(collapsed));
            }
        };

        const isCollapsed = window.localStorage.getItem('orders-sidebar-collapsed') === 'true';
        setSidebarState(sidebarMediaQuery.matches || isCollapsed, false);
        sidebarMediaQuery.addEventListener('change', ({ matches }) => {
            setSidebarState(
                matches || window.localStorage.getItem('orders-sidebar-collapsed') === 'true',
                false
            );
        });

        sidebarToggles.forEach((toggle) => {
            toggle.addEventListener('click', () => {
                setSidebarState(!layout.classList.contains('sidebar-collapsed'));
            });
        });

    });
</script>

</body>
</html>
