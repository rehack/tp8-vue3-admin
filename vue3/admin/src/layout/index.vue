<template>
    <div class="app-wrapper">
        <Sidebar class="sidebar-container" :class="{ 'is-collapse': layoutStore.sidebarCollapse }" />
        <div class="main-container">
            <Header />
            <TagsView />
            <main class="app-main">
                <router-view v-slot="{ Component }">
                    <transition name="fade-transform" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </router-view>
            </main>
        </div>
    </div>
</template>

<script setup>
import Sidebar from './components/Sidebar.vue'
import Header from './components/Header.vue'
import TagsView from './components/TagsView.vue'
import { useLayoutStore } from '../stores/layout'

const layoutStore = useLayoutStore()
</script>

<style lang="scss" scoped>
.app-wrapper {
    display: flex;
    height: 100vh;
    width: 100%;
}

.sidebar-container {
    width: 170px;
    height: 100vh;
    overflow: visible;
    flex-shrink: 0;
    transition: width 0.3s ease;
    background: #fff;

    &.is-collapse {
        width: 60px;
    }
}

.main-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.app-main {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
    background: #f6f6f6;
}

.fade-transform-leave-active,
.fade-transform-enter-active {
    transition: all 0.3s;
}

.fade-transform-enter-from {
    opacity: 0;
    transform: translateX(-20px);
}

.fade-transform-leave-to {
    opacity: 0;
    transform: translateX(20px);
}
</style>
