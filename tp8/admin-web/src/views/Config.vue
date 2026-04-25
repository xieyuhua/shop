<template>
  <div class="config-page">
    <el-card>
      <el-tabs v-model="activeTab">
        <el-tab-pane label="基础配置" name="basic">
          <el-form label-width="100px">
            <el-form-item label="网站名称">
              <el-input v-model="config.site_name" />
            </el-form-item>
            <el-form-item label="网站Logo">
              <el-input v-model="config.site_logo" />
            </el-form-item>
            <el-form-item label="ICP备案号">
              <el-input v-model="config.site_icp" />
            </el-form-item>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="商城配置" name="shop">
          <el-form label-width="100px">
            <el-form-item label="满多少免运">
              <el-input v-model="config.free_shipping_amount" type="number" />
            </el-form-item>
            <el-form-item label="运费">
              <el-input v-model="config.shipping_fee" type="number" />
            </el-form-item>
            <el-form-item label="积分抵现">
              <el-input v-model="config.points_rate" type="number" />
            </el-form-item>
          </el-form>
        </el-tab-pane>
      </el-tabs>
      <el-button type="primary" @click="handleSave">保存配置</el-button>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { getConfig, saveConfig } from '@/api/config'
import { ElMessage } from 'element-plus'

const activeTab = ref('basic')
const config = reactive({})

async function fetchData() {
  const res = await getConfig(activeTab.value)
  Object.assign(config, res.data || {})
}

async function handleSave() {
  try {
    await saveConfig(config)
    ElMessage.success('保存成功')
  } catch (e) {
    ElMessage.error(e.message)
  }
}

function handleTabChange() {
  fetchData()
}

onMounted(() => {
  fetchData()
})
</script>