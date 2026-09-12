import {
  CATEGORIES,
  loadExpenses,
  addExpense,
  todayISO,
  thisMonthExpenses,
  totalsByCategory,
  sumAmounts,
} from './storage.js'
import { renderMonthChart, formatMoney } from './chart.js'

export function mount(root) {
  root.innerHTML = shell()
  bind(root)
  refresh(root)
}

function shell() {
  const opts = CATEGORIES.map(
    (c) => `<option value="${c}">${c}</option>`,
  ).join('')

  return `
    <header class="top">
      <h1>Expense Tracker</h1>
      <p class="sub">Log spending. See this month’s pattern. No account needed.</p>
    </header>

    <main class="layout">
      <section class="panel" aria-labelledby="add-heading">
        <h2 id="add-heading">Add expense</h2>
        <form id="expense-form" class="form">
          <label>
            Amount
            <input name="amount" type="number" min="0.01" step="0.01" required placeholder="0.00" />
          </label>
          <label>
            Category
            <select name="category" required>${opts}</select>
          </label>
          <label>
            Date
            <input name="date" type="date" required value="${todayISO()}" />
          </label>
          <label class="full">
            Note <span class="muted">(optional)</span>
            <input name="note" type="text" maxlength="120" placeholder="Coffee, fuel…" />
          </label>
          <button type="submit" class="btn">Add</button>
        </form>
      </section>

      <section class="panel" aria-labelledby="chart-heading">
        <h2 id="chart-heading">This month</h2>
        <p id="month-total" class="month-total"></p>
        <div class="chart-wrap">
          <canvas id="month-chart" width="320" height="320" aria-label="Monthly spending by category"></canvas>
        </div>
      </section>

      <section class="panel full-span" aria-labelledby="list-heading">
        <h2 id="list-heading">All expenses</h2>
        <ul id="expense-list" class="list"></ul>
      </section>
    </main>
  `
}

function bind(root) {
  const form = root.querySelector('#expense-form')
  form.addEventListener('submit', (e) => {
    e.preventDefault()
    const fd = new FormData(form)
    addExpense({
      amount: fd.get('amount'),
      category: fd.get('category'),
      date: fd.get('date'),
      note: fd.get('note'),
    })
    form.reset()
    form.querySelector('[name="date"]').value = todayISO()
    refresh(root)
  })
}

function refresh(root) {
  const items = loadExpenses()
  renderList(root.querySelector('#expense-list'), items)

  const monthItems = thisMonthExpenses(items)
  const totals = totalsByCategory(monthItems)
  const total = sumAmounts(monthItems)
  const now = new Date()
  const label = now.toLocaleString(undefined, { month: 'long', year: 'numeric' })

  root.querySelector('#month-total').textContent = `${label} total: ${formatMoney(total)}`
  renderMonthChart(root.querySelector('#month-chart'), totals, label)
}

function renderList(ul, items) {
  ul.replaceChildren()
  if (!items.length) {
    const li = document.createElement('li')
    li.className = 'empty'
    li.textContent = 'No expenses yet. Add one above.'
    ul.append(li)
    return
  }
  for (const e of items) {
    const li = document.createElement('li')
    const amt = document.createElement('span')
    amt.className = 'amt'
    amt.textContent = formatMoney(e.amount)
    const cat = document.createElement('span')
    cat.className = 'cat'
    cat.textContent = e.category
    const date = document.createElement('span')
    date.className = 'date'
    date.textContent = e.date
    const note = document.createElement('span')
    note.className = 'note'
    note.textContent = e.note || '—'
    li.append(amt, cat, date, note)
    ul.append(li)
  }
}
