import request from './request'

export function getProductList(params) {
  return request({ url: '/api/admin/product', method: 'GET', params })
}

export function getProductOptions() {
  return request({ url: '/api/admin/product/options', method: 'GET' })
}

export function createProduct(data) {
  return request({ url: '/api/admin/product', method: 'POST', data })
}

export function updateProduct(data) {
  return request({ url: '/api/admin/product', method: 'PUT', data })
}

export function deleteProduct(id) {
  return request({ url: '/api/admin/product/' + id, method: 'DELETE' })
}