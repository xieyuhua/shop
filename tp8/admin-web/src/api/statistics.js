import request from './request'

export function getStatistics(params) {
  return request({ url: '/api/admin/statistics', method: 'GET', params })
}

export function getChartData(params) {
  return request({ url: '/api/admin/statistics/chart', method: 'GET', params })
}