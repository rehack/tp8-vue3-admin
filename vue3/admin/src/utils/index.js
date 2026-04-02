export function formatDate(date, format = 'YYYY-MM-DD HH:mm:ss') {
    if (!date) return ''
    const d = new Date(date)
    const year = d.getFullYear()
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    const hour = String(d.getHours()).padStart(2, '0')
    const minute = String(d.getMinutes()).padStart(2, '0')
    const second = String(d.getSeconds()).padStart(2, '0')

    return format
        .replace('YYYY', year)
        .replace('MM', month)
        .replace('DD', day)
        .replace('HH', hour)
        .replace('mm', minute)
        .replace('ss', second)
}

export function formatDateTime(date) {
    return formatDate(date, 'YYYY-MM-DD HH:mm:ss')
}

export function formatDateOnly(date) {
    return formatDate(date, 'YYYY-MM-DD')
}

export function parseJSON(str, defaultValue = {}) {
    try {
        return JSON.parse(str)
    } catch {
        return defaultValue
    }
}
