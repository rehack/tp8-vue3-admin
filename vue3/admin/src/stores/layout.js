import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useLayoutStore = defineStore('layout', () => {
    const sidebarCollapse = ref(false)

    function toggleSidebar() {
        sidebarCollapse.value = !sidebarCollapse.value
    }

    return {
        sidebarCollapse,
        toggleSidebar,
    }
})
