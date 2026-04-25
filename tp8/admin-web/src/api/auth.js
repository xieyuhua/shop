import request from './request'

export function login(data) {
  return request({ url: '/api/admin/login', method: 'POST', data })
}

export function logout() {
  return request({ url: '/api/admin/logout', method: 'POST' })
}

export function getUserInfo() {
  return request({ url: '/api/admin/user/info', method: 'GET' })
}