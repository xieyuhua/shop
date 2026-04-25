<template>
  <div class="upload-wrap">
    <el-upload
      v-bind="$attrs"
      :action="action"
      :headers="headers"
      :data="data"
      :limit="limit"
      :accept="accept"
      :file-list="fileList"
      :before-upload="handleBeforeUpload"
      :on-success="handleSuccess"
      :on-error="handleError"
      :on-progress="handleProgress"
      :on-change="handleChange"
      :on-remove="handleRemove"
      :on-exceed="handleExceed"
      :drag="drag"
      :disabled="disabled"
      list-type="picture-card"
    >
      <slot>
        <div class="upload-trigger">
          <el-icon><Plus /></el-icon>
          <div class="upload-text">上传{{ text }}</div>
        </div>
      </slot>
    </el-upload>
    
    <div v-if="tip" class="upload-tip">{{ tip }}</div>
    
    <el-dialog v-model="previewVisible" title="图片预览">
      <img :src="previewUrl" style="max-width: 100%" />
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { getToken } from '@/utils/storage'
import { ElMessage } from 'element-plus'

const props = defineProps({
  action: { type: String, default: '/api/admin/file/upload' },
  text: { type: String, default: '图片' },
  accept: { type: String, default: 'image/*' },
  limit: { type: Number, default: 9 },
  maxSize: { type: Number, default: 10 },
  drag: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  modelValue: { type: [String, Array], default: '' },
  data: { type: Object, default: () => ({}) },
  tip: { type: String, default: '' }
})

const emit = defineEmits(['update:modelValue', 'change', 'success', 'error'])

const fileList = ref([])
const uploading = ref(false)
const previewVisible = ref(false)
const previewUrl = ref('')

const headers = computed(() => ({
  'Authorization': 'Bearer ' + getToken()
}))

function handleBeforeUpload(file) {
  const size = props.maxSize * 1024 * 1024
  if (file.size > size) {
    ElMessage.error(`文件大小不能超过${props.maxSize}MB`)
    return false
  }
  uploading.value = true
}

function handleSuccess(res, file, fileList) {
  uploading.value = false
  
  if (res.code === 200) {
    const list = fileList.map(f => f.response?.data || f)
    const urls = list.map(f => f.filepath || f.url).filter(Boolean)
    
    emit('update:modelValue', props.limit === 1 ? urls[0] : urls)
    emit('change', list)
    emit('success', res.data)
    
    ElMessage.success(res.msg || '上传成功')
  } else {
    ElMessage.error(res.msg || '上传失败')
    emit('error', res)
  }
}

function handleError(err) {
  uploading.value = false
  ElMessage.error('上传失败')
  emit('error', err)
}

function handleProgress(event, file) {
  uploading.value = true
}

function handleChange(file, fileList) {
  if (file.status === 'ready') {
    fileList.splice(fileList.indexOf(file), 1)
  }
}

function handleRemove(file, fileList) {
  const urls = fileList.map(f => f.response?.data?.filepath || f.url).filter(Boolean)
  emit('update:modelValue', props.limit === 1 ? '' : urls)
  emit('change', fileList)
}

function handleExceed() {
  ElMessage.warning(`最多上传${props.limit}个文件`)
}
</script>

<style scoped>
.upload-wrap { display: inline-block; }
.upload-trigger {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100px;
  height: 100px;
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
}
.upload-trigger:hover { border-color: #409eff; }
.upload-icon { font-size: 28px; color: #8c939d; }
.upload-text { font-size: 12px; color: #8c939d; margin-top: 8px; }
.upload-tip { font-size: 12px; color: #909399; margin-top: 5px; }
</style>