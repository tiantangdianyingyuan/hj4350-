SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE `zjhj_bd_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT '收货人',
  `province_id` int(11) NOT NULL,
  `province` varchar(255) NOT NULL COMMENT '省份名称',
  `city_id` int(11) NOT NULL,
  `city` varchar(255) NOT NULL COMMENT '城市名称',
  `district_id` int(11) NOT NULL,
  `district` varchar(255) NOT NULL COMMENT '县区名称',
  `mobile` varchar(255) NOT NULL COMMENT '联系电话',
  `detail` varchar(1000) NOT NULL COMMENT '详细地址',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `zjhj_bd_copy_store`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '名称',
  `store_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '商城原名',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'url',
  `store_id` int(10) NOT NULL DEFAULT 0 COMMENT '店铺id',
  `ver` int(10) NOT NULL DEFAULT 3 COMMENT '版本',
  `created_at` timestamp(0) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp(0) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp(0) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_delete` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `zjhj_bd_copy_store_cat`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '名称',
  `cat_id` int(10) NOT NULL DEFAULT 0 COMMENT '分类id',
  `store_id` int(10) NOT NULL DEFAULT 0 COMMENT '店铺id',
  `created_at` timestamp(0) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp(0) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp(0) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_delete` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `zjhj_bd_copy_store_goods`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '名称',
  `cat_id` int(10) NOT NULL DEFAULT 0 COMMENT '分类id',
  `goods_id` int(10) NOT NULL DEFAULT 0 COMMENT '分类id',
  `pic_url` varchar(266) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'url',
  `store_id` int(10) NOT NULL DEFAULT 0 COMMENT '店铺id',
  `goods_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '原始数据',
  `is_copy` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已经copy',
  `created_at` timestamp(0) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp(0) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp(0) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_delete` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




