# 📝 Exit Request System for IPG KBM

Assalamualaikum and Greetings! 🙏

This system is specially developed to facilitate the exit request process at the Institute of Teacher Education Malay Language Campus (IPG KBM). With this system, we can manage exit requests more systematically and efficiently.

## 🌟 Key Features

### 1. Systematic Exit Requests
- Complete digital forms
- Two-level approval process (Department/Unit Head & Director)
- Automatic PDF generation with digital signatures
- Clear and easy-to-understand request status

### 2. Multi-Role System
- 👤 **Regular User**: Can make requests
- 👨‍💼 **Department/Unit Head**: Provides initial approval
- 👨‍💼 **Director**: Gives final approval
- 👨‍💻 **Admin**: Manages the system and users

### 3. Conveniences
- User-friendly dashboard
- Clear notification system
- Request search and filtering
- Comprehensive request history

## 🛠️ Technical Guide

### System Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web Server (Apache/Nginx)
- Composer (PHP Package Manager)
- wkhtmltopdf (for PDF generation)

### Installation Steps

1. **Clone Repository**
   ```bash
   git clone https://github.com/username/kerajaan.git
   cd kerajaan
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Setup Database**
   - Create a new database in MySQL
   - Import the SQL file from the `database/` folder
   - Update the `app/config/database.php` file

4. **System Configuration**
   - Copy the `app/config/config.example.php` file to `app/config/config.php`
   - Update settings as needed
   - Ensure the `app/pdf` and `app/uploads` folders are writable

5. **Web Server Settings**
   - Ensure `mod_rewrite` is enabled for Apache
   - Direct the root directory to the `public/` folder

### Linux Server Installation Guide

Below are the steps for installing the system on a Linux server. This guide is suitable for most Linux distributions, focusing on Ubuntu/Debian and CentOS/RHEL.

1. **Initial Preparation**
   
   First, we need to ensure the system is updated:
   ```bash
   # For Ubuntu/Debian
   sudo apt update && sudo apt upgrade -y
   
   # For CentOS/RHEL
   sudo yum update -y
   ```
   
   Then install the required software:
   ```bash
   # For Ubuntu/Debian
   sudo apt install -y apache2 mysql-server php php-cli php-fpm php-json php-common php-mysql php-zip php-gd php-mbstring php-curl php-xml php-pear php-bcmath git unzip curl
   
   # For CentOS/RHEL
   sudo yum install -y httpd mariadb-server php php-cli php-json php-common php-mysqlnd php-zip php-gd php-mbstring php-curl php-xml php-pear php-bcmath git unzip curl
   ```
   
   > Tip: The installed software may require around 500MB of space, so make sure your server has enough space.

2. **Start Required Services**
   
   Start and enable Apache and MySQL to run automatically when the server boots:
   ```bash
   # For Ubuntu/Debian
   sudo systemctl start apache2
   sudo systemctl enable apache2
   sudo systemctl start mysql
   sudo systemctl enable mysql
   
   # For CentOS/RHEL
   sudo systemctl start httpd
   sudo systemctl enable httpd
   sudo systemctl start mariadb
   sudo systemctl enable mariadb
   ```

3. **MySQL Security Configuration**
   
   For security, it's very important to configure MySQL properly:
   ```bash
   sudo mysql_secure_installation
   ```
   
   During this process, you can:
   - Set a strong root password
   - Remove anonymous users
   - Restrict root access to localhost only
   - Remove test databases

4. **Setup Database**
   
   Let's create a database and user for the application:
   ```bash
   sudo mysql -u root -p
   ```
   
   After logging in, run the following SQL commands:
   ```sql
   CREATE DATABASE kerajaan;
   CREATE USER 'kerajaan_user'@'localhost' IDENTIFIED BY 'secure_password';
   GRANT ALL PRIVILEGES ON kerajaan.* TO 'kerajaan_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```
   
   > Important: Make sure you replace 'secure_password' with a stronger and more secure password!

5. **Install Composer**
   
   Composer is needed to manage PHP libraries:
   ```bash
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   sudo chmod +x /usr/local/bin/composer
   ```

6. **Install wkhtmltopdf**
   
   This application needs wkhtmltopdf to generate PDFs:
   ```bash
   # For Ubuntu/Debian
   sudo apt install -y wkhtmltopdf
   
   # For CentOS/RHEL (slightly more complex)
   sudo yum install -y xorg-x11-fonts-75dpi xorg-x11-fonts-Type1
   sudo yum install -y https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox-0.12.6-1.centos7.x86_64.rpm
   ```

7. **Install Application**
   
   Copy the application code to the web server folder:
   ```bash
   cd /var/www/html
   sudo git clone https://github.com/username/kerajaan.git
   cd kerajaan
   sudo composer install --no-dev --optimize-autoloader
   ```
   
   > Note: This process may take several minutes depending on the server speed and internet connection.

8. **Set File Access Rights**
   
   Proper file permissions are important for security:
   ```bash
   # For Ubuntu/Debian
   sudo chown -R www-data:www-data /var/www/html/kerajaan
   
   # For CentOS/RHEL
   sudo chown -R apache:apache /var/www/html/kerajaan
   
   # Set appropriate permissions
   sudo chmod -R 755 /var/www/html/kerajaan
   sudo chmod -R 775 /var/www/html/kerajaan/app/pdf
   sudo chmod -R 775 /var/www/html/kerajaan/app/uploads
   ```

