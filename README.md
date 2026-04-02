# CRM 管理系统技术文档

## 1. 项目概述

本项目是一套基于 Vue 3 + ThinkPHP 8 的 CRM（客户关系管理）系统，采用前后端分离架构。

### 技术栈

**前端**
- Vue 3 (Composition API + `<script setup>`)
- Vite 8.x
- Vue Router 5.x
- Pinia (状态管理)
- Element Plus (UI 组件库)
- Axios (HTTP 请求)
- SCSS (样式预处理器)

**后端**
- ThinkPHP 8.x
- MySQL 5.7+ / 8.0
- Redis (缓存)
- PHP 8.1+

## 2. 目录结构

```
tp8-vue3-admin/
├── app/                      # ThinkPHP 后端应用
│   ├── api/                  # API 模块
│   │   ├── config/           # 数据库配置
│   │   ├── controller/       # 控制器
│   │   │   ├── bll/          # 业务逻辑控制器（需权限验证）
│   │   │   └── pub/          # 公共控制器（需 Token 但不验证权限）
│   │   ├── model/            # 数据模型
│   │   ├── service/          # 服务层
│   │   ├── validate/         # 验证器
│   │   ├── route/            # 路由定义
│   │   └── ...
│   ├── common.php            # 公共函数
│   ├── middleware/           # 中间件
│   │   └── Auth.php          # 权限认证中间件
│   └── commom/              # 公共类
│       └── exception/        # 异常类
├── config/                   # ThinkPHP 配置
├── route/                    # 路由配置
├── vue3/admin/               # Vue 前端项目
│   ├── src/
│   │   ├── api/              # API 请求封装
│   │   ├── assets/           # 静态资源
│   │   ├── components/       # 公共组件
│   │   ├── layout/           # 布局组件
│   │   ├── request/          # Axios 实例配置
│   │   ├── router/           # 路由配置
│   │   ├── stores/           # Pinia 状态管理
│   │   ├── utils/            # 工具函数
│   │   ├── views/            # 页面视图
│   │   ├── App.vue
│   │   └── main.js
│   ├── package.json
│   └── vite.config.js
├── public/                   # Web 入口
├── data.sql                  # 数据库初始化脚本
└── composer.json
```

## 3. 前端架构

### 3.1 动态路由机制

**核心逻辑**：路由由后端菜单数据动态生成，登录后调用 `addMenuRoutes()` 添加路由。

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   登录请求       │ ──► │  获取用户信息    │ ──► │  存储 menus      │
│  /gettoken/user │     │ /commom/sysuserinfo│   │ userStore.setUserInfo()│
└─────────────────┘     └─────────────────┘     └────────┬────────┘
                                                         │
                                                         ▼
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   页面访问       │ ◄── │   正常跳转       │ ◄── │  添加动态路由    │
│   刷新浏览器     │     │   /dashboard    │     │  addMenuRoutes()│
└─────────────────┘     └─────────────────┘     └─────────────────┘
```

**路由生成规则**（`router/index.js`）：
1. 扫描 `src/views/**/*.vue` 文件
2. 根据后端返回的菜单数据 `component` 字段定位组件
3. 菜单 `pid === 0` 为一级菜单，其子菜单 `pid === parentId`
4. 一级菜单自动 `redirect` 到第一个有效子菜单

**重要**：新增菜单页面时，后端 `auth_menu` 表的 `component` 字段需填写相对于 `src/views/` 的路径，如 `customer/index`。

### 3.2 状态管理

**useUserStore** (`stores/user.js`)
```javascript
state: {
    token: '',           // 登录令牌
    userInfo: {},       // 用户信息
    menus: [],          // 菜单权限数据
    roles: [],          // 角色列表
    rolesScope: []      // 角色数据权限范围
}
```

**useLayoutStore** (`stores/layout.js`)
```javascript
state: {
    sidebarCollapse: false  // 侧边栏折叠状态
}
```

### 3.3 请求封装

`src/request/index.js` 提供 Axios 实例，特性：
- 自动注入 `Authorization: Bearer <token>` 请求头
- 响应拦截器统一处理错误码 (`code !== 0`, `errorCode !== 0`)
- 401 响应自动跳转登录页并清除 Token
- 支持 `blob` 类型响应直接返回

### 3.4 布局组件

```
App.vue
└── layout/index.vue           # 主布局容器
    ├── Sidebar.vue             # 左侧菜单（树形结构转换）
    ├── Header.vue              # 顶部导航
    └── TagsView.vue             # 标签页导航
