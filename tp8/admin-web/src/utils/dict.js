import request from './request'

const dictCache = {}

export function getDict(group, refresh = false) {
  if (!refresh && dictCache[group]) {
    return Promise.resolve(dictCache[group])
  }
  
  return request({ url: '/api/admin/dict', method: 'GET', params: { group } })
    .then(res => {
      const options = {}
      if (res.data) {
        res.data.forEach(item => {
          options[item.value] = item.label
        })
      }
      dictCache[group] = options
      return options
    })
}

export function getDictLabel(group, value, defaultText = '-') {
  const options = dictCache[group]
  if (options && options[value] !== undefined) {
    return options[value]
  }
  return defaultText
}

export function getDictOptions(group) {
  return request({ url: '/api/admin/dict', method: 'GET', params: { group } })
    .then(res => res.data || [])
}

export function clearDictCache(group = '') {
  if (group) {
    delete dictCache[group]
  } else {
    Object.keys(dictCache).forEach(k => delete dictCache[k])
  }
}

export function formatStatus(value) {
  const map = { 0: '禁用', 1: '启用' }
  return map[value] || '-'
}

export function formatGender(value) {
  const map = { 0: '未知', 1: '男', 2: '女' }
  return map[value] || '-'
}

export function formatOrderStatus(value) {
  const map = {
    0: { text: '待付款', type: 'warning' },
    1: { text: '待发货', type: 'info' },
    2: { text: '待收货', type: 'primary' },
    3: { text: '已完成', type: 'success' },
    4: { text: '已取消', type: 'info' },
    5: { text: '已退款', type: 'danger' }
  }
  return map[value] || { text: '-', type: '' }
}

export function formatPayType(value) {
  const map = { 1: '微信', 2: '支付宝', 3: '余额' }
  return map[value] || '-'
}

export function formatMoney(value) {
  return '￥' + Number(value || 0).toFixed(2)
}

export function formatDate(value, format = 'YYYY-MM-DD HH:mm:ss') {
  if (!value) return '-'
  
  const date = new Date(value)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hour = String(date.getHours()).padStart(2, '0')
  const minute = String(date.getMinutes()).padStart(2, '0')
  const second = String(date.getSeconds()).padStart(2, '0')
  
  return format
    .replace('YYYY', year)
    .replace('MM', month)
    .replace('DD', day)
    .replace('HH', hour)
    .replace('mm', minute)
    .replace('ss', second)
}

export function formatTime(value) {
  if (!value) return '-'
  
  const date = new Date(value)
  const now = new Date()
  const diff = now - date
  
  if (diff < 60000) return '刚刚'
  if (diff < 3600000) return Math.floor(diff / 60000) + '分钟前'
  if (diff < 86400000) return Math.floor(diff / 3600000) + '小时前'
  if (diff < 604800000) return Math.floor(diff / 86400000) + '天前'
  
  return formatDate(value)
}