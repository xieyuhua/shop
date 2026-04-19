-- B2B2C多用户商城数据库结构
-- PHP 8.1 + ThinkPHP 8.1

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- 用户表
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `nickname` varchar(50) DEFAULT NULL COMMENT '昵称',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `avatar` varchar(255) DEFAULT '/static/images/avatar.png' COMMENT '头像',
  `mobile` varchar(20) DEFAULT NULL COMMENT '手机号',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `gender` tinyint unsigned DEFAULT 0 COMMENT '性别:0未知,1男,2女',
  `birthday` int unsigned DEFAULT 0 COMMENT '生日',
  `level` tinyint unsigned DEFAULT 1 COMMENT '会员等级',
  `points` int unsigned DEFAULT 0 COMMENT '积分',
  `balance` decimal(10,2) DEFAULT 0.00 COMMENT '余额',
  `frozen_balance` decimal(10,2) DEFAULT 0.00 COMMENT '冻结余额',
  `status` tinyint unsigned DEFAULT 1 COMMENT '状态:0禁用,1正常',
  `last_login_time` int unsigned DEFAULT 0 COMMENT '最后登录时间',
  `last_login_ip` varchar(50) DEFAULT NULL COMMENT '最后登录IP',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  `delete_time` int unsigned DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `mobile` (`mobile`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- ----------------------------
-- 用户收货地址表
-- ----------------------------
DROP TABLE IF EXISTS `user_address`;
CREATE TABLE `user_address` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `consignee` varchar(50) NOT NULL COMMENT '收货人',
  `mobile` varchar(20) NOT NULL COMMENT '手机号',
  `province` varchar(50) NOT NULL COMMENT '省份',
  `city` varchar(50) NOT NULL COMMENT '城市',
  `district` varchar(50) NOT NULL COMMENT '区县',
  `address` varchar(255) NOT NULL COMMENT '详细地址',
  `zipcode` varchar(20) DEFAULT NULL COMMENT '邮编',
  `is_default` tinyint unsigned DEFAULT 0 COMMENT '是否默认:0否,1是',
  `longitude` decimal(10,6) DEFAULT 0.000000 COMMENT '经度',
  `latitude` decimal(10,6) DEFAULT 0.000000 COMMENT '纬度',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户收货地址表';

-- ----------------------------
-- 店铺表
-- ----------------------------
DROP TABLE IF EXISTS `shop`;
CREATE TABLE `shop` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `shop_name` varchar(100) NOT NULL COMMENT '店铺名称',
  `shop_logo` varchar(255) DEFAULT NULL COMMENT '店铺Logo',
  `shop_banner` varchar(255) DEFAULT NULL COMMENT '店铺Banner',
  `shop_desc` text COMMENT '店铺描述',
  `contact_name` varchar(50) NOT NULL COMMENT '联系人',
  `contact_mobile` varchar(20) NOT NULL COMMENT '联系电话',
  `contact_email` varchar(100) DEFAULT NULL COMMENT '联系邮箱',
  `province` varchar(50) DEFAULT NULL COMMENT '省份',
  `city` varchar(50) DEFAULT NULL COMMENT '城市',
  `district` varchar(50) DEFAULT NULL COMMENT '区县',
  `address` varchar(255) DEFAULT NULL COMMENT '详细地址',
  `category_id` bigint unsigned DEFAULT 0 COMMENT '店铺分类ID',
  `business_license` varchar(255) DEFAULT NULL COMMENT '营业执照',
  `status` tinyint unsigned DEFAULT 0 COMMENT '状态:0待审核,1已开通,2已拒绝,3已关闭',
  `is_recommend` tinyint unsigned DEFAULT 0 COMMENT '是否推荐',
  `sort` int unsigned DEFAULT 100 COMMENT '排序',
  `total_sales` int unsigned DEFAULT 0 COMMENT '总销量',
  `total_amount` decimal(12,2) DEFAULT 0.00 COMMENT '总销售额',
  `frozen_amount` decimal(12,2) DEFAULT 0.00 COMMENT '冻结金额',
  `commission_rate` decimal(5,4) DEFAULT 0.0500 COMMENT '佣金比例',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='店铺表';

-- ----------------------------
-- 商品分类表
-- ----------------------------
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pid` bigint unsigned DEFAULT 0 COMMENT '上级ID',
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `icon` varchar(255) DEFAULT NULL COMMENT '分类图标',
  `image` varchar(255) DEFAULT NULL COMMENT '分类图片',
  `sort` int unsigned DEFAULT 100 COMMENT '排序',
  `is_show` tinyint unsigned DEFAULT 1 COMMENT '是否显示',
  `is_nav` tinyint unsigned DEFAULT 0 COMMENT '是否导航',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `is_show` (`is_show`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品分类表';

-- ----------------------------
-- 商品表
-- ----------------------------
DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` bigint unsigned NOT NULL COMMENT '店铺ID',
  `category_id` bigint unsigned NOT NULL COMMENT '分类ID',
  `name` varchar(200) NOT NULL COMMENT '商品名称',
  `subtitle` varchar(255) DEFAULT NULL COMMENT '副标题',
  `image` varchar(255) NOT NULL COMMENT '商品图片',
  `images` text COMMENT '商品图片组',
  `price` decimal(10,2) NOT NULL COMMENT '商品价格',
  `original_price` decimal(10,2) DEFAULT 0.00 COMMENT '原价',
  `cost_price` decimal(10,2) DEFAULT 0.00 COMMENT '成本价',
  `stock` int unsigned DEFAULT 0 COMMENT '库存',
  `sales` int unsigned DEFAULT 0 COMMENT '销量',
  `weight` decimal(8,2) DEFAULT 0.00 COMMENT '重量',
  `unit` varchar(20) DEFAULT '件' COMMENT '单位',
  `spec_type` tinyint unsigned DEFAULT 0 COMMENT '规格类型:0单规格,1多规格',
  `content` mediumtext COMMENT '商品详情',
  `is_on_sale` tinyint unsigned DEFAULT 1 COMMENT '是否上架',
  `is_recommend` tinyint unsigned DEFAULT 0 COMMENT '是否推荐',
  `is_new` tinyint unsigned DEFAULT 0 COMMENT '是否新品',
  `freight_type` tinyint unsigned DEFAULT 0 COMMENT '运费类型:0固定运费,1按件计算',
  `freight_id` bigint unsigned DEFAULT 0 COMMENT '运费模板ID',
  `freight_money` decimal(10,2) DEFAULT 0.00 COMMENT '运费金额',
  `status` tinyint unsigned DEFAULT 0 COMMENT '审核状态:0待审核,1已通过,2已拒绝',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `shop_id` (`shop_id`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`),
  KEY `is_on_sale` (`is_on_sale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品表';

-- ----------------------------
-- 商品SKU表
-- ----------------------------
DROP TABLE IF EXISTS `product_sku`;
CREATE TABLE `product_sku` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL COMMENT '商品ID',
  `sku_name` varchar(255) NOT NULL COMMENT 'SKU名称',
  `sku_code` varchar(50) DEFAULT NULL COMMENT 'SKU编码',
  `price` decimal(10,2) NOT NULL COMMENT '价格',
  `original_price` decimal(10,2) DEFAULT 0.00 COMMENT '原价',
  `cost_price` decimal(10,2) DEFAULT 0.00 COMMENT '成本价',
  `stock` int unsigned DEFAULT 0 COMMENT '库存',
  `sales` int unsigned DEFAULT 0 COMMENT '销量',
  `weight` decimal(8,2) DEFAULT 0.00 COMMENT '重量',
  `image` varchar(255) DEFAULT NULL COMMENT 'SKU图片',
  `specs` text COMMENT '规格属性',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品SKU表';

-- ----------------------------
-- 商品规格表
-- ----------------------------
DROP TABLE IF EXISTS `product_spec`;
CREATE TABLE `product_spec` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL COMMENT '商品ID',
  `name` varchar(50) NOT NULL COMMENT '规格名称',
  `values` text COMMENT '规格值',
  `sort` int unsigned DEFAULT 100 COMMENT '排序',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品规格表';

-- ----------------------------
-- 购物车表
-- ----------------------------
DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `product_id` bigint unsigned NOT NULL COMMENT '商品ID',
  `sku_id` bigint unsigned DEFAULT 0 COMMENT 'SKU ID',
  `shop_id` bigint unsigned NOT NULL COMMENT '店铺ID',
  `num` int unsigned DEFAULT 1 COMMENT '数量',
  `selected` tinyint unsigned DEFAULT 1 COMMENT '是否选中',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='购物车表';

-- ----------------------------
-- 订单表
-- ----------------------------
DROP TABLE IF EXISTS `order`;
CREATE TABLE `order` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `shop_id` bigint unsigned NOT NULL COMMENT '店铺ID',
  `order_no` varchar(32) NOT NULL COMMENT '订单编号',
  `total_num` int unsigned DEFAULT 1 COMMENT '商品数量',
  `total_price` decimal(10,2) DEFAULT 0.00 COMMENT '商品总价',
  `freight_price` decimal(10,2) DEFAULT 0.00 COMMENT '运费',
  `discount_price` decimal(10,2) DEFAULT 0.00 COMMENT '优惠金额',
  `pay_price` decimal(10,2) DEFAULT 0.00 COMMENT '实付金额',
  `points_discount` decimal(10,2) DEFAULT 0.00 COMMENT '积分抵扣',
  `coupon_id` bigint unsigned DEFAULT 0 COMMENT '优惠券ID',
  `coupon_price` decimal(10,2) DEFAULT 0.00 COMMENT '优惠券金额',
  `address_id` bigint unsigned DEFAULT 0 COMMENT '收货地址ID',
  `pay_type` tinyint unsigned DEFAULT 0 COMMENT '支付方式:1微信,2支付宝,3余额',
  `pay_status` tinyint unsigned DEFAULT 0 COMMENT '支付状态:0未支付,1已支付',
  `pay_time` int unsigned DEFAULT 0 COMMENT '支付时间',
  `delivery_type` tinyint unsigned DEFAULT 0 COMMENT '配送方式',
  `express_company` varchar(50) DEFAULT NULL COMMENT '快递公司',
  `express_no` varchar(50) DEFAULT NULL COMMENT '快递单号',
  `delivery_time` int unsigned DEFAULT 0 COMMENT '发货时间',
  `receive_time` int unsigned DEFAULT 0 COMMENT '收货时间',
  `complete_time` int unsigned DEFAULT 0 COMMENT '完成时间',
  `order_status` tinyint unsigned DEFAULT 0 COMMENT '订单状态:0待付款,1待发货,2待收货,3待评价,4已完成,5已取消,6退款中,7已退款',
  `remark` varchar(500) DEFAULT NULL COMMENT '订单备注',
  `is_comment` tinyint unsigned DEFAULT 0 COMMENT '是否已评价',
  `is_delete` tinyint unsigned DEFAULT 0 COMMENT '是否删除',
  `cancel_time` int unsigned DEFAULT 0 COMMENT '取消时间',
  `cancel_reason` varchar(255) DEFAULT NULL COMMENT '取消原因',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`),
  KEY `user_id` (`user_id`),
  KEY `shop_id` (`shop_id`),
  KEY `order_status` (`order_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单表';

-- ----------------------------
-- 订单商品表
-- ----------------------------
DROP TABLE IF EXISTS `order_goods`;
CREATE TABLE `order_goods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL COMMENT '订单ID',
  `product_id` bigint unsigned NOT NULL COMMENT '商品ID',
  `sku_id` bigint unsigned DEFAULT 0 COMMENT 'SKU ID',
  `shop_id` bigint unsigned NOT NULL COMMENT '店铺ID',
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `goods_name` varchar(200) NOT NULL COMMENT '商品名称',
  `goods_image` varchar(255) DEFAULT NULL COMMENT '商品图片',
  `sku_name` varchar(255) DEFAULT NULL COMMENT 'SKU名称',
  `price` decimal(10,2) NOT NULL COMMENT '商品单价',
  `num` int unsigned DEFAULT 1 COMMENT '购买数量',
  `total_price` decimal(10,2) DEFAULT 0.00 COMMENT '总价',
  `is_comment` tinyint unsigned DEFAULT 0 COMMENT '是否已评价',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单商品表';

-- ----------------------------
-- 订单售后表
-- ----------------------------
DROP TABLE IF EXISTS `order_aftersale`;
CREATE TABLE `order_aftersale` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL COMMENT '订单ID',
  `order_goods_id` bigint unsigned DEFAULT 0 COMMENT '订单商品ID',
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `shop_id` bigint unsigned NOT NULL COMMENT '店铺ID',
  `type` tinyint unsigned DEFAULT 1 COMMENT '售后类型:1仅退款,2退货退款',
  `reason` varchar(255) NOT NULL COMMENT '退款原因',
  `description` text COMMENT '退款说明',
  `images` text COMMENT '图片凭证',
  `refund_type` tinyint unsigned DEFAULT 1 COMMENT '退款方式:1退款,2退货退款',
  `refund_money` decimal(10,2) DEFAULT 0.00 COMMENT '退款金额',
  `status` tinyint unsigned DEFAULT 0 COMMENT '状态:0待处理,1已同意,2已拒绝,3待退货,4已完成,5已关闭',
  `reject_reason` varchar(255) DEFAULT NULL COMMENT '拒绝原因',
  `express_company` varchar(50) DEFAULT NULL COMMENT '快递公司',
  `express_no` varchar(50) DEFAULT NULL COMMENT '快递单号',
  `return_time` int unsigned DEFAULT 0 COMMENT '退货时间',
  `handle_time` int unsigned DEFAULT 0 COMMENT '处理时间',
  `complete_time` int unsigned DEFAULT 0 COMMENT '完成时间',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `shop_id` (`shop_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单售后表';

-- ----------------------------
-- 订单评价表
-- ----------------------------
DROP TABLE IF EXISTS `order_evaluate`;
CREATE TABLE `order_evaluate` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL COMMENT '订单ID',
  `order_goods_id` bigint unsigned DEFAULT 0 COMMENT '订单商品ID',
  `product_id` bigint unsigned NOT NULL COMMENT '商品ID',
  `shop_id` bigint unsigned NOT NULL COMMENT '店铺ID',
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `score` tinyint unsigned DEFAULT 5 COMMENT '综合评分',
  `score_describe` tinyint unsigned DEFAULT 5 COMMENT '描述相符',
  `score_service` tinyint unsigned DEFAULT 5 COMMENT '服务态度',
  `score_logistics` tinyint unsigned DEFAULT 5 COMMENT '物流服务',
  `content` text COMMENT '评价内容',
  `images` text COMMENT '评价图片',
  `reply` text COMMENT '商家回复',
  `reply_time` int unsigned DEFAULT 0 COMMENT '回复时间',
  `is_show` tinyint unsigned DEFAULT 1 COMMENT '是否显示',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单评价表';

-- ----------------------------
-- 优惠券表
-- ----------------------------
DROP TABLE IF EXISTS `coupon`;
CREATE TABLE `coupon` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` bigint unsigned DEFAULT 0 COMMENT '店铺ID:0为平台优惠券',
  `name` varchar(50) NOT NULL COMMENT '优惠券名称',
  `type` tinyint unsigned DEFAULT 1 COMMENT '类型:1满减券,2折扣券',
  `money` decimal(10,2) DEFAULT 0.00 COMMENT '满减金额',
  `discount` decimal(5,2) DEFAULT 0.00 COMMENT '折扣率',
  `min_money` decimal(10,2) DEFAULT 0.00 COMMENT '最低消费金额',
  `max_money` decimal(10,2) DEFAULT 0.00 COMMENT '最高优惠金额',
  `send_type` tinyint unsigned DEFAULT 1 COMMENT '发放方式:1公开领取,2手动发放,3订单赠送,4注册赠送',
  `total_num` int unsigned DEFAULT 0 COMMENT '发放总数:0不限制',
  `send_num` int unsigned DEFAULT 0 COMMENT '已发放数量',
  `receive_num` int unsigned DEFAULT 0 COMMENT '已领取数量',
  `use_num` int unsigned DEFAULT 0 COMMENT '已使用数量',
  `each_limit` int unsigned DEFAULT 1 COMMENT '每人限领数量',
  `start_time` int unsigned DEFAULT 0 COMMENT '开始时间',
  `end_time` int unsigned DEFAULT 0 COMMENT '结束时间',
  `valid_days` int unsigned DEFAULT 0 COMMENT '有效期天数:0表示使用时间范围',
  `category_ids` varchar(255) DEFAULT NULL COMMENT '可用分类ID',
  `product_ids` varchar(255) DEFAULT NULL COMMENT '可用商品ID',
  `is_show` tinyint unsigned DEFAULT 1 COMMENT '是否显示',
  `status` tinyint unsigned DEFAULT 1 COMMENT '状态:0禁用,1启用',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `shop_id` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='优惠券表';

-- ----------------------------
-- 用户优惠券表
-- ----------------------------
DROP TABLE IF EXISTS `user_coupon`;
CREATE TABLE `user_coupon` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `coupon_id` bigint unsigned NOT NULL COMMENT '优惠券ID',
  `shop_id` bigint unsigned DEFAULT 0 COMMENT '店铺ID',
  `order_id` bigint unsigned DEFAULT 0 COMMENT '使用订单ID',
  `status` tinyint unsigned DEFAULT 0 COMMENT '状态:0未使用,1已使用,2已过期',
  `start_time` int unsigned DEFAULT 0 COMMENT '开始时间',
  `end_time` int unsigned DEFAULT 0 COMMENT '结束时间',
  `use_time` int unsigned DEFAULT 0 COMMENT '使用时间',
  `create_time` int unsigned DEFAULT 0 COMMENT '领取时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `coupon_id` (`coupon_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户优惠券表';

-- ----------------------------
-- 支付记录表
-- ----------------------------
DROP TABLE IF EXISTS `payment`;
CREATE TABLE `payment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `order_id` bigint unsigned NOT NULL COMMENT '订单ID',
  `trade_no` varchar(64) NOT NULL COMMENT '交易号',
  `out_trade_no` varchar(64) DEFAULT NULL COMMENT '外部交易号',
  `type` tinyint unsigned DEFAULT 1 COMMENT '支付类型:1微信,2支付宝,3余额',
  `amount` decimal(10,2) NOT NULL COMMENT '支付金额',
  `status` tinyint unsigned DEFAULT 0 COMMENT '状态:0待支付,1成功,2失败',
  `pay_time` int unsigned DEFAULT 0 COMMENT '支付时间',
  `notify_data` text COMMENT '回调数据',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `trade_no` (`trade_no`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付记录表';

-- ----------------------------
-- 积分日志表
-- ----------------------------
DROP TABLE IF EXISTS `points_log`;
CREATE TABLE `points_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `type` tinyint unsigned DEFAULT 1 COMMENT '类型:1收入,2支出',
  `points` int NOT NULL COMMENT '积分',
  `balance` int NOT NULL COMMENT '余额',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `source_type` varchar(50) DEFAULT NULL COMMENT '来源类型',
  `source_id` bigint unsigned DEFAULT 0 COMMENT '来源ID',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='积分日志表';

-- ----------------------------
-- 余额日志表
-- ----------------------------
DROP TABLE IF EXISTS `balance_log`;
CREATE TABLE `balance_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT '用户ID',
  `shop_id` bigint unsigned DEFAULT 0 COMMENT '店铺ID',
  `type` tinyint unsigned DEFAULT 1 COMMENT '类型:1收入,2支出,3冻结,4解冻',
  `money` decimal(10,2) NOT NULL COMMENT '金额',
  `balance` decimal(10,2) NOT NULL COMMENT '变动金额',
  `before_money` decimal(10,2) DEFAULT 0.00 COMMENT '变动前金额',
  `after_money` decimal(10,2) DEFAULT 0.00 COMMENT '变动后金额',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `source_type` varchar(50) DEFAULT NULL COMMENT '来源类型',
  `source_id` bigint unsigned DEFAULT 0 COMMENT '来源ID',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `shop_id` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='余额日志表';

-- ----------------------------
-- 管理员表
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `nickname` varchar(50) DEFAULT NULL COMMENT '昵称',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `role_id` bigint unsigned DEFAULT 0 COMMENT '角色ID',
  `mobile` varchar(20) DEFAULT NULL COMMENT '手机号',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `status` tinyint unsigned DEFAULT 1 COMMENT '状态:0禁用,1正常',
  `login_num` int unsigned DEFAULT 0 COMMENT '登录次数',
  `last_login_time` int unsigned DEFAULT 0 COMMENT '最后登录时间',
  `last_login_ip` varchar(50) DEFAULT NULL COMMENT '最后登录IP',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员表';

-- ----------------------------
-- 管理员角色表
-- ----------------------------
DROP TABLE IF EXISTS `admin_role`;
CREATE TABLE `admin_role` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `rules` text COMMENT '权限规则',
  `status` tinyint unsigned DEFAULT 1 COMMENT '状态:0禁用,1启用',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员角色表';

-- ----------------------------
-- 系统设置表
-- ----------------------------
DROP TABLE IF EXISTS `setting`;
CREATE TABLE `setting` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL COMMENT '配置键',
  `group` varchar(50) DEFAULT 'basic' COMMENT '配置分组',
  `value` text COMMENT '配置值',
  `type` varchar(20) DEFAULT 'text' COMMENT '配置类型',
  `create_time` int unsigned DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统设置表';

-- ----------------------------
-- 初始化数据
-- ----------------------------

-- 添加超级管理员
INSERT INTO `admin` (`username`, `password`, `nickname`, `role_id`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '超级管理员', 0, 1);

-- 添加管理员角色
INSERT INTO `admin_role` (`name`, `rules`, `status`) VALUES
('超级管理员', '["*"]', 1),
('运营主管', '["dashboard","user","product","order","coupon"]', 1);

-- 添加默认分类
INSERT INTO `category` (`pid`, `name`, `icon`, `sort`, `is_show`, `is_nav`) VALUES
(0, '服装鞋包', '/static/images/category/1.png', 1, 1, 1),
(0, '数码电子', '/static/images/category/2.png', 2, 1, 1),
(0, '食品生鲜', '/static/images/category/3.png', 3, 1, 1),
(0, '美妆护肤', '/static/images/category/4.png', 4, 1, 1),
(0, '家居家纺', '/static/images/category/5.png', 5, 1, 1),
(0, '母婴用品', '/static/images/category/6.png', 6, 1, 1);

-- 添加子分类
INSERT INTO `category` (`pid`, `name`, `sort`, `is_show`, `is_nav`) VALUES
(1, '女装', 1, 1, 0),
(1, '男装', 2, 1, 0),
(1, '童装', 3, 1, 0),
(1, '箱包', 4, 1, 0),
(2, '手机', 1, 1, 0),
(2, '电脑', 2, 1, 0),
(2, '数码配件', 3, 1, 0),
(3, '水果', 1, 1, 0),
(3, '蔬菜', 2, 1, 0),
(3, '零食', 3, 1, 0);

-- 添加系统设置
INSERT INTO `setting` (`key`, `group`, `value`, `type`) VALUES
('mall_name', 'basic', '多用户B2B2C商城', 'text'),
('mall_logo', 'basic', '/static/images/logo.png', 'image'),
('mall_phone', 'basic', '400-000-0000', 'text'),
('mall_qq', 'basic', '12345678', 'text'),
('copyright', 'basic', '© 2024 All Rights Reserved', 'text'),
('icp', 'basic', 'ICP备XXXXXXXX号', 'text'),
('express_money', 'basic', '10', 'number'),
('free_express_money', 'basic', '99', 'number');

SET FOREIGN_KEY_CHECKS = 1;
