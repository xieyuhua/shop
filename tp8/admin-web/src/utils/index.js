import { ElMessage, ElMessageBox, ElLoading } from 'element-plus'

export function message(text, type = 'info') {
  ElMessage[type]({ message: text, duration: 3000 })
}

export function success(text) {
  message(text, 'success')
}

export function error(text) {
  message(text, 'error')
}

export function warning(text) {
  message(text, 'warning')
}

export function info(text) {
  message(text, 'info')
}

export function confirm(text, title = '提示') {
  return ElMessageBox.confirm(text, title, {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  })
}

export function prompt(text, title = '提示') {
  return ElMessageBox.prompt(text, title, {
    confirmButtonText: '确定',
    cancelButtonText: '取消'
  })
}

let loadingInstance = null

export function showLoading(target = 'body') {
  if (!loadingInstance) {
    loadingInstance = ElLoading.service({
      target: document.querySelector(target),
      fullscreen: false
    })
  }
}

export function hideLoading() {
  if (loadingInstance) {
    loadingInstance.close()
    loadingInstance = null
  }
}

export function download(url, filename = '') {
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  link.style.display = 'none'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

export function getFileUrl(file) {
  if (!file) return ''
  if (file.startsWith('http')) return file
  return '/' + file
}

export function getImageUrl(file) {
  if (!file) return ''
  if (file.startsWith('data:')) return file
  return getFileUrl(file)
}

export function debounce(fn, delay = 300) {
  let timer = null
  return function (...args) {
    if (timer) clearTimeout(timer)
    timer = setTimeout(() => fn.apply(this, args), delay)
  }
}

export function throttle(fn, delay = 300) {
  let last = 0
  return function (...args) {
    const now = Date.now()
    if (now - last > delay) {
      last = now
      fn.apply(this, args)
    }
  }
}

export function copyToClipboard(text) {
  if (navigator.clipboard) {
    return navigator.clipboard.writeText(text)
  }
  
  const textarea = document.createElement('textarea')
  textarea.value = text
  textarea.style.position = 'fixed'
  textarea.style.opacity = '0'
  document.body.appendChild(textarea)
  textarea.select()
  document.execCommand('copy')
  document.body.removeChild(textarea)
}

export function isPhone(value) {
  return /^1[3-9]\d{9}$/.test(value)
}

export function isEmail(value) {
  return /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test(value)
}

export function isIdCard(value) {
  return /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(value)
}