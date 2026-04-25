# 商城后台管理系统

基于 ThinkPHP 8.0 + Bootstrap 4.6 开发的管理后台

## 功能模块

- **用户管理** - 会员列表、编辑、禁用
- **商品管理** - 商品列表、添加、编辑、上下架
- **分类管理** - 商品分类树状管理
- **订单管理** - 订单列表、详情、发货、取消、退款
- **数据统计** - 销售趋势、订单统计
- **系统配置** - 基础配置、商城配置、支付配置

## 环境要求

- PHP >= 8.0
- MySQL >= 5.7
- Composer

## 安装步骤

1. 创建数据库并导入数据
```bash
mysql -u root -p mall < database/mall.sql
```

2. 配置数据库连接
复制 `.env` 文件并修改数据库配置

3. 安装依赖
```bash
composer install
```

4. 启动服务
```bash
php think run
```

## 登录后台

- URL: `/admin/login`
- 默认账号: `admin`
- 默认密码: `admin123`

## 目录结构

```
tp8/
├── app/
│   ├── controller/admin/   # 后台控制器
│   ├── model/admin/       # 数据模型
│   └── view/admin/       # 视图模板
├── config/              # 配置文件
├── database/            # 数据库脚本
├── public/static/admin/ # 静态资源
└── route/               # 路由配置
```