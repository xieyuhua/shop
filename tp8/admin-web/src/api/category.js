import request from './request'

export function getCategoryList() {
  return request({ url: '/api/admin/category', method: 'GET' })
}

export function createCategory(data) {
  return request({ url: '/api/admin/category', method: 'POST', data })
}

export function updateCategory(data) {
  return request({ url: '/api/admin/category', method: 'PUT', data })
}

export function deleteCategory(id) {
  return request({ url: '/api/admin/category/' + id, method: 'DELETE' })
}