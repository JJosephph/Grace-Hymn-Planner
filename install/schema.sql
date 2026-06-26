CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT COMMENT '用户ID',
  username VARCHAR(60) NOT NULL COMMENT '登录用户名',
  password_hash VARCHAR(255) NOT NULL COMMENT '密码哈希值',
  nickname VARCHAR(100) NOT NULL COMMENT '用户昵称',
  role VARCHAR(20) NOT NULL DEFAULT 'admin' COMMENT '用户角色：admin管理员，editor编辑，viewer只读',
  status VARCHAR(20) NOT NULL DEFAULT 'active' COMMENT '账号状态：active正常，disabled禁用',
  last_login_at DATETIME DEFAULT NULL COMMENT '最后登录时间',
  created_at DATETIME NOT NULL COMMENT '创建时间',
  updated_at DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (id),
  UNIQUE KEY uk_username (username),
  KEY idx_role (role),
  KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

CREATE TABLE tunes (
  id INT UNSIGNED AUTO_INCREMENT COMMENT '曲调ID',
  tune_name VARCHAR(255) NOT NULL COMMENT '曲调名称，例如 HYFRYDOL、OLD HUNDREDTH',
  tune_name_en VARCHAR(255) DEFAULT NULL COMMENT '曲调英文名或别名',
  composer VARCHAR(255) DEFAULT NULL COMMENT '曲作者',
  meter VARCHAR(100) DEFAULT NULL COMMENT '韵律，例如 8.7.8.7.D',
  key_signature VARCHAR(50) DEFAULT NULL COMMENT '常用调号',
  tempo VARCHAR(50) DEFAULT NULL COMMENT '常用速度或情绪，例如 solemn、moderate、joyful',
  note TEXT COMMENT '曲调备注',
  created_at DATETIME NOT NULL COMMENT '创建时间',
  updated_at DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (id),
  KEY idx_tune_name (tune_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='曲调表，用于管理同曲不同词';

CREATE TABLE hymns (
  id INT UNSIGNED AUTO_INCREMENT COMMENT '圣诗ID',
  tune_id INT UNSIGNED DEFAULT NULL COMMENT '关联曲调ID，用于同曲不同词管理',
  title_cn VARCHAR(255) NOT NULL COMMENT '中文诗歌名，唯一必填字段',
  title_en VARCHAR(255) DEFAULT NULL COMMENT '英文诗歌名',
  alias TEXT COMMENT '别名，可存多个名称，逗号分隔或文本记录',
  first_line VARCHAR(500) DEFAULT NULL COMMENT '第一句歌词，便于搜索和识别',
  lyrics MEDIUMTEXT COMMENT '完整歌词',
  ppt_lyrics MEDIUMTEXT COMMENT 'PPT简版歌词，可用于直接复制到PPT',
  author VARCHAR(255) DEFAULT NULL COMMENT '词作者',
  composer VARCHAR(255) DEFAULT NULL COMMENT '曲作者，如已关联曲调可为空',
  translator VARCHAR(255) DEFAULT NULL COMMENT '译者',
  source_book VARCHAR(255) DEFAULT NULL COMMENT '来源歌本',
  hymn_number VARCHAR(50) DEFAULT NULL COMMENT '歌本编号',
  key_signature VARCHAR(50) DEFAULT NULL COMMENT '调号',
  meter VARCHAR(100) DEFAULT NULL COMMENT '韵律',
  scripture_refs VARCHAR(500) DEFAULT NULL COMMENT '相关经文，例如 罗6:5-8；加2:20',
  doctrine_summary TEXT COMMENT '神学摘要，用于说明诗歌主题',
  usage_note TEXT COMMENT '使用建议，例如适合讲道前或证道回应',
  copyright_note TEXT COMMENT '版权说明',
  license_status VARCHAR(30) NOT NULL DEFAULT 'unknown' COMMENT '版权状态：unknown未知，public_domain公版，church_internal教会内部，licensed已授权',
  difficulty TINYINT UNSIGNED NOT NULL DEFAULT 3 COMMENT '演唱难度：1很易，5较难',
  familiarity TINYINT UNSIGNED NOT NULL DEFAULT 3 COMMENT '会众熟悉度：1陌生，5非常熟悉',
  tempo VARCHAR(50) DEFAULT NULL COMMENT '速度或情绪，例如 solemn、moderate、joyful',
  status VARCHAR(20) NOT NULL DEFAULT 'active' COMMENT '诗歌状态：active正常，hidden隐藏，archived归档',
  completeness_status VARCHAR(30) NOT NULL DEFAULT 'draft' COMMENT '资料完整度状态：draft草稿，incomplete不完整，usable基本可用，complete完整',
  completeness_score TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '资料完整度评分，0-100',
  missing_fields VARCHAR(500) DEFAULT NULL COMMENT '缺失字段标记，例如 lyrics,score_files,tags,scripture_refs',
  last_used_at DATETIME DEFAULT NULL COMMENT '最近使用时间',
  used_count INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '使用次数',
  created_at DATETIME NOT NULL COMMENT '创建时间',
  updated_at DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (id),
  KEY idx_tune_id (tune_id),
  KEY idx_title_cn (title_cn),
  KEY idx_status (status),
  KEY idx_completeness_status (completeness_status),
  KEY idx_familiarity (familiarity),
  KEY idx_difficulty (difficulty),
  KEY idx_last_used_at (last_used_at),
  CONSTRAINT fk_hymns_tune FOREIGN KEY (tune_id) REFERENCES tunes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='圣诗主表';

CREATE TABLE tag_groups (
  id INT UNSIGNED AUTO_INCREMENT COMMENT '标签分组ID',
  name VARCHAR(100) NOT NULL COMMENT '标签分组名称，例如 崇拜环节',
  code VARCHAR(100) NOT NULL COMMENT '标签分组编码，例如 worship_slot',
  description TEXT COMMENT '标签分组说明',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序值，越小越靠前',
  created_at DATETIME NOT NULL COMMENT '创建时间',
  updated_at DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (id),
  UNIQUE KEY uk_code (code),
  KEY idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='标签分组表';

CREATE TABLE tags (
  id INT UNSIGNED AUTO_INCREMENT COMMENT '标签ID',
  group_id INT UNSIGNED NOT NULL COMMENT '所属标签分组ID',
  name VARCHAR(100) NOT NULL COMMENT '标签名称，例如 十字架、与主同死',
  code VARCHAR(100) NOT NULL COMMENT '标签编码，例如 cross、dying_with_christ',
  description TEXT COMMENT '标签说明',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序值，越小越靠前',
  created_at DATETIME NOT NULL COMMENT '创建时间',
  updated_at DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (id),
  UNIQUE KEY uk_group_code (group_id, code),
  KEY idx_group_id (group_id),
  KEY idx_sort_order (sort_order),
  CONSTRAINT fk_tags_group FOREIGN KEY (group_id) REFERENCES tag_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='标签表';

CREATE TABLE hymn_tag (
  hymn_id INT UNSIGNED NOT NULL COMMENT '圣诗ID',
  tag_id INT UNSIGNED NOT NULL COMMENT '标签ID',
  created_at DATETIME NOT NULL COMMENT '创建时间',
  PRIMARY KEY (hymn_id, tag_id),
  KEY idx_tag_id (tag_id),
  CONSTRAINT fk_hymn_tag_hymn FOREIGN KEY (hymn_id) REFERENCES hymns(id) ON DELETE CASCADE,
  CONSTRAINT fk_hymn_tag_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='圣诗与标签关联表';

CREATE TABLE hymn_files (
  id INT UNSIGNED AUTO_INCREMENT COMMENT '附件ID',
  hymn_id INT UNSIGNED NOT NULL COMMENT '所属圣诗ID',
  file_type VARCHAR(50) NOT NULL COMMENT '附件类型：score_image歌谱图片，score_pdf歌谱PDF，ppt，doc，audio，other',
  file_name VARCHAR(255) NOT NULL COMMENT '服务器保存文件名',
  original_name VARCHAR(255) NOT NULL COMMENT '原始文件名',
  file_path VARCHAR(500) NOT NULL COMMENT '文件保存路径',
  file_size INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件大小，单位字节',
  mime_type VARCHAR(120) DEFAULT NULL COMMENT '文件MIME类型',
  is_cover TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否封面歌谱：0否，1是',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序值，越小越靠前',
  created_at DATETIME NOT NULL COMMENT '创建时间',
  PRIMARY KEY (id),
  KEY idx_hymn_id (hymn_id),
  KEY idx_file_type (file_type),
  CONSTRAINT fk_hymn_files_hymn FOREIGN KEY (hymn_id) REFERENCES hymns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='圣诗附件表';

CREATE TABLE tune_files (
  id INT UNSIGNED AUTO_INCREMENT COMMENT '曲调附件ID',
  tune_id INT UNSIGNED NOT NULL COMMENT '所属曲调ID',
  file_type VARCHAR(50) NOT NULL COMMENT '附件类型：score_image曲谱图片，score_pdf曲谱PDF，audio伴奏音频，other其他',
  file_name VARCHAR(255) NOT NULL COMMENT '服务器保存文件名',
  original_name VARCHAR(255) NOT NULL COMMENT '原始文件名',
  file_path VARCHAR(500) NOT NULL COMMENT '文件保存路径',
  file_size INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件大小，单位字节',
  mime_type VARCHAR(120) DEFAULT NULL COMMENT '文件MIME类型',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序值，越小越靠前',
  created_at DATETIME NOT NULL COMMENT '创建时间',
  PRIMARY KEY (id),
  KEY idx_tune_id (tune_id),
  KEY idx_file_type (file_type),
  CONSTRAINT fk_tune_files_tune FOREIGN KEY (tune_id) REFERENCES tunes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='曲调附件表';

CREATE TABLE service_plans (
  id INT UNSIGNED AUTO_INCREMENT COMMENT '崇拜计划ID',
  title VARCHAR(255) NOT NULL COMMENT '计划标题，例如 2026-06-28 主日崇拜',
  service_date DATE NOT NULL COMMENT '崇拜日期',
  sermon_title VARCHAR(255) DEFAULT NULL COMMENT '证道题目',
  sermon_scripture VARCHAR(255) DEFAULT NULL COMMENT '证道经文',
  sermon_theme TEXT COMMENT '证道主题句',
  sermon_outline MEDIUMTEXT COMMENT '证道大纲',
  sermon_keywords VARCHAR(500) DEFAULT NULL COMMENT '证道关键词，逗号分隔',
  notes TEXT COMMENT '备注',
  created_at DATETIME NOT NULL COMMENT '创建时间',
  updated_at DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (id),
  KEY idx_service_date (service_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='主日崇拜计划表';

CREATE TABLE service_plan_items (
  id INT UNSIGNED AUTO_INCREMENT COMMENT '崇拜计划诗歌项ID',
  service_plan_id INT UNSIGNED NOT NULL COMMENT '所属崇拜计划ID',
  hymn_id INT UNSIGNED NOT NULL COMMENT '关联圣诗ID',
  slot_type VARCHAR(50) NOT NULL DEFAULT 'candidate' COMMENT '诗歌环节：candidate候选，opening第一首，second第二首，before_sermon讲道前，response回应，communion圣餐，baptism洗礼，other其他',
  item_status VARCHAR(30) NOT NULL DEFAULT 'candidate' COMMENT '项目状态：candidate候选，selected已选，removed移除',
  note TEXT COMMENT '备注，例如适合原因、司琴提醒',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序值，越小越靠前',
  created_at DATETIME NOT NULL COMMENT '创建时间',
  updated_at DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (id),
  KEY idx_plan_id (service_plan_id),
  KEY idx_hymn_id (hymn_id),
  KEY idx_slot_type (slot_type),
  KEY idx_item_status (item_status),
  CONSTRAINT fk_plan_items_plan FOREIGN KEY (service_plan_id) REFERENCES service_plans(id) ON DELETE CASCADE,
  CONSTRAINT fk_plan_items_hymn FOREIGN KEY (hymn_id) REFERENCES hymns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='崇拜计划诗歌项表，包含候选与已选诗歌';

CREATE TABLE settings (
  id INT UNSIGNED AUTO_INCREMENT COMMENT '设置ID',
  setting_key VARCHAR(100) NOT NULL COMMENT '设置键',
  setting_value MEDIUMTEXT COMMENT '设置值',
  updated_at DATETIME NOT NULL COMMENT '更新时间',
  PRIMARY KEY (id),
  UNIQUE KEY uk_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统设置表';

CREATE TABLE activity_logs (
  id INT UNSIGNED AUTO_INCREMENT COMMENT '日志ID',
  user_id INT UNSIGNED DEFAULT NULL COMMENT '操作用户ID',
  action VARCHAR(100) NOT NULL COMMENT '操作类型，例如 hymn.create、hymn.update',
  target_type VARCHAR(50) DEFAULT NULL COMMENT '操作对象类型，例如 hymn、tag、service_plan',
  target_id INT UNSIGNED DEFAULT NULL COMMENT '操作对象ID',
  description TEXT COMMENT '操作描述',
  ip_address VARCHAR(45) DEFAULT NULL COMMENT '操作IP地址',
  user_agent VARCHAR(500) DEFAULT NULL COMMENT '浏览器User-Agent',
  created_at DATETIME NOT NULL COMMENT '创建时间',
  PRIMARY KEY (id),
  KEY idx_user_id (user_id),
  KEY idx_action (action),
  KEY idx_target (target_type, target_id),
  KEY idx_created_at (created_at),
  CONSTRAINT fk_activity_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='操作日志表';

