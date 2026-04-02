<template>
    <el-dialog
        :model-value="visible"
        :title="userData?.id ? '编辑用户' : '新增用户'"
        width="600px"
        @close="handleClose"
    >
        <el-form ref="formRef" :model="form" :rules="rules" label-width="100px">
            <el-form-item label="用户名" prop="username">
                <el-input
                    v-model="form.username"
                    placeholder="请输入用户名"
                    :disabled="!!userData?.id"
                />
            </el-form-item>
            <el-form-item label="密码" :prop="userData?.id ? '' : 'password'">
                <el-input
                    v-model="form.password"
                    type="password"
                    show-password
                    :placeholder="userData?.id ? '留空则不修改密码' : '请输入密码'"
                />
            </el-form-item>
            <el-form-item label="姓名" prop="realname">
                <el-input v-model="form.realname" placeholder="请输入姓名" />
            </el-form-item>
            <el-form-item label="手机号" prop="phone">
                <el-input v-model="form.phone" placeholder="请输入手机号" />
            </el-form-item>
            <el-form-item label="性别" prop="sex">
                <el-radio-group v-model="form.sex">
                    <el-radio :value="1">男</el-radio>
                    <el-radio :value="2">女</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item label="部门" prop="staff_dept">
                <el-cascader
                    v-model="form.staff_dept"
                    :options="deptTree"
                    :props="{ checkStrictly: true, value: 'id', label: 'department_name' }"
                    placeholder="请选择部门"
                    clearable
                    style="width: 100%"
                />
            </el-form-item>
            <el-form-item label="角色" prop="roles">
                <el-select
                    v-model="form.roles"
                    multiple
                    placeholder="请选择角色"
                    style="width: 100%"
                >
                    <el-option
                        v-for="role in roleList"
                        :key="role.id"
                        :label="role.name"
                        :value="role.id"
                    />
                </el-select>
            </el-form-item>
            <el-form-item label="状态" prop="status">
                <el-radio-group v-model="form.status">
                    <el-radio :value="1">正常</el-radio>
                    <el-radio :value="0">禁用</el-radio>
                </el-radio-group>
            </el-form-item>
        </el-form>
        <template #footer>
            <el-button @click="handleClose">取消</el-button>
            <el-button type="primary" @click="handleSubmit">确定</el-button>
        </template>
    </el-dialog>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { addStaff, updateStaff, getRoles, getDeptTree } from '../../../api/user'

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    userData: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['update:visible', 'success'])

const formRef = ref(null)
const deptTree = ref([])
const roleList = ref([])

const form = reactive({
    username: '',
    password: '',
    realname: '',
    phone: '',
    sex: 1,
    staff_dept: [],
    roles: [],
    status: 1,
})

const rules = {
    username: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
    realname: [{ required: true, message: '请输入姓名', trigger: 'blur' }],
    roles: [{ required: true, message: '请选择角色', trigger: 'change' }],
}

watch(
    () => props.visible,
    (val) => {
        if (val) {
            loadRoles()
            loadDeptTree()
            if (props.userData) {
                Object.assign(form, {
                    ...props.userData,
                    password: '',
                    staff_dept: props.userData.dept_id ? [props.userData.dept_id] : [],
                    roles: props.userData.roles?.map((r) => r.id) || [],
                })
            }
        }
    },
)

async function loadRoles() {
    try {
        const res = await getRoles()
        roleList.value = res || []
    } catch {}
}

async function loadDeptTree() {
    try {
        const res = await getDeptTree()
        deptTree.value = res || []
    } catch {}
}

async function handleSubmit() {
    try {
        await formRef.value.validate()
        const data = {
            ...form,
            dept_id: form.staff_dept?.[form.staff_dept.length - 1],
        }
        delete data.staff_dept

        if (props.userData?.id) {
            data.id = props.userData.id
            await updateStaff(data)
            ElMessage.success('更新成功')
        } else {
            await addStaff(data)
            ElMessage.success('新增成功')
        }
        emit('success')
        handleClose()
    } catch {}
}

function handleClose() {
    formRef.value?.resetFields()
    emit('update:visible', false)
}
</script>
