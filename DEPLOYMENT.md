# Deploying Forex Trading Platform to Linux VPS

Complete guide for deploying the forex trading platform to a production Linux VPS with Apache, MySQL, and SSL.

---

## Prerequisites

- Linux VPS (Ubuntu 20.04/22.04 or Debian 10/11 recommended)
- Root or sudo access
- Domain name pointed to your VPS IP (for SSL)
- Minimum 1GB RAM, 1 CPU core, 20GB disk

---

## Option 1: Automated Deployment (Recommended)

Use the provided deployment script for one-command setup:

```bash
# Download the deployment script
wget https://raw.githubusercontent.com/your-repo/new-forex/main/deploy.sh

# Make it executable
chmod +x deploy.sh

# Run the script
sudo ./deploy.sh
```

The script will:
- Install LAMP stack (Apache, MySQL, PHP 8.x)
- Create database and user
- Upload and configure the application
- Set up Apache virtual host
- Configure SSL with Let's Encrypt
- Set proper file permissions
- Create backup scripts

---

## Option 2: Manual Deployment

### Step 1: Update System and Install LAMP Stack

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install Apache
sudo apt install apache2 -y

# Install MySQL
sudo apt install mysql-server -y

# Install PHP and required extensions
sudo apt install php8.1 php8.1-mysql php8.1-curl php8.1-mbstring php8.1-xml php8.1-zip -y

# Enable Apache modules
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers

# Restart Apache
sudo systemctl restart apache2
```

### Step 2: Secure MySQL

```bash
sudo mysql_secure_installation
```

Follow prompts:
- Set root password: Yes
- Remove anonymous users: Yes
- Disallow root login remotely: Yes
- Remove test database: Yes
- Reload privilege tables: Yes

### Step 3: Create Database and User

```bash
sudo mysql -u root -p
```

Run these SQL commands:

```sql
CREATE DATABASE forex_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'forex_user'@'localhost' IDENTIFIED BY 'YourStrongPassword123!';
GRANT ALL PRIVILEGES ON forex_db.* TO 'forex_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 4: Upload Application Files

```bash
# Create application directory
sudo mkdir -p /var/www/forex

# Upload files (use SCP, SFTP, or Git)
# Example with SCP from local machine:
scp -r C:\xampp\htdocs\new_forex\* user@your-server-ip:/tmp/forex/

# Or clone from Git:
cd /var/www
sudo git clone https://github.com/your-repo/new-forex.git forex

# Set ownership
sudo chown -R www-data:www-data /var/www/forex

# Set permissions
sudo find /var/www/forex -type d -exec chmod 755 {} \;
sudo find /var/www/forex -type f -exec chmod 644 {} \;
sudo chmod 600 /var/www/forex/includes/db.php
```

### Step 5: Configure Database Connection

```bash
sudo nano /var/www/forex/includes/db.php
```

Update with your credentials:

```php
<?php
$servername = "localhost";
$username = "forex_user";
$password = "YourStrongPassword123!";
$dbname = "forex_db";

try{
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    die("Could not connect to the database $dbname :" . $e->getMessage());
}
```

### Step 6: Run Database Migration

```bash
# Visit in browser or use command line
php /var/www/forex/migrate.php

# Or manually import
sudo mysql -u forex_user -p forex_db < /var/www/forex/migrations/001_enhanced_schema.sql
```

### Step 7: Configure Apache Virtual Host

```bash
sudo nano /etc/apache2/sites-available/forex.conf
```

Add this configuration:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    ServerAdmin admin@your-domain.com
    
    DocumentRoot /var/www/forex
    
    <Directory /var/www/forex>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Security headers
        Header set X-Content-Type-Options "nosniff"
        Header set X-Frame-Options "SAMEORIGIN"
        Header set X-XSS-Protection "1; mode=block"
        Header set Referrer-Policy "strict-origin-when-cross-origin"
    </Directory>
    
    # Deny access to sensitive files
    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>
    
    <FilesMatch "\.(sql|md|json|lock)$">
        Require all denied
    </FilesMatch>
    
    ErrorLog ${APACHE_LOG_DIR}/forex_error.log
    CustomLog ${APACHE_LOG_DIR}/forex_access.log combined
</VirtualHost>
```

Enable the site:

```bash
sudo a2ensite forex.conf
sudo a2dissite 000-default.conf
sudo systemctl reload apache2
```

### Step 8: Set Up SSL with Let's Encrypt

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Obtain and install certificate
sudo certbot --apache -d your-domain.com -d www.your-domain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

Certbot will automatically:
- Obtain SSL certificate
- Configure Apache to use HTTPS
- Set up auto-renewal

### Step 9: Configure Firewall

```bash
# Install UFW
sudo apt install ufw -y

