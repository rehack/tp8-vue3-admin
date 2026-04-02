export function isExternal(path) {
    return /^(https?:|mailto:|tel:)/.test(path)
}

export function validUsername(str) {
    const validMap = ['admin', 'editor']
    return validMap.indexOf(str.trim()) >= 0
}

export function validURL(url) {
    const reg =
        /^(https?|ftp):\/\/([a-zA-Z0-9.-]+(:[a-zA-Z0-9.&%$-]+)*@)*((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}|([a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(:[0-9]+)*(\/($|[a-zA-Z0-9.,?'\\+&%$#=~_-]+))*$/
    return reg.test(url)
}

export function validPhone(str) {
    const reg = /^1[3-9]\d{9}$/
    return reg.test(str)
}
