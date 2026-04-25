<template>
  <el-card>
    <el-table :data="list" row-key="id" default-expand-all>
      <el-table-column prop="id" label="ID" width="60" />
      <el-table-column prop="name" label="分类名称" />
      <el-table-column prop="slug" label="别名" />
      <el-table-column prop="sort" label="排序" width="80" />
      <el-table-column prop="status" label="状态" width="80">
        <template #default="{ row }">
          <el-tag :type="row.status === 1 ? 'success' : 'danger'">{{ row.status === 1 ? '显示' : '隐藏' }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="150" fixed="right">
        <template #default="{ row }">
          <el-button type="primary" link @click="handleEdit(row)">编辑</el-button>
          <el-button type="danger" link @click="handleDelete(row)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
  </el-card>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getCategoryList, deleteCategory } from '@/api/category'
import { ElMessage, ElMessageBox } from 'element-plus'

const list = ref([])

function handleEdit(row) {
  window.location.href = '/category/edit?id=' + row.id
}

async function handleDelete(row) {
  await ElMessageBox.confirm('确定要删除该分类吗？', '提示', { type: 'warning' })
  try {
    await deleteCategory(row.id)
    ElMessage.success('删除成功')
    fetchData()
  } catch (e) {
    ElMessage.error(e.message)
  }
}

async function fetchData() {
  const res = await getCategoryList()
  list.value = res.data || []
}

onMounted(() => {
  fetchData()
})
</script>