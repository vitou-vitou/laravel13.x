const KEY = 'expense-tracker:v1'

export const CATEGORIES = [
  'Food',
  'Transport',
  'Housing',
  'Utilities',
  'Other',
]

export function loadExpenses() {
  try {
    const raw = localStorage.getItem(KEY)
    if (!raw) return []
    const data = JSON.parse(raw)
    return Array.isArray(data) ? data : []
  } catch {
    return []
  }
}

export function saveExpenses(items) {
  localStorage.setItem(KEY, JSON.stringify(items))
}

export function addExpense(expense) {
  const items = loadExpenses()
  const row = {
    id: crypto.randomUUID(),
    amount: Number(expense.amount),
    category: expense.category,
    note: expense.note?.trim() || '',
    date: expense.date || todayISO(),
  }
  items.unshift(row)
  saveExpenses(items)
  return row
}

export function todayISO() {
  const d = new Date()
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

export function thisMonthExpenses(items = loadExpenses()) {
  const now = new Date()
  const y = now.getFullYear()
  const m = now.getMonth()
  return items.filter((e) => {
    const d = new Date(e.date + 'T00:00:00')
    return d.getFullYear() === y && d.getMonth() === m
  })
}

export function totalsByCategory(items) {
  const map = Object.fromEntries(CATEGORIES.map((c) => [c, 0]))
  for (const e of items) {
    const key = CATEGORIES.includes(e.category) ? e.category : 'Other'
    map[key] += Number(e.amount) || 0
  }
  return map
}

export function sumAmounts(items) {
  return items.reduce((n, e) => n + (Number(e.amount) || 0), 0)
}
