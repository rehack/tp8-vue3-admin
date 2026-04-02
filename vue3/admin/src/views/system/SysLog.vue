<template>
    <div class="log-container">
        <el-tabs v-model="activeTab" @tab-change="handleTabChange">
            <el-tab-pane label="操作日志" name="oplog">
                <el-card>
                    <div class="search-bar">
                        <el-date-picker
                            v-model="oplogParams.date"
                            type="datetimerange"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期"
                            value-format="YYYY-MM-DD HH:mm:ss"
                            style="margin-right: 16px;max-width: 20%;"
                        />
                        <el-input
                            v-model="oplogParams.fuzzy_keyword"
                            placeholder="搜索操作内容"
                            style="width: 300px"
                            clearable
                            @keyup.enter="loadOplog"
                        >
                            <template #append>
                                <el-button icon="Search" @click="loadOplog" />
                            </template>
                        </el-input>
                    </div>

                    <el-table
                        v-loading="oplogLoading"
                        :data="oplogList"
                        stripe
                        style="width: 100%; margin-top: 16px"
                    >
                        <el-table-column prop="id" label="ID" width="80" />
                        <el-table-column prop="user" label="操作人" width="120">
                            <template #default="{ row }">
                                {{ row.user?.realname || row.user_id }}
                            </template>
                        </el-table-column>
                        <el-table-column prop="apirule" label="操作接口" min-width="200">
                            <template #default="{ row }">
                                {{ row.apirule?.name || '-' }}
                            </template>
                        </el-table-column>
                        <el-table-column
                            prop="param"
                            label="请求参数"
                            min-width="200"
                            show-overflow-tooltip
                        />
                        <el-table-column prop="create_time" label="操作时间" width="180">
                            <template #default="{ row }">
                                {{ formatDate(row.create_time) }}
                            </template>
                        </el-table-column>
                    </el-table>

                    <el-pagination
                        background
                        v-model:current-page="oplogPagination.currentPage"
                        v-model:page-size="oplogPagination.pageSize"
                        :total="oplogPagination.total"
                        :page-sizes="[10, 20, 50, 100]"
                        layout="total, sizes, prev, pager, next, jumper"
                        style="margin-top: 16px; justify-content: flex-end"
                        @update:current-page="loadOplog"
                        @update:page-size="loadOplog"
                    />
                </el-card>
            </el-tab-pane>

            <el-tab-pane label="登录日志" name="loginlog">
                <el-card>
                    <div class="search-bar">
                        <el-date-picker
                            v-model="loginlogParams.date"
                            type="datetimerange"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期"
                            value-format="YYYY-MM-DD HH:mm:ss"
                            style="margin-right: 16px;max-width: 20%;"
                        />
                        <el-input
                            v-model="loginlogParams.fuzzy_keyword"
                            placeholder="搜索登录信息"
                            style="width: 300px"
                            clearable
                            @keyup.enter="loadLoginlog"
                        >
                            <template #append>
                                <el-button icon="Search" @click="loadLoginlog" />
                            </template>
                        </el-input>
                        
                    </div>

                    <el-table
                        v-loading="loginlogLoading"
                        :data="loginlogList"
                        stripe
                        style="width: 100%; margin-top: 16px"
                    >
                        <el-table-column prop="id" label="ID" width="80" />
                        <el-table-column prop="user" label="登录人" width="120">
                            <template #default="{ row }">
                                {{ row.user?.realname || row.user_id }}
                            </template>
                        </el-table-column>
                        <el-table-column prop="login_ip" label="登录IP" width="150" />
                        <el-table-column
                            prop="url"
                            label="登录URL"
                            min-width="200"
                            show-overflow-tooltip
                        />
                        <el-table-column prop="login_time" label="登录时间" width="180">
                            <template #default="{ row }">
                                {{ formatDate(row.login_time) }}
                            </template>
                        </el-table-column>
                    </el-table>

                    <el-pagination
                        background
                        v-model:current-page="loginlogPagination.currentPage"
                        v-model:page-size="loginlogPagination.pageSize"
                        :total="loginlogPagination.total"
                        :page-sizes="[50, 100, 300, 1000]"
                        layout="total, sizes, prev, pager, next, jumper"
                        style="margin-top: 16px; justify-content: flex-end"
                        @update:current-page="loadLoginlog"
                        @update:page-size="loadLoginlog"
                    />
                </el-card>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getOplog, getLoginlog } from '../../api/system'
import { formatDate } from '../../utils'

const activeTab = ref('oplog')

const oplogLoading = ref(false)
const oplogList = ref([])
const oplogParams = reactive({
    fuzzy_keyword: '',
    date: null,
    uid: '',
})
const oplogPagination = reactive({
    currentPage: 1,
    pageSize: 50,
    total: 0,
})

const loginlogLoading = ref(false)
const loginlogList = ref([])
const loginlogParams = reactive({
    fuzzy_keyword: '',
    date: null,
    uid: '',
})
const loginlogPagination = reactive({
    currentPage: 1,
    pageSize: 50,
    total: 0,
})

async function loadOplog() {
    oplogLoading.value = true
    try {
        const res = await getOplog({
            fuzzy_keyword: oplogParams.fuzzy_keyword,
            pageSize: oplogPagination.pageSize,
            currentPage: oplogPagination.currentPage,
            date: oplogParams.date,
            uid: oplogParams.uid,
        })
        oplogList.value = res?.data ?? []
        oplogPagination.total = Number(res?.total) || 0
    } catch {
        ElMessage.error('加载操作日志失败')
    } finally {
        oplogLoading.value = false
    }
}

async function loadLoginlog() {
    loginlogLoading.value = true
    try {
        const res = await getLoginlog({
            fuzzy_keyword: loginlogParams.fuzzy_keyword,
            pageSize: loginlogPagination.pageSize,
            currentPage: loginlogPagination.currentPage,
            date: loginlogParams.date,
            uid: loginlogParams.uid,
        })
        loginlogList.value = res?.data ?? []
        loginlogPagination.total = Number(res?.total) || 0
    } catch {
        ElMessage.error('加载登录日志失败')
    } finally {
        loginlogLoading.value = false
    }
}

function handleTabChange(tab) {
    if (tab === 'oplog') {
        loadOplog()
    } else {
        loadLoginlog()
    }
}

onMounted(() => {
    loadOplog()
})
</script>

<style lang="scss" scoped>
.log-container {
    .search-bar {
        display: flex;
        align-items: center;
    }
}
</style>
