<template>
  <div class="order-page">
    <el-card>
      <el-table :data="list" v-loading="loading">
        <el-table-column prop="order_no" label="订单号" width="180" />
        <el-table-column prop="receiver_name" label="收货人" width="100" />
        <el-table-column prop="receiver_mobile" label="电话" width="120" />
        <el-table-column prop="total_amount" label="订单金额" width="100">
          <template #default="{ row }">￥{{ row.total_amount }}</template>
        </el-table-column>
        <el-table-column prop="pay_amount" label="实付金额" width="100">
          <template #default="{ row }">￥{{ row.pay_amount }}</template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">{{ getStatusText(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="下单时间" width="170" />
        <el-table-column label="操作" width="100" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="handleDetail(row)">详情</el-button>
          </template>
        </el-table-column>
      </el-table>
      
      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.limit"
        :total="pagination.total"
        layout="total, prev, pager, next"
        style="margin-top: 20px; justify-content: flex-end"
        @change="fetchData"
      />
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { getOrderList } from '@/api/order'
import { ElMessage } from 'element-plus'

const list = ref([])
const loading = ref(false)
const pagination = reactive({ page: 1, limit: 15, total: 0 })

const statusMap = { 0: '待付款', 1: '待发货', 2: '待收货', 3: '已完成', 4: '已取消', 5: '已退款' }

function getStatusText(status) {
  return statusMap[status] || '未知'
}

function getStatusType(status) {
  const types = ['', 'warning', 'info', 'success', 'info', 'danger']
  return types[status] || ''
}

function handleDetail(row) {
  window.location.href = '/order/detail?id=' + row.id
}

async function fetchData() {
  loading.value = true
  try {
    const res = await getOrderList({ page: pagination.page, limit: pagination.limit })
    list.value = res.data.list || []
    pagination.total = res.data.total || 0
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>