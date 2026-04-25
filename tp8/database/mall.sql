-- 商城数据库表结构
-- MySQL 8.0+

SET NAMES utf8mb4;

-- 管理员表
CREATE TABLE IF NOT EXISTS `tp_admin` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE COMMENT '用户名',
  `password` VARCHAR(100) NOT NULL COMMENT '密码',
  `nickname` VARCHAR(50) DEFAULT NULL COMMENT '昵称',
  `avatar` VARCHAR(255) DEFAULT NULL COMMENT '头像',
  `phone` VARCHAR(20) DEFAULT NULL COMMENT '手机号',
  `email` VARCHAR(100) DEFAULT NULL COMMENT '邮箱',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=正常',
  `login_ip` VARCHAR(50) DEFAULT NULL COMMENT '最后登录IP',
  `login_time` DATETIME DEFAULT NULL COMMENT '最后登录时间',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员表';

-- 会员表
CREATE TABLE IF NOT EXISTS `tp_user` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) DEFAULT NULL COMMENT '用户名',
  `mobile` VARCHAR(20) NOT NULL UNIQUE COMMENT '手机号',
  `email` VARCHAR(100) DEFAULT NULL COMMENT '邮箱',
  `password` VARCHAR(100) NOT NULL COMMENT '密码',
  `nickname` VARCHAR(50) DEFAULT NULL COMMENT '昵称',
  `avatar` VARCHAR(255) DEFAULT NULL COMMENT '头像',
  `gender` TINYINT DEFAULT 0 COMMENT '性别:0=未知,1=男,2=女',
  `balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '账户余额',
  `points` INT NOT NULL DEFAULT 0 COMMENT '积分',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=正常',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='会员表';

-- 商品分类表
CREATE TABLE IF NOT EXISTS `tp_category` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `pid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
  `name` VARCHAR(100) NOT NULL COMMENT '分类名称',
  `slug` VARCHAR(100) NOT NULL COMMENT '分类别名',
  `image` VARCHAR(255) DEFAULT NULL COMMENT '分类图片',
  `description` VARCHAR(500) DEFAULT NULL COMMENT '描述',
  `sort` INT NOT NULL DEFAULT 100 COMMENT '排序',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '状态:0=隐藏,1=显示',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品分类表';

-- 商品表
CREATE TABLE IF NOT EXISTS `tp_product` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NOT NULL COMMENT '分类ID',
  `name` VARCHAR(200) NOT NULL COMMENT '商品名称',
  `slug` VARCHAR(200) NOT NULL COMMENT '商品别名',
  `image` VARCHAR(255) DEFAULT NULL COMMENT '商品图片',
  `images` TEXT DEFAULT NULL COMMENT '商品相册(JSON)',
  `description` TEXT DEFAULT NULL COMMENT '商品描述',
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '销售价格',
  `original_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '原价',
  `cost_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '成本价',
  `stock` INT NOT NULL DEFAULT 0 COMMENT '库存',
  `sales` INT NOT NULL DEFAULT 0 COMMENT '销量',
  `virtual_sales` INT NOT NULL DEFAULT 0 COMMENT '虚拟销量',
  `specs` TEXT DEFAULT NULL COMMENT '规格(JSON)',
  `is_spec` TINYINT NOT NULL DEFAULT 0 COMMENT '是否多规格',
  `is_hot` TINYINT NOT NULL DEFAULT 0 COMMENT '是否热卖',
  `is_new` TINYINT NOT NULL DEFAULT 0 COMMENT '是否新品',
  `is_recommend` TINYINT NOT NULL DEFAULT 0 COMMENT '是否推荐',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '状态:0=下架,1=上架',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_category_id` (`category_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品表';

-- 购物车表
CREATE TABLE IF NOT EXISTS `tp_cart` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `product_id` INT UNSIGNED NOT NULL COMMENT '商品ID',
  `product_name` VARCHAR(200) NOT NULL COMMENT '商品名称',
  `product_image` VARCHAR(255) DEFAULT NULL COMMENT '商品图片',
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '单价',
  `specs` VARCHAR(500) DEFAULT NULL COMMENT '规格',
  `quantity` INT NOT NULL DEFAULT 1 COMMENT '数量',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='购物车表';

