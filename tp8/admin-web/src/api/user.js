import request from './request'

export function getUserList(params) {
  return request({ url: '/api/admin/user', method: 'GET', params })
}

export function createUser(data) {
  return request({ url: '/api/admin/user', method: 'POST', data })
}

export function updateUser(data) {
  return request({ url: '/api/admin/user', method: 'PUT', data })
}

export function deleteUser(id) {
  return request({ url: '/api/admin/user/' + id, method: 'DELETE' })
}