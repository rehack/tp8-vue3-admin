<template>
    <el-breadcrumb separator="/">
        <el-breadcrumb-item v-for="item in breadcrumbs" :key="item.path">
            {{ item.meta?.title || item.name }}
        </el-breadcrumb-item>
    </el-breadcrumb>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const breadcrumbs = computed(() => {
    return route.matched.filter((item) => item.meta?.title)
})
</script>
