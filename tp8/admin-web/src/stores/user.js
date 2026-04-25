import { defineStore } from 'pinia'
import { ref } from 'vue'
import { getToken, setToken as saveToken, removeToken, getUserInfo, setUserInfo } from '@/utils/storage'
import { login as apiLogin, logout as apiLogout } from '@/api/auth'

export const useUserStore = defineStore('user', () => {
  const token = ref(getToken())
  const userInfo = ref(getUserInfo() || {})

  async function login(username, password) {
    const res = await apiLogin(username, password)
    if (res.code === 200) {
      token.value = res.data.token
      userInfo.value = res.data.user
      saveToken(res.data.token)
      setUserInfo(res.data.user)
      return true
    }
    return false
  }

  async function logout() {
    try {
      await apiLogout()
    } catch (e) {}
    token.value = ''
    userInfo.value = {}
    removeToken()
  }

  return { token, userInfo, login, logout }
})