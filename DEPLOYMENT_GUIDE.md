# 实验教学管理系统 - 部署指南

## 📋 系统要求

### 服务器环境
- **操作系统**: Ubuntu 20.04+ / CentOS 7+ / Windows Server 2019+
- **CPU**: 2核心以上
- **内存**: 4GB以上
- **存储**: 50GB以上可用空间
- **网络**: 稳定的互联网连接

### 软件依赖

#### 后端环境
- **PHP**: 8.0+
- **Composer**: 2.0+
- **Web服务器**: Nginx 1.18+ / Apache 2.4+
- **数据库**: MySQL 8.0+ / MariaDB 10.5+
- **缓存**: Redis 6.0+ (可选)

#### 前端环境
- **Node.js**: 16.0+
- **npm**: 8.0+

## 🚀 快速部署

### 1. 环境准备

#### Ubuntu/Debian 系统
```bash
# 更新系统
sudo apt update && sudo apt upgrade -y

# 安装基础软件
sudo apt install -y nginx mysql-server redis-server git curl

# 安装 PHP 8.0
sudo apt install -y php8.0-fpm php8.0-mysql php8.0-redis php8.0-xml php8.0-mbstring php8.0-curl php8.0-zip php8.0-gd

# 安装 Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 安装 Node.js
curl -fsSL https://deb.nodesource.com/setup_16.x | sudo -E bash -
sudo apt install -y nodejs
```

#### CentOS/RHEL 系统
```bash
# 更新系统
sudo yum update -y

# 安装 EPEL 仓库
sudo yum install -y epel-release

# 安装基础软件
sudo yum install -y nginx mysql-server redis git curl

# 安装 PHP 8.0 (需要 Remi 仓库)
sudo yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm
sudo yum-config-manager --enable remi-php80
sudo yum install -y php php-fpm php-mysql php-redis php-xml php-mbstring php-curl php-zip php-gd

# 安装 Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 安装 Node.js
curl -fsSL https://rpm.nodesource.com/setup_16.x | sudo bash -
sudo yum install -y nodejs
```

### 2. 数据库配置

```bash
# 启动 MySQL 服务
sudo systemctl start mysql
sudo systemctl enable mysql

# 安全配置
sudo mysql_secure_installation

# 创建数据库和用户
mysql -u root -p
```

```sql
-- 创建数据库
CREATE DATABASE experiment_teaching CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 创建用户
CREATE USER 'experiment_user'@'localhost' IDENTIFIED BY 'your_strong_password';

-- 授权
GRANT ALL PRIVILEGES ON experiment_teaching.* TO 'experiment_user'@'localhost';
FLUSH PRIVILEGES;

-- 退出
EXIT;
```

### 3. 项目部署

#### 下载项目代码
```bash
# 创建项目目录
sudo mkdir -p /var/www/experiment-teaching
cd /var/www/experiment-teaching

# 克隆代码 (假设从Git仓库)
sudo git clone https://github.com/your-repo/experiment-teaching-system.git .

# 设置权限
sudo chown -R www-data:www-data /var/www/experiment-teaching
sudo chmod -R 755 /var/www/experiment-teaching
```

#### 后端部署
```bash
# 进入后端目录
cd /var/www/experiment-teaching/backend

# 安装依赖
composer install --optimize-autoloader --no-dev

# 复制环境配置
cp .env.example .env

# 编辑环境配置
nano .env
```

**环境配置示例** (`.env`):
```env
APP_NAME="实验教学管理系统"
APP_ENV=production
APP_KEY=base64:your_app_key_here
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=experiment_teaching
DB_USERNAME=experiment_user
DB_PASSWORD=your_strong_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

```bash
# 生成应用密钥
php artisan key:generate

# 运行数据库迁移
php artisan migrate

# 运行数据填充
php artisan db:seed

# 创建存储链接
php artisan storage:link

# 缓存配置
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 设置权限
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### 前端部署
```bash
# 进入前端目录
cd /var/www/experiment-teaching/frontend

# 安装依赖
npm install

# 构建生产版本
npm run build

# 复制构建文件到 Web 根目录
sudo cp -r dist/* /var/www/experiment-teaching/public/
```

### 4. Web 服务器配置

#### Nginx 配置
```bash
# 创建站点配置
sudo nano /etc/nginx/sites-available/experiment-teaching
```

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/experiment-teaching/public;
    index index.php index.html;

    # 前端路由支持
    location / {
        try_files $uri $uri/ /index.html;
    }

    # API 路由
    location /api {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP 处理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 静态文件缓存
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # 安全配置
    location ~ /\.ht {
        deny all;
    }

    # 文件上传大小限制
    client_max_body_size 10M;
}
```

```bash
# 启用站点
sudo ln -s /etc/nginx/sites-available/experiment-teaching /etc/nginx/sites-enabled/

# 测试配置
sudo nginx -t

# 重启 Nginx
sudo systemctl restart nginx
sudo systemctl enable nginx
```

#### Apache 配置 (可选)
```bash
# 创建虚拟主机配置
sudo nano /etc/apache2/sites-available/experiment-teaching.conf
```

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/experiment-teaching/public

    <Directory /var/www/experiment-teaching/public>
        AllowOverride All
        Require all granted
    </Directory>

    # PHP 配置
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.0-fpm.sock|fcgi://localhost"
    </FilesMatch>

    # 错误日志
    ErrorLog ${APACHE_LOG_DIR}/experiment-teaching_error.log
    CustomLog ${APACHE_LOG_DIR}/experiment-teaching_access.log combined
</VirtualHost>
```

