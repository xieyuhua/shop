<template>
  <el-table
    v-bind="$attrs"
    :data="data"
    v-loading="loading"
    stripe
    border
    @selection-change="handleSelectionChange"
  >
    <el-table-column v-if="showSelection" type="selection" width="55" />
    <el-table-column v-if="showIndex" type="index" label="序号" width="60" />
    
    <slot>
      <slot v-for="col in columns" :key="col.prop">
        <el-table-column
          v-if="!col.hidden"
          :prop="col.prop"
          :label="col.label"
          :width="col.width"
          :min-width="col.minWidth"
          :align="col.align || 'center'"
          :fixed="col.fixed"
          :sortable="col.sortable"
        >
          <template #default="{ row }">
            <slot :name="col.prop" :row="row">
              <template v-if="col.formatter">{{ col.formatter(row[col.prop], row) }}</template>
              <template v-else-if="col.dict">{{ getDictLabel(col.dict, row[col.prop]) }}</template>
              <template v-else>{{ row[col.prop] }}</template>
            </slot>
          </template>
        </el-table-column>
      </slot>
    </slot>

    <el-table-column v-if="showAction" label="操作" :width="actionWidth" :fixed="actionFixed">
      <template #default="{ row }">
        <slot name="action" :row="row">
          <el-button type="primary" link @click="emit('edit', row)">编辑</el-button>
          <el-button type="danger" link @click="emit('delete', row)">删除</el-button>
        </slot>
      </template>
    </el-table-column>
  </el-table>

  <el-pagination
    v-if="showPagination"
    v-model:current-page="page"
    v-model:page-size="limit"
    :total="total"
    :page-sizes="[15, 30, 50, 100]"
    layout="total, sizes, prev, pager, next, jumper"
    class="pagination"
    @change="handlePageChange"
  />
</template>

<script setup>
import { ref, reactive, watch, computed } from 'vue'
import { getDictLabel } from '@/utils/dict'

const props = defineProps({
  columns: { type: Array, default: () => [] },
  data: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  total: { type: Number, default: 0 },
  page: { type: Number, default: 1 },
  limit: { type: Number, default: 15 },
  showSelection: { type: Boolean, default: false },
  showIndex: { type: Boolean, default: true },
  showAction: { type: Boolean, default: true },
  showPagination: { type: Boolean, default: true },
  actionWidth: { type: [String, Number], default: '150' },
  actionFixed: { type: String, default: 'right' }
})

const emit = defineEmits(['edit', 'delete', 'selection-change', 'page-change'])

const page = ref(props.page)
const limit = ref(props.limit)

watch(() => props.page, v => page.value = v)
watch(() => props.limit, v => limit.value = v)

function handleSelectionChange(val) {
  emit('selection-change', val)
}

function handlePageChange() {
  emit('page-change', { page: page.value, limit: limit.value })
}
</script>

<style scoped>
.pagination {
  margin-top: 15px;
  justify-content: flex-end;
}
</style>