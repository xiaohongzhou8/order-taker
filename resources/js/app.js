import '@fortawesome/fontawesome-free/css/all.min.css';

let selectedOrder = null;

document.addEventListener('click', async (event) => {
    const dateStep = event.target.closest('[data-order-date-step]');

    if (dateStep) {
        event.preventDefault();
        event.stopPropagation();
        const dateInput = document.querySelector('[data-order-date]');
        const [year, month, day] = dateInput.value.split('-').map(Number);
        const date = new Date(Date.UTC(year, month - 1, day + Number(dateStep.dataset.orderDateStep)));
        const nextDate = [
            date.getUTCFullYear(),
            String(date.getUTCMonth() + 1).padStart(2, '0'),
            String(date.getUTCDate()).padStart(2, '0'),
        ].join('-');

        dateInput.value = nextDate;
        await refreshOrdersForDate(nextDate);
        return;
    }

    const itemAction = event.target.closest('[data-item-action]');

    if (itemAction) {
        event.preventDefault();
        event.stopPropagation();

        const orderForm = itemAction.closest('[data-order-edit-form]');
        const itemRow = itemAction.closest('[data-order-item-id]');
        const orderId = orderForm.dataset.orderId;
        const itemId = itemRow.dataset.orderItemId;
        const quantity = Number(itemRow.dataset.quantity);
        const action = itemAction.dataset.itemAction;
        const nextQuantity = action === 'increase' ? quantity + 1 : quantity - 1;
        const request = action === 'delete'
            ? fetch(`/orders/${orderId}/items/${itemId}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            : fetch(`/orders/${orderId}/items/${itemId}`, {
                method: 'PATCH',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ quantity: nextQuantity }),
            });
        const response = await request;

        if (response.ok) {
            const payload = await response.json();
            updateSidebarOrder(payload.order);
            renderOrderDetails(payload.order);
        }

        return;
    }

    const deleteButton = event.target.closest('[data-order-delete]');

    if (deleteButton) {
        event.preventDefault();
        event.stopPropagation();

        if (!window.confirm('Delete this order?')) {
            return;
        }

        const response = await fetch(`/orders/${deleteButton.dataset.orderDelete}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });

        if (response.ok) {
            const orderItem = deleteButton.closest('.order-item');
            orderItem.remove();

            if (!document.querySelector('.order-item')) {
                document.querySelector('#orderList').innerHTML = '<p class="no-orders">No orders yet.</p>';
            }
        }

        return;
    }

    const orderItem = event.target.closest('.order-item');

    if (orderItem) {
        const response = await fetch(`/orders/${orderItem.dataset.orderId}`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.ok) {
            const payload = await response.json();
            selectedOrder = payload.order;
            renderOrderDetails(payload.order);
            document.querySelectorAll('.order-item').forEach((item) => item.classList.remove('active'));
            orderItem.classList.add('active');
            window.history.pushState({}, '', `/orders/${orderItem.dataset.orderId}`);
        }

        return;
    }

    const action = event.target.closest('[data-dashboard-action]');

    if (!action) {
        return;
    }

    if (action.dataset.dashboardAction === 'print') {
        if (!selectedOrder) {
            window.alert('Select an order before printing.');
            return;
        }

        printAsciiReceipt(selectedOrder);
        return;
    }

    if (action.dataset.dashboardAction === 'close-order-form') {
        const response = await fetch('/dashboard', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (response.ok) {
            const documentHtml = await response.text();
            const parsed = new DOMParser().parseFromString(documentHtml, 'text/html');
            document.querySelector('#workspacePanel').innerHTML = parsed.querySelector('#workspacePanel').innerHTML;
            window.history.pushState({}, '', '/dashboard');
        }

        return;
    }

    if (action.dataset.dashboardAction !== 'new-order') {
        return;
    }

    event.preventDefault();
    const response = await fetch('/orders', {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            customer_name: 'New order',
            price: 0,
        }),
    });

    if (response.ok) {
        const payload = await response.json();
        const order = payload.order;
        const orderList = document.querySelector('#orderList');
        const noOrders = orderList.querySelector('.no-orders');
        const orderItem = document.createElement('div');

        noOrders?.remove();
        orderItem.className = 'order-item active';
        orderItem.dataset.orderId = order.id;
        orderItem.innerHTML = `
            <div class="order-number">${escapeHtml(order.order_number)}</div>
            <h4 class="order-name">${escapeHtml(order.customer_name)}</h4>
            <div class="order-price">€${Number(order.price).toFixed(2)}</div>
        `;
        orderList.prepend(orderItem);
        document.querySelectorAll('.order-item').forEach((item) => item.classList.remove('active'));
        orderItem.classList.add('active');
        renderOrderDetails(order, true);
        window.history.pushState({}, '', `/orders/${order.id}`);
    }
});

