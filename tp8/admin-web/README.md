# 商城后台管理系统

基于 ThinkPHP 8.0 + Vue 3 + Element Plus 开发的前后端分离商城后台管理系统。

## 技术栈

### 后端
- ThinkPHP 8.0
- ThinkPHP ORM 4.0
- JWT 认证
- MySQL 8.0

### 前端
- Vue 3.4
- Vite 5.0
- Pinia (状态管理)
- Vue Router 4
- Element Plus 2.4
- Axios
- ECharts 5
- Vue I18n 9

## 项目结构

```
tp8/                          # 后端项目
├── app/
│   ├── api/                  # API模块
│   │   ├── controller/       # API控制器
│   │   ├── middleware/       # 中间件
│   │   └── validate/         # 验证器
│   ├── model/admin/         # 数据模型 (think-orm)
│   ├── service/             # 业务服务
│   └── ExceptionHandle.php  # 异常处理
├── config/                   # 配置文件
├── database/                # 数据库脚本
├── extend/                  # 扩展类库
├── public/                  # 公共资源
├── route/                   # 路由配置
├── runtime/                 # 运行时
└── vendor/                  # 依赖

admin-web/                   # 前端项目
├── public/
├── src/
│   ├── api/                 # API请求
│   ├── assets/              # 静态资源
│   ├── components/         # 通用组件
│   │   ├── BaseTable.vue    # 通用表格
│   │   ├── BaseForm.vue     # 通用表单
│   │   ├── SearchForm.vue   # 搜索表单
│   │   └── ImageUpload.vue # 图片上传
│   ├── i18n/               # 国际化
│   ├── layout/              # 布局组件
│   ├── router/             # 路由配置
│   ├── stores/              # Pinia状态
│   ├── utils/               # 工具函数
│   │   ├── dict.js          # 字典格式化
│   │   ├── hooks.js        # Vue组合式API
│   │   ├── index.js        # 消息/确认
│   │   └── storage.js      # 存储
│   └── views/               # 页面视图
├── index.html
├── package.json
└── vite.config.js
```

## 功能模块

| 模块 | 接口数 | 说明 |
|------|-------|------|
| 用户管理 | 4 | 会员列表/添加/编辑/删除 |
| 商品管理 | 4 | 商品列表/添加/编辑/删除 |
| 分类管理 | 4 | 分类列表/添加/编辑/删除 |
| 订单管理 | 5 | 订单列表/详情/发货/取消/退款 |
| 数据统计 | 2 | 统计数据/图表 |
| 系统配置 | 2 | 配置读取/保存 |
| 文件管理 | 3 | 上传/列表/删除 |
| 数据字典 | 1 | 字典获取 |

## API 接口

```javascript
// 认证
POST /api/admin/login     # 登录
POST /api/admin/logout    # 登出
GET  /api/admin/user/info  # 用户信息

// 用户
GET    /api/admin/user    # 用户列表
POST   /api/admin/user    # 添加用户
PUT    /api/admin/user    # 更新用户
DELETE /api/admin/user/:id

// 商品
GET    /api/admin/product
POST   /api/admin/product
PUT    /api/admin/product
DELETE /api/admin/product/:id

// 订单
GET    /api/admin/order
GET    /api/admin/order/:id
POST   /api/admin/order/ship
POST   /api/admin/order/:id/cancel
POST   /api/admin/order/:id/refund

// 统计
GET /api/admin/statistics
GET /api/admin/statistics/chart

// 文件
POST /api/admin/file/upload
GET  /api/admin/file
DELETE /api/admin/file/:id
```

## 快速开始

### 1. 环境要求
- PHP >= 8.0
- MySQL >= 5.7
- Node.js >= 18
- Composer

### 2. 安装后端

```bash
cd tp8

# 创建数据库
mysql -u root -p mall < database/mall.sql

# 安装依赖
composer install

# 配置环境
cp .example.env .env
# 编辑 .env 配置数据库连接

# 启动服务
php think run
```

### 3. 安装前端

```bash
cd admin-web

# 安装依赖
npm install

# 启动开发服务器
npm run dev

# 构建生产
npm run build
```

### 4. 登录

- 账号: `admin`
- 密码: `admin123`

## 使用指南

### 通用组件

```vue
<!-- 搜索表单 -->
<SearchForm :items="searchItems" @search="handleSearch">
  <template #action>
    <el-button type="primary" @click="handleAdd">添加</el-button>
  </template>
</SearchForm>

<!-- 表格 -->
<BaseTable :columns="columns" :data="tableData" loading @edit @delete />

<!-- 表单弹窗 -->
<BaseForm ref="formRef" :items="formItems" @submit="handleSubmit" />
```

### 组合式API

```javascript
import { usePagination } from '@/utils/hooks'

const { page, limit, total, onPageChange, onLimitChange } = usePagination()
```

### 字典格式化

```javascript
import { formatStatus, formatOrderStatus, formatMoney, formatDate } from '@/utils/dict'

// 状态格式化
formatStatus(value)     // 启用/禁用

// 订单状态格式化
formatOrderStatus(value) // { text: '待付款', type: 'warning' }

// 金额格式化
formatMoney(99.00)     // ¥99.00

// 日期格式化
formatDate('2024-01-01 12:00:00', 'YYYY-MM-DD')
```

## 响应格式

```json
{
  "code": 200,
  "msg": "success",
  "data": {},
  "time": 1704067200
}
```

## 许可证

MIT License