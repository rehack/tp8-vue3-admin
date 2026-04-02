export function hasPermission(permissions, route) {
    if (!route.meta?.permission) return true
    return permissions.includes(route.meta.permission)
}

export function filterAsyncRoutes(routes, permissions) {
    const res = []
    routes.forEach((route) => {
        const tmp = { ...route }
        if (hasPermission(permissions, tmp)) {
            if (tmp.children) {
                tmp.children = filterAsyncRoutes(tmp.children, permissions)
            }
            res.push(tmp)
        }
    })
    return res
}
