import request from './request'

export function uploadFile(data, config = {}) {
  return request({
    url: '/api/admin/file/upload',
    method: 'POST',
    data,
    headers: { 'Content-Type': 'multipart/form-data' },
    ...config
  })
}

export function getFileList(params) {
  return request({ url: '/api/admin/file', method: 'GET', params })
}

export function deleteFile(id) {
  return request({ url: '/api/admin/file/' + id, method: 'DELETE' })
}

export function getDict(group) {
  return request({ url: '/api/admin/dict', method: 'GET', params: { group } })
}

export function getConfig(group) {
  return request({ url: '/api/admin/config', method: 'GET', params: { group } })
}

export function saveConfig(data) {
  return request({ url: '/api/admin/config', method: 'POST', data })
}