import { ref, readonly } from 'vue'

const loading = ref(false)
const loadingText = ref('')

export function useLoading(initialText = '加载中...') {
  function show(text = initialText) {
    loading.value = true
    loadingText.value = text
  }

  function hide() {
    loading.value = false
    loadingText.value = ''
  }

  function toggle(text = initialText) {
    if (loading.value) {
      hide()
    } else {
      show(text)
    }
  }

  return { show, hide, toggle, loading: readonly(loading), loadingText: readonly(loadingText) }
}

export function useRequest() {
  async function request(promise, options = {}) {
    const { loading: showLoading = true, loadingText = '加载中...', onSuccess, onError } = options
    
    if (showLoading) {
      loading.value = true
      loadingText.value = loadingText
    }

    try {
      const res = await promise
      if (showLoading) {
        loading.value = false
      }

      if (res.code === 200) {
        onSuccess?.(res.data)
        return res.data
      } else {
        onError?.(res.msg)
        return null
      }
    } catch (error) {
      if (showLoading) {
        loading.value = false
      }
      onError?.(error.message)
      return null
    }
  }

  return { request, loading: readonly(loading), loadingText: readonly(loadingText) }
}

export function useSearch(initialParams = {}) {
  const params = ref({ ...initialParams })
  const loading = ref(false)

  function search(newParams = {}) {
    params.value = { ...params.value, ...newParams }
  }

  function reset() {
    params.value = { ...initialParams }
  }

  return { params: readonly(params), loading, search, reset }
}

export function usePagination() {
  const page = ref(1)
  const limit = ref(15)
  const total = ref(0)

  function setPage(p) {
    page.value = p
  }

  function setLimit(l) {
    limit.value = l
  }

  function setTotal(t) {
    total.value = t
  }

  function onPageChange(p) {
    page.value = p
  }

  function onLimitChange(l) {
    limit.value = l
    page.value = 1
  }

  return {
    page: readonly(page),
    limit: readonly(limit),
    total: readonly(total),
    setPage,
    setLimit,
    setTotal,
    onPageChange,
    onLimitChange
  }
}