-- 订单表
CREATE TABLE IF NOT EXISTS `tp_order` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_no` VARCHAR(50) NOT NULL UNIQUE COMMENT '订单编号',
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `receiver_name` VARCHAR(50) NOT NULL COMMENT '收货人',
  `receiver_mobile` VARCHAR(20) NOT NULL COMMENT '联系电话',
  `receiver_province` VARCHAR(50) DEFAULT NULL COMMENT '省份',
  `receiver_city` VARCHAR(50) DEFAULT NULL COMMENT '城市',
  `receiver_district` VARCHAR(50) DEFAULT NULL COMMENT '区县',
  `receiver_address` VARCHAR(255) NOT NULL COMMENT '详细地址',
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '订单总金额',
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '优惠金额',
  `pay_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '实付金额',
  `points_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '积分抵扣',
  `freight_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '运费',
  `pay_type` TINYINT DEFAULT NULL COMMENT '支付方式',
  `pay_time` DATETIME DEFAULT NULL COMMENT '支付时间',
  `pay_no` VARCHAR(100) DEFAULT NULL COMMENT '支付流水号',
  `express_company` VARCHAR(100) DEFAULT NULL COMMENT '快递公司',
  `express_no` VARCHAR(100) DEFAULT NULL COMMENT '快递单号',
  `ship_time` DATETIME DEFAULT NULL COMMENT '发货时间',
  `status` TINYINT NOT NULL DEFAULT 0 COMMENT '状态:0=待付款,1=待发货,2=待收货,3=已完成,4=已取消,5=已退款',
  `remark` VARCHAR(500) DEFAULT NULL COMMENT '订单备注',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_user_id` (`user_id`),
  KEY `idx_order_no` (`order_no`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单表';

-- 订单商品表
CREATE TABLE IF NOT EXISTS `tp_order_item` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL COMMENT '订单ID',
  `order_no` VARCHAR(50) NOT NULL COMMENT '订单编号',
  `product_id` INT UNSIGNED NOT NULL COMMENT '商品ID',
  `product_name` VARCHAR(200) NOT NULL COMMENT '商品名称',
  `product_image` VARCHAR(255) DEFAULT NULL COMMENT '商品图片',
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '单价',
  `specs` VARCHAR(500) DEFAULT NULL COMMENT '规格',
  `quantity` INT NOT NULL DEFAULT 1 COMMENT '数量',
  `total_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '小计',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单商品表';

-- 钱包记录表
CREATE TABLE IF NOT EXISTS `tp_wallet_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `type` TINYINT NOT NULL COMMENT '类型:1=充值,2=消费,3=退款',
  `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '金额',
  `before_balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '变化前余额',
  `after_balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '变化后余额',
  `remark` VARCHAR(500) DEFAULT NULL COMMENT '备注',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='钱包记录表';

-- 系统配置表
CREATE TABLE IF NOT EXISTS `tp_config` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `group` VARCHAR(50) NOT NULL DEFAULT 'basic' COMMENT '分组',
  `name` VARCHAR(100) NOT NULL COMMENT '配置名称',
  `value` TEXT DEFAULT NULL COMMENT '配置值',
  `description` VARCHAR(500) DEFAULT NULL COMMENT '描述',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_group` (`group`),
  UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置表';

-- 数据字典表
CREATE TABLE IF NOT EXISTS `tp_dict` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `group` VARCHAR(50) NOT NULL DEFAULT 'default' COMMENT '分组',
  `value` VARCHAR(100) NOT NULL COMMENT '值',
  `label` VARCHAR(100) NOT NULL COMMENT '标签',
  `sort` INT NOT NULL DEFAULT 100 COMMENT '排序',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=正常',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='数据字典表';

-- 文件表
CREATE TABLE IF NOT EXISTS `tp_file` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `group` VARCHAR(50) NOT NULL DEFAULT 'default' COMMENT '分组',
  `filename` VARCHAR(255) NOT NULL COMMENT '文件名',
  `filepath` VARCHAR(255) NOT NULL COMMENT '文件路径',
  `filesize` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件大小',
  `filetype` VARCHAR(50) DEFAULT NULL COMMENT '文件类型',
  `ext` VARCHAR(20) DEFAULT NULL COMMENT '扩展名',
  `mime` VARCHAR(100) DEFAULT NULL COMMENT 'MIME类型',
  `use_num` INT NOT NULL DEFAULT 0 COMMENT '引用次数',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=正常',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文件表';

-- 操作日志表
CREATE TABLE IF NOT EXISTS `tp_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` TINYINT NOT NULL DEFAULT 0 COMMENT '类型:1=登录,2=退出,3=创建,4=更新,5=删除',
  `content` VARCHAR(500) DEFAULT NULL COMMENT '内容',
  `admin_id` INT UNSIGNED DEFAULT NULL COMMENT '管理员ID',
  `admin_name` VARCHAR(50) DEFAULT NULL COMMENT '管理员',
  `ip` VARCHAR(50) DEFAULT NULL COMMENT 'IP地址',
  `url` VARCHAR(255) DEFAULT NULL COMMENT 'URL',
  `method` VARCHAR(10) DEFAULT NULL COMMENT '请求方法',
  `param` TEXT DEFAULT NULL COMMENT '请求参数',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_type` (`type`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='操作日志表';

-- 插入超级管理员 (密码: admin123)
INSERT INTO `tp_admin` (`username`, `password`, `nickname`, `status`) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '超级管理员', 1);

