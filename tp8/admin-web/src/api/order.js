import request from './request'

export function getOrderList(params) {
  return request({ url: '/api/admin/order', method: 'GET', params })
}

export function getOrderDetail(id) {
  return request({ url: '/api/admin/order/' + id, method: 'GET' })
}

export function shipOrder(data) {
  return request({ url: '/api/admin/order/ship', method: 'POST', data })
}

export function cancelOrder(id) {
  return request({ url: '/api/admin/order/' + id + '/cancel', method: 'POST' })
}

export function refundOrder(id) {
  return request({ url: '/api/admin/order/' + id + '/refund', method: 'POST' })
}