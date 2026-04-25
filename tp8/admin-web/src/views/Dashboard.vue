<template>
  <div class="dashboard">
    <el-row :gutter="20">
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-icon" style="background: #409eff">
              <el-icon size="30"><ShoppingCart /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-label">今日订单</div>
              <div class="stat-value">{{ stats.today_order || 0 }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-icon" style="background: #67c23a">
              <el-icon size="30"><Money /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-label">今日销售额</div>
              <div class="stat-value">￥{{ stats.today_sales || 0 }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-icon" style="background: #e6a23c">
              <el-icon size="30"><User /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-label">新增会员</div>
              <div class="stat-value">{{ stats.today_user || 0 }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-icon" style="background: #f56c6c">
              <el-icon size="30"><Goods /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-label">在售商品</div>
              <div class="stat-value">{{ stats.today_product || 0 }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>
    
    <el-row :gutter="20" style="margin-top: 20px">
      <el-col :span="12">
        <el-card>
          <template #header>待处理订单</template>
          <div class="pending-list">
            <div class="pending-item">
              <span>待付款</span>
              <el-badge :value="stats.pending_payment" type="warning" />
            </div>
            <div class="pending-item">
              <span>待发货</span>
              <el-badge :value="stats.pending_ship" type="info" />
            </div>
            <div class="pending-item">
              <span>待收货</span>
              <el-badge :value="stats.pending_receive" type="primary" />
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="12">
        <el-card>
          <template #header>快捷操作</template>
          <div class="quick-actions">
            <el-button type="primary" @click="$router.push('/product')">商品管理</el-button>
            <el-button type="success" @click="$router.push('/order')">订单管理</el-button>
            <el-button type="warning" @click="$router.push('/user')">会员管理</el-button>
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getStatistics } from '@/api/statistics'

const stats = ref({})

onMounted(async () => {
  try {
    const res = await getStatistics({ type: 'today' })
    stats.value = res.data || {}
  } catch (e) {
    console.error(e)
  }
})
</script>

<style scoped>
.stat-card { display: flex; align-items: center; gap: 20px; }
.stat-icon {
  width: 60px; height: 60px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center; color: #fff;
}
.stat-info { flex: 1; }
.stat-label { color: #909399; font-size: 14px; }
.stat-value { font-size: 24px; font-weight: bold; color: #303133; }
.pending-list { display: flex; justify-content: space-around; }
.pending-item { text-align: center; }
.quick-actions { display: flex; gap: 10px; }
</style>