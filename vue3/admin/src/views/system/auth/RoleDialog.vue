<template>
    <el-dialog
        :model-value="visible"
        :title="form.id ? '编辑角色' : '新增角色'"
        width="800px"
        @close="handleClose"
    >
        <el-tabs v-model="activeTab">
            <el-tab-pane label="基本信息" name="basic">
                <el-form ref="formRef" :model="form" :rules="rules" label-width="100px">
                    <el-form-item label="角色名称" prop="name">
                        <el-input v-model="form.name" placeholder="请输入角色名称" />
                    </el-form-item>
                    <el-form-item label="状态" prop="status">
                        <el-radio-group v-model="form.status">
                            <el-radio :value="1">正常</el-radio>
                            <el-radio :value="0">禁用</el-radio>
                        </el-radio-group>
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
                    :props="{ label: 'name', children: 'children' }"
                    node-key="id"
                    :check-strictly="false"
                    show-checkbox
                    default-expand-all
                    style="background: #f5f7fa; padding: 8px; max-height: 400px; overflow-y: auto"
                />
            </el-tab-pane>

            <el-tab-pane label="数据范围" name="scope">
                <el-form label-width="120px">
                    <el-form-item label="视图范围">
                        <el-input-number v-model="form.view_scope" :min="0" />
                        <div style="color: #909399; font-size: 12px; margin-top: 4px">
                            0 或留空表示无限制，> 0 表示可访问的数据范围级别
                        </div>
                    </el-form-item>
                </el-form>
            </el-tab-pane>
        </el-tabs>

        <template #footer>
            <el-button @click="handleClose">取消</el-button>
            <el-button type="primary" @click="handleSubmit">确定</el-button>
        </template>
    </el-dialog>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { ElMessage } from 'element-plus'
import { saveAuthRole } from '../../../api/system'

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    roleData: {
        type: Object,
        default: null,
    },
    allMenus: {
        type: Array,
        default: () => [],
    },
    allRules: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['update:visible', 'success'])

const activeTab = ref('basic')
const formRef = ref(null)
const menuTreeRef = ref(null)
const rulesTreeRef = ref(null)

const form = reactive({
    id: null,
    name: '',
    status: 1,
    menu_id: [],
    rules_id: [],
    view_scope: 0,
})

const rules = {
    name: [{ required: true, message: '请输入角色名称', trigger: 'blur' }],
    status: [{ required: true, message: '请选择状态', trigger: 'change' }],
}

watch(
    () => props.visible,
    (val) => {
        if (val) {
            activeTab.value = 'basic'
            if (props.roleData) {
                form.id = props.roleData.id || null
                form.name = props.roleData.name || ''
                form.status = props.roleData.status ?? 1
                form.view_scope = props.roleData.view_scope ?? 0
                form.rules_id = Array.isArray(props.roleData.rules_id)
                    ? props.roleData.rules_id
                    : []

                // set menu tree
                setTimeout(() => {
                    if (menuTreeRef.value && props.roleData.menu_id?.length) {
                        menuTreeRef.value.setCheckedKeys(props.roleData.menu_id)
                    } else if (menuTreeRef.value) {
                        menuTreeRef.value.setCheckedKeys([])
                    }
                    // set rules tree
                    if (rulesTreeRef.value && form.rules_id.length) {
                        rulesTreeRef.value.setCheckedKeys(form.rules_id)
                    } else if (rulesTreeRef.value) {
                        rulesTreeRef.value.setCheckedKeys([])
                    }
                }, 0)
            } else {
                // reset for new role
                form.id = null
                form.name = ''
                form.status = 1
                form.view_scope = 0
                form.rules_id = []
                setTimeout(() => {
                    menuTreeRef.value?.setCheckedKeys([])
                    rulesTreeRef.value?.setCheckedKeys([])
                }, 0)
            }
        }
    },
)

async function handleSubmit() {
    try {
        await formRef.value.validate()
        const menuIds = menuTreeRef.value?.getCheckedKeys() || []
        const rulesIds = rulesTreeRef.value?.getCheckedKeys() || []
        const data = {
            id: form.id || undefined,
            name: form.name,
            status: form.status,
            view_scope: form.view_scope,
            menu_id: menuIds,
            rules_id: rulesIds,
        }
        await saveAuthRole(data)
        ElMessage.success(form.id ? '更新成功' : '新增成功')
        emit('success')
        handleClose()
    } catch {}
}

function handleClose() {
    formRef.value?.resetFields()
    menuTreeRef.value?.setCheckedKeys([])
    rulesTreeRef.value?.setCheckedKeys([])
    activeTab.value = 'basic'
    emit('update:visible', false)
}
</script>
