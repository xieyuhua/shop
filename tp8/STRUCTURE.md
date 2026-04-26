# 商城后台API项目结构

## 目录结构

```
tp8/
├── app/
│   ├── api/
│   │   ├── controller/     # 11个控制器
│   │   │   ├── ApiController.php    # 基类
│   │   │   ├── Login.php           # 登录 (AuthService)
│   │   │   ├── User.php           # 用户 (UserService)
│   │   │   ├── Product.php         # 商品 (ProductService)
│   │   │   ├── Category.php        # 分类 (CategoryService)
│   │   │   ├── Order.php          # 订单 (OrderService)
│   │   │   ├── File.php           # 文件 (FileService)
│   │   │   ├── Config.php        # 配置 (ConfigModel)
│   │   │   ├── Dict.php         # 字典 (DictService)
│   │   │   ├── Statistics.php   # 统计 (StatisticsService)
│   │   │   └── Notify.php      # 通知 (NotifyService)
│   │   │
│   │   ├── middleware/     # 5个中间件
│   │   │   ├── AuthMiddleware.php
│   │   │   ├── RateLimitMiddleware.php
│   │   │   ├── SecurityMiddleware.php
│   │   │   ├── AccessLogMiddleware.php
│   │   │   └── ApiCacheMiddleware.php
│   │   │
│   │   └── validate/       # 5个验证器
│   │       ├── LoginValidate.php
│   │       ├── UserValidate.php
│   │       ├── ProductValidate.php
│   │       ├── OrderValidate.php
│   │       └── AValidate.php
│   │
│   ├── model/
│   │   └── admin/         # 8个Model
│   │       ├── AdminModel.php
│   │       ├── UserModel.php
│   │       ├── ProductModel.php
│   │       ├── CategoryModel.php
│   │       ├── OrderModel.php
│   │       ├── OrderItemModel.php
│   │       ├── ConfigModel.php
│   │       └── FileModel.php
│   │
│   └── service/            # 17个Service
│       ├── Service.php           # 基类
│       ├── AuthService.php       # 登录/登出
│       ├── UserService.php    # 用户CRUD
│       ├── ProductService.php # 商品CRUD
│       ├── CategoryService.php # 分类CRUD
│       ├── OrderService.php   # 订单CRUD
│       ├── FileService.php   # 文件上传
│       ├── DictService.php  # 数据字典
│       ├── StatisticsService.php # 统计分析
│       ├── NotifyService.php  # 消息通知
│       ├── QueueService.php   # 任务队列
│       ├── HelperService.php   # 工具函数
│       ├── ValidateService.php # 验证服务
│       ├── ExportService.php # 导出服务
│       ├── CacheService.php  # 缓存服务
│       ├── RedisService.php   # Redis
│       └── LogService.php    # 日志服务
│
├── admin-web/
│   ├── src/
│   │   ├── api/              # API模块
│   │   │   ├── request.js     # axios封装
│   │   │   ├── auth.js        # 认证
│   │   │   ├── user.js       # 用户
│   │   │   ├── product.js     # 商品
│   │   │   ├── category.js    # 分类
│   │   │   ├── order.js      # 订单
│   │   │   ├── config.js      # 配置
│   │   │   ├── statistics.js # 统计
│   │   │   └── notify.js     # 通知
│   │   │
│   │   ├── components/       # 公共组件
│   │   │   ├── BaseTable.vue
│   │   │   ├── BaseForm.vue
│   │   │   ├── SearchForm.vue
│   │   │   └── ImageUpload.vue
│   │   │
│   │   ├── views/            # 页面
│   │   │   ├── Login.vue
│   │   │   ├── Dashboard.vue
│   │   │   ├── User.vue
│   │   │   ├── Product.vue
│   │   │   ├── Category.vue
│   │   │   ├── Order.vue
│   │   │   ├── Statistics.vue
│   │   │   └── Config.vue
│   │   │
│   │   ├── layout/           # 布局
│   │   │   └── AdminLayout.vue
│   │   │
│   │   ├── stores/           # 状态管理
│   │   │   └── user.js
│   │   │
│   │   ├── router/          # 路由
│   │   │   └── index.js
│   │   │
│   │   ├── utils/           # 工具函数
│   │   │   ├── index.js
│   │   │   ├── storage.js
│   │   │   ├── dict.js
│   │   │   └── hooks.js
│   │   │
│   │   └── i18n/            # 国际化
│   │       └── index.js
│   │
│   └── vite.config.js
│
├── database/
│   ├── mall.sql           # 主数据库(12表)
│   ├── notify.sql        # 通知表
│   ├── queue.sql        # 队列表
│   └── README.md        # 表结构说明
│
├── route/
│   └── api.php           # 路由配置
│
├── config/              # 配置文件
│   ├── app.php
│   ├── database.php
│   ├── cache.php
│   ├── middleware.php
│   └── ...
│
├── .env                  # 环境配置
└── composer.json        # 依赖
```

