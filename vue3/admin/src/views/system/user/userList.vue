<template>
    <div class="user-container">
        <el-card>
            <template #header>
                <div class="card-header">
                    <span>用户管理</span>
                    <el-button type="primary" @click="handleAdd">
                        <el-icon><Plus /></el-icon>新增用户
                    </el-button>
                </div>
            </template>

            <div class="search-bar">
                <el-input
                    v-model="searchKeyword"
                    placeholder="搜索用户名/姓名/手机号"
                    style="width: 240px; margin-right: 10px"
                    clearable
                    @keyup.enter="handleSearch"
                >
                    <!-- <template #append>
                        <el-button icon="Search" @click="handleSearch" />
                    </template> -->
                </el-input>
                <el-select
                    v-model="jobstatus"
                    placeholder="在职状态"
                    style="width: 120px; margin-right: 10px"
                    clearable
                    @change="handleSearch"
                >
                    <el-option label="在职" :value="1" />
                    <el-option label="离职" :value="0" />
                </el-select>
                <el-button type="primary" @click="handleSearch">搜索</el-button>
            </div>

            <el-table
                v-loading="loading"
                :data="userList"
                stripe
                style="width: 100%; margin-top: 16px"
            >
                <el-table-column prop="id" label="ID" width="80" />
                <el-table-column prop="username" label="用户名" width="120" />
                <el-table-column prop="realname" label="姓名" width="120" />
                <el-table-column prop="phone" label="手机号" width="130" />
                <el-table-column prop="sex" label="性别" width="80">
                    <template #default="{ row }">
                        {{ row.sex === 1 ? '男' : row.sex === 2 ? '女' : '-' }}
                    </template>
                </el-table-column>
                <el-table-column prop="dept" label="部门" width="150">
                    <template #default="{ row }">
                        {{ row.dept?.department_name || '-' }}
                    </template>
                </el-table-column>
                <el-table-column prop="roles" label="角色" min-width="200">
                    <template #default="{ row }">
                        <el-tag
                            v-for="role in row.roles"
                            :key="role.id"
                            size="small"
                            style="margin-right: 4px"
                        >
                            {{ role.name }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="status" label="状态" width="100">
                    <template #default="{ row }">
                        <el-tag :type="row.status === 1 ? 'success' : 'danger'">
                            {{ row.status === 1 ? '正常' : '禁用' }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="create_time" label="创建时间" width="180">
                    <template #default="{ row }">
                        {{ formatDate(row.create_time) }}
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="200" fixed="right">
                    <template #default="{ row }">
                        <el-button link type="primary" @click="handleEdit(row)">编辑</el-button>
                        <el-button link type="danger" @click="handleDelete(row)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>

            <el-pagination
                background
                v-model:current-page="pagination.currentPage"
                v-model:page-size="pagination.pageSize"
                :total="pagination.total"
                :page-sizes="[10, 20, 50, 100]"
                layout="total, sizes, prev, pager, next, jumper"
                style="margin-top: 16px; justify-content: flex-end"
                @update:current-page="loadData"
                @update:page-size="loadData"
            />
        </el-card>

        <user-dialog v-model:visible="dialogVisible" :user-data="currentUser" @success="loadData" />
    </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { getStaffList, updateStaff } from '@/api/user'
import { formatDate } from '@/utils'
import UserDialog from './UserDialog.vue'

const loading = ref(false)
const searchKeyword = ref('')
const jobstatus = ref(1)
const userList = ref([])
const dialogVisible = ref(false)
const currentUser = ref(null)

const pagination = reactive({
    currentPage: 1,
    pageSize: 10,
    total: 0,
})

// 监听分页变化，自动加载数据
// watch([pagination.currentPage, pagination.pageSize], () => {
//     loadData()
// })

async function loadData() {
    loading.value = true
    try {
        const res = await getStaffList({
            fuzzy_keyword: searchKeyword.value,
            jobstatus: jobstatus.value,
            pageSize: pagination.pageSize,
            currentPage: pagination.currentPage,
        })
        userList.value = res?.data ?? []
        pagination.total = Number(res?.total) || 0
    } catch (error) {
        ElMessage.error('加载数据失败')
    } finally {
        loading.value = false
    }
}

function handleSearch() {
    pagination.currentPage = 1
    if( jobstatus.value === undefined) jobstatus.value = ''
    loadData()
}

function handleAdd() {
    currentUser.value = null
    dialogVisible.value = true
}

function handleEdit(row) {
    currentUser.value = { ...row }
    dialogVisible.value = true
}

async function handleDelete(row) {
    try {
        await ElMessageBox.confirm(`确定要删除用户 "${row.realname}" 吗？`, '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning',
        })
        ElMessage.success('删除成功')
        loadData()
    } catch {}
}

onMounted(() => {
    loadData()
})
</script>

<style lang="scss" scoped>
.user-container {
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .search-bar {
        margin-bottom: 0;
    }
}
</style>
