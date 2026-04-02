<template>
    <div v-if="!item.hidden">
        <!-- 有子菜单的渲染为 el-sub-menu -->
        <el-sub-menu v-if="item.children && item.children.length > 0" :index="currentPath">
            <template #title>
                <el-icon v-if="item.icon">
                    <component :is="item.icon" />
                </el-icon>
                <span>{{ item.meta?.title }}</span>
            </template>
            <sidebar-item
                v-for="child in item.children"
                :key="child.path"
                :item="child"
                :base-path="child.path"
                :base-index="currentPath + '/' + child.path"
            />
        </el-sub-menu>

        <!-- 没有子菜单的渲染为 el-menu-item -->
        <el-menu-item v-else :index="currentPath">
            <el-icon v-if="item.icon">
                <component :is="item.icon" />
            </el-icon>
            <template #title>
                <span>{{ item.meta?.title }}</span>
            </template>
        </el-menu-item>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    basePath: {
        type: String,
        default: '',
    },
    baseIndex: {
        type: String,
        default: '',
    },
})

// 计算当前菜单的完整路径
// baseIndex 优先，否则用 basePath，最后用 item.path
const currentPath = computed(() => {
    if (props.baseIndex) {
        return props.baseIndex.replace(/\/+/g, '/')
    }
    if (props.basePath) {
        return props.basePath.replace(/\/+/g, '/')
    }
    return (props.item.path || '').replace(/\/+/g, '/')
})
</script>
