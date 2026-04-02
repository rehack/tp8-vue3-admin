import request from '../request'

export function getCustomerList(data) {
    return request({
        url: '/customer/getlist',
        method: 'GET',
        params: data,
    })
}
