<template>
    <div class="system-container">
        <el-breadcrumb separator="/">
            <el-breadcrumb-item :to="{ path: '/dashboard' }">首页</el-breadcrumb-item>
            <el-breadcrumb-item>系统管理</el-breadcrumb-item>
        </el-breadcrumb>
        <div class="system-content">
            <el-row :gutter="20">
                <el-col :span="6">
                    <el-card class="menu-card" @click="goTo('/system/user')">
                        <el-icon :size="48" color="#409EFF"><User /></el-icon>
                        <p class="card-title">用户管理</p>
                        <p class="card-desc">管理系统用户账号</p>
                    </el-card>
                </el-col>
                <el-col :span="6">
                    <el-card class="menu-card" @click="goTo('/system/role')">
                        <el-icon :size="48" color="#67C23A"><Key /></el-icon>
                        <p class="card-title">角色权限</p>
                        <p class="card-desc">管理角色与菜单权限</p>
                    </el-card>
                </el-col>
                <el-col :span="6">
                    <el-card class="menu-card" @click="goTo('/system/log')">
                        <el-icon :size="48" color="#E6A23C"><Document /></el-icon>
                        <p class="card-title">系统日志</p>
                        <p class="card-desc">操作日志与登录日志</p>
                    </el-card>
                </el-col>
                <el-col :span="6">
                    <el-card class="menu-card" @click="goTo('/system/setting')">
                        <el-icon :size="48" color="#F56C6C"><Setting /></el-icon>
                        <p class="card-title">系统设置</p>
                        <p class="card-desc">系统配置管理</p>
                    </el-card>
                </el-col>
            </el-row>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { User, Key, Document, Setting } from '@element-plus/icons-vue'

const router = useRouter()

function goTo(path) {
    router.push(path)
}
</script>

<style lang="scss" scoped>
.system-container {
    padding: 16px;

    .system-content {
        margin-top: 20px;

        .menu-card {
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;

            &:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            }

            .card-title {
                font-size: 18px;
                font-weight: bold;
                color: #333;
                margin: 16px 0 8px 0;
            }

            .card-desc {
                font-size: 14px;
                color: #999;
                margin: 0;
            }
        }
    }
}
</style>
