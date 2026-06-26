# Grace Hymn Planner｜恩典圣诗选诗系统

Grace Hymn Planner 是给教会司会使用的圣诗资料库与主日选诗工作台。系统支持圣诗录入、细粒度标签、曲调管理、歌谱附件、完整度提示、本周候选池、诗歌环节分配和清单导出。

## 运行环境

- PHP 8.0+
- MySQL 5.7.44 兼容
- PDO 与 pdo_mysql
- Nginx + PHP-FPM、Apache + PHP 或普通虚拟主机

数据库统一使用 `utf8mb4_unicode_ci`，未使用 MySQL 8 专属语法。

## 安装步骤

1. 将项目部署到服务器目录。
2. 推荐将网站运行目录指向 `public`。如果虚拟主机只能把项目根目录作为运行目录，也可以直接部署到项目根目录，系统已兼容这种方式。
3. 确保 `config`、`public/uploads`、`storage/logs` 可写。
4. 创建一个空 MySQL 数据库。
5. 浏览器访问 `/install`。
6. 填写数据库信息和管理员账号。
7. 安装器会创建数据表、写入默认标签、生成配置文件并创建 `install.lock`。

安装完成后访问 `/login` 登录。

## 目录权限

需要可写：

- `config`
- `public/uploads`
- `public/uploads/hymns`
- `public/uploads/tunes`
- `storage/logs`

安装后会生成：

- `config/database.php`
- `config/app.php`
- `install.lock`

这些文件不会提交到 Git。

## 部署路径说明

推荐方式：

```text
网站运行目录：Grace-Hymn-Planner/public
访问安装器：/install
静态资源路径：/assets/app.css
```

虚拟主机常见方式：

```text
网站运行目录：Grace-Hymn-Planner
访问安装器：/install
静态资源路径：/public/assets/app.css
```

根目录部署时，项目根目录的 `index.php` 会转发到 `public/index.php`，页面会自动使用 `/public/assets/...` 资源路径。

## Nginx 配置示例

```nginx
server {
    listen 80;
    server_name example.com;
    root /www/wwwroot/Grace-Hymn-Planner/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi-80.sock;
        fastcgi_index index.php;
        include fastcgi.conf;
    }

    location ~* \.(php|phtml)$ {
        try_files $uri =404;
    }
}
```

## Apache 配置示例

将站点根目录指向 `public`，并启用 `mod_rewrite`。项目已包含 `public/.htaccess`：

```apache
<VirtualHost *:80>
    ServerName example.com
    DocumentRoot /var/www/Grace-Hymn-Planner/public

    <Directory /var/www/Grace-Hymn-Planner/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## 数据库说明

核心表包括：

- `users`：用户与角色
- `hymns`：圣诗主表
- `tunes`：曲调 / 旋律
- `tag_groups`、`tags`、`hymn_tag`：标签体系
- `hymn_files`、`tune_files`：附件
- `service_plans`、`service_plan_items`：本周崇拜计划与候选/选用诗歌
- `settings`、`activity_logs`：设置与日志预留

完整 SQL 位于 `install/schema.sql`。

## 默认管理员

默认管理员不是硬编码账号。安装时在 `/install` 页面创建，密码使用 `password_hash()` 存储。

## 文件上传

圣诗附件保存到：

```text
public/uploads/hymns/{hymn_id}/
```

支持格式：

```text
pdf, jpg, jpeg, png, webp, ppt, pptx, doc, docx, mp3, m4a
```

禁止上传 PHP、脚本和可执行文件类型。

## 常见问题

### 访问页面 404

请确认网站运行目录设置。如果指向项目根目录，静态资源应通过 `/public/assets/app.css` 访问；如果指向 `public`，静态资源应通过 `/assets/app.css` 访问。还需要确认 Nginx `try_files` 或 Apache `mod_rewrite` 已配置。

### 安装器提示目录不可写

给 `config`、`public/uploads`、`storage/logs` 设置写入权限后刷新 `/install`。

### 想重新安装

删除 `install.lock`、`config/database.php`、`config/app.php`，并清空数据库后重新访问 `/install`。生产环境请谨慎操作。

### 中文搜索不够精确

MVP 使用 MySQL `LIKE` 搜索，兼容 MySQL 5.7。后续可接入更强的中文分词或专用搜索服务。