```bash
# 启用站点和模块
sudo a2ensite experiment-teaching
sudo a2enmod rewrite proxy_fcgi setenvif
sudo systemctl restart apache2
sudo systemctl enable apache2
```

### 5. SSL 证书配置

#### 使用 Let's Encrypt
```bash
# 安装 Certbot
sudo apt install -y certbot python3-certbot-nginx

# 获取证书
sudo certbot --nginx -d your-domain.com

# 自动续期
sudo crontab -e
# 添加以下行
0 12 * * * /usr/bin/certbot renew --quiet
```

### 6. 系统服务配置

#### 启动必要服务
```bash
# 启动 Redis
sudo systemctl start redis
sudo systemctl enable redis

# 启动 PHP-FPM
sudo systemctl start php8.0-fpm
sudo systemctl enable php8.0-fpm

# 检查服务状态
sudo systemctl status nginx mysql redis php8.0-fpm
```

#### 配置队列处理 (可选)
```bash
# 创建队列服务
sudo nano /etc/systemd/system/experiment-queue.service
```

```ini
[Unit]
Description=Experiment Teaching Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/experiment-teaching/backend
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3
Restart=always

[Install]
WantedBy=multi-user.target
```

```bash
# 启动队列服务
sudo systemctl daemon-reload
sudo systemctl start experiment-queue
sudo systemctl enable experiment-queue
```

## 🔧 性能优化

### 1. PHP 优化
```bash
# 编辑 PHP 配置
sudo nano /etc/php/8.0/fpm/php.ini
```

```ini
# 内存限制
memory_limit = 256M

# 文件上传
upload_max_filesize = 10M
post_max_size = 10M

# 执行时间
max_execution_time = 60

# OPcache 配置
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 2. MySQL 优化
```bash
# 编辑 MySQL 配置
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

```ini
[mysqld]
# 缓冲池大小 (设置为内存的 70-80%)
innodb_buffer_pool_size = 2G

# 查询缓存
query_cache_type = 1
query_cache_size = 64M

# 连接数
max_connections = 200

# 慢查询日志
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

### 3. Redis 优化
```bash
# 编辑 Redis 配置
sudo nano /etc/redis/redis.conf
```

```ini
# 内存限制
maxmemory 512mb
maxmemory-policy allkeys-lru

# 持久化
save 900 1
save 300 10
save 60 10000
```

## 🔒 安全配置

### 1. 防火墙设置
```bash
# 安装 UFW
sudo apt install -y ufw

# 配置防火墙规则
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'

# 启用防火墙
sudo ufw enable
```

### 2. 文件权限
```bash
# 设置正确的文件权限
sudo find /var/www/experiment-teaching -type f -exec chmod 644 {} \;
sudo find /var/www/experiment-teaching -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/experiment-teaching/backend/storage
sudo chmod -R 775 /var/www/experiment-teaching/backend/bootstrap/cache
```

### 3. 隐藏敏感信息
```bash
# 隐藏 Nginx 版本
sudo nano /etc/nginx/nginx.conf
# 添加: server_tokens off;

# 隐藏 PHP 版本
sudo nano /etc/php/8.0/fpm/php.ini
# 设置: expose_php = Off
```

## 📊 监控和维护

### 1. 日志监控
```bash
# 查看应用日志
tail -f /var/www/experiment-teaching/backend/storage/logs/laravel.log

# 查看 Nginx 日志
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# 查看 MySQL 日志
tail -f /var/log/mysql/error.log
```

### 2. 性能监控
```bash
# 安装监控工具
sudo apt install -y htop iotop nethogs

# 监控系统资源
htop
iotop
nethogs
```

### 3. 备份策略
```bash
# 创建备份脚本
sudo nano /usr/local/bin/backup-experiment-teaching.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/backup/experiment-teaching"
DATE=$(date +%Y%m%d_%H%M%S)

# 创建备份目录
mkdir -p $BACKUP_DIR

# 备份数据库
mysqldump -u experiment_user -p experiment_teaching > $BACKUP_DIR/database_$DATE.sql

# 备份文件
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/experiment-teaching

# 删除 7 天前的备份
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

```bash
# 设置执行权限
sudo chmod +x /usr/local/bin/backup-experiment-teaching.sh

# 添加定时任务
sudo crontab -e
# 添加: 0 2 * * * /usr/local/bin/backup-experiment-teaching.sh
```

## 🆘 故障排除

### 常见问题

1. **500 错误**
   - 检查 PHP 错误日志
   - 验证文件权限
   - 检查 .env 配置

2. **数据库连接失败**
   - 验证数据库服务状态
   - 检查连接参数
   - 确认用户权限

3. **文件上传失败**
   - 检查 PHP 上传限制
   - 验证存储目录权限
   - 确认磁盘空间

4. **页面加载缓慢**
   - 检查服务器资源使用
   - 优化数据库查询
   - 启用缓存

### 联系支持
- 技术支持: support@example.com
- 紧急联系: +86-xxx-xxxx-xxxx

---

**部署指南版本**: v1.0  
**更新时间**: 2025-07-03  
**适用系统**: 实验教学管理系统 v1.0
