<template>
    <div class="role-container">
        <el-card>
            <template #header>
                <div class="card-header">
                    <span>角色权限管理</span>
                    <el-button type="primary" @click="handleAdd">
                        <el-icon><Plus /></el-icon>新增角色
                    </el-button>
                </div>
            </template>

            <el-table v-loading="loading" :data="roleList" stripe style="width: 100%">
                <el-table-column prop="id" label="ID" width="80" />
                <el-table-column prop="name" label="角色名称" width="200" />
                <el-table-column prop="status" label="状态" width="100">
                    <template #default="{ row }">
                        <el-tag :type="row.status === 1 ? 'success' : 'danger'" size="small">
                            {{ row.status === 1 ? '正常' : '禁用' }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="rules_id" label="API权限" min-width="200">
                    <template #default="{ row }">
                        <span v-if="!row.rules_id">-</span>
                        <el-tag
                            v-else
                            v-for="rid in (row.rules_id + '').split(',')"
                            :key="rid"
                            size="small"
                            type="warning"
                            style="margin-right: 4px; margin-bottom: 2px"
                        >
                            {{ rid }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="view_scope" label="数据范围" width="120">
                    <template #default="{ row }">
                        {{ row.view_scope ?? '-' }}
                    </template>
                </el-table-column>
                <el-table-column prop="menu_id" label="菜单权限" min-width="300">
                    <template #default="{ row }">
                        <el-tag
                            v-for="menu in row.menus"
                            :key="menu.id"
                            size="small"
                            style="margin-right: 4px; margin-bottom: 2px"
                        >
                            {{ menu.title }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="200" fixed="right">
                    <template #default="{ row }">
                        <el-button link type="primary" @click="handleEdit(row)">编辑</el-button>
                        <el-button link type="danger" @click="handleDelete(row)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </el-card>

        <role-dialog
            v-model:visible="dialogVisible"
            :role-data="currentRole"
            :all-menus="allMenus"
            :all-rules="allRules"
            @success="loadData"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { getRoles } from '../../../api/user'
import { getMenus, getAuthRules, deleteRole } from '../../../api/system'

const loading = ref(false)
const roleList = ref([])
const allMenus = ref([])
const allRules = ref([])
const dialogVisible = ref(false)
const currentRole = ref(null)

async function loadData() {
    loading.value = true
    try {
        const [rolesRes, menusRes, rulesRes] = await Promise.all([getRoles(), getMenus(), getAuthRules()])
        roleList.value = rolesRes || []
        allMenus.value = menusRes || []
        allRules.value = rulesRes || []
    } catch (error) {
        ElMessage.error('加载数据失败')
    } finally {
        loading.value = false
    }
}

function handleAdd() {
    currentRole.value = null
    dialogVisible.value = true
}

function handleEdit(row) {
    // rules_id stored as comma-separated string, convert to array for tree
    const rulesIdArr = row.rules_id
        ? (row.rules_id + '').split(',').map((v) => Number(v)).filter(Boolean)
        : []
    currentRole.value = {
        ...row,
        menu_id: row.menus?.map((m) => m.id) || [],
        rules_id: rulesIdArr,
    }
    dialogVisible.value = true
}

async function handleDelete(row) {
    try {
        await ElMessageBox.confirm(`确定要删除角色 "${row.name}" 吗？`, '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning',
        })
        await deleteRole({ id: row.id })
        ElMessage.success('删除成功')
        loadData()
    } catch (e) {
        // cancelled or error
    }
}

onMounted(() => {
    loadData()
})
</script>

<style lang="scss" scoped>
.role-container {
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
}
</style>
