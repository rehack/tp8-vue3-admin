<template>
    <div class="header-container">
        <div class="header-left">
            <el-icon class="collapse-btn" @click="layoutStore.toggleSidebar">
                <Fold v-if="!layoutStore.sidebarCollapse" />
                <Expand v-else />
            </el-icon>
            <breadcrumb />
        </div>
        <div class="header-right">
            <el-dropdown @command="handleCommand">
                <span class="user-dropdown">
                    <el-avatar :size="32" style="margin-right: 8px">
                        {{ userStore.userInfo?.realname?.[0] || 'U' }}
                    </el-avatar>
                    <span>{{ userStore.userInfo?.realname || '用户' }}</span>
                    <el-icon style="margin-left: 4px"><ArrowDown /></el-icon>
                </span>
                <template #dropdown>
                    <el-dropdown-menu>
                        <el-dropdown-item command="profile">个人中心</el-dropdown-item>
                        <el-dropdown-item command="resetPwd">修改密码</el-dropdown-item>
                        <el-dropdown-item divided command="logout">退出登录</el-dropdown-item>
                    </el-dropdown-menu>
                </template>
            </el-dropdown>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { ElMessageBox } from 'element-plus'
import { Fold, Expand, ArrowDown } from '@element-plus/icons-vue'
import { useUserStore } from '../../stores/user'
import { useLayoutStore } from '../../stores/layout'
import Breadcrumb from './Breadcrumb.vue'

const router = useRouter()
const userStore = useUserStore()
const layoutStore = useLayoutStore()

async function handleCommand(command) {
    switch (command) {
        case 'logout':
            try {
                await ElMessageBox.confirm('确定要退出登录吗？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                })
                userStore.logout()
                router.push('/login')
            } catch {}
            break
        case 'profile':
            router.push('/profile')
            break
        case 'resetPwd':
            router.push('/reset-password')
            break
    }
}
</script>

<style lang="scss" scoped>
.header-container {
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 16px;
    background: #fff;
    border-bottom: 1px solid #e6e6e6;

    .header-left {
        display: flex;
        align-items: center;

        .collapse-btn {
            font-size: 20px;
            cursor: pointer;
            color: #606266;
            transition: color 0.2s;
            margin-right: 16px;

            &:hover {
                color: #409eff;
            }
        }
    }

    .header-right {
        .user-dropdown {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
    }
}
</style>
