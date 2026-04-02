<template>
    <div class="tags-view-container">
        <div class="tags-view-wrapper">
            <router-link
                v-for="tag in visitedViews"
                :key="tag.path"
                :to="{ path: tag.path }"
                class="tags-view-item"
                :class="{ active: isActive(tag) }"
            >
                {{ tag.meta?.title }}
                <el-icon
                    v-if="!isAffix(tag)"
                    class="close-icon"
                    @click.prevent.stop="closeSelectedTag(tag)"
                >
                    <Close />
                </el-icon>
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const visitedViews = ref([])

const visitedViews_ = computed(() => {
    const views = []
    if (route.name) {
        views.push(route)
    }
    return views
})

const isActive = (tag) => {
    return tag.path === route.path
}

const isAffix = (tag) => {
    return tag.meta?.affix
}

const closeSelectedTag = (view) => {
    const index = visitedViews.value.findIndex((v) => v.path === view.path)
    if (index > -1) {
        visitedViews.value.splice(index, 1)
    }
}

// 初始化
visitedViews.value = visitedViews_.value
</script>

<style lang="scss" scoped>
.tags-view-container {
    height: 34px;
    width: 100%;
    background: #fff;
    border-bottom: 1px solid #d8dce5;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.12);

    .tags-view-wrapper {
        display: flex;
        align-items: center;
        height: 100%;
        padding: 0 8px;
        overflow-x: auto;

        .tags-view-item {
            display: inline-flex;
            align-items: center;
            padding: 0 8px;
            height: 26px;
            line-height: 26px;
            margin-right: 4px;
            font-size: 12px;
            color: #495057;
            background: #fff;
            border: 1px solid #d8dce5;
            border-radius: 4px;
            text-decoration: none;

            &.active {
                background: #409eff;
                color: #fff;
                border-color: #409eff;

                .close-icon {
                    color: #fff;
                }
            }

            .close-icon {
                margin-left: 4px;
                font-size: 10px;
                color: #999;

                &:hover {
                    color: #333;
                }
            }
        }
    }
}
</style>
