<template>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h2>管理后台</h2>
            </div>
            <el-form
                ref="loginFormRef"
                :model="loginForm"
                :rules="loginRules"
                class="login-form"
                @keyup.enter="handleLogin"
            >
                <el-form-item prop="username">
                    <el-input
                        v-model="loginForm.username"
                        placeholder="请输入用户名"
                        :prefix-icon="User"
                        size="large"
                    />
                </el-form-item>
                <el-form-item prop="password">
                    <el-input
                        v-model="loginForm.password"
                        type="password"
                        placeholder="请输入密码"
                        :prefix-icon="Lock"
                        size="large"
                        show-password
                    />
                </el-form-item>
                <el-form-item>
                    <el-button
                        type="primary"
                        size="large"
                        :loading="loading"
                        class="login-button"
                        style="width: 100%"
                        @click="handleLogin"
                    >
                        {{ loading ? '登录中...' : '登录' }}
                    </el-button>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElNotification } from 'element-plus'
import { User, Lock } from '@element-plus/icons-vue'
import request from '../../request'
import { useUserStore } from '../../stores/user'

const router = useRouter()
const userStore = useUserStore()

const loginFormRef = ref(null)
const loading = ref(false)

const loginForm = reactive({
    username: '',
    password: '',
    login_env: 'PC',
})

const loginRules = {
    username: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
    password: [{ required: true, message: '请输入密码', trigger: 'blur' }],
}

async function handleLogin() {
    if (!loginFormRef.value) return

    try {
        await loginFormRef.value.validate()
    } catch {
        return
    }

    loading.value = true

    try {
        // 1. 调用登录接口获取 token
        const loginRes = await request({
            url: '/gettoken/user',
            method: 'POST',
            data: loginForm,
        })

        if (!loginRes || !loginRes.token) {
            throw new Error('登录失败：服务器返回数据格式错误')
        }

        // 2. 保存 token
        userStore.setToken(loginRes.token)

        // 3. 获取用户信息
        const userInfoRes = await request({
            url: '/commom/sysuserinfo',
            method: 'GET',
        })

        console.log('用户信息:', userInfoRes)

        // 4. 保存用户信息和菜单
        userStore.setUserInfo(userInfoRes)

        ElNotification.success({
            title: '登录成功',
            message: '正在跳转...',
        })

        // 5. 跳转到首页
        await router.replace('/dashboard')
    } catch (error) {
        console.error('登录失败:', error)
        ElMessage.error(error.message || '登录失败，请检查用户名和密码')
        localStorage.removeItem('token')
        localStorage.removeItem('userInfo')
    } finally {
        loading.value = false
    }
}
</script>

<style lang="scss" scoped>
.login-container {
    min-height: 100vh;
    width: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;

    .login-box {
        width: 400px;
        padding: 40px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);

        .login-header {
            text-align: center;
            margin-bottom: 32px;

            h2 {
                font-size: 28px;
                color: #333;
                margin: 0;
            }
        }
    }
}
</style>
