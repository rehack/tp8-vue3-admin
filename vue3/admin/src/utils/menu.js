// Element Plus 图标名称映射
const iconMap = {
    'el-icon-phone-outline': 'Phone',
    'el-icon-coin': 'Coin',
    'el-icon-data-line': 'DataLine',
    'el-icon-s-data': 'DataAnalysis',
    'el-icon-s-finance': 'Money',
    'el-icon-document-copy': 'DocumentCopy',
    'el-icon-magic-stick': 'MagicStick',
    'el-icon-s-custom': 'UserFilled',
    'el-icon-setting': 'Setting',
    'el-icon-user': 'User',
    'el-icon-menu': 'Menu',
    'el-icon-document': 'Document',
    'el-icon-s-order': 'Order',
    'el-icon-goods': 'Goods',
    'el-icon-view': 'View',
    'el-icon-edit': 'Edit',
    'el-icon-delete': 'Delete',
    'el-icon-plus': 'Plus',
    'el-icon-search': 'Search',
    'el-icon-refresh': 'Refresh',
    'el-icon-download': 'Download',
    'el-icon-upload': 'Upload',
}

/**
 * 获取图标名称
 */
function getIconName(iconCls) {
    if (!iconCls) return null
    return iconMap[iconCls] || null
}

/**
 * 构建菜单路由
 */
export function buildMenuRoutes(menus) {
    if (!menus || menus.length === 0) {
        return []
    }

    // 1. 先构建树形结构
    const menuTree = buildTree(menus)

    // 2. 转换为路由格式
    return menuTree.map((menu) => convertToRoute(menu))
}

/**
 * 将扁平数组构建成树形结构
 */
function buildTree(menus) {
    const map = {}
    const roots = []

    menus.forEach((menu) => {
        map[menu.id] = { ...menu, children: [] }
    })

    menus.forEach((menu) => {
        if (menu.pid === 0 || menu.pid === '0' || !menu.pid) {
            roots.push(map[menu.id])
        } else {
            if (map[menu.pid]) {
                map[menu.pid].children.push(map[menu.id])
            } else {
                roots.push(map[menu.id])
            }
        }
    })

    return roots
}

/**
 * 转换菜单为路由格式
 */
function convertToRoute(menu) {
    const route = {
        path: menu.path || `/menu-${menu.id}`,
        name: `menu-${menu.id}`,
        meta: {
            title: menu.title,
        },
        icon: getIconName(menu.iconCls),
        children: [],
    }

    if (menu.children && menu.children.length > 0) {
        route.children = menu.children.map((child) => convertToRoute(child))
    }

    return route
}

/**
 * 获取所有菜单的路径（用于权限验证）
 */
export function getAllMenuPaths(menus) {
    const paths = []

    function traverse(items) {
        items.forEach((item) => {
            paths.push(item.path)
            if (item.children && item.children.length > 0) {
                traverse(item.children)
            }
        })
    }

    traverse(menus)
    return paths
}