9. **Application Configuration**
   
   Set up configuration files:
   ```bash
   # Set general configuration
   sudo cp app/config/config.example.php app/config/config.php
   sudo nano app/config/config.php
   
   # Set database configuration
   sudo cp app/config/database.example.php app/config/database.php
   sudo nano app/config/database.php
   ```
   
   Make sure URL, database name, username, and password are entered correctly.

10. **Import Initial Data**
    
    Insert initial data into the database:
    ```bash
    sudo mysql -u kerajaan_user -p kerajaan < database/kerajaan.sql
    ```

11. **Virtual Host Configuration**
    
    Set up a domain for the application:
    ```bash
    # For Ubuntu/Debian
    sudo nano /etc/apache2/sites-available/kerajaan.conf
    ```
    
    Or for CentOS/RHEL:
    ```bash
    sudo nano /etc/httpd/conf.d/kerajaan.conf
    ```
    
    Enter this configuration (change your-domain.com to the actual domain):
    
    ```apache
    <VirtualHost *:80>
        ServerName your-domain.com
        ServerAdmin webmaster@your-domain.com
        DocumentRoot /var/www/html/kerajaan/public

        <Directory /var/www/html/kerajaan/public>
            Options -Indexes +FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/kerajaan-error.log
        CustomLog ${APACHE_LOG_DIR}/kerajaan-access.log combined
    </VirtualHost>
    ```

12. **Enable Web Configuration**
    
    For Ubuntu/Debian:
    ```bash
    sudo a2ensite kerajaan.conf
    sudo a2enmod rewrite
    sudo systemctl restart apache2
    ```
    
    For CentOS/RHEL:
    ```bash
    sudo systemctl restart httpd
    ```

13. **Configure Firewall**
    
    Open required ports on the firewall:
    ```bash
    # For Ubuntu/Debian with UFW
    sudo ufw allow 80/tcp
    sudo ufw allow 443/tcp
    
    # For CentOS/RHEL with FirewallD
    sudo firewall-cmd --permanent --add-service=http
    sudo firewall-cmd --permanent --add-service=https
    sudo firewall-cmd --reload
    ```

14. **Regular Maintenance**
    
    Some tips for system maintenance:
    
    * **Daily Database Backup**
      
      Set up a daily backup schedule with crontab:
      ```bash
      sudo crontab -e
      ```
      
      Add this line for backup at 2 AM:
      ```
      0 2 * * * mysqldump -u kerajaan_user -p'secure_password' kerajaan > /var/backups/kerajaan_$(date +\%Y\%m\%d).sql
      ```
      
      > Reminder: Store backups in an external location or cloud for additional security.
      
    * **Temporary File Cleaning**
      
      Remove temporary files older than a week:
      ```
      0 3 * * 0 find /var/www/html/kerajaan/app/pdf/temp -type f -mtime +7 -delete
      ```

15. **System Monitoring**
    
    * **Check Logs For Issues**
      ```bash
      # For Ubuntu/Debian
      sudo tail -f /var/log/apache2/kerajaan-error.log
      
      # For CentOS/RHEL
      sudo tail -f /var/log/httpd/kerajaan-error.log
      ```
    
    * **Keep System Updated**
      ```bash
      # For Ubuntu/Debian - can be scheduled weekly
      sudo apt update && sudo apt upgrade -y
      
      # For CentOS/RHEL
      sudo yum update -y
      ```

**HTTPS Installation (Highly Recommended)**

For better security, please install an SSL certificate with Let's Encrypt:

```bash
# For Ubuntu/Debian
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d your-domain.com

# For CentOS/RHEL
sudo yum install -y certbot python3-certbot-apache
sudo certbot --apache -d your-domain.com
```

This certificate needs to be renewed every 90 days, but Let's Encrypt will do this automatically.

## 👥 Roles and Responsibilities

### Regular User
- Create exit requests
- View request status
- Download approved forms

### Department/Unit Head
- Review department staff requests
- Approve or reject requests
- Write notes if necessary

### Director
- Provide final approval
- Review requests that have been supported
- Monitor staff movement

### System Admin
- Manage user accounts
- Set user roles
- Monitor system usage

## 📱 System Usage

### How to Create a Request
1. Log in to the system
2. Click "New Request"
3. Fill in the required information
4. Review and submit the request
5. Wait for approval from Head and Director

### How to Check Status
1. Log in to the system
2. View the list of requests on the dashboard
3. Click on a request for details
4. Status will be displayed with different colors:
   - 🟡 Pending Approval
   - 🔵 Approved by Head
   - 🟢 Approved
   - 🔴 Rejected

## 📞 Support

If you encounter any problems or need assistance:
- Contact the System Admin at ext. XXXX
- Email: admin@ipgkbm.edu.my
- ICT Room, Level 1, Block A

## 🔄 Updates

This system will be updated from time to time for improvements. Any suggestions can be submitted to the ICT team for consideration.

## 📜 License

Copyright © 2024 Institute of Teacher Education Malay Language Campus
Developed by ICT Unit, IPG KBM 