# Allow SSH (important!)
sudo ufw allow OpenSSH

# Allow HTTP and HTTPS
sudo ufw allow 'Apache Full'

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status
```

### Step 10: Create Backup Script

```bash
sudo nano /usr/local/bin/backup-forex.sh
```

Add this content:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/forex"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="forex_db"
DB_USER="forex_user"
DB_PASS="YourStrongPassword123!"

mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/forex

# Keep only last 7 days
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

Make it executable and schedule:

```bash
sudo chmod +x /usr/local/bin/backup-forex.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
```

Add this line:

```
0 2 * * * /usr/local/bin/backup-forex.sh >> /var/log/forex-backup.log 2>&1
```

---

## Post-Deployment Configuration

### Update Manifest for Your Domain

Edit `/var/www/forex/manifest.webmanifest`:

```json
{
  "name": "Forex Trading Platform",
  "short_name": "Forex",
  "start_url": "/",
  "scope": "/",
  ...
}
```

### Update Service Worker Paths

Edit `/var/www/forex/service-worker.js`:

```javascript
const APP_SHELL = [
  '/',
  '/index.php',
  '/assets/css/style.css',
  ...
];
```

### Test PWA Installation

1. Visit your domain: https://your-domain.com
2. Chrome: Menu → Install app
3. iOS Safari: Share → Add to Home Screen

---

## Security Hardening

### 1. Hide PHP Version

```bash
sudo nano /etc/php/8.1/apache2/php.ini
```

Set:
```ini
expose_php = Off
```

### 2. Disable Directory Listing

Already configured in VirtualHost with `Options -Indexes`

### 3. Set Up Fail2Ban (Optional)

```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 4. Regular Updates

```bash
# Add to cron for weekly updates
0 3 * * 0 apt update && apt upgrade -y
```

---

## Monitoring & Logs

### Check Apache Logs

```bash
# Error log
sudo tail -f /var/log/apache2/forex_error.log

# Access log
sudo tail -f /var/log/apache2/forex_access.log
```

### Check PHP Errors

```bash
sudo tail -f /var/log/apache2/error.log
```

### Monitor System Resources

```bash
# Install htop
sudo apt install htop -y

# Run
htop
```

---

## Performance Optimization

### Enable Gzip Compression

```bash
sudo a2enmod deflate
```

Add to VirtualHost:

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>
```

### Enable Browser Caching

Add to VirtualHost:

```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## Troubleshooting

### Issue: "500 Internal Server Error"

Check error logs:
```bash
sudo tail -f /var/log/apache2/forex_error.log
```

Common causes:
- Incorrect file permissions
- PHP syntax errors
- Database connection issues

### Issue: PWA Not Installing

Requirements:
- Must be served over HTTPS
- Valid manifest.webmanifest
- Service worker registered
- Icons accessible

Check:
```bash
curl -I https://your-domain.com/manifest.webmanifest
curl -I https://your-domain.com/service-worker.js
```

### Issue: Database Connection Failed

Verify credentials:
```bash
mysql -u forex_user -p forex_db
```

Check if MySQL is running:
```bash
sudo systemctl status mysql
```

### Issue: Charts Not Loading

Check if PHP extensions are installed:
```bash
php -m | grep -E 'curl|json|mbstring'
```

Verify file permissions:
```bash
ls -la /var/www/forex/helpers/
```

---

## Updating the Application

```bash
# Backup first
sudo /usr/local/bin/backup-forex.sh

# Pull updates (if using Git)
cd /var/www/forex
sudo -u www-data git pull

# Or upload new files via SCP

# Clear cache if needed
sudo rm -rf /var/www/forex/cache/*

# Restart Apache
sudo systemctl restart apache2
```

---

## Rollback Procedure

```bash
# Restore database
gunzip < /var/backups/forex/db_YYYYMMDD_HHMMSS.sql.gz | mysql -u forex_user -p forex_db

# Restore files
sudo tar -xzf /var/backups/forex/files_YYYYMMDD_HHMMSS.tar.gz -C /

# Restart Apache
sudo systemctl restart apache2
```

---

## Resources

- Apache Documentation: https://httpd.apache.org/docs/
- Let's Encrypt: https://letsencrypt.org/
- PHP Manual: https://www.php.net/manual/
- MySQL Documentation: https://dev.mysql.com/doc/

---

## Support

For issues or questions:
- Check logs: `/var/log/apache2/`
- Review `README.md` for application usage
- Check `DATABASE_MIGRATION.md` for schema details
