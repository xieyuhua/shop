import axios from 'axios'
import { getToken, removeToken, setToken, setUserInfo } from '@/utils/storage'
import { ElMessage } from 'element-plus'

const service = axios.create({
  baseURL: '/',
  timeout: 30000
})

service.interceptors.request.use(
  config => {
    const token = getToken()
    if (token) {
      config.headers['Authorization'] = 'Bearer ' + token
    }
    return config
  },
  error => Promise.reject(error)
)

service.interceptors.response.use(
  response => {
    const res = response.data
    
    if (res.code === 200) {
      if (res.data && res.data.token) {
        setToken(res.data.token)
        setUserInfo(res.data.user)
      }
      return res
    }
    
    if (res.code === 401) {
      removeToken()
      window.location.href = '/login'
      return Promise.reject(new Error(res.msg || '未授权'))
    }
    
    return Promise.reject(new Error(res.msg || '请求失败'))
  },
  error => {
    let msg = '网络错误'
    
    if (error.response) {
      switch (error.response.status) {
        case 401:
          removeToken()
          window.location.href = '/login'
          msg = '登录已失效'
          break
        case 403:
          msg = '没有权限'
          break
        case 404:
          msg = '请求资源不存在'
          break
        case 422:
          msg = error.response.data?.msg || '数据验证失败'
          break
        case 500:
          msg = '服务器错误'
          break
        default:
          msg = error.response.data?.msg || error.message
      }
    }
    
    ElMessage.error(msg)
    return Promise.reject(new Error(msg))
  }
)

export default service