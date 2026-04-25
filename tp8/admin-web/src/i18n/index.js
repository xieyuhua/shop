import { createI18n } from 'vue-i18n'

const messages = {
  'zh-cn': {
    common: {
      confirm: '确定',
      cancel: '取消',
      save: '保存',
      delete: '删除',
      edit: '编辑',
      add: '添加',
      search: '搜索',
      reset: '重置',
      export: '导出',
      import: '导入',
      submit: '提交',
      back: '返回',
      success: '操作成功',
      error: '操作失败',
      loading: '加载中...',
      noData: '暂无数据',
      confirmDelete: '确定要删除吗？',
      successDelete: '删除成功',
      successSave: '保存成功'
    },
    status: {
      enabled: '启用',
      disabled: '禁用',
      normal: '正常'
    },
    user: {
      title: '会员管理',
      username: '用户名',
      mobile: '手机号',
      email: '邮箱',
      nickname: '昵称',
      balance: '余额',
      points: '积分'
    },
    product: {
      title: '商品管理',
      name: '商品名称',
      category: '商品分类',
      price: '价格',
      stock: '库存',
      sales: '销量'
    },
    order: {
      title: '订单管理',
      orderNo: '订单号',
      receiver: '收货人',
      mobile: '联系电话',
      amount: '订单金额',
      status: '订单状态'
    }
  },
  en: {
    common: {
      confirm: 'Confirm',
      cancel: 'Cancel',
      save: 'Save',
      delete: 'Delete',
      edit: 'Edit',
      add: 'Add',
      search: 'Search',
      reset: 'Reset',
      export: 'Export',
      import: 'Import',
      submit: 'Submit',
      back: 'Back',
      success: 'Success',
      error: 'Error',
      loading: 'Loading...',
      noData: 'No Data',
      confirmDelete: 'Are you sure to delete?',
      successDelete: 'Deleted successfully',
      successSave: 'Saved successfully'
    },
    status: {
      enabled: 'Enabled',
      disabled: 'Disabled',
      normal: 'Normal'
    },
    user: {
      title: 'User Management',
      username: 'Username',
      mobile: 'Mobile',
      email: 'Email',
      nickname: 'Nickname',
      balance: 'Balance',
      points: 'Points'
    },
    product: {
      title: 'Product Management',
      name: 'Product Name',
      category: 'Category',
      price: 'Price',
      stock: 'Stock',
      sales: 'Sales'
    },
    order: {
      title: 'Order Management',
      orderNo: 'Order No.',
      receiver: 'Receiver',
      mobile: 'Mobile',
      amount: 'Amount',
      status: 'Status'
    }
  }
}

export const i18n = createI18n({
  legacy: false,
  locale: 'zh-cn',
  fallbackLocale: 'en',
  messages
})

export function t(key, params = {}) {
  const lang = localStorage.getItem('language') || 'zh-cn'
  const keys = key.split('.')
  let value = messages[lang]
  
  for (const k of keys) {
    value = value?.[k]
  }
  
  if (typeof value === 'string' && params) {
    Object.keys(params).forEach(k => {
      value = value.replace(new RegExp(`{${k}}`, 'g'), params[k])
    })
  }
  
  return value || key
}

export function setLanguage(lang) {
  localStorage.setItem('language', lang)
  i18n.global.locale = lang
}