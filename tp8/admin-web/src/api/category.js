import request from './request'

export function getCategoryList(params) {
  return request({ url: '/api/admin/category', method: 'GET', params })
}

export function getCategoryTree() {
  return request({ url: '/api/admin/category/tree', method: 'GET' })
}

export function getCategoryOptions() {
  return request({ url: '/api/admin/category/options', method: 'GET' })
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