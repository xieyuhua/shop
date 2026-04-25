<template>
  <el-config-provider :locale="locale" :size="size">
    <router-view />
  </el-config-provider>
</template>

<script setup>
import { ref, watch } from 'vue'
import zhCn from 'element-plus/es/locale/lang/zh-cn'
import en from 'element-plus/es/locale/lang/en'

const locale = ref(zhCn)
const size = ref('default')

const languageMap = {
  'zh-cn': zhCn,
  'en': en
}

watch(() => localStorage.getItem('language'), (val) => {
  locale.value = languageMap[val] || zhCn
}, { immediate: true })
</script>