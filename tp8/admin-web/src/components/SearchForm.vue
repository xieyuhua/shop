<template>
  <div class="search-form">
    <el-form :model="form" :inline="inline" :label-width="labelWidth">
      <el-form-item v-for="item in searchItems" :key="item.prop" :label="item.label">
        <el-input
          v-if="item.type === 'input'"
          v-model="form[item.prop]"
          :placeholder="item.placeholder || item.label"
          clearable
          @change="handleSearch"
        />
        <el-select
          v-else-if="item.type === 'select'"
          v-model="form[item.prop]"
          :placeholder="item.placeholder || item.label"
          clearable
          @change="handleSearch"
        >
          <el-option
            v-for="opt in item.options"
            :key="opt.value"
            :label="opt.label"
            :value="opt.value"
          />
        </el-select>
        <el-date-picker
          v-else-if="item.type === 'date'"
          v-model="form[item.prop]"
          type="daterange"
          range-separator="-"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
          value-format="YYYY-MM-DD"
          @change="handleDateChange(item.prop)"
        />
        <el-date-picker
          v-else-if="item.type === 'datetime'"
          v-model="form[item.prop]"
          type="datetimerange"
          range-separator="-"
          start-placeholder="开始时间"
          end-placeholder="结束时间"
          value-format="YYYY-MM-DD HH:mm:ss"
          @change="handleDateChange(item.prop)"
        />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="handleSearch">
          <el-icon><Search /></el-icon>搜索
        </el-button>
        <el-button @click="handleReset">
          <el-icon><RefreshLeft /></el-icon>重置
        </el-button>
        <slot name="action" />
      </el-form-item>
    </el-form>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'

const props = defineProps({
  items: { type: Array, default: () => [] },
  inline: { type: Boolean, default: true },
  labelWidth: { type: String, default: '80px' }
})

const emit = defineEmits(['search', 'reset'])

const form = reactive({})

const searchItems = props.items.filter(item => item.search !== false)

watch(() => props.items, items => {
  items.forEach(item => {
    const prop = item.prop
    if (form[prop] === undefined) {
      form[prop] = item.default ?? null
    }
  })
}, { immediate: true, deep: true })

function handleSearch() {
  const params = {}
  Object.keys(form).forEach(key => {
    if (form[key] !== null && form[key] !== '') {
      params[key] = form[key]
    }
  })
  emit('search', params)
}

function handleReset() {
  Object.keys(form).forEach(key => {
    const item = searchItems.find(i => i.prop === key)
    form[key] = item?.default ?? null
  })
  emit('reset')
}

function handleDateChange(prop) {
  handleSearch()
}

function setForm(key, value) {
  form[key] = value
}

function getForm() {
  return { ...form }
}

defineExpose({ setForm, getForm, handleSearch, handleReset })
</script>

<style scoped>
.search-form { margin-bottom: 15px; }
</style>