<template>
    <div class="auth-container">
        <el-card>
            <template #header>
                <div class="card-header">
                    <span>权限管理</span>
                </div>
            </template>

            <el-tabs v-model="activeTab" class="auth-tabs">
                <!-- Tab1: 权限规则 -->
                <el-tab-pane label="权限规则" name="rules">
                    <div class="tab-toolbar">
                        <el-button type="primary" @click="handleAddRule(null)">
                            <el-icon><Plus /></el-icon>新增规则
                        </el-button>
                    </div>
                    <el-table
                        v-loading="ruleLoading"
                        :data="ruleList"
                        row-key="id"
                        :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
                        default-expand-all
                        stripe
                    >
                        <el-table-column prop="title" label="规则名称/路由" min-width="250" />
                        <el-table-column prop="title" label="规则标题" width="150" />
                        <el-table-column prop="type" label="类型" width="120">
                            <template #default="{ row }">
                                <el-tag v-if="row.type === 1" type="success" size="small">菜单/查询</el-tag>
                                <el-tag v-else-if="row.type === 2" type="warning" size="small">新增</el-tag>
                                <el-tag v-else-if="row.type === 3" type="warning" size="small">修改</el-tag>
                                <el-tag v-else-if="row.type === 4" type="danger" size="small">删除</el-tag>
                                <el-tag v-else-if="row.type === 5" type="info" size="small">导出</el-tag>
                                <el-tag v-else type="info" size="small">{{ row.type }}</el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column prop="status" label="状态" width="100">
                            <template #default="{ row }">
                                <el-tag :type="row.status === 1 ? 'success' : 'danger'" size="small">
                                    {{ row.status === 1 ? '正常' : '禁用' }}
                                </el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column prop="sort" label="排序" width="100" />
                        <el-table-column label="操作" width="220" fixed="right">
                            <template #default="{ row }">
                                <el-button link type="primary" size="small" @click="handleAddRule(row)">新增子规则</el-button>
                                <el-button link type="primary" size="small" @click="handleEditRule(row)">编辑</el-button>
                                <el-button link type="danger" size="small" @click="handleDeleteRule(row)">删除</el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-tab-pane>

                <!-- Tab2: 角色管理 -->
                <el-tab-pane label="角色管理" name="roles">
                    <div class="tab-toolbar">
                        <el-button type="primary" @click="handleAddRole">
                            <el-icon><Plus /></el-icon>新增角色
                        </el-button>
                    </div>
                    <el-table v-loading="roleLoading" :data="roleList" stripe style="width: 100%">
                        <el-table-column prop="id" label="ID" width="80" />
                        <el-table-column prop="name" label="角色名称" width="180" />
                        <el-table-column prop="title" label="角色标题" width="150" />
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
                                    v-for="rid in splitRules(row.rules_id)"
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
                                {{ formatViewScope(row.view_scope) }}
                            </template>
                        </el-table-column>
                        <el-table-column prop="menus" label="菜单权限" min-width="300">
                            <template #default="{ row }">
                                <el-tag
                                    v-for="menu in row.menus"
                                    :key="menu.id"
                                    size="small"
                                    style="margin-right: 4px; margin-bottom: 2px"
                                >
                                    {{ menu.title }}
                                </el-tag>
                                <span v-if="!row.menus?.length">-</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="操作" width="160" fixed="right">
                            <template #default="{ row }">
                                <el-button link type="primary" size="small" @click="handleEditRole(row)">编辑</el-button>
                                <el-button link type="danger" size="small" @click="handleDeleteRole(row)">删除</el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-tab-pane>
            </el-tabs>
        </el-card>

        <!-- 规则新增/编辑弹窗 -->
        <el-dialog v-model="ruleDialogVisible" :title="ruleForm.id ? '编辑规则' : '新增规则'" width="500px" @close="ruleDialogClose">
            <el-form ref="ruleFormRef" :model="ruleForm" :rules="ruleFormRules" label-width="100px">
                <el-form-item label="上级规则" prop="pid">
                    <el-tree-select
                        v-model="ruleForm.pid"
                        :data="ruleTreeData"
                        :props="{ label: 'title', value: 'id', children: 'children' }"
                        check-strictly
                        placeholder="不选则为顶级"
                        clearable
                        style="width: 100%"
                    />
                </el-form-item>
                <el-form-item label="规则标题" prop="title">
                    <el-input v-model="ruleForm.title" placeholder="如：获取职员列表" />
                </el-form-item>
                <el-form-item label="规则路由" prop="name">
                    <el-input v-model="ruleForm.name" placeholder="如：bll.System/getStaff" />
                </el-form-item>
                <el-form-item label="类型" prop="type">
                    <el-select v-model="ruleForm.type" placeholder="请选择类型" style="width: 100%">
                        <el-option :value="0" label="目录" />
                        <el-option :value="1" label="菜单/查询" />
                        <el-option :value="2" label="新增" />
                        <el-option :value="3" label="修改" />
                        <el-option :value="4" label="删除" />
                        <el-option :value="5" label="导出" />
                    </el-select>
                </el-form-item>
                <el-form-item label="状态" prop="status">
                    <el-radio-group v-model="ruleForm.status">
                        <el-radio :value="1">正常</el-radio>
                        <el-radio :value="0">禁用</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="排序" prop="sort">
                    <el-input-number v-model="ruleForm.sort" :min="0" />
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="ruleDialogVisible = false">取消</el-button>
                <el-button type="primary" @click="handleSaveRule">确定</el-button>
            </template>
        </el-dialog>

        <!-- 角色新增/编辑弹窗 -->
        <el-dialog
            v-model="roleDialogVisible"
            :title="roleForm.id ? '编辑角色' : '新增角色'"
            width="850px"
            @close="roleDialogClose"
        >
            <el-tabs v-model="roleActiveTab">
                <el-tab-pane label="基本信息" name="basic">
                    <el-form ref="roleFormRef" :model="roleForm" :rules="roleFormRules" label-width="100px">
                        <el-form-item label="角色名称" prop="name">
                            <el-input v-model="roleForm.name" placeholder="请输入角色名称" />
                        </el-form-item>
                        <el-form-item label="角色标题" prop="title">
                            <el-input v-model="roleForm.title" placeholder="请输入角色标题" />
                        </el-form-item>
                        <el-form-item label="状态" prop="status">
                            <el-radio-group v-model="roleForm.status">
                                <el-radio :value="1">正常</el-radio>
                                <el-radio :value="0">禁用</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="数据范围" prop="view_scope">
                            <el-select v-model="roleForm.view_scope" placeholder="请选择数据范围" style="width: 100%">
                                <el-option value="all" label="全部数据" />
                                <el-option value="dept" label="本部门数据" />
                                <el-option value="self" label="仅本人数据" />
                            </el-select>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>

                <el-tab-pane label="菜单权限" name="menus">
                    <el-tree
                        ref="menuTreeRef"
                        :data="allMenus"
                        :props="{ label: 'title', children: 'children' }"
                        node-key="id"
                        :check-strictly="false"
                        show-checkbox
                        default-expand-all
                        style="background: #f5f7fa; padding: 8px; max-height: 400px; overflow-y: auto"
                    />
                </el-tab-pane>

                <el-tab-pane label="API权限" name="rules">
                    <el-tree
                        ref="rulesTreeRef"
                        :data="allRules"
                        :props="{ label: 'title', children: 'children' }"
                        node-key="id"
                        :check-strictly="false"
                        show-checkbox
                        default-expand-all
                        style="background: #f5f7fa; padding: 8px; max-height: 400px; overflow-y: auto"
                    />
                </el-tab-pane>
            </el-tabs>
            <template #footer>
                <el-button @click="roleDialogVisible = false">取消</el-button>
                <el-button type="primary" @click="handleSaveRole">确定</el-button>
            </template>
        </el-dialog>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { getAuthRules, saveAuthRule, deleteAuthRule, getMenus, saveAuthRole, deleteRole } from '@/api/system'
