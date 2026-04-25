import request from './request'

export function getConfig(group) {
  return request({ url: '/api/admin/config', method: 'GET', params: { group } })
}

export function saveConfig(data) {
  return request({ url: '/api/admin/config', method: 'POST', data })
}