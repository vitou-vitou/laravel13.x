import './echo.js';

export function showOrderNotification({ orderId, customer, amount, title = 'New order received' }) {
    if (!('Notification' in window) || Notification.permission !== 'granted') {
        return;
    }

    new Notification(title, {
        body: `${customer} — ${amount}`,
        tag: `order-${orderId}`,
    });
}

export function refreshDashboardMetrics() {
    if (typeof Livewire === 'undefined') {
        return;
    }

    const component = Livewire.all().find((entry) => entry.name === 'dashboard-metrics');

    component?.$refresh();
}

export function bindEnableButton(buttonId) {
    const button = document.getElementById(buttonId);

    if (!button) {
        return;
    }

    if (!('Notification' in window)) {
        button.classList.add('hidden');

        return;
    }

    const updateButton = () => {
        if (Notification.permission === 'granted') {
            button.textContent = button.dataset.enabledLabel ?? 'Notifications enabled';
            button.disabled = true;
            button.classList.add('opacity-60', 'cursor-not-allowed');
        }
    };

    button.addEventListener('click', () => {
        Notification.requestPermission().finally(updateButton);
    });

    updateButton();
}

export function bindEchoListener() {
    if (!window.Echo) {
        return;
    }

    window.Echo.private('orders')
        .listen('.NewOrderCreated', (payload) => {
            showOrderNotification({
                orderId: payload.orderId,
                customer: payload.customer ?? 'Customer',
                amount: payload.amount ?? '',
            });

            refreshDashboardMetrics();
        });
}

export function notifyCheckoutFromDom() {
    const element = document.getElementById('checkout-order-notification');

    if (!element) {
        return;
    }

    try {
        const payload = JSON.parse(element.textContent ?? '{}');

        if (!payload.orderId) {
            return;
        }

        showOrderNotification({
            orderId: payload.orderId,
            customer: payload.customer ?? 'Customer',
            amount: payload.amount ?? '',
            title: 'Order placed',
        });

        refreshDashboardMetrics();
    } catch {
        // Ignore malformed payload.
    }
}

document.addEventListener('DOMContentLoaded', () => {
    bindEnableButton('enable-order-notifications');
    bindEchoListener();
    notifyCheckoutFromDom();
});
