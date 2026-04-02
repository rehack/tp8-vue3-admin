import { createRouter, createWebHistory } from 'vue-router'
import NProgress from 'nprogress'
import 'nprogress/nprogress.css'

NProgress.configure({ showSpinner: false })

// 使用 import.meta.glob 扫描所有视图文件
const views = import.meta.glob('../views/**/*.vue')

// 根据组件路径解析组件
function getViewComponent(componentPath) {
    if (!componentPath) return null
    const fullPath = `../views/${componentPath}.vue`
    return views[fullPath] || null
}

// 根据后端菜单数据动态生成路由
function generateRoutesFromMenus(menus) {
    if (!menus || menus.length === 0) return []

    const routes = []

    menus.forEach((menu) => {
        // 一级菜单 (pid 为 0 或空)
        if (menu.pid === 0 || menu.pid === '0' || !menu.pid) {
            const basePath = menu.path.replace(/^\//, '')
            const route = {
                path: basePath,
                name: `menu-${menu.id}`,
                meta: {
                    title: menu.title,
                    icon: menu.iconCls,
                },
            }

            // 有子菜单的一级菜单，重定向到第一个子菜单
            const children = menus.filter(m => m.pid === menu.id)
            if (children.length > 0) {
                // 过滤出有有效组件的子菜单
                const validChildren = children.filter(child => getViewComponent(child.component))
                if (validChildren.length > 0) {
                    route.redirect = { name: `menu-${validChildren[0].id}` }
                    route.children = validChildren.map(child => ({
                        path: child.path,
                        name: `menu-${child.id}`,
                        meta: {
                            title: child.title,
                        },
                        component: getViewComponent(child.component),
                    }))
                    routes.push(route)
                }
            } else {
                // 没有子菜单的页面组件
                const component = getViewComponent(menu.component)
                if (component) {
                    route.component = component
                    routes.push(route)
                }
            }
        }
    })

    return routes
}

// 获取存储的用户信息
function getStoredUserInfo() {
    try {
        const userInfo = localStorage.getItem('userInfo')
        return userInfo ? JSON.parse(userInfo) : null
    } catch {
        return null
    }
}

// 创建路由实例
const userInfo = getStoredUserInfo()
const dynamicRoutes = generateRoutesFromMenus(userInfo?.menus)

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/login',
            name: 'Login',
            component: () => import('../views/login/index.vue'),
            meta: { title: '登录' },
        },
        {
            path: '/',
            component: () => import('../layout/index.vue'),
            name: 'root',
            redirect: '/dashboard',
            children: [
                {
                    path: 'dashboard',
                    name: 'Dashboard',
                    component: () => import('../views/dashboard/index.vue'),
                    meta: { title: '首页' },
                },
                ...dynamicRoutes,
            ],
        },
        // 404 页面
        {
            path: '/:pathMatch(.*)*',
            name: 'NotFound',
            component: () => import('../views/notFound.vue'),
            meta: { title: '页面不存在' },
        },
    ],
})

// 路由守卫
router.beforeEach((to, from) => {
    NProgress.start()
    document.title = to.meta.title ? `${to.meta.title} - 管理后台` : '管理后台'

    const token = localStorage.getItem('token')

    if (to.path === '/login') {
        if (token) return '/dashboard'
        return true
    }

    if (!token) {
        return '/login'
    }

    return true
})

router.afterEach(() => {
    NProgress.done()
})

export default router
