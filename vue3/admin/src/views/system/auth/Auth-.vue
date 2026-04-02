<template>
    <div class="auth-container">
        <el-card>
            <template #header>
                <div class="card-header">
                    <span>权限规则管理</span>
                    <el-button type="primary" @click="handleAdd(null)">
                        <el-icon><Plus /></el-icon>新增规则
                    </el-button>
                </div>
            </template>

            <el-table
                v-loading="loading"
                :data="ruleList"
                row-key="id"
                :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
                default-expand-all
                stripe
                style="width: 100%"
            >
                <el-table-column prop="name" label="规则名称" min-width="300" />
                <el-table-column prop="type" label="类型" width="100">
                    <template #default="{ row }">
                        <el-tag :type="row.type === 1 ? 'success' : 'warning'" size="small">
                            {{ row.type === 1 ? '菜单' : 'API' }}
                        </el-tag>
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
                <el-table-column label="操作" width="200" fixed="right">
                    <template #default="{ row }">
                        <el-button link type="primary" @click="handleAdd(row)">新增子规则</el-button>
                        <el-button link type="primary" @click="handleEdit(row)">编辑</el-button>
                        <el-button link type="danger" @click="handleDelete(row)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </el-card>

        <!-- 新增/编辑弹窗 -->
        <el-dialog
            v-model="dialogVisible"
            :title="formData.id ? '编辑规则' : '新增规则'"
            width="500px"
            @close="handleClose"
        >
            <el-form ref="formRef" :model="formData" :rules="rules" label-width="100px">
                <el-form-item label="上级规则" prop="pid">
                    <el-tree-select
                        v-model="formData.pid"
                        :data="ruleTreeData"
                        :props="{ label: 'name', value: 'id', children: 'children' }"
                        check-strictly
                        placeholder="请选择上级规则（不选则为顶级）"
                        clearable
                        style="width: 100%"
                    />
                </el-form-item>
                <el-form-item label="规则名称" prop="name">
                    <el-input v-model="formData.name" placeholder="如：bll.System/getStaff" />
                </el-form-item>
                <el-form-item label="类型" prop="type">
                    <el-radio-group v-model="formData.type">
                        <el-radio :value="1">菜单</el-radio>
                        <el-radio :value="2">API</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="状态" prop="status">
                    <el-radio-group v-model="formData.status">
                        <el-radio :value="1">正常</el-radio>
                        <el-radio :value="0">禁用</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="排序" prop="sort">
                    <el-input-number v-model="formData.sort" :min="0" />
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="handleClose">取消</el-button>
                <el-button type="primary" @click="handleSubmit">确定</el-button>
            </template>
        </el-dialog>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { getAuthRules, saveAuthRule, deleteAuthRule } from '@/api/system'

const loading = ref(false)
const ruleList = ref([])
const dialogVisible = ref(false)
const formRef = ref(null)

const formData = reactive({
    id: null,
    pid: null,
    name: '',
    type: 1,
    status: 1,
    sort: 0,
})

const rules = {
    name: [{ required: true, message: '请输入规则名称', trigger: 'blur' }],
    type: [{ required: true, message: '请选择类型', trigger: 'change' }],
    status: [{ required: true, message: '请选择状态', trigger: 'change' }],
}

const ruleTreeData = ref([])

async function loadData() {
    loading.value = true
    try {
        const res = await getAuthRules()
        ruleList.value = res || []
        ruleTreeData.value = res || []
    } catch (e) {
        ElMessage.error('加载权限规则失败')
    } finally {
        loading.value = false
    }
}

function handleAdd(parentRow) {
    formData.id = null
    formData.pid = parentRow?.id || null
    formData.name = ''
    formData.type = 1
    formData.status = 1
    formData.sort = 0
    dialogVisible.value = true
}

function handleEdit(row) {
    formData.id = row.id
    formData.pid = row.pid || null
    formData.name = row.name
    formData.type = row.type
    formData.status = row.status
    formData.sort = row.sort ?? 0
    dialogVisible.value = true
}

async function handleSubmit() {
    try {
        await formRef.value.validate()
        await saveAuthRule(formData)
        ElMessage.success(formData.id ? '更新成功' : '新增成功')
        handleClose()
        loadData()
    } catch (e) {
        // error already shown by interceptor
    }
}

async function handleDelete(row) {
    try {
        await ElMessageBox.confirm(`确定要删除规则 "${row.name}" 吗？`, '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning',
        })
        await deleteAuthRule({ id: row.id })
        ElMessage.success('删除成功')
        loadData()
    } catch (e) {
        // cancelled or error
    }
}

function handleClose() {
    formRef.value?.resetFields()
    dialogVisible.value = false
}

onMounted(() => {
    loadData()
})
</script>

<style lang="scss" scoped>
.auth-container {
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
}
</style>