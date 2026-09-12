# Expense Tracker

Simple web app: log expenses by category, see this month’s totals on a chart. No login.

## Stack

- Vite + vanilla JS
- localStorage
- Chart.js

## Run

```bash
cd examples/expense-tracker
npm install
npm run dev
```

Open the URL Vite prints (usually http://localhost:5173).

## Build

```bash
npm run build
npm run preview
```

## MVP

1. Add amount + category (+ optional note / date)
2. List persists after reload
3. Doughnut chart shows this month’s totals by category
