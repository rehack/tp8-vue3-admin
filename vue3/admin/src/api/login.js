import request from '../request'

export function login(data) {
    return request({
        url: '/gettoken/user',
        method: 'POST',
        data,
    })
}

export function getUserInfo() {
    return request({
        url: '/commom/sysuserinfo',
        method: 'GET',
    })
}

export function logout() {
    return request({
        url: '/logout',
        method: 'POST',
    })
}
