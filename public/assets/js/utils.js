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

export const money = (amount = 0) => {
  return amount.toLocaleString('en-US', { style: 'currency', currency: 'USD' })
}

export default {
  api,
  money
}