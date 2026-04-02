<template>
    <div class="customer-list">
        <div class="search-bar">
            <el-input
                v-model="searchName"
                placeholder="客户姓名"
                style="width: 160px; margin-right: 10px"
                clearable
                @keyup.enter="handleSearch"
            />
            <el-input
                v-model="searchPhone"
                placeholder="手机号"
                style="width: 160px; margin-right: 10px"
                clearable
                @keyup.enter="handleSearch"
            />
            <el-button type="primary" @click="handleSearch">搜索</el-button>
        </div>

        <el-table v-loading="loading" :data="tableData" stripe style="width: 100%; margin-top: 16px">
            <el-table-column type="index" width="50" />
            <el-table-column prop="id" label="ID" width="260" show-overflow-tooltip />
            <el-table-column prop="name" label="客户姓名" width="120" />
            <el-table-column prop="phone_number" label="手机号" width="130" />
            <el-table-column prop="vip_num" label="会员号" width="120" />
            <el-table-column prop="pre_user_name" label="报备人" width="120" />
            <el-table-column prop="cost_amount" label="消费总额" width="120" />
            <el-table-column prop="remark" label="备注" width="220" show-overflow-tooltip />
            <el-table-column prop="status" label="状态" width="100">
                <template #default="{ row }">
                    <el-tag :type="row.status === 1 ? 'success' : 'info'">
                        {{ row.status === 1 ? '有效' : '无效' }}
                    </el-tag>
                </template>
            </el-table-column>
            <el-table-column prop="create_date" label="创建时间" width="180" />
            <el-table-column label="操作">
                <template #default="{ row }">
                    <el-button link type="primary" @click="handleView(row)">详情</el-button>
                </template>
            </el-table-column>
        </el-table>

        <el-pagination
            background
            v-model:current-page="currentPage"
            v-model:page-size="pageSize"
            :total="total"
            :page-sizes="[100, 200, 500, 1000]"
            layout="total, sizes, prev, pager, next, jumper"
            style="margin-top: 16px; justify-content: flex-end"
            @update:current-page="handleCurrentChange"
            @update:page-size="handleSizeChange"
        />
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { getCustomerList } from '@/api/customer'

const loading = ref(false)
const searchName = ref('')
const searchPhone = ref('')
const tableData = ref([])
const currentPage = ref(1)
const pageSize = ref(100)
const total = ref(0)

// 监听分页变化，自动加载数据
// watch([currentPage, pageSize], () => {
//     loadData()
// })

async function loadData() {
    loading.value = true
    try {
        const res = await getCustomerList({
            page: currentPage.value,
            page_size: pageSize.value,
            name: searchName.value,
            phone: searchPhone.value,
        })
        tableData.value = res?.data ?? []
        total.value = Number(res?.total) || 0
    } catch (error) {
        console.error('加载数据失败:', error)
        tableData.value = []
        total.value = 0
    } finally {
        loading.value = false
    }
}

function handleSearch() {
    currentPage.value = 1
    loadData()
}

function handleSizeChange(val) {
    // pageSize.value = val
    // currentPage.value = 1
    loadData()
}

function handleCurrentChange(val) {
    // currentPage.value = val
    loadData()
}

function handleView(row) {
    console.log('查看:', row)
}

onMounted(() => {
    loadData()
})
</script>

<style lang="scss" scoped>
.customer-list {
    height: 100%;
    display: flex;
    flex-direction: column;
}
</style>
