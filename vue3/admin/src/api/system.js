import request from '../request'

export function getMenus() {
    return request({
        url: '/common/menus',
        method: 'GET',
    })
}

export function getAuthRules() {
    return request({
        url: '/common/authrules',
        method: 'GET',
    })
}

export function saveAuthRule(data) {
    return request({
        url: '/system/authrule',
        method: 'POST',
        data,
    })
}

export function deleteAuthRule(data) {
    return request({
        url: '/system/authrule',
        method: 'DELETE',
        data,
    })
}

export function saveAuthRole(data) {
    return request({
        url: '/system/authsetting',
        method: 'POST',
        data,
    })
}

export function deleteRole(data) {
    return request({
        url: '/system/role',
        method: 'DELETE',
        data,
    })
}

export function getOplog(data) {
    return request({
        url: '/system/oplog',
        method: 'POST',
        data,
    })
}

export function getLoginlog(data) {
    return request({
        url: '/system/loginlog',
        method: 'POST',
        data,
    })
}
