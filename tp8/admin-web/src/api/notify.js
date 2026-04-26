import request from './request'

export function getNotifyList(params) {
  return request({ url: '/api/admin/notify', method: 'GET', params })
}

export function getUnreadCount() {
  return request({ url: '/api/admin/notify/unread', method: 'GET' })
}

export function readNotify(id) {
  return request({ url: '/api/admin/notify/read', method: 'POST', data: { id } })
}

export function deleteNotify(id) {
  return request({ url: '/api/admin/notify/' + id, method: 'DELETE' })
}