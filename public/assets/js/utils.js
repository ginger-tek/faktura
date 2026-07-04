export const api = async (u, m = null, b = null, t = null) => {
  const res = await fetch(`/api/${u}`, {
    method: m || 'GET',
    headers: { 'Content-Type': t || 'application/json' },
    body: b ? JSON.stringify(b) : null,
    credentials: 'include'
  })
  let data
  if (res.status === 401 && !u.match(/^auth\/login$/))
    return location.reload()
  if (res.headers.get('Content-Type')?.includes('application/json'))
    data = await res.json()
  else
    data = await res.text()
  return res.ok ? data : Promise.reject({ ...data, status: res.status })
}

export const toCurrency = (amount = 0) => {
  return amount.toLocaleString(navigator.language || navigator.userLanguage, { style: 'currency', currency: 'USD' })
}

export const toDate = (value, format = 'datetime') => {
  if (!value) return '--'
  let dtObj
  if (typeof value === 'string')
    dtObj = new Date(value.match(/^\d{4}-\d{2}-\d{2}$/) ? value + ' 00:00:00' : value)
  else if (typeof value === 'number')
    dtObj = new Date(value * 1000)
  if (format === 'date') return dtObj.toLocaleDateString()
  else if (format === 'time') return dtObj.toLocaleTimeString()
  return dtObj.toLocaleString()
}

export const toYMD = (dateObj) => {
  if (!dateObj) return '--'
  return dateObj.toISOString().split('T')[0]
}

export default {
  api,
  toCurrency,
  toDate,
  toYMD
}