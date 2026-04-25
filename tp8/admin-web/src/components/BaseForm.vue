<template>
  <el-dialog
    v-model="visible"
    :title="title"
    :width="width"
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <el-form
      ref="formRef"
      :model="form"
      :rules="rules"
      :label-width="labelWidth"
    >
      <el-form-item
        v-for="item in formItems"
        :key="item.prop"
        :label="item.label"
        :prop="item.prop"
      >
        <el-input
          v-if="item.type === 'input'"
          v-model="form[item.prop]"
          :placeholder="item.placeholder"
          :disabled="item.disabled"
        />
        <el-input
          v-else-if="item.type === 'textarea'"
          v-model="form[item.prop]"
          :placeholder="item.placeholder"
          :rows="item.rows || 3"
          type="textarea"
        />
        <el-input-number
          v-else-if="item.type === 'number'"
          v-model="form[item.prop]"
          :min="item.min"
          :max="item.max"
          :step="item.step || 1"
        />
        <el-select
          v-else-if="item.type === 'select'"
          v-model="form[item.prop]"
          :placeholder="item.placeholder"
          :disabled="item.disabled"
        >
          <el-option
            v-for="opt in item.options"
            :key="opt.value"
            :label="opt.label"
            :value="opt.value"
          />
        </el-select>
        <el-switch
          v-else-if="item.type === 'switch'"
          v-model="form[item.prop]"
        />
        <el-date-picker
          v-else-if="item.type === 'date'"
          v-model="form[item.prop]"
          type="date"
          :placeholder="item.placeholder"
          value-format="YYYY-MM-DD"
        />
        <el-date-picker
          v-else-if="item.type === 'datetime'"
          v-model="form[item.prop]"
          type="datetime"
          :placeholder="item.placeholder"
          value-format="YYYY-MM-DD HH:mm:ss"
        />
        <el-upload
          v-else-if="item.type === 'image'"
          :action="uploadAction"
          :headers="headers"
          :limit="1"
          :show-file-list="false"
          :on-success="(res) => handleUploadSuccess(res, item.prop)"
        >
          <el-image
            v-if="form[item.prop]"
            :src="form[item.prop]"
            :preview-src-list="[form[item.prop]]"
            fit="cover"
            style="width: 100px; height: 100px;"
          />
          <el-button v-else>
            <el-icon><Plus /></el-icon>选择图片
          </el-button>
        </el-upload>
      </el-form-item>
    </el-form>
    
    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" :loading="loading" @click="handleSubmit">
        {{ confirmText }}
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { getToken } from '@/utils/storage'
import { ElMessage } from 'element-plus'

const props = defineProps({
  title: { type: String, default: '编辑' },
  width: { type: String, default: '500px' },
  items: { type: Array, default: () => [] },
  rules: { type: Object, default: () => ({}) },
  labelWidth: { type: String, default: '100px' },
  confirmText: { type: String, default: '确定' },
  uploadAction: { type: String, default: '/api/admin/file/upload' }
})

const emit = defineEmits(['update:modelValue', 'submit'])

const visible = ref(false)
const loading = ref(false)
const formRef = ref()
const form = reactive({})

const headers = computed(() => ({
  'Authorization': 'Bearer ' + getToken()
}))

function open(data = {}) {
  visible.value = true
  
  props.items.forEach(item => {
    form[item.prop] = data[item.prop] ?? item.default ?? null
  })
}

function handleClose() {
  visible.value = false
  formRef.value?.resetFields()
}

async function handleSubmit() {
  if (!formRef.value) return
  
  await formRef.value.validate((valid) => {
    if (!valid) return
    
    loading.value = true
    try {
      emit('submit', { ...form })
    } finally {
      loading.value = false
    }
  })
}

function handleUploadSuccess(res, prop) {
  if (res.code === 200) {
    form[prop] = res.data.filepath
  } else {
    ElMessage.error(res.msg || '上传失败')
  }
}

function setForm(key, value) {
  form[key] = value
}

defineExpose({ open, handleClose, setForm })
</script>