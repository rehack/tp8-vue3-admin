<template>
    <div class="consultation-list">
        <el-card>
            <template #header>
                <span>咨询详情</span>
            </template>

            <el-table v-loading="loading" :data="tableData" stripe style="width: 100%">
                <el-table-column prop="id" label="ID" width="80" />
                <el-table-column prop="customer_name" label="客户姓名" width="120" />
                <el-table-column prop="consult_time" label="咨询时间" width="180" />
                <el-table-column prop="consult_type" label="咨询类型" width="120" />
                <el-table-column prop="content" label="咨询内容" />
                <el-table-column prop="status" label="状态" width="100">
                    <template #default="{ row }">
                        <el-tag :type="row.status === 1 ? 'success' : 'warning'">
                            {{ row.status === 1 ? '已回复' : '待回复' }}
                        </el-tag>
                    </template>
                </el-table-column>
            </el-table>
        </el-card>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const loading = ref(false)
const tableData = ref([])

onMounted(() => {
    tableData.value = [
        {
            id: 1,
            customer_name: '张三',
            consult_time: '2026-03-30 09:00',
            consult_type: '产品咨询',
            content: '想了解产品价格',
            status: 1,
        },
    ]
})
</script>
