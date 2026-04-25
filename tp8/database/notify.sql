-- 消息通知表
CREATE TABLE IF NOT EXISTS `tp_notify` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '接收用户ID',
  `admin_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '接收管理员ID',
  `type` TINYINT NOT NULL DEFAULT 1 COMMENT '类型:1=系统,2=订单,3=用户,4=商品',
  `title` VARCHAR(100) NOT NULL COMMENT '标题',
  `content` TEXT NOT NULL COMMENT '内容',
  `is_read` TINYINT NOT NULL DEFAULT 0 COMMENT '是否已读:0=未读,1=已读',
  `read_time` DATETIME DEFAULT NULL COMMENT '阅读时间',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_user_id` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='消息通知表';

-- 插入测试通知
INSERT INTO `tp_notify` (`user_id`, `type`, `title`, `content`, `create_time`) VALUES 
(1, 1, '欢迎使用', '欢迎使用商城后台管理系统', NOW());