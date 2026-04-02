<template>
    <div class="dashboard-container">
        <el-row :gutter="20">
            <el-col :span="6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #409eff">
                        <el-icon :size="32"><User /></el-icon>
                    </div>
                    <div class="stat-info">
                        <p class="stat-value">{{ stats.userCount }}</p>
                        <p class="stat-label">用户总数</p>
                    </div>
                </div>
            </el-col>
            <el-col :span="6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #67c23a">
                        <el-icon :size="32"><Document /></el-icon>
                    </div>
                    <div class="stat-info">
                        <p class="stat-value">{{ stats.orderCount }}</p>
                        <p class="stat-label">订单数量</p>
                    </div>
                </div>
            </el-col>
            <el-col :span="6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e6a23c">
                        <el-icon :size="32"><Goods /></el-icon>
                    </div>
                    <div class="stat-info">
                        <p class="stat-value">{{ stats.productCount }}</p>
                        <p class="stat-label">商品数量</p>
                    </div>
                </div>
            </el-col>
            <el-col :span="6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f56c6c">
                        <el-icon :size="32"><Warning /></el-icon>
                    </div>
                    <div class="stat-info">
                        <p class="stat-value">{{ stats.pendingCount }}</p>
                        <p class="stat-label">待处理</p>
                    </div>
                </div>
            </el-col>
        </el-row>

        <el-row :gutter="20" style="margin-top: 20px">
            <el-col :span="16">
                <el-card class="chart-card">
                    <template #header>
                        <span>数据概览</span>
                    </template>
                    <div class="chart-placeholder">
                        <p>图表区域</p>
                    </div>
                </el-card>
            </el-col>
            <el-col :span="8">
                <el-card class="chart-card">
                    <template #header>
                        <span>快捷操作</span>
                    </template>
                    <div class="quick-actions">
                        <el-button type="primary" plain>用户管理</el-button>
                        <el-button type="success" plain>订单管理</el-button>
                        <el-button type="warning" plain>商品管理</el-button>
                        <el-button type="danger" plain>系统设置</el-button>
                    </div>
                </el-card>
            </el-col>
        </el-row>

        <el-row :gutter="20" style="margin-top: 20px">
            <el-col :span="24">
                <el-card>
                    <template #header>
                        <span>最近操作日志</span>
                    </template>
                    <el-table :data="recentLogs" style="width: 100%">
                        <el-table-column prop="user" label="操作人" width="120" />
                        <el-table-column prop="action" label="操作内容" />
                        <el-table-column prop="time" label="操作时间" width="180" />
                        <el-table-column prop="ip" label="IP地址" width="150" />
                    </el-table>
                </el-card>
            </el-col>
        </el-row>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { User, Document, Goods, Warning } from '@element-plus/icons-vue'

const stats = reactive({
    userCount: 128,
    orderCount: 456,
    productCount: 1024,
    pendingCount: 32,
})

const recentLogs = ref([
    { user: '管理员', action: '登录系统', time: '2026-03-31 10:30:00', ip: '192.168.1.1' },
    { user: '管理员', action: '新增用户：张三', time: '2026-03-31 09:20:00', ip: '192.168.1.1' },
    { user: '管理员', action: '修改角色权限', time: '2026-03-30 18:45:00', ip: '192.168.1.2' },
])
</script>

<style lang="scss" scoped>
.dashboard-container {
    padding: 20px;

    .stat-card {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            margin-right: 16px;
        }

        .stat-info {
            .stat-value {
                font-size: 28px;
                font-weight: bold;
                color: #333;
                margin: 0;
            }

            .stat-label {
                font-size: 14px;
                color: #999;
                margin: 4px 0 0 0;
            }
        }
    }

    .chart-card {
        .chart-placeholder {
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            background: #f5f7fa;
            border-radius: 4px;
        }

        .quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;

            .el-button {
                margin: 0;
            }
        }
    }
}
</style>