document.addEventListener('change', async (event) => {
    const dateInput = event.target.closest('[data-order-date]');

    if (dateInput && dateInput.value) {
        await refreshOrdersForDate(dateInput.value);
    }
});

async function refreshOrdersForDate(date) {
    const response = await fetch(`/dashboard?date=${encodeURIComponent(date)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });

    if (!response.ok) {
        return;
    }

    const documentHtml = await response.text();
    const parsed = new DOMParser().parseFromString(documentHtml, 'text/html');
    const parsedOrderList = parsed.querySelector('#orderList');
    const orderList = document.querySelector('#orderList');

    if (parsedOrderList && orderList) {
        orderList.innerHTML = parsedOrderList.innerHTML;
        selectedOrder = null;
        document.querySelector('#workspacePanel').innerHTML = '<p class="no-order-detail">No order selected. Click on an order from the sidebar to view its details.</p>';
        window.history.pushState({}, '', `/dashboard?date=${encodeURIComponent(date)}`);
    }
}

function escapeHtml(value) {
    const element = document.createElement('span');
    element.textContent = value;
    return element.innerHTML;
}

function updateSidebarOrder(order) {
    const orderItem = document.querySelector(`.order-item[data-order-id="${order.id}"]`);

    if (!orderItem) {
        return;
    }

    const price = orderItem.querySelector('.order-price');

    if (price) {
        price.textContent = `€${Number(order.price).toFixed(2)}`;
    }
}

function renderNewOrderItems(form) {
    const items = form._newOrderItems || [];
    const container = form.querySelector('[data-new-order-items]');
    const total = items.reduce((sum, item) => sum + (Number(item.price) * item.quantity), 0);

    form.querySelector('[name="price"]').value = total.toFixed(2);
    container.innerHTML = items.length
        ? items.map((item, index) => `
            <div class="new-order-item-row">
                <span>${escapeHtml(item.quantity)}</span>
                <strong>${escapeHtml(item.name)}</strong>
                <span>€${Number(item.price).toFixed(2)}</span>
                <button type="button" class="item-delete-button" data-new-item-delete="${index}" aria-label="Remove ${escapeHtml(item.name)}">
                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                </button>
                <input type="hidden" name="items[${index}][item_number]" value="${escapeHtml(item.item_number)}">
                <input type="hidden" name="items[${index}][quantity]" value="${escapeHtml(item.quantity)}">
            </div>`)
            .join('')
        : '<div class="no-order-items">Enter a menu item number to add food.</div>';
}

function renderOrderDetails(order, isNewOrder = false) {
    selectedOrder = order;
    const workspacePanel = document.querySelector('#workspacePanel');
    workspacePanel.dataset.orderId = String(order.id);
    const sortedItems = [...(order.items || [])].sort((first, second) => (
        Number(first.item_number) - Number(second.item_number)
    ));
    const name = isNewOrder ? order.order_number : order.customer_name;
    const itemEntry = `
        <section class="order-detail-section">
            <button type="button" class="new-order-button" data-open-item-modal>
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                Add item by number
            </button>
        </section>`;
    const notes = Array.isArray(order.notes) && order.notes.length
        ? `<section class="order-detail-section"><h2 class="detail-label">Notes</h2><div class="detail-content"><ul>${order.notes.map((note) => `<li>${escapeHtml(note)}</li>`).join('')}</ul></div></section>`
        : '';
    const items = sortedItems.length
        ? `<section class="order-detail-section"><h2 class="detail-label">Items</h2><div class="detail-content order-items"><div class="order-item-detail order-item-detail-heading"><span>Amount</span><span>Nr</span><span>Name</span><span>Price</span><span></span></div>${sortedItems.map((item) => `<div class="order-item-detail" data-order-item-id="${item.id}" data-quantity="${item.quantity}"><div class="item-quantity-cell item-quantity-controls"><button type="button" data-item-action="increase" aria-label="Increase ${escapeHtml(item.name)} quantity"><i class="fa-solid fa-plus" aria-hidden="true"></i></button><span>${escapeHtml(item.quantity)}</span><button type="button" data-item-action="decrease" aria-label="Decrease ${escapeHtml(item.name)} quantity"><i class="fa-solid fa-minus" aria-hidden="true"></i></button></div><span class="item-number-cell">${escapeHtml(item.item_number)}</span><span class="item-name-cell">${escapeHtml(item.name)}</span><strong class="item-price-cell">€${Number(item.price).toFixed(2)}</strong><button type="button" class="item-delete-button" data-item-action="delete" aria-label="Delete ${escapeHtml(item.name)}"><i class="fa-solid fa-trash" aria-hidden="true"></i></button></div>`).join('')}</div></section>`
        : '';

    workspacePanel.innerHTML = `
        <form class="order-card order-edit-form" data-order-edit-form data-order-id="${order.id}">
        <div class="print-heading">Food order</div>
        <div class="order-detail-header">
            <div>
                <h2 class="detail-label">Name</h2>
                <input class="order-edit-input order-name-input" name="customer_name" value="${escapeHtml(name)}" required>
            </div>
            <div class="order-total"><span class="order-total-label">Total</span><strong>€${Number(order.price).toFixed(2)}</strong></div>
        </div>
        ${itemEntry}
        ${items}
            <section class="order-detail-section">
                <label class="detail-label" for="order-comments">Comments</label>
                <textarea class="order-edit-input" id="order-comments" name="description" rows="4">${escapeHtml(order.description || '')}</textarea>
            </section>
            ${notes}
            <div class="order-edit-actions"><button class="new-order-button" type="submit">Save changes</button><p class="form-error" data-order-edit-feedback aria-live="polite"></p></div>
            <div class="item-number-modal" data-item-modal hidden>
                <div class="item-number-modal-backdrop" data-close-item-modal></div>
                <div class="item-number-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="item-number-modal-title">
                    <button type="button" class="item-number-modal-close" data-close-item-modal aria-label="Close item number dialog">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                    <h2 id="item-number-modal-title">Add item</h2>
                    <p>Enter a food item number and press Enter.</p>
                    <input class="order-edit-input" data-item-number-entry inputmode="numeric" autocomplete="off" autofocus>
                    <p class="form-error" data-item-entry-feedback role="alert"></p>
                    <button type="button" class="workspace-close" data-close-item-modal>Cancel</button>
                </div>
            </div>
        </form>`;
}

function printAsciiReceipt(order) {
    const width = 42;
    const line = '='.repeat(width);
    const divider = '-'.repeat(width);
    const fit = (value, length) => String(value).slice(0, length).padEnd(length, ' ');
    const money = (value) => `€${Number(value).toFixed(2)}`;
    const sortedItems = [...(order.items || [])].sort((first, second) => (
        Number(first.item_number) - Number(second.item_number)
    ));
    const itemLines = sortedItems.length
        ? sortedItems.flatMap((item) => {
            const itemNumber = String(item.item_number).padStart(3, ' ');
            const name = fit(item.name, 22);
            const quantity = String(item.quantity).padStart(3, ' ');
            const price = money(Number(item.price) * Number(item.quantity)).padStart(10, ' ');

            return [`${quantity}  ${itemNumber}  ${name}${price}`];
        })
        : ['  No items'];
    const comments = order.description
        ? [`Comments: ${order.description}`]
        : [];
    const receipt = [
        line,
        fit('FOOD ORDER', width),
        line,
        `Order: ${order.order_number}`,
        `Name:  ${order.customer_name}`,
        divider,
        'Qty  Nr   Item              Total',
        divider,
        ...itemLines,
        divider,
        `TOTAL:${money(order.price).padStart(width - 6, ' ')}`,
        ...comments,
        line,
    ].join('\n');
    const printWindow = window.open('', '_blank', 'width=480,height=700');

    if (!printWindow) {
        window.alert('Allow pop-ups to open the ASCII print preview.');
        return;
    }

    printWindow.document.write(`
        <!doctype html>
        <html>
            <head>
                <title>${escapeHtml(order.order_number)} receipt</title>
                <style>
                    body { margin: 24px; background: #fff; color: #000; }
                    pre { font: 14px/1.45 "Courier New", monospace; white-space: pre-wrap; }
                    @media print { body { margin: 0; } }
                </style>
            </head>
            <body><pre id="receipt"></pre></body>
        </html>
    `);
    printWindow.document.close();
    printWindow.document.querySelector('#receipt').textContent = receipt;
    printWindow.focus();
    printWindow.print();
}

document.addEventListener('submit', async (event) => {
    const editForm = event.target.closest('[data-order-edit-form]:not([data-order-form])');

    if (editForm) {
        event.preventDefault();
        await saveOrderChanges(editForm);
        return;
    }

    const form = event.target.closest('[data-order-form]');

    if (!form) {
        return;
    }

    event.preventDefault();
    const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (response.ok) {
        const payload = await response.json();
        const order = payload.order;
        renderOrderDetails(order);

        const orderList = document.querySelector('#orderList');
        const orderItem = document.createElement('div');
        orderItem.className = 'order-item active';
        orderItem.dataset.orderId = order.id;
        orderItem.innerHTML = `
            <div class="order-number">${escapeHtml(order.order_number)}</div>
            <h4 class="order-name">${escapeHtml(order.customer_name)}</h4>
            <div class="order-price">€${Number(order.price).toFixed(2)}</div>
            `;
        orderList.prepend(orderItem);
        window.history.pushState({}, '', '/dashboard');
        return;
    }

    if (response.status === 422) {
        const payload = await response.json();
        Object.entries(payload.errors).forEach(([field, messages]) => {
            const error = form.querySelector(`[data-error-for="${field}"]`);
            if (error) {
                error.textContent = messages[0];
            }
        });

    }
});

async function saveOrderChanges(form) {
    const feedback = form.querySelector('[data-order-edit-feedback]');
    feedback.textContent = 'Saving...';

    let response;

    try {
        response = await fetch(`/orders/${form.dataset.orderId}`, {
            method: 'PATCH',
            body: new FormData(form),
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
    } catch {
        feedback.textContent = 'Unable to save changes.';
        return;
    }

    if (!response.ok) {
        feedback.textContent = 'Unable to save changes.';
        return;
    }

    const payload = await response.json();
    selectedOrder = { ...selectedOrder, ...payload.order };
    const orderItem = document.querySelector(`.order-item[data-order-id="${payload.order.id}"] .order-name`);

    if (orderItem) {
        orderItem.textContent = payload.order.customer_name;
    }

    feedback.textContent = 'Saved automatically.';
}

document.addEventListener('input', (event) => {
    const field = event.target.closest('[data-order-edit-form] input[name="customer_name"], [data-order-edit-form] textarea[name="description"]');

    if (!field) {
        return;
    }

    const form = field.closest('[data-order-edit-form]');
    clearTimeout(form._autoSaveTimer);
    form._autoSaveTimer = setTimeout(() => saveOrderChanges(form), 500);
});

function openItemModal() {
    const modal = document.querySelector('[data-item-modal]');

    if (!modal) {
        return;
    }

    modal.hidden = false;
    modal.querySelector('[data-item-number-entry]').focus();
}

function closeItemModal() {
    const modal = document.querySelector('[data-item-modal]');

    if (modal) {
        modal.hidden = true;
    }
}

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-open-item-modal]')) {
        openItemModal();
        return;
    }

    if (event.target.closest('[data-close-item-modal]')) {
        closeItemModal();
    }
});

document.addEventListener('keydown', async (event) => {
    const input = event.target.closest('[data-item-number-entry]');

    if (!input || event.key !== 'Enter') {
        return;
    }

    event.preventDefault();
    const panel = input.closest('#workspacePanel');
    const feedback = panel.querySelector('[data-item-entry-feedback]');
    const itemNumber = Number(input.value.trim());

    if (!Number.isInteger(itemNumber) || itemNumber < 1) {
        feedback.textContent = 'Enter a valid food item number.';
        return;
    }

    let response;

    try {
        response = await fetch(`/orders/${panel.dataset.orderId}/items`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ item_number: itemNumber }),
        });
    } catch {
        feedback.textContent = 'Unable to reach the server. Try again.';
        return;
    }

    if (!response.ok) {
        feedback.textContent = response.status === 422
            ? 'No food item has that number.'
            : 'Unable to add that item.';
        return;
    }

    const payload = await response.json();
    updateSidebarOrder(payload.order);
    renderOrderDetails(payload.order);
    openItemModal();
});
