import { defineStore } from 'pinia'

// 从 localStorage 恢复数据
function getStorageData() {
    try {
        const userInfo = localStorage.getItem('userInfo')
        return userInfo ? JSON.parse(userInfo) : null
    } catch {
        return null
    }
}

export const useUserStore = defineStore('user', {
    state: () => {
        const storageData = getStorageData()
        return {
            token: localStorage.getItem('token') || '',
            userInfo: storageData,
            menus: storageData?.menus || [],
            roles: storageData?.roles || [],
            rolesScope: storageData?.roles_scope || [],
        }
    },

    getters: {
        isLoggedIn: (state) => !!state.token,
        getUserInfo: (state) => state.userInfo,
        getMenus: (state) => state.menus,
        getRoles: (state) => state.roles,
    },

    actions: {
        setUserInfo(userInfo) {
            this.userInfo = userInfo
            this.menus = userInfo?.menus || []
            this.roles = userInfo?.roles || []
            this.rolesScope = userInfo?.roles_scope || []
            localStorage.setItem('userInfo', JSON.stringify(userInfo))
        },

        setToken(token) {
            this.token = token
            localStorage.setItem('token', token)
        },

        // 初始化时从 localStorage 恢复
        restoreFromStorage() {
            const storageData = getStorageData()
            if (storageData) {
                this.userInfo = storageData
                this.menus = storageData?.menus || []
                this.roles = storageData?.roles || []
                this.rolesScope = storageData?.roles_scope || []
            }
        },

        logout() {
            this.token = ''
            this.userInfo = null
            this.menus = []
            this.roles = []
            this.rolesScope = []
            localStorage.removeItem('token')
            localStorage.removeItem('userInfo')
        },
    },
})
