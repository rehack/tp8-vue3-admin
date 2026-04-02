import request from '../request'

export function getStaffList(data) {
    return request({
        url: '/system/stafflist',
        method: 'POST',
        data,
    })
}

export function addStaff(data) {
    return request({
        url: '/system/newstaff',
        method: 'POST',
        data,
    })
}

export function updateStaff(data) {
    return request({
        url: '/system/updatestaff',
        method: 'POST',
        data,
    })
}

export function getRoles() {
    return request({
        url: '/common/sysroles',
        method: 'GET',
    })
}

export function getDeptTree() {
    return request({
        url: '/common/depttree',
        method: 'GET',
    })
}

export function resetPassword(newPassword) {
    return request({
        url: '/system/resetpassword',
        method: 'POST',
        data: { new_password: newPassword },
    })
}
