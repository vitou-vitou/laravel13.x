import { Chart, DoughnutController, ArcElement, Tooltip, Legend } from 'chart.js'

Chart.register(DoughnutController, ArcElement, Tooltip, Legend)

let chart

export function renderMonthChart(canvas, totals, monthLabel) {
  const labels = Object.keys(totals)
  const values = Object.values(totals)
  const hasData = values.some((v) => v > 0)

  if (chart) {
    chart.destroy()
    chart = null
  }

  chart = new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels: hasData ? labels : ['No expenses yet'],
      datasets: [
        {
          data: hasData ? values : [1],
          backgroundColor: hasData
            ? ['#2a9d8f', '#e9c46a', '#f4a261', '#e76f51', '#264653']
            : ['#d8dee4'],
          borderWidth: 0,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: { position: 'bottom' },
        title: { display: false },
        tooltip: {
          enabled: hasData,
          callbacks: {
            label(ctx) {
              const v = ctx.parsed
              return `${ctx.label}: ${formatMoney(v)}`
            },
          },
        },
      },
    },
  })

  return { chart, monthLabel, hasData }
}

export function formatMoney(n) {
  return new Intl.NumberFormat(undefined, {
    style: 'currency',
    currency: 'USD',
  }).format(n)
}