-- 插入测试会员 (密码: 123456)
INSERT INTO `tp_user` (`mobile`, `password`, `nickname`, `status`) VALUES 
('13800138000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '测试用户', 1);

-- 插入商品分类
INSERT INTO `tp_category` (`name`, `slug`, `sort`, `status`) VALUES 
('数码电子', 'digital', 1, 1),
('服装鞋包', 'clothing', 2, 1),
('食品生鲜', 'food', 3, 1),
('家居生活', 'home', 4, 1),
('图书音像', 'book', 5, 1);

-- 插入商品
INSERT INTO `tp_product` (`category_id`, `name`, `slug`, `price`, `original_price`, `stock`, `sales`, `status`) VALUES 
(1, 'iPhone 15 Pro', 'iphone-15-pro', 8999.00, 9999.00, 100, 50, 1),
(1, 'MacBook Pro 14', 'macbook-pro-14', 14999.00, 16999.00, 50, 20, 1),
(1, 'AirPods Pro', 'airpods-pro', 1999.00, 2299.00, 200, 100, 1),
(2, '秋冬毛衣', 'autumn-sweater', 299.00, 399.00, 500, 80, 1),
(2, '牛仔裤', 'jeans-pants', 199.00, 299.00, 300, 60, 1),
(3, '进口车厘子', 'cherry', 99.00, 149.00, 1000, 200, 1);

-- 插入系统配置
INSERT INTO `tp_config` (`group`, `name`, `value`, `description`) VALUES 
('basic', 'site_name', '商城系统', '网站名称'),
('basic', 'site_logo', '', '网站Logo'),
('basic', 'site_icp', '', 'ICP备案号'),
('shop', 'free_shipping_amount', '99', '满多少免运费'),
('shop', 'shipping_fee', '10', '运费'),
('shop', 'points_rate', '10', '积分抵现比例'),
('payment', 'wechat_appid', '', '微信AppID'),
('payment', 'wechat_mchid', '', '微信商户号'),
('payment', 'alipay_appid', '', '支付宝AppID');

-- 插入数据字典
INSERT INTO `tp_dict` (`group`, `value`, `label`, `sort`, `status`) VALUES 
('status', '1', '启用', 1, 1),
('status', '0', '禁用', 2, 1),
('gender', '0', '未知', 1, 1),
('gender', '1', '男', 2, 1),
('gender', '2', '女', 3, 1),
('order_status', '0', '待付款', 1, 1),
('order_status', '1', '待发货', 2, 1),
('order_status', '2', '待收货', 3, 1),
('order_status', '3', '已完成', 4, 1),
('order_status', '4', '已取消', 5, 1),
('order_status', '5', '已退款', 6, 1),
('pay_type', '1', '微信支付', 1, 1),
('pay_type', '2', '支付宝', 2, 1),
('pay_type', '3', '余额支付', 3, 1);