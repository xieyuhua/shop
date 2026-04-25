<template>
  <div class="user-page">
    <SearchForm
      ref="searchRef"
      :items="searchItems"
      @search="handleSearch"
      @reset="handleReset"
    >
      <template #action>
        <el-button type="primary" @click="handleAdd">
          <el-icon><Plus /></el-icon>添加
        </el-button>
      </template>
    </SearchForm>

    <BaseTable
      :columns="columns"
      :data="tableData"
      :loading="loading"
      :total="pagination.total"
      :page="pagination.page"
      :limit="pagination.limit"
      @edit="handleEdit"
      @delete="handleDelete"
      @page-change="handlePageChange"
    />

    <BaseForm
      ref="formRef"
      title="会员"
      :items="formItems"
      @submit="handleSubmit"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { getUserList, createUser, updateUser, deleteUser } from '@/api/user'
import { confirm, success, error } from '@/utils'

const searchRef = ref()
const formRef = ref()
const loading = ref(false)
const tableData = ref([])

const searchItems = [
  { prop: 'keyword', label: '关键词', type: 'input', placeholder: '搜索用户名/手机号/昵称' }
]

const columns = [
  { prop: 'id', label: 'ID', width: '60' },
  { prop: 'mobile', label: '手机号', width: '120' },
  { prop: 'nickname', label: '昵称' },
  { prop: 'balance', label: '余额', width: '100', formatter: val => '￥' + val },
  { prop: 'points', label: '积分', width: '80' },
  { prop: 'status', label: '状态', width: '80', dict: 'status' },
  { prop: 'create_time', label: '注册时间', width: '170' }
]

const formItems = [
  { prop: 'mobile', type: 'input', label: '手机号', rules: [{ required: true, message: '请输入手机号' }] },
  { prop: 'password', type: 'input', label: '密码' },
  { prop: 'nickname', type: 'input', label: '昵称' },
  { prop: 'email', type: 'input', label: '邮箱' },
  { prop: 'balance', type: 'number', label: '余额', default: 0 },
  { prop: 'points', type: 'number', label: '积分', default: 0 },
  { prop: 'status', type: 'select', label: '状态', options: [{ value: 1, label: '正常' }, { value: 0, label: '禁用' }], default: 1 }
]

const pagination = reactive({
  page: 1,
  limit: 15,
  total: 0
})

const searchParams = reactive({})

async function fetchData() {
  loading.value = true
  try {
    const params = { page: pagination.page, limit: pagination.limit, ...searchParams }
    const res = await getUserList(params)
    tableData.value = res.data.list || []
    pagination.total = res.data.total || 0
  } finally {
    loading.value = false
  }
}

function handleSearch(params) {
  Object.assign(searchParams, params)
  pagination.page = 1
  fetchData()
}

function handleReset() {
  Object.keys(searchParams).forEach(k => delete searchParams[k])
  pagination.page = 1
  fetchData()
}

function handlePageChange({ page, limit }) {
  pagination.page = page
  pagination.limit = limit
  fetchData()
}

function handleAdd() {
  formRef.value?.open()
}

function handleEdit(row) {
  formRef.value?.open(row)
}

async function handleSubmit(data) {
  const res = data.id ? await updateUser(data) : await createUser(data)
  if (res.code === 200) {
    success(res.msg || '保存成功')
    formRef.value?.handleClose()
    fetchData()
  } else {
    error(res.msg || '保存失败')
  }
}

async function handleDelete(row) {
  await confirm('确定要删除该会员吗？')
  const res = await deleteUser(row.id)
  if (res.code === 200) {
    success('删除成功')
    fetchData()
  } else {
    error(res.msg || '删除失败')
  }
}

onMounted(() => {
  fetchData()
})
</script>