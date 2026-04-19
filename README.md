# B2B2C 多用户商城系统

基于 ThinkPHP 8.1 + PHP 8.1+ 开发的多用户 B2B2C 商城系统，支持多商家入驻、小程序接口、管理后台。

## 功能特点

### 用户端（小程序/H5）
- 用户注册登录（用户名/手机号/邮箱）
- 商品浏览、搜索、分类
- 购物车管理
- 订单流程（创建、支付、取消、收货、评价）
- 收货地址管理
- 售后申请（退款/退货退款）
- 优惠券领取和使用
- 会员积分、余额管理

### 商家端
- 商家入驻申请
- 商品管理（发布、编辑、上架下架）
- 订单管理（发货、物流）
- 售后处理
- 数据统计

### 管理后台
- 仪表盘统计
- 用户管理
- 商家管理（审核、状态）
- 商品管理（审核、上下架）
- 分类管理
- 订单管理
- 售后管理
- 优惠券管理

## 技术栈

- PHP 8.1+
- ThinkPHP 8.1
- MySQL 5.7+ / 8.0+
- Redis（缓存）
- RESTful API

## 项目结构

```
shop/
├── app/
│   ├── admin/              # 管理后台应用
│   │   ├── controller/     # 控制器
│   │   └── route.php       # 路由配置
│   ├── api/                # 小程序/API应用
│   │   ├── controller/     # 控制器
│   │   ├── middleware/      # 中间件
│   │   └── route.php       # 路由配置
│   └── common/             # 公共模块
│       ├── controller/     # 公共控制器
│       ├── library/        # 公共类库
│       └── model/          # 数据模型
├── config/                 # 配置文件
├── database/               # 数据库文件
├── public/                 # 入口和静态资源
├── route/                  # 路由配置
└── runtime/                # 运行时目录
```

## API 接口

### 用户端接口 (/api)

| 模块 | 接口 | 说明 |
|------|------|------|
| 认证 | POST /api/auth/register | 用户注册 |
| 认证 | POST /api/auth/login | 用户登录 |
| 认证 | POST /api/auth/logout | 退出登录 |
| 用户 | GET /api/user/index | 用户中心 |
| 地址 | GET/POST /api/address/* | 收货地址 |
| 购物车 | GET/POST /api/cart/* | 购物车 |
| 订单 | GET/POST /api/order/* | 订单 |
| 售后 | GET/POST /api/aftersale/* | 售后 |
| 商品 | GET /api/product/* | 商品 |
| 店铺 | GET /api/shop/* | 店铺 |
| 优惠券 | GET/POST /api/coupon/* | 优惠券 |

### 管理后台接口 (/admin)

| 模块 | 接口 | 说明 |
|------|------|------|
| 登录 | POST /admin/login | 管理员登录 |
| 仪表盘 | GET /admin/dashboard | 统计数据 |
| 用户 | GET/POST /admin/user/* | 用户管理 |
| 分类 | GET/POST /admin/category/* | 分类管理 |
| 商品 | GET/POST /admin/product/* | 商品管理 |
| 店铺 | GET/POST /admin/shop/* | 店铺管理 |
| 订单 | GET/POST /admin/order/* | 订单管理 |
| 售后 | GET/POST /admin/aftersale/* | 售后管理 |
| 优惠券 | GET/POST /admin/coupon/* | 优惠券管理 |

## 安装部署

### 环境要求

- PHP >= 8.1
- MySQL >= 5.7
- Redis >= 5.0
- Composer >= 2.0

### 安装步骤

1. 克隆代码
```bash
git clone <repository_url> shop
cd shop
```

2. 安装依赖
```bash
composer install
```

3. 配置环境变量
```bash
cp .env.example .env
# 编辑 .env 配置数据库连接
```

4. 创建数据库
```sql
CREATE DATABASE mall_b2b2c DEFAULT CHARSET utf8mb4;
```

5. 导入数据库结构
```bash
mysql -u root -p mall_b2b2c < database/install.sql
```

6. 启动开发服务器
```bash
php think run
```

访问 `http://localhost:8000`

## 默认账号

### 管理后台
- 用户名: admin
- 密码: 123456

## 小程序对接

在小程序中使用以下方式调用接口：

```javascript
// 登录
wx.request({
  url: 'http://your-domain/api/auth/login',
  method: 'POST',
  data: { username, password },
  success: (res) => {
    wx.setStorageSync('token', res.data.data.token)
  }
})

// 携带Token请求
wx.request({
  url: 'http://your-domain/api/user/index',
  header: { 'Authorization': wx.getStorageSync('token') },
  success: (res) => { ... }
})
```

## License

Apache-2.0 License