```

## 4. 后端架构

### 4.1 控制器分层

| 目录 | 说明 | 特性 |
|------|------|------|
| `controller/bll/` | 业务逻辑层 | 需权限验证（继承 `bll\BaseController`）|
| `controller/pub/` | 公共接口层 | 需 Token 但不验证权限（继承 `pub\BaseController`）|

### 4.2 权限认证流程

```
┌─────────────────┐
│  请求进入        │
└────────┬────────┘
         ▼
┌─────────────────┐     YES    ┌─────────────────┐
│  是否在 except   │ ────────► │  跳过权限检查    │
│ Action 列表？    │           └─────────────────┘
└────────┬────────┘
         │ NO
         ▼
┌─────────────────┐
│  从 Token 获取   │
│  用户 uid       │
└────────┬────────┘
         ▼
┌─────────────────┐     NO     ┌─────────────────┐
│  检查路由是否    │ ────────► │  抛出 AuthException│
│  在用户角色权限中 │          │  (403 无权限)    │
└────────┬────────┘           └─────────────────┘
         │ YES
         ▼
┌─────────────────┐
│  获取角色        │
│  view_scope     │
└────────┬────────┘
         ▼
┌─────────────────┐
│  继续处理请求    │
└─────────────────┘
```

### 4.3 Token 机制

**生成** (`BaseToken::generateToken()`)
- 32 位随机字符串 + 时间戳 + 盐值 → MD5 加密

**存储**
- ThinkPHP Cache（默认 Redis）
- Key: `token` 值
- Value: `{"uid": 1, "scope": 10, ...}`

**验证**
- 从请求头 `Authorization: Bearer <token>` 提取
- Cache 查询，超时或不存在则抛出 `TokenException`

### 4.4 数据模型关联

**SysUsers**（用户）
```
belongsToMany → AuthRole (通过 auth_access)
hasMany → Customer
belongsTo → Department
```

**AuthRole**（角色）
```
belongsToMany → SysUsers (通过 auth_access)
belongsToMany → AuthMenu (通过 auth_role_menu)
hasMany → AuthRule (通过 auth_rule.role_id)
```

## 5. 数据库设计

### 5.1 核心表结构

**sys_users**（用户表）
| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| username | varchar | 用户名 |
| password | varchar | 密码（BCrypt）|
| realname | varchar | 真实姓名 |
| dept_id | int | 部门ID |
| status | tinyint | 状态 0/1 |
| create_time | int | 创建时间 |
| last_login_time | int | 最后登录时间 |

**auth_role**（角色表）
| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| name | varchar | 角色名称 |
| view_scope | int | 数据权限范围 |
| rules_id | varchar | 关联 rule_ids |

**auth_menu**（菜单表）
| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| pid | int | 父级ID |
| title | varchar | 菜单标题 |
| path | varchar | 路由路径 |
| component | varchar | 组件路径 |
| iconCls | varchar | 图标 |
| status | tinyint | 状态 |

**auth_rule**（权限规则表）
| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| name | varchar | 规则名称（路由）|
| title | varchar | 规则标题 |
| status | tinyint | 状态 |

**auth_access**（用户-角色关联表）
| 字段 | 类型 | 说明 |
|------|------|------|
| uid | int | 用户ID |
| role_id | int | 角色ID |

**auth_role_menu**（角色-菜单关联表）
| 字段 | 类型 | 说明 |
|------|------|------|
| role_id | int | 角色ID |
| menu_id | int | 菜单ID |

**department**（部门表）
| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| pid | int | 父级ID |
| name | varchar | 部门名称 |

### 5.2 数据字典表

**data_tree**
| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| pid | int | 父级ID |
| name | varchar | 名称 |
| type | varchar | 类型 |

## 6. API 接口文档

### 6.1 认证接口

**POST /gettoken/user** - 用户登录
```
请求: { username, password, login_env: 'PC' }
响应: { code: 0, msg: 'success', token: 'xxx' }
```

**GET /commom/sysuserinfo** - 获取当前用户信息（含菜单）
```
响应: {
    id, username, realname, dept_id,
    roles: ['管理员'],
    roles_scope: [1],
    menus: [{ id, pid, title, path, component, iconCls }, ...]
}
```

### 6.2 公共接口

**GET /commom/sysusers** - 获取所有用户列表
**GET /common/sysroles** - 获取角色列表
**GET /common/depttree** - 获取部门树结构
**GET /common/department** - 获取部门列表
**GET /common/datatree** - 获取数据字典
**GET /common/authrules** - 获取权限规则列表
**GET /common/menus** - 获取菜单列表

### 6.3 系统管理接口

**POST /system/stafflist** - 获取职员列表
```
请求: { page, limit, search: { realname, dept_id } }
```

**POST /system/newstaff** - 新增职员
**POST /system/updatestaff** - 修改职员
**POST /system/resetpassword** - 重置密码
**POST /system/datadictionary** - 数据字典管理
**POST /system/authsetting** - 角色权限配置
**POST /system/authrule** - 权限规则增删改
**DELETE /system/role** - 删除角色
**POST /system/oplog** - 操作日志
**POST /system/loginlog** - 登录日志

### 6.4 客户管理接口

**GET /customer/getlist** - 客户列表
**GET /consultation/getlist** - 咨询列表
**GET /consultation/detail** - 咨询详情
**POST /consultation/save** - 保存咨询
**GET /consultation/delete** - 删除咨询

### 6.5 响应格式

**成功响应**
```json
{
    "code": 0,
    "msg": "success",
    "data": { ... }
}
```

**错误响应**
```json
{
    "code": 错误码,
    "msg": "错误信息"
}
```

**ThinkPHP 验证错误**
```json
{
    "errorCode": 错误码,
    "msg": "错误信息"
}
```

## 7. 环境配置

### 7.1 前端环境变量

`.env.development`
```
VITE_API_BASE_URL=http://localhost:8000
```

`.env.production`
```
VITE_API_BASE_URL=https://api.example.com
```

### 7.2 后端环境变量

`.env`
```
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=crm
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4
DB_PREFIX=

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

## 8. 常用命令

### 前端
```bash
cd vue3/admin
pnpm install          # 安装依赖
pnpm dev              # 开发服务器
pnpm build            # 生产构建
pnpm lint             # 代码检查
pnpm format           # 代码格式化
```

### 后端
```bash
php think run          # 启动开发服务器（端口 8000）
php think queue:work  # 队列Worker
php think optimize    # 清除缓存并优化
```

## 9. 常见问题

### 9.1 测试账号
admin  123456

### 9.2 登录后点击菜单 404
动态路由未正确添加。检查：
1. 后端 `auth_menu.component` 字段是否正确填写组件路径
2. 组件文件是否存在于 `src/views/` 目录

### 9.3 权限不足 403
1. 检查用户角色是否关联了对应菜单
2. 检查 `auth_rule` 表是否有对应路由规则
3. 确认路由 path 与 `auth_rule.name` 匹配

### 9.4 Token 过期
前端请求返回 401，自动跳转登录页。需重新登录获取新 Token。
