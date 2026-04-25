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
  error => {
    return Promise.reject(error)
  }
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
    if (error.response?.status === 401) {
      removeToken()
      window.location.href = '/login'
    }
    ElMessage.error(error.message || '网络错误')
    return Promise.reject(error)
  }
)

export default service