CREATE TABLE `zjhj_bd_admin_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `app_max_count` int(11) NOT NULL DEFAULT '-1' COMMENT '创建小程序最大数量-1.无限制',
  `permissions` text NOT NULL COMMENT '账户权限',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `expired_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '账户过期时间',
  `is_delete` int(11) NOT NULL DEFAULT '0',
  `we7_user_id` int(11) NOT NULL COMMENT '默认填0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_admin_register` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `mobile` varchar(255) NOT NULL DEFAULT '' COMMENT '手机号',
  `name` varchar(45) NOT NULL DEFAULT '' COMMENT '姓名/企业名',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '申请原因',
  `wechat_id` varchar(64) NOT NULL DEFAULT '' COMMENT '微信号',
  `id_card_front_pic` varchar(2000) NOT NULL DEFAULT '' COMMENT '身份证正面',
  `id_card_back_pic` varchar(2000) NOT NULL DEFAULT '' COMMENT '身份证反面',
  `business_pic` varchar(2000) NOT NULL DEFAULT '' COMMENT '营业执照',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审核状态：0=待审核，1=通过，2=不通过',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_aliapp_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `appid` varchar(32) NOT NULL,
  `app_private_key` varchar(2000) NOT NULL,
  `alipay_public_key` varchar(2000) NOT NULL,
  `cs_tnt_inst_id` varchar(32) NOT NULL DEFAULT '',
  `cs_scene` varchar(32) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_aliapp_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `tpl_name` varchar(255) NOT NULL,
  `tpl_id` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL DEFAULT '0',
  `article_cat_id` int(11) NOT NULL COMMENT '分类id：1=关于我们，2=服务中心 , 3=拼团',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0隐藏 1显示',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` longtext NOT NULL COMMENT '内容',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_delete` smallint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `deleted_at` timestamp NOT NULL COMMENT '删除时间',
  `created_at` timestamp NOT NULL COMMENT '创建时间',
  `updated_at` timestamp NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `store_id` (`mall_id`) USING BTREE,
  KEY `is_delete` (`is_delete`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_attachment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `storage_id` int(11) NOT NULL,
  `attachment_group_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `mall_id` int(11) NOT NULL DEFAULT '0',
  `mch_id` int(11) NOT NULL DEFAULT '0' COMMENT '多商户id',
  `name` varchar(128) NOT NULL,
  `size` int(11) NOT NULL COMMENT '大小：字节',
  `url` varchar(2080) NOT NULL,
  `thumb_url` varchar(2080) NOT NULL DEFAULT '',
  `type` tinyint(2) NOT NULL COMMENT '类型：1=图片，2=视频',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='附件、文件';


CREATE TABLE `zjhj_bd_attachment_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL,
  `is_delete` smallint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_attachment_storage` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '存储类型：1=本地，2=阿里云，3=腾讯云，4=七牛',
  `config` longtext NOT NULL COMMENT '存储配置',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0=未启用，1=已启用',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='附件存储器';


CREATE TABLE `zjhj_bd_auth_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `creator_id` int(11) NOT NULL COMMENT '创建者ID',
  `mall_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL DEFAULT '',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '角色描述、备注',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;


CREATE TABLE `zjhj_bd_auth_role_permission` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permissions` longtext NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;


CREATE TABLE `zjhj_bd_auth_role_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;


CREATE TABLE `zjhj_bd_balance_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '类型：1=收入，2=支出',
  `money` decimal(10,2) NOT NULL COMMENT '变动金额',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '变动说明',
  `custom_desc` longtext NOT NULL COMMENT '自定义详细说明|记录',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL DEFAULT '0',
  `pic_url` varchar(2080) NOT NULL COMMENT '图片',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `page_url` varchar(2048) NOT NULL DEFAULT '' COMMENT '页面路径',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL COMMENT '创建时间',
  `deleted_at` timestamp NOT NULL COMMENT '删除时间',
  `updated_at` timestamp NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_bargain_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `banner_id` int(11) NOT NULL,
  `mall_id` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='砍价轮播图';


CREATE TABLE `zjhj_bd_bargain_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL,
  `mall_id` int(11) NOT NULL,
  `min_price` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '最低价',
  `begin_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '活动开始时间',
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '活动结束时间',
  `time` int(11) NOT NULL DEFAULT '0' COMMENT '砍价小时数',
  `status_data` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '砍价方式数据',
  `is_delete` smallint(6) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '活动是否开放 0--不开放 1--开放',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否允许中途下单 1--允许 2--不允许',
  `stock` int(11) NOT NULL DEFAULT '0' COMMENT '活动库存',
  `initiator` int(11) NOT NULL DEFAULT '0' COMMENT '发起人数',
  `participant` int(11) NOT NULL DEFAULT '0' COMMENT '参与人数',
  `min_price_goods` int(11) NOT NULL DEFAULT '0' COMMENT '砍到最小价格数',
  `underway` int(11) NOT NULL DEFAULT '0' COMMENT '进行中的',
  `success` int(11) NOT NULL DEFAULT '0' COMMENT '成功的',
  `fail` int(11) NOT NULL DEFAULT '0' COMMENT '失败的',
  PRIMARY KEY (`id`),
  UNIQUE KEY `goods_id` (`goods_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='砍价商品设置';


CREATE TABLE `zjhj_bd_bargain_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bargain_goods_id` int(11) NOT NULL COMMENT '砍价商品id',
  `token` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '售价',
  `min_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最低价',
  `time` int(11) NOT NULL DEFAULT '0' COMMENT '砍价时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0--进行中 1--成功 2--失败',
  `bargain_goods_data` longtext NOT NULL COMMENT '砍价设置',
  `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `is_delete` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_bargain_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='砍价设置';


CREATE TABLE `zjhj_bd_bargain_user_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bargain_order_id` int(11) NOT NULL COMMENT '砍价订单ID',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '砍价的金额',
  `is_delete` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `token` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户参与砍价所砍的金额';


CREATE TABLE `zjhj_bd_booking_cats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `sort` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_booking_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `form_data` longtext NOT NULL COMMENT '自定义表单',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_booking_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_booking_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `is_share` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启分销',
  `is_sms` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启短信通知',
  `is_mail` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启邮件通知',
  `is_print` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启订单打印',
  `is_cat` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示分类',
  `is_form` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用默认form',
  `form_data` longtext NOT NULL COMMENT 'form默认表单',
  `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `payment_type` longtext NOT NULL COMMENT '支付方式',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_booking_store` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL COMMENT '商品',
  `attr_id` int(11) NOT NULL COMMENT '商品规格',
  `num` int(11) NOT NULL DEFAULT '1' COMMENT '商品数量',
  `mch_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `sign` varchar(65) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_check_in_award_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `number` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '奖励数量',
  `day` int(11) NOT NULL DEFAULT '0' COMMENT '领取奖励的天数',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '奖励类型integral--积分|balance--余额',
  `status` tinyint(1) NOT NULL COMMENT '领取类型1--普通签到领取|2--连续签到领取|3--累计签到领取',
  `is_delete` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='签到奖励设置';


CREATE TABLE `zjhj_bd_check_in_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启 0--关闭|1--开启',
  `is_remind` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否提醒 0--关闭|1--开启',
  `time` varchar(255) NOT NULL COMMENT '提醒时间',
  `continue_type` tinyint(1) NOT NULL COMMENT '连续签到周期1--不限|2--周清|3--月清',
  `rule` longtext NOT NULL COMMENT '签到规则',
  `is_delete` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='签到设置';


CREATE TABLE `zjhj_bd_check_in_customize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_check_in_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `data` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='签到定时任务执行记录表';


CREATE TABLE `zjhj_bd_check_in_sign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `number` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '签到奖励数量',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '签到奖励类型integral--积分|balance--余额',
  `day` int(11) NOT NULL DEFAULT '1' COMMENT '签到天数',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1--普通签到奖励 2--连续签到奖励 3--累计签到奖励',
  `is_delete` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `token` varchar(255) NOT NULL,
  `award_id` int(11) NOT NULL DEFAULT '0' COMMENT '签到奖励id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='签到领取奖励';


CREATE TABLE `zjhj_bd_check_in_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` int(11) NOT NULL DEFAULT '0' COMMENT '累计签到时间',
  `continue` int(11) NOT NULL DEFAULT '0' COMMENT '连续签到时间',
  `is_remind` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启签到提醒',
  `created_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NOT NULL,
  `continue_start` timestamp NULL DEFAULT NULL COMMENT '连续签到的起始日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='签到插件--用户表';


CREATE TABLE `zjhj_bd_check_in_user_remind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` timestamp NOT NULL,
  `is_remind` tinyint(1) NOT NULL,
  `is_delete` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户签到提醒记录';


CREATE TABLE `zjhj_bd_clerk_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_clerk_user_store_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `clerk_user_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_core_action_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT '操作人ID',
  `model` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '模型名称',
  `model_id` int(11) NOT NULL COMMENT '模模型ID',
  `before_update` text CHARACTER SET utf8mb4 NOT NULL COMMENT '更新之前的数据',
  `after_update` text CHARACTER SET utf8mb4 NOT NULL COMMENT '更新之后的数据',
  `created_at` timestamp NOT NULL COMMENT '创建时间',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `store_id` (`mall_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_german2_ci;


CREATE TABLE `zjhj_bd_core_exception_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `level` tinyint(4) NOT NULL DEFAULT '1' COMMENT '异常等级1.报错|2.警告|3.记录信息',
  `title` mediumtext NOT NULL COMMENT '异常标题',
  `content` mediumtext NOT NULL COMMENT '异常内容',
  `created_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_core_plugin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `display_name` varchar(64) NOT NULL,
  `version` varchar(64) NOT NULL DEFAULT '',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_core_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel` varchar(64) NOT NULL,
  `job` blob NOT NULL,
  `pushed_at` int(11) NOT NULL,
  `ttr` int(11) NOT NULL,
  `delay` int(11) NOT NULL DEFAULT '0',
  `priority` int(11) unsigned NOT NULL DEFAULT '1024',
  `reserved_at` int(11) DEFAULT NULL,
  `attempt` int(11) DEFAULT NULL,
  `done_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `channel` (`channel`),
  KEY `reserved_at` (`reserved_at`),
  KEY `priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_core_queue_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_id` int(11) NOT NULL DEFAULT '0' COMMENT '队列返回值',
  `token` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='队列存储';


CREATE TABLE `zjhj_bd_core_session` (
  `id` char(40) NOT NULL,
  `expire` int(11) DEFAULT NULL,
  `DATA` blob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_core_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(64) CHARACTER SET utf8 NOT NULL,
  `delay_seconds` int(11) NOT NULL,
  `is_executed` int(1) NOT NULL,
  `class` varchar(128) CHARACTER SET utf8 NOT NULL,
  `params` longtext,
  `content` longtext,
  `is_delete` int(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_core_validate_code` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `target` varchar(255) NOT NULL,
  `code` varchar(128) NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `is_validated` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已验证：0=未验证，1-已验证',
  PRIMARY KEY (`id`),
  KEY `target` (`target`),
  KEY `code` (`code`),
  KEY `created_at` (`created_at`),
  KEY `updated_at` (`updated_at`),
  KEY `is_validated` (`is_validated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短信、邮箱验证码';


CREATE TABLE `zjhj_bd_core_validate_code_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `target` varchar(255) NOT NULL DEFAULT '',
  `content` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT '优惠券名称',
  `type` smallint(1) NOT NULL COMMENT '优惠券类型：1=折扣，2=满减',
  `discount` decimal(3,1) NOT NULL DEFAULT '10.0' COMMENT '折扣率',
  `pic_url` int(11) NOT NULL DEFAULT '0' COMMENT '未用',
  `desc` varchar(2000) NOT NULL DEFAULT '' COMMENT '未用',
  `min_price` decimal(10,2) NOT NULL COMMENT '最低消费金额',
  `sub_price` decimal(10,2) NOT NULL COMMENT '优惠金额',
  `total_count` int(11) NOT NULL DEFAULT '-1' COMMENT '可发放的数量（剩余数量）',
  `sort` int(11) NOT NULL DEFAULT '1' COMMENT '排序按升序排列',
  `expire_type` smallint(1) NOT NULL COMMENT '到期类型：1=领取后N天过期，2=指定有效期',
  `expire_day` int(11) NOT NULL DEFAULT '0' COMMENT '有效天数，expire_type=1时',
  `begin_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '有效期开始时间',
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '有效期结束时间',
  `appoint_type` smallint(11) NOT NULL COMMENT '1 指定分类 2 指定商品 3全部',
  `rule` varchar(2000) NOT NULL DEFAULT '' COMMENT '使用说明',
  `is_member` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否指定会员等级',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `deleted_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `store_id` (`mall_id`) USING BTREE,
  KEY `is_delete` (`is_delete`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_coupon_auto_send` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL COMMENT '优惠卷',
  `event` int(11) NOT NULL DEFAULT '1' COMMENT '触发事件：1=分享，2=购买并付款',
  `send_count` int(11) NOT NULL DEFAULT '0' COMMENT '最多发放次数，0表示不限制',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `deleted_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_coupon_cat_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) NOT NULL COMMENT '优惠券',
  `cat_id` int(11) NOT NULL COMMENT '分类',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_coupon_center` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL COMMENT '优惠券id',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_coupon_goods_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) NOT NULL COMMENT '优惠券',
  `goods_warehouse_id` int(11) NOT NULL COMMENT '商品',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_coupon_mall_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_coupon_id` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_coupon_member_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL COMMENT '优惠券id',
  `member_level` int(11) NOT NULL COMMENT '会员id',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_delivery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `express_id` int(11) NOT NULL DEFAULT '0' COMMENT '快递公司id',
  `customer_account` varchar(255) NOT NULL DEFAULT '' COMMENT '电子面单客户账号',
  `customer_pwd` varchar(255) NOT NULL DEFAULT '' COMMENT '电子面单密码',
  `month_code` varchar(255) NOT NULL DEFAULT '' COMMENT '月结编码',
  `outlets_name` varchar(255) NOT NULL DEFAULT '' COMMENT '网点名称',
  `outlets_code` varchar(255) NOT NULL DEFAULT '' COMMENT '网点编码',
  `company` varchar(255) NOT NULL DEFAULT '' COMMENT '发件人公司',
  `name` varchar(255) NOT NULL COMMENT '发件人名称',
  `tel` varchar(255) NOT NULL DEFAULT '' COMMENT '发件人电话',
  `mobile` varchar(255) NOT NULL DEFAULT '' COMMENT '发件人手机',
  `zip_code` varchar(255) NOT NULL DEFAULT '' COMMENT '发件人邮政编码',
  `province` varchar(255) NOT NULL COMMENT '发件人省',
  `city` varchar(255) NOT NULL COMMENT '发件人市',
  `district` varchar(255) NOT NULL COMMENT '发件人区',
  `address` varchar(255) NOT NULL COMMENT '发件人详细地址',
  `is_sms` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否订阅短信',
  `template_size` varchar(255) NOT NULL DEFAULT '' COMMENT '快递鸟电子面单模板规格',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_diy_alone_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `type` varchar(255) DEFAULT '' COMMENT '类型 auth--授权页面',
  `params` longtext COMMENT '参数',
  `is_delete` smallint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_open` smallint(1) DEFAULT '0' COMMENT '是否显示 0--不显示 1--显示',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_diy_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `show_navs` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示导航条：0=不显示，1=显示',
  `is_disable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '禁用状态：0=启用，1=禁用',
  `is_home_page` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是首页0--否 1--是',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_diy_page_nav` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `template_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_diy_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `data` longtext NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_favorite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `goods_id` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `is_delete` int(11) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_formid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `form_id` varchar(1000) NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `remains` int(11) NOT NULL COMMENT '剩余次数',
  `expired_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_free_delivery_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `detail` longtext NOT NULL,
  `is_delete` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_fxhb_activity` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启活动：0.关闭|1.开启',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '红包分配方式：1.随机|2.平均',
  `number` int(11) NOT NULL COMMENT '拆包人数',
  `count_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '红包总金额',
  `least_price` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '最低消费金额',
  `effective_time` int(11) NOT NULL COMMENT '代金券有效期',
  `open_effective_time` int(11) NOT NULL COMMENT '拆红包有效期',
  `coupon_type` tinyint(1) NOT NULL COMMENT '代金券使用场景：1.指定分类|2.指定商品|3.全场通用',
  `sponsor_num` int(11) NOT NULL DEFAULT '-1' COMMENT '该用户可发起活动的次数',
  `help_num` int(11) NOT NULL DEFAULT '-1' COMMENT '帮拆的次数',
  `sponsor_count` int(11) NOT NULL DEFAULT '-1' COMMENT '此活动可发红包总次数',
  `sponsor_count_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '次数扣除方式：0.活动成功扣除|1.活动发起就扣除',
  `start_time` timestamp NOT NULL COMMENT '活动开始时间',
  `end_time` timestamp NOT NULL COMMENT '活动结束时间',
  `remark` text NOT NULL COMMENT '活动规则 ',
  `pic_url` varchar(255) NOT NULL DEFAULT '' COMMENT '活动图片',
  `share_title` text NOT NULL COMMENT '分享标题',
  `share_pic_url` varchar(255) NOT NULL DEFAULT '' COMMENT '分享图片',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `mall_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL COMMENT '活动名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_fxhb_activity_cat_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) NOT NULL COMMENT '活动ID',
  `cat_id` int(11) NOT NULL COMMENT '分类',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_fxhb_activity_goods_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) NOT NULL COMMENT '活动ID',
  `goods_warehouse_id` int(11) NOT NULL COMMENT '商品',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_fxhb_user_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `fxhb_activity_id` int(11) NOT NULL COMMENT '活动ID',
  `number` int(11) NOT NULL COMMENT '拆包人数',
  `count_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '红包总金额',
  `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `is_delete` tinyint(1) NOT NULL,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `data` longtext NOT NULL COMMENT '活动发起时活动的设置',
  `status` tinyint(1) NOT NULL COMMENT '状态0--进行中 1--成功 2--失败',
  `mall_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `user_coupon_id` int(11) NOT NULL,
  `get_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '拆红包获得的金额',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户参与红包';


CREATE TABLE `zjhj_bd_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `goods_warehouse_id` int(11) NOT NULL,
  `status` smallint(1) NOT NULL DEFAULT '0' COMMENT '上架状态：0=下架，1=上架',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '售价',
  `use_attr` smallint(1) NOT NULL DEFAULT '1' COMMENT '是否使用规格：0=不使用，1=使用',
  `attr_groups` text CHARACTER SET utf8 NOT NULL COMMENT '商品规格组',
  `goods_stock` int(11) NOT NULL DEFAULT '0' COMMENT '商品库存',
  `virtual_sales` int(11) NOT NULL DEFAULT '0' COMMENT '已出售量',
  `confine_count` int(11) NOT NULL DEFAULT '-1' COMMENT '购物数量限制',
  `pieces` int(11) NOT NULL DEFAULT '0' COMMENT '单品满件包邮',
  `forehead` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单口满额包邮',
  `freight_id` int(11) NOT NULL COMMENT '运费模板ID',
  `give_integral` int(11) NOT NULL DEFAULT '0' COMMENT '赠送积分',
  `give_integral_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '赠送积分类型1.固定值 |2.百分比',
  `forehead_integral` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '可抵扣积分',
  `forehead_integral_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '可抵扣积分类型1.固定值 |2.百分比',
  `accumulative` tinyint(1) NOT NULL DEFAULT '0' COMMENT '允许多件累计折扣',
  `individual_share` smallint(1) NOT NULL DEFAULT '0' COMMENT '是否单独分销设置：0=否，1=是',
  `attr_setting_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '分销设置类型 0.普通设置|1.详细设置',
  `is_level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否享受会员价购买',
  `is_level_alone` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否单独设置会员价',
  `share_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '佣金配比 0--固定金额 1--百分比',
  `sign` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '商品标示用于区分商品属于什么模块',
  `app_share_pic` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '自定义分享图片',
  `app_share_title` varchar(65) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '自定义分享标题',
  `is_default_services` tinyint(1) NOT NULL DEFAULT '1' COMMENT '默认服务 0.否|1.是',
  `sort` int(11) NOT NULL DEFAULT '100' COMMENT '排序',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `payment_people` int(11) NOT NULL DEFAULT '0' COMMENT '支付人数',
  `payment_num` int(11) NOT NULL DEFAULT '0' COMMENT '支付件数',
  `payment_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '支付金额',
  `payment_order` int(11) NOT NULL DEFAULT '0' COMMENT '支付订单数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品通用信息表';


CREATE TABLE `zjhj_bd_goods_attr` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL,
  `sign_id` varchar(255) NOT NULL DEFAULT '' COMMENT '规格ID标识',
  `stock` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '库存',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `no` varchar(255) NOT NULL DEFAULT '' COMMENT '货号',
  `weight` int(11) NOT NULL DEFAULT '0' COMMENT '重量（克）',
  `pic_url` varchar(255) NOT NULL DEFAULT '' COMMENT '规格图片',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_goods_cards` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(65) NOT NULL DEFAULT '',
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `expire_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '到期类型：1=领取后N天过期，2=指定有效期',
  `expire_day` int(11) NOT NULL DEFAULT '0' COMMENT '有效天数',
  `begin_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '有效期开始时间',
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '有效期结束时间',
  `total_count` int(11) NOT NULL DEFAULT '-1' COMMENT '卡券数量',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_goods_card_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL,
  `card_id` int(11) NOT NULL,
  `is_delete` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_goods_cats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父级ID',
  `name` varchar(45) NOT NULL DEFAULT '' COMMENT '分类名称',
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `sort` int(11) NOT NULL DEFAULT '100' COMMENT '排序，升序',
  `big_pic_url` varchar(255) NOT NULL DEFAULT '',
  `advert_pic` varchar(255) NOT NULL DEFAULT '' COMMENT '广告图片',
  `advert_url` varchar(255) NOT NULL DEFAULT '' COMMENT '广告链接',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用:0.禁用|1.启用',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_goods_cat_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_warehouse_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_goods_member_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `level` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `goods_attr_id` int(11) NOT NULL,
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  `goods_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_goods_services` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(65) NOT NULL DEFAULT '' COMMENT '服务名称',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注、描述',
  `sort` int(11) NOT NULL DEFAULT '100',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '默认服务',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_goods_service_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_goods_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `share_commission_first` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '一级分销佣金比例',
  `share_commission_second` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '二级分销佣金比例',
  `share_commission_third` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '三级分销佣金比例',
  `goods_id` int(11) NOT NULL,
  `goods_attr_id` int(11) NOT NULL DEFAULT '0',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_goods_warehouse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '商品名称',
  `original_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '原价',
  `cost_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '成本价',
  `detail` longtext CHARACTER SET utf8 NOT NULL COMMENT '商品详情，图文',
  `cover_pic` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '商品缩略图',
  `pic_url` text CHARACTER SET utf8 NOT NULL COMMENT '商品轮播图',
  `video_url` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '商品视频',
  `unit` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '件' COMMENT '单位',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `store_id` (`mall_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='商品';


CREATE TABLE `zjhj_bd_home_block` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `name` varchar(65) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '样式类型：1.默认|2.样式一|3.样式二',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_home_nav` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `name` varchar(65) NOT NULL DEFAULT '' COMMENT '导航名称',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '导航链接',
  `open_type` varchar(65) NOT NULL DEFAULT '' COMMENT '打开方式',
  `icon_url` varchar(255) NOT NULL DEFAULT '' COMMENT '导航图标',
  `sort` int(11) NOT NULL DEFAULT '100' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0.隐藏|1.显示',
  `params` text NOT NULL COMMENT '导航参数',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_integral_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '类型：1=收入，2=支出',
  `integral` int(11) NOT NULL COMMENT '变动积分',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '变动说明',
  `custom_desc` longtext NOT NULL COMMENT '自定义详细说明|记录',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_integral_mall_banners` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `banner_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_integral_mall_cats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT '100',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_integral_mall_coupons` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `exchange_num` int(11) NOT NULL DEFAULT '-1' COMMENT '兑换次数-1.不限制',
  `integral_num` int(11) NOT NULL COMMENT '所需兑换积分',
  `send_count` int(11) NOT NULL COMMENT '发放优惠券总数',
  `price` decimal(10,2) NOT NULL COMMENT '价格',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_integral_mall_coupons_orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `mall_id` int(11) NOT NULL,
  `order_no` varchar(255) NOT NULL DEFAULT '',
  `integral_mall_coupon_id` int(11) NOT NULL COMMENT '积分商城优惠券ID',
  `integral_mall_coupon_info` text NOT NULL COMMENT '积分商城优惠券信息',
  `user_coupon_id` int(11) NOT NULL COMMENT '关联用户优惠券ID',
  `price` decimal(11,2) NOT NULL COMMENT '优惠券价格',
  `integral_num` int(11) NOT NULL COMMENT '优惠券积分',
  `is_pay` tinyint(1) NOT NULL DEFAULT '0',
  `pay_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pay_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支付方式：1.在线支付 2.货到付款 3.余额支付',
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_integral_mall_coupons_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `mall_id` int(11) NOT NULL,
  `integral_mall_coupon_id` int(11) NOT NULL COMMENT '积分商城优惠券ID',
  `user_coupon_id` int(11) NOT NULL COMMENT '关联用户优惠券ID',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_integral_mall_coupon_order_submit_result` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(32) NOT NULL DEFAULT '',
  `data` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_integral_mall_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL,
  `mall_id` int(11) NOT NULL,
  `is_home` tinyint(1) NOT NULL DEFAULT '0' COMMENT '放置首页0.否|1.是',
  `integral_num` int(11) NOT NULL DEFAULT '0',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_integral_mall_goods_attr` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `integral_num` int(11) NOT NULL DEFAULT '0' COMMENT '商品所需积分',
  `goods_id` int(11) NOT NULL,
  `goods_attr_id` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_integral_mall_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL DEFAULT '',
  `integral_num` int(11) NOT NULL COMMENT '商品所需积分',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_integral_mall_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `is_share` int(11) NOT NULL DEFAULT '0',
  `is_sms` int(11) NOT NULL DEFAULT '0',
  `is_mail` int(11) NOT NULL DEFAULT '0',
  `is_print` int(11) NOT NULL DEFAULT '0',
  `is_territorial_limitation` int(11) NOT NULL DEFAULT '0',
  `desc` text NOT NULL COMMENT '积分说明',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `payment_type` longtext NOT NULL COMMENT '支付方式',
  `send_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发货方式',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_lottery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL COMMENT '规格',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0关闭 1开启',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未完成 1已完成 2超限 3过期',
  `stock` int(11) NOT NULL DEFAULT '0' COMMENT '库存',
  `start_at` timestamp NOT NULL,
  `end_at` timestamp NOT NULL,
  `join_min_num` int(11) NOT NULL DEFAULT '0' COMMENT '参加最少人数限制',
  `sort` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `participant` int(11) NOT NULL DEFAULT '0' COMMENT '参与人',
  `invitee` int(11) NOT NULL DEFAULT '0' COMMENT '被邀请人',
  `code_num` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖券码数量',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_lottery_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `banner_id` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_lottery_default` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `lottery_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_lottery_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lottery_id` int(11) NOT NULL,
  `status` smallint(1) NOT NULL DEFAULT '0' COMMENT '0未抽奖 1待开奖 2未中奖 3中奖 4已领取 ',
  `goods_id` int(11) NOT NULL COMMENT '规格id',
  `child_id` int(11) NOT NULL DEFAULT '0' COMMENT '受邀请userid',
  `lucky_code` varchar(255) NOT NULL COMMENT '幸运码',
  `raffled_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '领取时间',
  `obtain_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_lottery_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `lottery_log_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_lottery_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：分享即送 1： 被分享人参与抽奖',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '小程序标题',
  `rule` longtext NOT NULL COMMENT '规则',
  `created_at` timestamp NOT NULL,
  `payment_type` longtext NOT NULL COMMENT '支付方式',
  `send_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发货方式',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_mail_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL DEFAULT '-1',
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `send_mail` longtext CHARACTER SET utf8 NOT NULL COMMENT '发件人邮箱',
  `send_pwd` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '授权码',
  `send_name` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '发件人名称',
  `receive_mail` longtext CHARACTER SET utf8 NOT NULL COMMENT '收件人邮箱 多个用英文逗号隔开',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '是否开启邮件通知 0--关闭 1--开启',
  `is_delete` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_mall` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `user_id` int(11) unsigned NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `is_recycle` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否回收',
  `is_disable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城';


CREATE TABLE `zjhj_bd_mall_banner_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `banner_id` int(11) NOT NULL COMMENT '轮播图id',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_mall_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `is_quick_shop` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否快速购买',
  `is_sell_well` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否热销',
  `is_negotiable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否面议商品',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城商品额外信息表';


CREATE TABLE `zjhj_bd_mall_members` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `level` int(11) unsigned NOT NULL COMMENT '会员等级',
  `name` varchar(65) NOT NULL DEFAULT '' COMMENT '等级名称',
  `auto_update` tinyint(1) NOT NULL COMMENT '是否开启自动升级',
  `money` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '会员完成订单金额满足则升级',
  `discount` decimal(11,1) NOT NULL DEFAULT '0.0' COMMENT '会员折扣',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态 0--禁用 1--启用',
  `pic_url` varchar(255) NOT NULL DEFAULT '' COMMENT '会员图片',
  `is_purchase` tinyint(1) NOT NULL COMMENT '会员是否可购买',
  `price` decimal(11,2) NOT NULL COMMENT '购买会员价格',
  `rules` mediumtext NOT NULL COMMENT '会员规则',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_mall_member_orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `mall_id` int(11) NOT NULL,
  `order_no` varchar(30) NOT NULL DEFAULT '' COMMENT '订单号',
  `pay_price` decimal(10,2) NOT NULL COMMENT '支付金额',
  `pay_type` tinyint(1) NOT NULL COMMENT '支付方式 1.线上支付',
  `is_pay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否支付 0--未支付 1--支付',
  `pay_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '支付时间',
  `detail` text NOT NULL COMMENT '会员更新详情',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_mall_member_rights` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `title` varchar(65) NOT NULL DEFAULT '',
  `content` varchar(255) NOT NULL DEFAULT '',
  `pic_url` varchar(255) NOT NULL DEFAULT '',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_mall_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `key` varchar(65) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_mch` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否营业0.否|1.是',
  `is_recommend` tinyint(1) NOT NULL DEFAULT '0' COMMENT '好店推荐：0.不推荐|1.推荐',
  `review_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审核状态：0=待审核，1.审核通过.2=审核不通过',
  `review_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '审核结果、备注',
  `review_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
  `realname` varchar(65) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `wechat` varchar(65) NOT NULL DEFAULT '' COMMENT '微信号',
  `mobile` varchar(255) NOT NULL DEFAULT '' COMMENT '手机号码',
  `mch_common_cat_id` int(11) NOT NULL COMMENT '商户所属类目',
  `transfer_rate` int(11) NOT NULL DEFAULT '0' COMMENT '商户手续费',
  `account_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '账户余额',
  `sort` int(11) NOT NULL DEFAULT '100' COMMENT '店铺排序|升序',
  `form_data` mediumtext NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_mch_account_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL,
  `money` decimal(11,2) NOT NULL COMMENT '金额',
  `desc` text NOT NULL COMMENT '备注说明',
  `type` tinyint(1) NOT NULL COMMENT '类型：1=收入，2=支出',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_mch_cash` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL COMMENT '多商户ID',
  `money` decimal(10,2) NOT NULL COMMENT '提现金额',
  `order_no` varchar(255) NOT NULL DEFAULT '' COMMENT '订单号',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '提现状态：0=待处理，1=同意，2=拒绝',
  `transfer_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0.待转账 | 1.已转账  | 2.拒绝转账',
  `type` varchar(65) NOT NULL DEFAULT '0' COMMENT 'wx 微信| alipay 支付宝 | bank 银行卡 | balance 余额',
  `type_data` varchar(255) NOT NULL DEFAULT '' COMMENT '不同提现类型，提交的数据',
  `virtual_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '实际上打款方式',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_mch_common_cat` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '类目名称',
  `sort` int(11) NOT NULL DEFAULT '100' COMMENT '排序：升序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_mch_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mch_id` int(11) NOT NULL,
  `mall_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0.申请上架|1.申请中|2.同意上架|3.拒绝上架',
  `sort` int(11) NOT NULL DEFAULT '100' COMMENT '商户的排序',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_mch_mall_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL,
  `is_share` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启分销0.否|1.是',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_mch_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `is_transfer` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否转入商户0.否|1.是',
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_mch_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL,
  `is_share` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启分销0.否|1.是',
  `is_sms` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启短信提醒',
  `is_mail` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启邮件通知',
  `is_print` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启订单打印',
  `is_territorial_limitation` tinyint(1) NOT NULL DEFAULT '0' COMMENT '区域购买限制',
  `send_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '发货方式0.快递和自提|1.快递|2.自提',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_mch_visit_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `num` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_miaosha_banners` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `banner_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_miaosha_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `goods_warehouse_id` int(11) NOT NULL,
  `open_time` tinyint(1) NOT NULL COMMENT '开放时间',
  `open_date` date NOT NULL,
  `buy_limit` int(11) NOT NULL DEFAULT '-1' COMMENT '限单 -1|不限单',
  `virtual_miaosha_num` int(11) NOT NULL DEFAULT '0' COMMENT '虚拟秒杀量',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_miaosha_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `over_time` int(11) NOT NULL DEFAULT '1' COMMENT '未支付订单取消时间',
  `is_share` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启分销',
  `is_sms` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否短信提醒',
  `is_mail` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启邮件通知',
  `is_print` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启订单打印',
  `is_territorial_limitation` tinyint(1) NOT NULL DEFAULT '0' COMMENT '区域购买限制',
  `open_time` text NOT NULL COMMENT '秒杀开放时间',
  `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `payment_type` longtext NOT NULL,
  `send_type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='秒杀设置';


CREATE TABLE `zjhj_bd_mp_template_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `open_id` varchar(255) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '模板消息是否发送成功0--失败|1--成功',
  `data` longtext NOT NULL COMMENT '模板消息内容',
  `error` longtext NOT NULL COMMENT '错误信息',
  `created_at` timestamp NOT NULL,
  `token` varchar(255) NOT NULL DEFAULT '' COMMENT '模板消息发送标示',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='模板消息发送记录表';


CREATE TABLE `zjhj_bd_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `group` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0' COMMENT '多商户id，0表示商城订单',
  `order_no` varchar(255) NOT NULL DEFAULT '' COMMENT '订单号',
  `total_price` decimal(10,2) NOT NULL COMMENT '订单总金额(含运费)',
  `total_pay_price` decimal(10,2) NOT NULL COMMENT '实际支付总费用(含运费）',
  `express_original_price` decimal(10,2) NOT NULL COMMENT '运费(后台修改前)',
  `express_price` decimal(10,2) NOT NULL COMMENT '运费(后台修改后)',
  `total_goods_price` decimal(10,2) NOT NULL COMMENT '订单商品总金额(优惠后)',
  `total_goods_original_price` decimal(10,2) NOT NULL COMMENT '订单商品总金额(优惠前)',
  `member_discount_price` decimal(10,2) NOT NULL COMMENT '会员优惠价格(正数表示优惠，负数表示加价)',
  `use_user_coupon_id` int(11) NOT NULL COMMENT '使用的用户优惠券id',
  `coupon_discount_price` decimal(10,2) NOT NULL COMMENT '优惠券优惠金额',
  `use_integral_num` int(11) NOT NULL COMMENT '使用积分数量',
  `integral_deduction_price` decimal(10,2) NOT NULL COMMENT '积分抵扣金额',
  `name` varchar(65) NOT NULL DEFAULT '' COMMENT '收件人姓名',
  `mobile` varchar(255) NOT NULL DEFAULT '' COMMENT '收件人手机号',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '收件人地址',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '用户订单备注',
  `order_form` longtext COMMENT '自定义表单（JSON）',
  `words` varchar(255) NOT NULL DEFAULT '' COMMENT '商家留言',
  `seller_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '商家订单备注',
  `is_pay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否支付：0.未支付|1.已支付',
  `pay_type` tinyint(4) NOT NULL COMMENT '支付方式：1.在线支付 2.货到付款 3.余额支付',
  `pay_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '支付时间',
  `is_send` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否发货：0.未发货|1.已发货',
  `send_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '发货时间',
  `express` varchar(65) NOT NULL DEFAULT '' COMMENT '物流公司',
  `express_no` varchar(255) NOT NULL DEFAULT '' COMMENT '物流订单号',
  `is_sale` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否过售后时间',
  `is_confirm` tinyint(1) NOT NULL DEFAULT '0' COMMENT '收货状态：0.未收货|1.已收货',
  `confirm_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '确认收货时间',
  `cancel_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单取消状态：0.未取消|1.已取消|2.申请取消',
  `cancel_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '订单取消时间',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `is_recycle` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否加入回收站 0.否|1.是',
  `is_offline` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否到店自提：0.否|1.是',
  `offline_qrcode` varchar(255) NOT NULL DEFAULT '' COMMENT '核销码',
  `clerk_id` int(11) NOT NULL DEFAULT '0' COMMENT '核销员ID',
  `store_id` int(11) NOT NULL DEFAULT '0' COMMENT '自提门店ID',
  `sign` varchar(255) NOT NULL DEFAULT '' COMMENT '订单标识，用于区分插件',
  `token` varchar(32) NOT NULL DEFAULT '',
  `support_pay_types` longtext COMMENT '支持的支付方式，空表示支持系统设置支持的所有方式',
  `is_comment` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否评价0.否|1.是',
  `comment_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sale_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否申请售后',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '订单状态|1.已完成|0.进行中不能对订单进行任何操作',
  `back_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '后台优惠(正数表示优惠，负数表示加价)',
  `auto_cancel_time` timestamp NULL DEFAULT NULL COMMENT '自动取消时间',
  `auto_confirm_time` timestamp NULL DEFAULT NULL COMMENT '自动确认收货时间',
  `auto_sales_time` timestamp NULL DEFAULT NULL COMMENT '自动售后时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_order_clerk` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `affirm_pay_type` tinyint(1) NOT NULL DEFAULT '-1' COMMENT '确认收款类型|1.小程序收款|2.后台收款',
  `clerk_type` tinyint(1) NOT NULL DEFAULT '-1' COMMENT '确认核销类型|1.小程序核销|2.后台核销',
  `clerk_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '核销备注',
  `mall_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_order_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL,
  `order_detail_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` tinyint(4) NOT NULL COMMENT '评分：1=差评，2=中评，3=好',
  `content` text NOT NULL COMMENT '评价内容',
  `pic_url` text NOT NULL COMMENT '评价图片',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示：0.不显示|1.显示',
  `is_virtual` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否虚拟用户',
  `virtual_user` varchar(255) NOT NULL DEFAULT '' COMMENT '虚拟用户名',
  `virtual_avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '虚拟头像',
  `virtual_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '虚拟评价时间',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `goods_warehouse_id` int(11) NOT NULL COMMENT '商品库ID',
  `sign` varchar(255) NOT NULL DEFAULT '',
  `reply_content` text NOT NULL COMMENT '商家回复内容',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `is_anonymous` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否匿名 0.否|1.是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_order_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `num` int(11) NOT NULL COMMENT '购买商品数量',
  `unit_price` decimal(10,2) NOT NULL COMMENT '商品单价',
  `total_original_price` decimal(10,2) NOT NULL COMMENT '商品原总价(优惠前)',
  `total_price` decimal(10,2) NOT NULL COMMENT '商品总价(优惠后)',
  `member_discount_price` decimal(10,2) NOT NULL COMMENT '会员优惠金额(正数表示优惠，负数表示加价)',
  `goods_info` longtext NOT NULL COMMENT '购买商品信息',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_refund` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否退款',
  `refund_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '售后状态 0--未售后 1--售后中 2--售后结束',
  `back_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '后台优惠(正数表示优惠，负数表示加价)',
  `sign` varchar(255) NOT NULL DEFAULT '' COMMENT '订单详情标识，用于区分插件',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_order_express_single` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL COMMENT '订单id',
  `express_code` varchar(255) NOT NULL COMMENT '快递公司编码',
  `ebusiness_id` varchar(255) NOT NULL COMMENT '快递鸟id',
  `print_teplate` longtext NOT NULL,
  `order` longtext NOT NULL COMMENT '订单信息',
  `is_delete` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_order_pay_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `data` longtext COMMENT 'json数据',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_order_refund` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_detail_id` int(11) NOT NULL COMMENT '关联订单详情',
  `order_no` varchar(255) NOT NULL DEFAULT '' COMMENT '退款单号',
  `type` tinyint(1) NOT NULL COMMENT '售后类型：1=退货退款，2=换货',
  `refund_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '退款金额',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '用户退款备注、说明',
  `pic_list` mediumtext NOT NULL COMMENT '用户上传图片凭证',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1.待商家处理 2.同意 3.拒绝',
  `status_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '商家处理时间',
  `merchant_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '商家同意|拒绝备注、理由',
  `is_send` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户是否发货 0.未发货1.已发货',
  `send_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '发货时间',
  `express` varchar(65) NOT NULL DEFAULT '' COMMENT '快递公司',
  `express_no` varchar(255) NOT NULL DEFAULT '' COMMENT '快递单号',
  `address_id` int(11) NOT NULL DEFAULT '0' COMMENT '退换货地址ID',
  `is_confirm` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商家确认操作',
  `confirm_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '确认时间',
  `merchant_express` varchar(65) NOT NULL DEFAULT '' COMMENT '商家发货快递公司',
  `merchant_express_no` varchar(255) NOT NULL DEFAULT '' COMMENT '商家发货快递单号',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_order_submit_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(32) NOT NULL,
  `data` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_payment_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_order_union_id` int(11) NOT NULL,
  `order_no` varchar(32) NOT NULL,
  `amount` decimal(9,2) NOT NULL,
  `is_pay` int(1) NOT NULL DEFAULT '0' COMMENT '支付状态：0=未支付，1=已支付',
  `pay_type` int(1) NOT NULL DEFAULT '0' COMMENT '支付方式：1=微信支付，2=货到付款，3=余额支付，4=支付宝支付',
  `title` varchar(128) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `notify_class` varchar(512) NOT NULL,
  `refund` decimal(9,2) NOT NULL DEFAULT '0.00' COMMENT '已退款金额',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_payment_order_union` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `order_no` varchar(32) NOT NULL,
  `amount` decimal(9,2) NOT NULL,
  `is_pay` int(1) NOT NULL DEFAULT '0' COMMENT '支付状态：0=未支付，1=已支付',
  `pay_type` int(1) NOT NULL DEFAULT '0' COMMENT '支付方式：1=微信支付，2=货到付款，3=余额支付，4=支付宝支付',
  `title` varchar(128) NOT NULL,
  `support_pay_types` text COMMENT '支持的支付方式（JSON）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_payment_refund` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_no` varchar(255) NOT NULL DEFAULT '' COMMENT '退款单号',
  `amount` decimal(9,2) NOT NULL DEFAULT '0.00' COMMENT '退款金额',
  `is_pay` int(1) NOT NULL DEFAULT '0' COMMENT '支付状态 0--未支付|1--已支付',
  `pay_type` int(1) NOT NULL DEFAULT '0' COMMENT '支付方式：1=微信支付，2=货到付款，3=余额支付，4=支付宝支付',
  `title` varchar(128) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `out_trade_no` varchar(255) NOT NULL DEFAULT '' COMMENT '支付单号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='退款订单';


CREATE TABLE `zjhj_bd_payment_transfer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_no` varchar(255) NOT NULL COMMENT '提交微信或支付宝的订单号',
  `transfer_order_no` varchar(255) NOT NULL COMMENT '发起 打款的订单号',
  `amount` decimal(9,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `is_pay` int(1) NOT NULL DEFAULT '0' COMMENT '支付状态 0--未支付|1--已支付',
  `pay_type` varchar(255) NOT NULL DEFAULT '' COMMENT '方式：wechat--微信打款 alipay--支付宝打款',
  `title` varchar(128) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='平台向用户打款';


CREATE TABLE `zjhj_bd_pintuan_banners` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `banner_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_pintuan_cats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT '100',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_pintuan_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `is_alone_buy` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否允许单独购买',
  `mall_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '拼团结束时间',
  `groups_restrictions` int(11) NOT NULL DEFAULT '-1' COMMENT '拼团次数限制',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  `is_sell_well` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否热销',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_pintuan_goods_attr` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pintuan_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '拼团价',
  `pintuan_stock` int(11) NOT NULL COMMENT '拼团库存',
  `pintuan_goods_groups_id` int(11) NOT NULL COMMENT '拼团设置ID',
  `goods_attr_id` int(11) NOT NULL COMMENT '商城商品规格ID',
  `goods_id` int(11) NOT NULL COMMENT '商城商品ID',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_pintuan_goods_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL,
  `people_num` int(11) NOT NULL DEFAULT '2' COMMENT '拼团人数',
  `preferential_price` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '团长优惠',
  `pintuan_time` int(11) NOT NULL DEFAULT '1' COMMENT '拼团限间',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_pintuan_goods_member_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `level` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `goods_id` int(11) NOT NULL COMMENT '商城商品ID',
  `goods_attr_id` int(11) NOT NULL COMMENT '商城商品规格ID',
  `pintuan_goods_groups_id` int(11) NOT NULL COMMENT '拼团设置ID',
  `pintuan_goods_attr_id` int(11) NOT NULL COMMENT '拼团商品规格ID',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_pintuan_goods_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `share_commission_first` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '一级分销佣金比例',
  `share_commission_second` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '二级分销佣金比例',
  `share_commission_third` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '三级分销佣金比例',
  `goods_id` int(11) NOT NULL,
  `goods_attr_id` int(11) NOT NULL COMMENT '商城商品规格ID',
  `pintuan_goods_groups_id` int(11) NOT NULL COMMENT '拼团设置ID',
  `pintuan_goods_attr_id` int(11) NOT NULL DEFAULT '0' COMMENT '拼团商品规格ID',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_pintuan_orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `preferential_price` decimal(10,2) NOT NULL COMMENT '团长优惠',
  `mall_id` int(11) NOT NULL,
  `success_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '成团时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0.待付款|1.拼团中|2.拼团成功|3.拼团失败',
  `people_num` int(11) NOT NULL COMMENT '成团所需人数',
  `pintuan_time` int(11) NOT NULL DEFAULT '2' COMMENT '拼团限时(小时)',
  `pintuan_goods_groups_id` int(11) NOT NULL COMMENT '阶梯团ID',
  `goods_id` int(11) NOT NULL COMMENT '商品ID',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_pintuan_order_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL COMMENT '商城订单ID',
  `user_id` int(11) NOT NULL,
  `pintuan_order_id` int(11) NOT NULL COMMENT '组团订单ID',
  `is_parent` tinyint(11) NOT NULL DEFAULT '0' COMMENT '是否为团长',
  `is_groups` tinyint(4) NOT NULL COMMENT '0.单独购买|1.拼团购买',
  `created_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_pintuan_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `is_share` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启分销',
  `is_sms` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否短信提醒',
  `is_mail` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启邮件通知',
  `is_print` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启订单打印',
  `rules` text NOT NULL COMMENT '拼团规则',
  `is_territorial_limitation` tinyint(1) NOT NULL DEFAULT '0' COMMENT '区域购买限制',
  `advertisement` text NOT NULL COMMENT '拼团广告',
  `is_advertisement` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启拼团广告',
  `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `payment_type` longtext NOT NULL COMMENT '支付方式',
  `send_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发货方式',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='砍价设置';


CREATE TABLE `zjhj_bd_pond` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '别名',
  `type` int(11) NOT NULL COMMENT '1.红包2.优惠卷3.积分4.实物.5.无',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '积分数量',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '红包价格',
  `image_url` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `coupon_id` int(11) NOT NULL DEFAULT '0' COMMENT '优惠卷',
  `stock` int(11) NOT NULL DEFAULT '0' COMMENT '库存',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;


CREATE TABLE `zjhj_bd_pond_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL DEFAULT '0',
  `pond_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` int(11) NOT NULL COMMENT ' 0未领取1 已领取',
  `type` int(11) NOT NULL COMMENT '1.红包2.优惠卷3.积分4.实物5无',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '积分数量',
  `detail` varchar(2000) NOT NULL DEFAULT '' COMMENT '优惠券信息',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `raffled_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;


CREATE TABLE `zjhj_bd_pond_log_coupon_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_coupon_id` int(11) NOT NULL COMMENT '用户优惠券id',
  `pond_log_id` int(11) NOT NULL COMMENT '奖品记录id',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_pond_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `pond_log_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_pond_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '小程序标题',
  `type` smallint(1) NOT NULL COMMENT '1.天 2 用户',
  `probability` int(11) NOT NULL DEFAULT '0' COMMENT '概率',
  `oppty` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖次数',
  `start_at` timestamp NOT NULL COMMENT '开始时间',
  `end_at` timestamp NOT NULL COMMENT '结束时间',
  `deplete_integral_num` int(11) NOT NULL DEFAULT '0' COMMENT '消耗积分',
  `rule` longtext NOT NULL COMMENT '规则',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `payment_type` longtext NOT NULL COMMENT '支付方式',
  `send_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发货方式',
  `is_sms` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开启短信提醒',
  `is_mail` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开启邮件提醒',
  `is_print` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开启打印',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_postage_rules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(65) NOT NULL DEFAULT '',
  `detail` longtext NOT NULL COMMENT '规则详情',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否默认',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '计费方式【1=>按重计费、2=>按件计费】',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_printer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL COMMENT '类型',
  `name` varchar(255) NOT NULL COMMENT '名称',
  `setting` longtext NOT NULL COMMENT '设置',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_printer_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `printer_id` int(11) NOT NULL COMMENT '打印机id',
  `block_id` int(11) NOT NULL DEFAULT '0' COMMENT '模板id',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0关闭 1启用',
  `is_attr` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0不使用规格 1使用规格',
  `type` longtext NOT NULL COMMENT 'order(下单打印)-> 0关闭 1开启 \r\npay (付款打印)-> 0关闭 1开启 \r\nconfirm (确认收货打印)-> 0关闭 1开启 \r\n ',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_qr_code_parameter` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(30) NOT NULL DEFAULT '',
  `data` mediumtext NOT NULL,
  `created_at` timestamp NOT NULL,
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '小程序路径',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_quick_shop_cats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_recharge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '名称',
  `pay_price` decimal(10,2) NOT NULL COMMENT '支付价格',
  `send_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '赠送价格',
  `is_delete` smallint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `send_integral` int(11) NOT NULL DEFAULT '0' COMMENT '赠送的积分',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_recharge_orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `order_no` varchar(32) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL,
  `pay_price` decimal(10,2) NOT NULL COMMENT '充值金额',
  `send_price` decimal(10,2) NOT NULL COMMENT '赠送金额',
  `pay_type` tinyint(4) NOT NULL COMMENT '支付方式 1.线上支付',
  `is_pay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否支付 0--未支付 1--支付',
  `pay_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `send_integral` int(11) NOT NULL DEFAULT '0' COMMENT '赠送的积分',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_refund_address` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(65) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `address_detail` varchar(255) NOT NULL DEFAULT '',
  `mobile` varchar(255) NOT NULL DEFAULT '',
  `remark` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_scratch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `type` int(11) NOT NULL COMMENT '1.红包2.优惠卷3.积分4.实物.5.无',
  `status` tinyint(1) NOT NULL COMMENT '状态 0 关闭 1开启',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '积分数量',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '红包价格',
  `coupon_id` int(11) NOT NULL DEFAULT '0' COMMENT '优惠券',
  `stock` int(11) NOT NULL DEFAULT '0' COMMENT '库存',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_scratch_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `scratch_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0' COMMENT ' 0预领取 1 未领取 2 已领取',
  `type` int(11) NOT NULL DEFAULT '0' COMMENT '1.红包2.优惠卷3.积分4.实物5无',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '积分数量',
  `detail` longtext NOT NULL COMMENT '优惠券信息',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `raffled_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `deleted_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_scratch_log_coupon_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_coupon_id` int(11) NOT NULL COMMENT '用户优惠券id',
  `scratch_log_id` int(11) NOT NULL COMMENT '记录id',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_scratch_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `scratch_log_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_scratch_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '小程序标题',
  `type` smallint(1) NOT NULL COMMENT '1.天 2 用户',
  `probability` int(11) NOT NULL DEFAULT '0' COMMENT '概率',
  `oppty` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖次数',
  `start_at` timestamp NOT NULL COMMENT '开始时间',
  `end_at` timestamp NOT NULL COMMENT '结束时间',
  `deplete_integral_num` int(11) NOT NULL DEFAULT '0' COMMENT '消耗积分',
  `rule` longtext NOT NULL COMMENT '规则',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `payment_type` longtext NOT NULL COMMENT '支付方式',
  `send_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发货方式',
  `is_sms` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开启短信提醒',
  `is_mail` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开启邮件提醒',
  `is_print` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开启打印',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '分销商名称',
  `mobile` varchar(255) NOT NULL DEFAULT '' COMMENT '分销商手机号',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '可提现佣金',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '用户申请分销商状态0--申请中 1--成功 2--失败',
  `total_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '累计佣金',
  `content` longtext COMMENT '备注',
  `is_delete` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime NOT NULL,
  `apply_at` datetime DEFAULT NULL COMMENT '申请时间',
  `become_at` datetime DEFAULT NULL COMMENT '成为分销商时间',
  `reason` longtext COMMENT '审核原因',
  `first_children` int(11) NOT NULL DEFAULT '0' COMMENT '直接下级数量',
  `all_children` int(11) NOT NULL DEFAULT '0' COMMENT '所有下级数量',
  `all_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总佣金数量(包括已发放和未发放且未退款的佣金）',
  `all_order` int(11) NOT NULL DEFAULT '0' COMMENT '分销订单数量',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分销商信息';


CREATE TABLE `zjhj_bd_share_cash` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_no` varchar(255) NOT NULL DEFAULT '' COMMENT '订单号',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '提现金额',
  `service_charge` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '提现手续费（%）',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '提现方式 auto--自动打款 wechat--微信打款 alipay--支付宝打款 bank--银行转账 balance--打款到余额',
  `extra` longtext COMMENT '额外信息 例如微信账号、支付宝账号等',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '提现状态 0--申请 1--同意 2--已打款 3--驳回',
  `is_delete` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime NOT NULL,
  `content` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='提现记录表';


CREATE TABLE `zjhj_bd_share_cash_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '1' COMMENT '类型 1--收入 2--支出',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动佣金',
  `desc` longtext,
  `custom_desc` longtext,
  `is_delete` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_share_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_detail_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT '购物者用户id',
  `first_parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '上一级用户id',
  `second_parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '上二级用户id',
  `third_parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '上三级用户id',
  `first_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '上一级分销佣金',
  `second_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '上二级分销佣金',
  `third_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '上三级分销佣金',
  `is_refund` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未退款 1退款',
  `is_transfer` tinyint(1) NOT NULL DEFAULT '0' COMMENT '佣金发放状态：0=未发放，1=已发放',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分销订单';


CREATE TABLE `zjhj_bd_share_order_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `share_setting` longtext NOT NULL COMMENT '分销设置情况',
  `order_share_info` longtext NOT NULL COMMENT '订单分销情况',
  `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_share_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `is_delete` int(11) NOT NULL DEFAULT '0' COMMENT '是否删除 0--未删除 1--已删除',
  `deleted_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分销设置';


CREATE TABLE `zjhj_bd_shopping_buys` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_shopping_likes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_shopping_like_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `like_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL,
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_shopping_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启0.关闭|1.开启',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_step_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `currency` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '奖金池',
  `step_num` int(11) NOT NULL DEFAULT '0' COMMENT '挑战步数',
  `bail_currency` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '保证金',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `type` smallint(1) NOT NULL DEFAULT '0' COMMENT '0进行中 1 已完成 2 已解散',
  `begin_at` date NOT NULL COMMENT '开始时间',
  `end_at` date NOT NULL COMMENT '结束时间',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_step_activity_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `activity_log_id` int(11) NOT NULL COMMENT 'a',
  `num` int(11) NOT NULL COMMENT '提交步数',
  `open_date` date NOT NULL COMMENT '创建时间',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_step_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `step_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `step_currency` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '缴纳金',
  `reward_currency` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '奖励金额',
  `status` tinyint(255) NOT NULL DEFAULT '0' COMMENT '0报名1达标  2成功 3失败 4解散',
  `created_at` timestamp NOT NULL,
  `raffled_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_step_ad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `unit_id` varchar(255) NOT NULL DEFAULT '' COMMENT '广告id',
  `site` int(11) NOT NULL DEFAULT '0' COMMENT '位置',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0关闭 1开启',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_step_banner_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `banner_id` int(11) NOT NULL COMMENT '轮播图id',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_step_daily` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `step_id` int(11) NOT NULL,
  `ratio` int(11) NOT NULL COMMENT '兑换概率',
  `real_num` int(11) NOT NULL COMMENT '真实步数',
  `num` int(11) NOT NULL COMMENT '兑换加成后数量',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_step_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `currency` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '活力币',
  `goods_id` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_step_goods_attr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `attr_id` int(11) NOT NULL COMMENT '规格',
  `currency` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '活力币',
  `goods_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_step_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `step_id` int(11) NOT NULL,
  `type` int(11) NOT NULL COMMENT '1收入 2 支出',
  `currency` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '活力币',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `data` longtext NOT NULL COMMENT '详情',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_step_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `mall_id` int(11) NOT NULL,
  `num` int(11) NOT NULL COMMENT '商品数量',
  `total_pay_price` decimal(10,2) NOT NULL COMMENT '订单实际支付价格',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `currency` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `token` varchar(255) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_step_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `convert_max` int(11) NOT NULL DEFAULT '0' COMMENT '每日最高兑换数',
  `convert_ratio` int(11) NOT NULL DEFAULT '0' COMMENT '兑换比率',
  `currency_name` varchar(255) NOT NULL DEFAULT '' COMMENT '活力币别名',
  `activity_pic` varchar(255) NOT NULL DEFAULT '' COMMENT '活动背景',
  `ranking_pic` varchar(255) NOT NULL DEFAULT '' COMMENT '排行榜背景',
  `qrcode_pic` longtext NOT NULL COMMENT '海报缩略图',
  `invite_ratio` int(11) NOT NULL DEFAULT '0' COMMENT '邀请比率',
  `remind_at` varchar(255) NOT NULL DEFAULT '16' COMMENT '提醒时间',
  `rule` longtext NOT NULL COMMENT '活动规则',
  `activity_rule` longtext NOT NULL COMMENT '活动规则',
  `ranking_num` int(11) NOT NULL DEFAULT '0' COMMENT '全国排行限制',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '小程序标题',
  `share_title` varchar(255) NOT NULL DEFAULT '' COMMENT '转发标题',
  `qrcode_title` varchar(255) NOT NULL DEFAULT '' COMMENT '海报文字',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `payment_type` longtext NOT NULL COMMENT '支付方式',
  `send_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发货方式',
  `is_share` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启分销',
  `is_sms` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开启短信提醒',
  `is_mail` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开启邮件提醒',
  `is_print` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开启打印',
  `is_territorial_limitation` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启区域允许购买',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_step_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `ratio` int(11) NOT NULL DEFAULT '0' COMMENT '概率加成',
  `step_currency` decimal(10,2) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '邀请ID',
  `invite_ratio` int(11) NOT NULL DEFAULT '0' COMMENT '邀请好友加成',
  `is_remind` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否提醒',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_store` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(65) NOT NULL DEFAULT '' COMMENT '店铺名称',
  `mobile` varchar(255) NOT NULL DEFAULT '' COMMENT '联系电话',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `province_id` int(11) NOT NULL DEFAULT '0',
  `city_id` int(11) NOT NULL DEFAULT '0',
  `district_id` int(11) NOT NULL DEFAULT '0',
  `longitude` varchar(255) NOT NULL DEFAULT '' COMMENT '经度',
  `latitude` varchar(255) NOT NULL DEFAULT '' COMMENT '纬度',
  `score` int(11) NOT NULL DEFAULT '5' COMMENT '店铺评分',
  `cover_url` varchar(255) NOT NULL DEFAULT '' COMMENT '店铺封面图',
  `pic_url` text NOT NULL COMMENT '门店轮播图',
  `business_hours` varchar(125) NOT NULL DEFAULT '' COMMENT '营业时间',
  `description` text NOT NULL COMMENT '门店描述',
  `scope` mediumtext NOT NULL COMMENT '门店经营范围',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '默认总店0.否|1.是',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_template_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '模板消息是否发送成功0--失败|1--成功',
  `data` longtext NOT NULL COMMENT '模板消息内容',
  `error` longtext NOT NULL COMMENT '错误信息',
  `created_at` timestamp NULL DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL COMMENT '模板消息发送标示',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='模板消息发送记录表';


CREATE TABLE `zjhj_bd_topic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `type` int(11) NOT NULL COMMENT '分类',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `sub_title` varchar(255) NOT NULL DEFAULT '' COMMENT '副标题（未用）',
  `content` longtext NOT NULL COMMENT '专题内容',
  `layout` smallint(1) NOT NULL DEFAULT '0' COMMENT '布局方式：0=小图，1=大图模式',
  `sort` int(11) NOT NULL DEFAULT '1' COMMENT '排序：升序',
  `cover_pic` varchar(255) NOT NULL COMMENT '封面图',
  `read_count` int(11) NOT NULL DEFAULT '0' COMMENT '阅读量',
  `agree_count` int(11) NOT NULL DEFAULT '0' COMMENT '点赞数（未用）',
  `virtual_read_count` int(11) NOT NULL DEFAULT '0' COMMENT '虚拟阅读量',
  `virtual_agree_count` int(11) NOT NULL DEFAULT '0' COMMENT '虚拟点赞数（未用）',
  `virtual_favorite_count` int(11) NOT NULL DEFAULT '0' COMMENT '虚拟收藏量',
  `qrcode_pic` varchar(255) NOT NULL DEFAULT '' COMMENT '自定义分享图片(海报图)',
  `app_share_title` varchar(65) NOT NULL DEFAULT '' COMMENT '自定义分享标题',
  `is_chosen` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否精选',
  `is_delete` tinyint(11) NOT NULL DEFAULT '0' COMMENT '删除',
  `deleted_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`mall_id`) USING BTREE,
  KEY `is_delete` (`is_delete`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_topic_favorite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_topic_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT '名称',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0隐藏 1开启',
  `sort` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0' COMMENT '多商户ID',
  `username` varchar(64) NOT NULL,
  `password` varchar(128) NOT NULL,
  `nickname` varchar(45) NOT NULL DEFAULT '',
  `auth_key` varchar(128) NOT NULL,
  `access_token` varchar(128) NOT NULL,
  `mobile` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `access_token` (`access_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_user_auth_login` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `token` varchar(255) NOT NULL DEFAULT '',
  `is_pass` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否确认登录',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_user_card` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `card_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT '名称',
  `pic_url` varchar(255) NOT NULL COMMENT '图片',
  `content` longtext NOT NULL COMMENT '详情',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `is_use` int(11) NOT NULL DEFAULT '0' COMMENT '是否使用 0--未使用 1--已使用',
  `clerk_id` int(11) NOT NULL DEFAULT '0' COMMENT '核销人id',
  `store_id` int(11) NOT NULL DEFAULT '0' COMMENT '门店ID',
  `clerked_at` timestamp NOT NULL COMMENT ' 核销时间',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '发放卡券的订单id',
  `order_detail_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单详情ID',
  `data` longtext COMMENT '额外信息字段',
  `start_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_user_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT '用户',
  `coupon_id` int(11) NOT NULL COMMENT '优惠卷',
  `sub_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '满减',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '折扣',
  `coupon_min_price` decimal(10,2) NOT NULL COMMENT '最低消费金额',
  `type` int(11) NOT NULL DEFAULT '1' COMMENT '优惠券类型：1=折扣，2=满减',
  `start_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '有效期开始时间',
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '有效期结束时间',
  `is_use` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已使用：0=未使用，1=已使用',
  `is_delete` smallint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  `receive_type` varchar(255) NOT NULL DEFAULT '' COMMENT '获取方式',
  `coupon_data` longtext NOT NULL COMMENT '优惠券信息json格式',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `store_id` (`mall_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `coupon_id` (`coupon_id`) USING BTREE,
  KEY `is_delete` (`is_delete`) USING BTREE,
  KEY `is_use` (`is_use`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_user_coupon_auto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_coupon_id` int(11) NOT NULL,
  `auto_coupon_id` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_user_coupon_center` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL DEFAULT '0' COMMENT '商城ID',
  `user_coupon_id` int(11) NOT NULL DEFAULT '0' COMMENT '优惠券ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `is_delete` int(11) NOT NULL DEFAULT '0' COMMENT '是否删除 0--不删除 1--删除',
  `created_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户领取的优惠券关联表（领券中心）';


CREATE TABLE `zjhj_bd_user_coupon_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `member_level` int(11) NOT NULL DEFAULT '0' COMMENT '会员等级',
  `user_coupon_id` int(11) NOT NULL,
  `is_delete` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_user_identity` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户身份表',
  `user_id` int(11) NOT NULL,
  `is_super_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为超级管理员',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为管理员',
  `is_operator` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为操作员|员工',
  `member_level` int(11) NOT NULL DEFAULT '0' COMMENT '会员等级:0.普通成员',
  `is_distributor` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为分销商',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_user_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `platform_user_id` varchar(255) NOT NULL DEFAULT '' COMMENT '用户所属平台的用户id',
  `integral` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
  `total_integral` int(11) NOT NULL DEFAULT '0' COMMENT '最高积分',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `total_balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总余额',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '上级id',
  `is_blacklist` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否黑名单',
  `contact_way` varchar(255) NOT NULL DEFAULT '' COMMENT '联系方式',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `junior_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '成为下级时间',
  `platform` varchar(255) NOT NULL DEFAULT '' COMMENT '用户所属平台标识',
  `temp_parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '临时上级',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_video` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT '标题',
  `type` tinyint(1) NOT NULL COMMENT '视频来源 0--源地址 1--腾讯视频',
  `url` varchar(2048) NOT NULL DEFAULT '' COMMENT '链接',
  `pic_url` varchar(255) NOT NULL COMMENT '封面图',
  `content` longtext NOT NULL COMMENT '详情介绍',
  `sort` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_we7_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `acid` int(11) NOT NULL COMMENT '微擎应用的acid',
  `is_delete` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_wxapp_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `appid` varchar(128) NOT NULL,
  `appsecret` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `mchid` varchar(32) NOT NULL,
  `key` varchar(32) NOT NULL,
  `cert_pem` varchar(2000) NOT NULL DEFAULT '',
  `key_pem` varchar(2000) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_wxapp_jump_appid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `appid` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `zjhj_bd_wxapp_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `tpl_name` varchar(65) NOT NULL DEFAULT '',
  `tpl_id` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


SET foreign_key_checks = 1;
