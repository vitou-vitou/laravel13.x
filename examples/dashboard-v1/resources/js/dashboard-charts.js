import Chart from 'chart.js/auto';

const dataElement = document.getElementById('dashboard-charts-data');

if (dataElement) {
    const chartData = JSON.parse(dataElement.textContent);

    const revenueCanvas = document.getElementById('revenue-trend-chart');
    if (revenueCanvas) {
        new Chart(revenueCanvas, {
            type: 'line',
            data: {
                labels: chartData.revenueTrend.labels,
                datasets: [
                    {
                        label: 'Revenue ($)',
                        data: chartData.revenueTrend.values,
                        borderColor: 'rgb(37, 99, 235)',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => `$${value}`,
                        },
                    },
                },
            },
        });
    }

    const statusCanvas = document.getElementById('order-status-chart');
    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: chartData.statusBreakdown.labels,
                datasets: [
                    {
                        data: chartData.statusBreakdown.values,
                        backgroundColor: [
                            'rgb(16, 185, 129)',
                            'rgb(245, 158, 11)',
                            'rgb(244, 63, 94)',
                        ],
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                },
            },
        });
    }
}
