export const api = async (u, m = null, b = null, t = null) => {
  const res = await fetch(`/api/${u}`, {
    method: m || 'GET',
    headers: { 'Content-Type': t || 'application/json' },
    body: b ? JSON.stringify(b) : null,
    credentials: 'include'
  })
  let data
  if (res.headers.get('Content-Type')?.includes('application/json'))
    data = await res.json()
  else
    data = await res.text()
  return res.ok ? data : Promise.reject({ ...data, status: res.status })
}

export const toMoney = (amount = 0) => {
  return amount.toLocaleString('en-US', { style: 'currency', currency: 'USD' })
}

export const toDate = (timestamp, format = 'datetime') => {
  let dtObj
  if (typeof timestamp === 'string')
    dtObj = new Date(timestamp)
  else if (typeof timestamp === 'number')
    dtObj = new Date(timestamp * 1000)
  if (format === 'date') return dtObj.toLocaleDateString()
  else if (format === 'time') return dtObj.toLocaleTimeString()
  return dtObj.toLocaleString()
}

export default {
  api,
  toMoney,
  toDate
}