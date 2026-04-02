<template>
    <div class="arrival-list">
        <el-card>
            <template #header>
                <span>到院明细</span>
            </template>

            <el-table v-loading="loading" :data="tableData" stripe style="width: 100%">
                <el-table-column prop="id" label="ID" width="80" />
                <el-table-column prop="customer_name" label="客户姓名" width="120" />
                <el-table-column prop="arrival_time" label="到院时间" width="180" />
                <el-table-column prop="purpose" label="到院目的" />
                <el-table-column prop="result" label="处理结果" />
                <el-table-column prop="handler" label="接待人" width="100" />
            </el-table>

            <el-pagination
                v-model:current-page="pagination.currentPage"
                v-model:page-size="pagination.pageSize"
                :total="pagination.total"
                :page-sizes="[10, 20, 50]"
                layout="total, sizes, prev, pager, next"
                style="margin-top: 16px; justify-content: flex-end"
            />
        </el-card>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'

const loading = ref(false)
const tableData = ref([])
const pagination = reactive({ currentPage: 1, pageSize: 10, total: 0 })

onMounted(() => {
    tableData.value = [
        {
            id: 1,
            customer_name: '张三',
            arrival_time: '2026-03-30 10:00',
            purpose: '咨询',
            result: '已处理',
            handler: '王医生',
        },
    ]
    pagination.total = 1
})
</script>