import { getRoles } from '@/api/user'

const activeTab = ref('rules')

// ============================================================
// 权限规则相关
// ============================================================
const ruleLoading = ref(false)
const ruleList = ref([])
const ruleTreeData = ref([])
const ruleDialogVisible = ref(false)
const ruleFormRef = ref(null)

const ruleForm = reactive({
    id: null,
    pid: null,
    name: '',
    title: '',
    type: 1,
    status: 1,
    sort: 0,
})

const ruleFormRules = {
    name: [{ required: true, message: '请输入规则路由', trigger: 'blur' }],
    title: [{ required: true, message: '请输入规则标题', trigger: 'blur' }],
    type: [{ required: true, message: '请选择类型', trigger: 'change' }],
    status: [{ required: true, message: '请选择状态', trigger: 'change' }],
}

async function loadRules() {
    ruleLoading.value = true
    try {
        const res = await getAuthRules()
        ruleList.value = res || []
        ruleTreeData.value = res || []
    } catch {
        ElMessage.error('加载权限规则失败')
    } finally {
        ruleLoading.value = false
    }
}

function handleAddRule(parentRow) {
    ruleForm.id = null
    ruleForm.pid = parentRow?.id || null
    ruleForm.name = ''
    ruleForm.title = ''
    ruleForm.type = 1
    ruleForm.status = 1
    ruleForm.sort = 0
    ruleDialogVisible.value = true
}

function handleEditRule(row) {
    ruleForm.id = row.id
    ruleForm.pid = row.pid || null
    ruleForm.name = row.name
    ruleForm.title = row.title || ''
    ruleForm.type = row.type ?? 1
    ruleForm.status = row.status ?? 1
    ruleForm.sort = row.sort ?? 0
    ruleDialogVisible.value = true
}

