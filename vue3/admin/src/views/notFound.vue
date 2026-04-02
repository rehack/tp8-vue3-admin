<template>
    <div class="not-found">
        <div class="content">
            <h1>404</h1>
            <p>抱歉，您访问的页面不存在</p>
            <el-button type="primary" @click="$router.push('/')">返回首页</el-button>
        </div>
    </div>
</template>

<style scoped>
.not-found {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    background: #f0f2f5;
}

.content {
    text-align: center;
}

.content h1 {
    font-size: 120px;
    color: #409eff;
    margin: 0;
    line-height: 1;
}

.content p {
    font-size: 18px;
    color: #606266;
    margin: 20px 0 30px;
}
</style>
