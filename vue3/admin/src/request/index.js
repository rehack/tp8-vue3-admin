import axios from 'axios'
import { ElMessage } from 'element-plus'
import router from '../router'

const service = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000',
    timeout: 30000,
})

// 请求拦截器
service.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token')
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        return config
    },
    (error) => {
        return Promise.reject(error)
    },
)

// 响应拦截器
service.interceptors.response.use(
    (response) => {
        // 如果是 blob 类型，直接返回
        if (response.config.responseType === 'blob') {
            return response.data
        }

        const res = response.data

        // 如果返回的是普通响应（没有 code 字段或者 code === 0），直接返回
        // 只有明确表示错误的 code 才报错
        if (res.code && res.code !== 0) {
            ElMessage.error(res.msg || '请求失败')
            return Promise.reject(new Error(res.msg || '请求失败'))
        }

        // 检查 errorCode（ThinkPHP 验证错误等使用 errorCode）
        if (res.errorCode && res.errorCode !== 0) {
            ElMessage.error(res.msg || '请求失败')
            return Promise.reject(new Error(res.msg || '请求失败'))
        }

        return res
    },
    (error) => {
        if (error.response) {
            switch (error.response.status) {
                case 401:
                    ElMessage.error('登录已过期，请重新登录')
                    localStorage.removeItem('token')
                    localStorage.removeItem('userInfo')
                    router.push('/login')
                    break
                case 403:
                    ElMessage.error('没有权限访问该资源')
                    break
                case 404:
                    ElMessage.error('请求的资源不存在')
                    break
                case 500:
                    ElMessage.error('服务器错误')
                    break
                default:
                    ElMessage.error(error.response.data?.msg || '请求失败')
            }
        } else if (error.request) {
            ElMessage.error('网络错误，请检查网络连接')
        } else {
            ElMessage.error(error.message || '请求失败')
        }
        return Promise.reject(error)
    },
)

export default service
