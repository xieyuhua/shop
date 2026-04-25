-- 任务队列表
CREATE TABLE IF NOT EXISTS `tp_queue` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `job` VARCHAR(100) NOT NULL COMMENT '任务名称',
  `data` TEXT COMMENT '任务数据(JSON)',
  `status` TINYINT NOT NULL DEFAULT 0 COMMENT '状态:0=待执行,1=执行中,2=成功,3=失败',
  `attempts` TINYINT NOT NULL DEFAULT 0 COMMENT '重试次数',
  `delay` INT NOT NULL DEFAULT 0 COMMENT '延迟秒数',
  `available_at` DATETIME NOT NULL COMMENT '可执行时间',
  `start_time` DATETIME DEFAULT NULL COMMENT '开始时间',
  `finish_time` DATETIME DEFAULT NULL COMMENT '完成时间',
  `result` TEXT COMMENT '执行结果',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_status` (`status`),
  KEY `idx_available_at` (`available_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='任务队列表';