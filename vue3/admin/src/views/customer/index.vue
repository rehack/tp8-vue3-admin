<template>
    <div class="customer-container">
        <el-breadcrumb separator="/">
            <el-breadcrumb-item :to="{ path: '/dashboard' }">首页</el-breadcrumb-item>
            <el-breadcrumb-item>客户管理</el-breadcrumb-item>
        </el-breadcrumb>
        <div class="menu-cards">
            <el-card class="menu-card" @click="$router.push('/customer/custpool')">
                <el-icon :size="48" color="#409EFF"><User /></el-icon>
                <p class="card-title">总客户池</p>
                <p class="card-desc">查看所有客户信息</p>
            </el-card>
            <el-card class="menu-card" @click="$router.push('/customer/arrival')">
                <el-icon :size="48" color="#67C23A"><Document /></el-icon>
                <p class="card-title">到院明细</p>
                <p class="card-desc">客户到院记录</p>
            </el-card>
            <el-card class="menu-card" @click="$router.push('/customer/consultation')">
                <el-icon :size="48" color="#E6A23C"><Phone /></el-icon>
                <p class="card-title">咨询详情</p>
                <p class="card-desc">客户咨询记录</p>
            </el-card>
        </div>
    </div>
</template>

<script setup>
import { User, Document, Phone } from '@element-plus/icons-vue'
</script>

<style lang="scss" scoped>
.customer-container {
    padding: 16px;

    .menu-cards {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 20px;

        .menu-card {
            width: 280px;
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