async function handleSaveRule() {
    try {
        await ruleFormRef.value.validate()
        await saveAuthRule({ ...ruleForm })
        ElMessage.success(ruleForm.id ? '更新成功' : '新增成功')
        ruleDialogVisible.value = false
        loadRules()
    } catch {}
}

async function handleDeleteRule(row) {
    try {
        await ElMessageBox.confirm(`确定要删除规则 "${row.name}" 吗？`, '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning',
        })
        await deleteAuthRule({ id: row.id })
        ElMessage.success('删除成功')
        loadRules()
    } catch {}
}

function ruleDialogClose() {
    ruleFormRef.value?.resetFields()
}

// ============================================================
// 角色管理相关
// ============================================================
const roleLoading = ref(false)
const roleList = ref([])
const allMenus = ref([])
const allRules = ref([])
const roleDialogVisible = ref(false)
const roleActiveTab = ref('basic')
const roleFormRef = ref(null)
const menuTreeRef = ref(null)
const rulesTreeRef = ref(null)

const roleForm = reactive({
    id: null,
    name: '',
    title: '',
    status: 1,
    view_scope: 'self',
    menu_id: [],
    rules_id: [],
})

const roleFormRules = {
    name: [{ required: true, message: '请输入角色名称', trigger: 'blur' }],
    status: [{ required: true, message: '请选择状态', trigger: 'change' }],
    view_scope: [{ required: true, message: '请选择数据范围', trigger: 'change' }],
}

async function loadRoles() {
    roleLoading.value = true
    try {
        const [rolesRes, menusRes, rulesRes] = await Promise.all([getRoles(), getMenus(), getAuthRules()])
        roleList.value = rolesRes || []
        allMenus.value = menusRes || []
        allRules.value = rulesRes || []
    } catch {
        ElMessage.error('加载角色数据失败')
    } finally {
        roleLoading.value = false
    }
}

function handleAddRole() {
    roleActiveTab.value = 'basic'
    roleForm.id = null
    roleForm.name = ''
    roleForm.title = ''
    roleForm.status = 1
    roleForm.view_scope = 'self'
    roleForm.menu_id = []
    roleForm.rules_id = []
    roleDialogVisible.value = true
    setTimeout(() => {
        menuTreeRef.value?.setCheckedKeys([])
        rulesTreeRef.value?.setCheckedKeys([])
    }, 0)
}

function handleEditRole(row) {
    roleActiveTab.value = 'basic'
    roleForm.id = row.id
    roleForm.name = row.name || ''
    roleForm.title = row.title || ''
    roleForm.status = row.status ?? 1
    roleForm.view_scope = row.view_scope || 'self'
    const rulesIdArr = row.rules_id
        ? (row.rules_id + '').split(',').map((v) => Number(v)).filter(Boolean)
        : []
    roleForm.rules_id = rulesIdArr

    roleDialogVisible.value = true
    setTimeout(() => {
        const menuIds = row.menus?.map((m) => m.id) || []
        menuTreeRef.value?.setCheckedKeys(menuIds)
        rulesTreeRef.value?.setCheckedKeys(rulesIdArr)
    }, 0)
}

async function handleSaveRole() {
    try {
        await roleFormRef.value.validate()
        const menuIds = menuTreeRef.value?.getCheckedKeys() || []
        const rulesIds = rulesTreeRef.value?.getCheckedKeys() || []
        const data = {
            id: roleForm.id || undefined,
            name: roleForm.name,
            title: roleForm.title,
            status: roleForm.status,
            view_scope: roleForm.view_scope,
            menu_id: menuIds,
            rules_id: rulesIds,
        }
        await saveAuthRole(data)
        ElMessage.success(roleForm.id ? '更新成功' : '新增成功')
        roleDialogVisible.value = false
        loadRoles()
    } catch {}
}

async function handleDeleteRole(row) {
    try {
        await ElMessageBox.confirm(`确定要删除角色 "${row.name}" 吗？`, '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning',
        })
        await deleteRole({ id: row.id })
        ElMessage.success('删除成功')
        loadRoles()
    } catch {}
}

function roleDialogClose() {
    roleFormRef.value?.resetFields()
    menuTreeRef.value?.setCheckedKeys([])
    rulesTreeRef.value?.setCheckedKeys([])
}

function splitRules(rulesId) {
    if (!rulesId) return []
    return (rulesId + '').split(',').filter(Boolean)
}

function formatViewScope(scope) {
    const map = { all: '全部数据', dept: '本部门', self: '仅本人' }
    return map[scope] || scope || '-'
}

onMounted(() => {
    loadRules()
    loadRoles()
})
</script>

<style lang="scss" scoped>
.auth-container {
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .auth-tabs {
        :deep(.el-tabs__header) {
            margin-bottom: 16px;
        }
    }

    .tab-toolbar {
        margin-bottom: 12px;
    }
}
</style>
