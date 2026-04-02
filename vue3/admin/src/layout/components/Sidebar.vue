<template>
    <div class="sidebar" :class="{ 'is-collapse': layoutStore.sidebarCollapse }">
        <div class="logo-container">
            <h1 v-if="!layoutStore.sidebarCollapse" class="logo-title">CRM管理系统</h1>
            <img v-else src="../../assets/logo.png" alt="Logo" class="logo-image" />

        </div>
        <el-menu
            :default-active="activeMenu"
            :router="true"
            :collapse="layoutStore.sidebarCollapse"
            :background-color="'#ffffff'"
            :text-color="'#606266'"
            :active-text-color="'#409eff'"
            :unique-opened="true"
            :collapse-transition="false"
            mode="vertical"
            class="sidebar-menu"
        >
            <template v-for="menu in menus" :key="menu.path">
                <!-- 有子菜单的渲染为 el-sub-menu -->
                <el-sub-menu v-if="menu.children && menu.children.length > 0" :index="menu.path">
                    <template #title>
                        <el-icon v-if="menu.iconCls">
                            <component :is="menu.iconCls" />
                        </el-icon>
                        <span>{{ menu.title }}</span>
                    </template>
                    <el-menu-item
                        v-for="child in menu.children"
                        :key="child.path"
                        :index="`${menu.path}/${child.path}`"
                    >
                        <span>{{ child.title }}</span>
                    </el-menu-item>
                </el-sub-menu>

                <!-- 没有子菜单的渲染为 el-menu-item -->
                <el-menu-item :data-title="menu.title" v-else :index="menu.path">
                    <el-icon v-if="menu.iconCls">
                        <component :is="menu.iconCls" />
                    </el-icon>
                    <span>{{ menu.title }}</span>
                </el-menu-item>
            </template>
        </el-menu>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useUserStore } from '../../stores/user'
import { useLayoutStore } from '../../stores/layout'

const route = useRoute()
const userStore = useUserStore()
const layoutStore = useLayoutStore()

const activeMenu = computed(() => route.path)

// 将扁平菜单构建成树形
const menus = computed(() => {
    const rawMenus = userStore.menus || []
    const map = {}
    const roots = []

    rawMenus.forEach((menu) => {
        map[menu.id] = { ...menu, children: [] }
    })

    rawMenus.forEach((menu) => {
        if (menu.pid === 0 || menu.pid === '0' || !menu.pid) {
            roots.push(map[menu.id])
        } else if (map[menu.pid]) {
            map[menu.pid].children.push(map[menu.id])
        }
    })

    return roots
})
</script>

<style lang="scss" scoped>
$bg-light: #ffffff;
$bg-hover: #efefef;
$bg-active: #ecf5ff;
$text-dark: #111010;
$text-normal: #606266;
$text-light: #909399;
$accent: #409eff;
$accent-dark: rgba(64, 158, 255, 0.1);

