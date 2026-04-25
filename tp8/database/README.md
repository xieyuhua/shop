# 数据库表结构说明

## 管理员表 (tp_admin)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| username | varchar(50) | 用户名 (唯一) |
| password | varchar(100) | 密码 |
| nickname | varchar(50) | 昵称 |
| avatar | varchar(255) | 头像 |
| phone | varchar(20) | 手机号 |
| email | varchar(100) | 邮箱 |
| status | tinyint | 状态:0=禁用,1=正常 |
| login_ip | varchar(50) | 最后登录IP |
| login_time | datetime | 最后登录时间 |
| create_time | datetime | 创建时间 |
| update_time | datetime | 更新时间 |

## 会员表 (tp_user)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| username | varchar(50) | 用户名 |
| mobile | varchar(20) | 手机号 (唯一) |
| email | varchar(100) | 邮箱 |
| password | varchar(100) | 密码 |
| nickname | varchar(50) | 昵称 |
| avatar | varchar(255) | 头像 |
| gender | tinyint | 性别:0=未知,1=男,2=女 |
| balance | decimal(10,2) | 账户余额 |
| points | int | 积分 |
| status | tinyint | 状态:0=禁用,1=正常 |

## 商品分类表 (tp_category)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| pid | int | 父级ID |
| name | varchar(100) | 分类名称 |
| slug | varchar(100) | 分类别名 |
| image | varchar(255) | 分类图片 |
| description | varchar(500) | 描述 |
| sort | int | 排序 |
| status | tinyint | 状态:0=隐藏,1=显示 |

## 商品表 (tp_product)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| category_id | int | 分类ID |
| name | varchar(200) | 商品名称 |
| slug | varchar(200) | 商品别名 |
| image | varchar(255) | 商品图片 |
| images | text | 商品相册(JSON) |
| description | text | 商品描述 |
| price | decimal(10,2) | 销售价格 |
| original_price | decimal(10,2) | 原价 |
| cost_price | decimal(10,2) | 成本价 |
| stock | int | 库存 |
| sales | int | 销量 |
| virtual_sales | int | 虚拟销量 |
| specs | text | 规格(JSON) |
| is_hot | tinyint | 是否热卖 |
| is_new | tinyint | 是否新品 |
| is_recommend | tinyint | 是否推荐 |
| status | tinyint | 状态:0=下架,1=上架 |

## 订单表 (tp_order)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| order_no | varchar(50) | 订单编号 (唯一) |
| user_id | int | 用户ID |
| receiver_name | varchar(50) | 收货人 |
| receiver_mobile | varchar(20) | 联系电话 |
| receiver_province | varchar(50) | 省份 |
| receiver_city | varchar(50) | 城市 |
| receiver_district | varchar(50) | 区县 |
| receiver_address | varchar(255) | 详细地址 |
| total_amount | decimal(10,2) | 订单总金额 |
| discount_amount | decimal(10,2) | 优惠金额 |
| pay_amount | decimal(10,2) | 实付金额 |
| points_amount | decimal(10,2) | 积分抵扣 |
| freight_amount | decimal(10,2) | 运费 |
| pay_type | tinyint | 支付方式 |
| pay_time | datetime | 支付时间 |
| express_company | varchar(100) | 快递公司 |
| express_no | varchar(100) | 快递单号 |
| ship_time | datetime | 发货时间 |
| status | tinyint | 状态:0=待付款,1=待发货,2=待收货,3=已完成,4=已取消,5=已退款 |

## 订单商品表 (tp_order_item)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| order_id | int | 订单ID |
| order_no | varchar(50) | 订单编号 |
| product_id | int | 商品ID |
| product_name | varchar(200) | 商品名称 |
| product_image | varchar(255) | 商品图片 |
| price | decimal(10,2) | 单价 |
| specs | varchar(500) | 规格 |
| quantity | int | 数量 |
| total_price | decimal(10,2) | 小计 |

## 购物车表 (tp_cart)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| user_id | int | 用户ID |
| product_id | int | 商品ID |
| product_name | varchar(200) | 商品名称 |
| product_image | varchar(255) | 商品图片 |
| price | decimal(10,2) | 单价 |
| specs | varchar(500) | 规格 |
| quantity | int | 数量 |

## 钱包记录表 (tp_wallet_log)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| user_id | int | 用户ID |
| type | tinyint | 类型:1=充值,2=消费,3=退款 |
| amount | decimal(10,2) | 金额 |
| before_balance | decimal(10,2) | 变化前余额 |
| after_balance | decimal(10,2) | 变化后余额 |
| remark | varchar(500) | 备注 |

## 系统配置表 (tp_config)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| group | varchar(50) | 分组 |
| name | varchar(100) | 配置名称 |
| value | text | 配置值 |
| description | varchar(500) | 描述 |

## 数据字典表 (tp_dict)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| group | varchar(50) | 分组 |
| value | varchar(100) | 值 |
| label | varchar(100) | 标签 |
| sort | int | 排序 |
| status | tinyint | 状态 |

## 文件表 (tp_file)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| group | varchar(50) | 分组 |
| filename | varchar(255) | 文件名 |
| filepath | varchar(255) | 文件路径 |
| filesize | int | 文件大小 |
| ext | varchar(20) | 扩展名 |
| mime | varchar(100) | MIME类型 |

## 操作日志表 (tp_log)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint | 主键 |
| type | tinyint | 类型 |
| content | varchar(500) | 内容 |
| admin_id | int | 管理员ID |
| ip | varchar(50) | IP地址 |
| url | varchar(255) | URL |
| method | varchar(10) | 请求方法 |

## 消息通知表 (tp_notify)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| user_id | int | 接收用户ID |
| admin_id | int | 接收管理员ID |
| type | tinyint | 类型 |
| title | varchar(100) | 标题 |
| content | text | 内容 |
| is_read | tinyint | 是否已读 |
| read_time | datetime | 阅读时间 |

## 任务队列表 (tp_queue)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint | 主键 |
| job | varchar(100) | 任务名称 |
| data | text | 任务数据 |
| status | tinyint | 状态 |
| attempts | tinyint | 重试次数 |
| delay | int | 延迟秒数 |
| available_at | datetime | 可执行时间 |