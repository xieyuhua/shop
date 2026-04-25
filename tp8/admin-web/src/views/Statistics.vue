<template>
  <div class="statistics-page">
    <el-row :gutter="20">
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-info">
              <div class="stat-label">订单数</div>
              <div class="stat-value">{{ stats.order_count || 0 }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-info">
              <div class="stat-label">销售额</div>
              <div class="stat-value">￥{{ Number(stats.order_amount || 0).toFixed(2) }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-info">
              <div class="stat-label">新增会员</div>
              <div class="stat-value">{{ stats.user_count || 0 }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-info">
              <div class="stat-label">在售商品</div>
              <div class="stat-value">{{ stats.product_count || 0 }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>
    
    <el-row :gutter="20" style="margin-top: 20px">
      <el-col :span="24">
        <el-card>
          <template #header>
            <div style="display:flex;justify-content:space-between;align-items:center">
              <span>销售趋势</span>
              <el-radio-group v-model="chartType" size="small" @change="fetchChart">
                <el-radio-button :value="7">7天</el-radio-button>
                <el-radio-button :value="30">30天</el-radio-button>
              </el-radio-group>
            </div>
          </template>
          <div id="chart" style="height: 300px"></div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import * as echarts from 'echarts'
import { getStatistics, getChartData } from '@/api/statistics'

const stats = ref({})
const chartData = ref([])
const chartType = ref(7)

async function fetchData() {
  const res = await getStatistics({ type: chartType.value === 7 ? '7days' : '30days' })
  stats.value = res.data || {}
}

async function fetchChart() {
  const res = await getChartData({ days: chartType.value })
  chartData.value = res.data || []
  nextTick(() => renderChart())
}

function renderChart() {
  const chart = echarts.init(document.getElementById('chart'))
  const dates = chartData.value.map(item => item.date)
  const amounts = chartData.value.map(item => Number(item.amount || 0))
  
  chart.setOption({
    tooltip: { trigger: 'axis' },
    xAxis: { type: 'category', data: dates },
    yAxis: { type: 'value' },
    series: [{ data: amounts, type: 'line', smooth: true, areaStyle: { opacity: 0.3 } }]
  })
}

onMounted(() => {
  fetchData()
  fetchChart()
})
</script>

<style scoped>
.stat-card { text-align: center; padding: 10px 0; }
.stat-label { color: #909399; font-size: 14px; }
.stat-value { font-size: 24px; font-weight: bold; color: #303133; margin-top: 10px; }
</style>