## 环境变量 (.env)

```
APP_DEBUG = true

[DB]
DB_DRIVER = mysql
DB_HOST = 127.0.0.1
DB_PORT = 3306
DB_NAME = mall
DB_USER = root
DB_PASS =
DB_PREFIX = tp_

[CACHE]
CACHE_DRIVER = file
CACHE_EXPIRE = 3600

[REDIS]
REDIS_HOST = 127.0.0.1
REDIS_PORT = 6379

[JWT]
JWT_SECRET = mall_jwt_secret_key_2024
JWT_EXPIRE = 7200

[RATE_LIMIT]
RATE_LIMIT_ENABLED = true
RATE_LIMIT_MAX = 100
```

## API路由表

| 方法 | 路径 | 控制器 | Service |
|------|------|--------|---------|
| POST | /admin/login | Login | AuthService |
| POST | /admin/logout | Login | AuthService |
| GET | /admin/user/info | Login | AuthService |
| GET | /admin/user | User | UserService |
| POST | /admin/user | User | UserService |
| PUT | /admin/user | User | UserService |
| DELETE | /admin/user/:id | User | UserService |
| GET | /admin/user/options | User | UserService |
| GET | /admin/product | Product | ProductService |
| POST | /admin/product | Product | ProductService |
| PUT | /admin/product | Product | ProductService |
| DELETE | /admin/product/:id | Product | ProductService |
| GET | /admin/product/options | Product | ProductService |
| GET | /admin/category | Category | CategoryService |
| GET | /admin/category/tree | Category | CategoryService |
| POST | /admin/category | Category | CategoryService |
| PUT | /admin/category | Category | CategoryService |
| DELETE | /admin/category/:id | Category | CategoryService |
| GET | /admin/category/options | Category | CategoryService |
| GET | /admin/order | Order | OrderService |
| GET | /admin/order/:id | Order | OrderService |
| POST | /admin/order/ship | Order | OrderService |
| POST | /admin/order/:id/cancel | Order | OrderService |
| POST | /admin/order/:id/refund | Order | OrderService |
| GET | /admin/statistics | Statistics | StatisticsService |
| GET | /admin/statistics/chart | Statistics | StatisticsService |
| GET | /admin/config | Config | ConfigModel |
| POST | /admin/config | Config | ConfigModel |
| POST | /admin/file/upload | File | FileService |
| GET | /admin/file | File | FileService |
| DELETE | /admin/file/:id | File | FileService |
| GET | /admin/dict | Dict | DictService |
| GET | /admin/notify | Notify | NotifyService |
| GET | /admin/notify/unread | Notify | NotifyService |
| POST | /admin/notify/read | Notify | NotifyService |
| DELETE | /admin/notify/:id | Notify | NotifyService |

## Service基类方法

| 方法 | 说明 |
|------|------|
| `db($name)` | 获取查询构造器 |
| `find($id)` | 按ID查找 |
| `findOrFail($id)` | 按ID查找或抛异常 |
| `select($where)` | 条件查询 |
| `paginate($query, $page, $limit)` | 分页 |
| `create($data)` | 创建 |
| `update($id, $data)` | 更新 |
| `delete($id)` | 删除 |
| `exists($where)` | 记录是否存在 |
| `getByField($field, $value)` | 按字段查找 |
| `count($where)` | 计数 |
| `success($data, $msg)` | 成功响应 |
| `error($msg, $code)` | 错误响应 |
| `result($data, $msg, $code)` | 自定义响应 |

## 调用链

```
HTTP请求
    ↓
Middleware (认证/限流/日志)
    ↓
Controller (参数接收 → 调用Service → 返回响应)
    ↓
Service (业务逻辑处理)
    ↓
Model (数据操作)
    ↓
Database
```

## 优化记录

### 2024-01 已完成优化

1. **删除重复Model**
   - 删除6个重复文件 (User.php, Admin.php, Product.php, Category.php, Order.php, OrderItem.php)
   - 只保留*Model.php文件

2. **重构Service基类**
   - 添加17个CRUD通用方法
   - 统一响应格式

3. **创建业务Service**
   - AuthService: 登录/登出
   - UserService: 用户CRUD
   - ProductService: 商品CRUD
   - CategoryService: 分类CRUD
   - OrderService: 订单CRUD
   - FileService: 文件上传
   - StatisticsService: 统计分析
   - NotifyService: 消息通知

4. **重构Controller**
   - 只做参数接收和响应返回
   - 平均10-20行/文件

5. **完善路由**
   - 添加options相关路由
   - 添加通知相关路由
   - 添加树形结构路由

6. **完善前端API**
   - 添加notify.js
   - 完善category.js (添加tree/options)
   - 完善product.js (添加options)

7. **配置优化**
   - 添加Redis缓存配置
   - 完善.env环境变量
   - 添加限流配置