.sidebar {
    height: 100%;
    overflow: visible;
    display: flex;
    flex-direction: column;

    // Logo 区域
    .logo-container {
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        background: linear-gradient(135deg, #409eff 0%, #337ecc 100%);
        position: relative;
        overflow: hidden;

        &::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 50%);
        }

        .logo-title {
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            letter-spacing: 1px;
            white-space: nowrap;
            position: relative;
            z-index: 1;
        }

        .collapse-icon {
            color: #fff;
            font-size: 20px;
            position: relative;
            z-index: 1;
        }
    }

    // 菜单区域
    .sidebar-menu {
        border: none;
        flex: 1;
        width: 100%;
        overflow-y: auto;
        overflow-x: hidden;
        background: $bg-light;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
        transition: width 0.3s ease;

        // 自定义滚动条
        &::-webkit-scrollbar {
            width: 4px;
        }

        &::-webkit-scrollbar-track {
            background: transparent;
        }

        &::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 2px;

            &:hover {
                background: rgba(0, 0, 0, 0.15);
            }
        }

        // 菜单项基础样式
        :deep(.el-menu-item),
        :deep(.el-sub-menu__title) {
            // margin: 2px 10px;
            margin: 2px;
            border-radius: 6px;
            padding-left: 16px ;
            height: 44px;
            line-height: 44px;
            position: relative;
            transform: translateZ(0);
            will-change: transform;

            .el-icon {
                margin-right: 10px;
                font-size: 16px;
                color: $text-light;
                transition: color 0.2s;
            }

            span {
                color: $text-normal;
                font-size: 14px;
                transition: color 0.2s;
            }

            &:hover {
                background: $bg-hover ;

                .el-icon {
                    color: $accent;
                }

                span {
                    color: $text-dark;
                }
            }
        }

        // 一级菜单激活状态
        :deep(.el-menu-item.is-active) {
            background: $bg-active ;

            &::before {
                content: '';
                position: absolute;
                left: 0;
                top: 10px;
                bottom: 10px;
                width: 3px;
                background: $accent;
                border-radius: 0 3px 3px 0;
            }

            .el-icon {
                color: $accent;
            }

            span {
                color: $accent ;
                font-weight: 500;
            }
        }

        // 子菜单样式
        :deep(.el-sub-menu) {
            &.is-active > .el-sub-menu__title {
                .el-icon {
                    color: $accent;
                }

                span {
                    color: $text-dark;
                }
            }

            // 子菜单展开箭头
            .el-sub-menu__icon-arrow {
                color: $text-light;
            }
        }

        // 二级菜单样式 - 防止抖动
        :deep(.el-menu--inline) {
            margin-top: 0;
            overflow: hidden;

            .el-menu-item {
                margin-left: 20px;
                margin-right: 10px;
                margin-top: 2px;
                margin-bottom: 2px;
                padding-left: 36px ;
                height: 40px;
                line-height: 40px;
                min-height: 40px;
                transform: translateZ(0);
                will-change: transform;

                &:last-child {
                    margin-bottom: 4px;
                }

                &:hover {
                    background: $bg-hover ;
                }

                &.is-active {
                    background: $bg-active ;

                    &::before {
                        display: none;
                    }

                    span {
                        color: $accent ;
                    }
                }
            }
        }

        // 折叠状态样式
        :deep(.el-menu--collapse) {
            width: 100%;

            .el-menu-item,
            .el-sub-menu__title {
                display: flex !important;
                flex-direction: row;
                align-items: center;
                justify-content: center;
                padding: 0 !important;
                position: relative;

                // 图标居中
                .el-icon {
                    margin: 0;
                    font-size: 18px;
                    width: 100%;
                    text-align: center;
                }

                // 隐藏文字
                > span:not(.el-icon):not(.el-sub-menu__icon-arrow) {
                    display: none;
                }

                .el-sub-menu__icon-arrow {
                    display: none;
                }

                // 隐藏子菜单
                .el-menu--inline {
                    display: none;
                }

                // Hover tooltip
                &::after {
                    content: attr(data-title);
                    position: absolute;
                    left: 100%;
                    top: 50%;
                    transform: translateY(-50%);
                    margin-left: 10px;
                    padding: 8px 14px;
                    background: rgba(0, 0, 0, 0.85);
                    color: #fff;
                    font-size: 13px;
                    white-space: nowrap;
                    border-radius: 4px;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
                    opacity: 0;
                    visibility: hidden;
                    transition: opacity 0.2s, visibility 0.2s;
                    z-index: 9999;
                    pointer-events: none;
                }

                &:hover {
                    background: $bg-hover;

                    &::after {
                        opacity: 1;
                        visibility: visible;
                    }
                }
            }
        }
    }

    // 折叠状态
    &.is-collapse {
        .logo-container {
            justify-content: center;
            padding: 0;
        }

        .sidebar-menu {
            width: 60px;
        }
    }
}
</style>
