# 📝 Sistem Permohonan Keluar IPG KBM

Assalamualaikum dan Salam Sejahtera! 🙏

Sistem ini dibangunkan khas untuk memudahkan proses permohonan keluar di Institut Pendidikan Guru Kampus Bahasa Melayu (IPG KBM). Dengan adanya sistem ini, kita dapat menguruskan permohonan keluar dengan lebih sistematik dan efisien.

## 🌟 Ciri-Ciri Utama

### 1. Permohonan Keluar Yang Sistematik
- Borang digital yang lengkap
- Proses kelulusan dua peringkat (Ketua Jabatan/Unit & Pengarah)
- Penjanaan PDF automatik dengan tandatangan digital
- Status permohonan yang jelas dan mudah difahami

### 2. Sistem Multi-Peranan
- 👤 **Pengguna Biasa**: Boleh membuat permohonan
- 👨‍💼 **Ketua Jabatan/Unit**: Memberi sokongan pertama
- 👨‍💼 **Pengarah**: Memberi kelulusan muktamad
- 👨‍💻 **Admin**: Menguruskan sistem dan pengguna

### 3. Kemudahan-Kemudahan
- Dashboard yang mesra pengguna
- Sistem notifikasi yang jelas
- Carian dan tapisan permohonan
- Sejarah permohonan yang lengkap

## 🛠️ Panduan Teknikal

### Keperluan Sistem
- PHP 7.4 ke atas
- MySQL 5.7 ke atas
- Web Server (Apache/Nginx)
- Composer (PHP Package Manager)
- wkhtmltopdf (untuk penjanaan PDF)

### Cara-Cara Pemasangan

1. **Clone Repository**
   ```bash
   git clone https://github.com/username/kerajaan.git
   cd kerajaan
   ```

2. **Pasang Dependencies**
   ```bash
   composer install
   ```

3. **Setup Database**
   - Buat database baru di MySQL
   - Import fail SQL dari folder `database/`
   - Kemaskini fail `app/config/database.php`

4. **Konfigurasi Sistem**
   - Salin fail `app/config/config.example.php` ke `app/config/config.php`
   - Kemaskini tetapan mengikut keperluan
   - Pastikan folder `app/pdf` dan `app/uploads` boleh ditulis

5. **Tetapan Web Server**
   - Pastikan `mod_rewrite` diaktifkan untuk Apache
   - Arahkan root directory ke folder `public/`

### Panduan Pemasangan untuk Server Linux

1. **Prasyarat**
   - Update pakej sistem:
     ```bash
     sudo apt update && sudo apt upgrade -y  # Ubuntu/Debian
     # ATAU
     sudo yum update -y  # CentOS/RHEL
     ```
   
   - Pasang pakej yang diperlukan:
     ```bash
     # Ubuntu/Debian
     sudo apt install -y apache2 mysql-server php php-cli php-fpm php-json php-common php-mysql php-zip php-gd php-mbstring php-curl php-xml php-pear php-bcmath git unzip curl
     
     # CentOS/RHEL
     sudo yum install -y httpd mariadb-server php php-cli php-json php-common php-mysqlnd php-zip php-gd php-mbstring php-curl php-xml php-pear php-bcmath git unzip curl
     ```

2. **Mula dan Aktifkan Servis**
   ```bash
   # Ubuntu/Debian
   sudo systemctl start apache2
   sudo systemctl enable apache2
   sudo systemctl start mysql
   sudo systemctl enable mysql
   
   # CentOS/RHEL
   sudo systemctl start httpd
   sudo systemctl enable httpd
   sudo systemctl start mariadb
   sudo systemctl enable mariadb
   ```

3. **Keselamatan MySQL**
   ```bash
   sudo mysql_secure_installation
   ```
   - Ikuti arahan untuk menetapkan kata laluan root dan mengkonfigurasi tetapan keselamatan.

4. **Buat Pangkalan Data dan Pengguna**
   ```bash
   sudo mysql -u root -p
   ```
   
   ```sql
   CREATE DATABASE kerajaan;
   CREATE USER 'kerajaan_user'@'localhost' IDENTIFIED BY 'kata_laluan_selamat';
   GRANT ALL PRIVILEGES ON kerajaan.* TO 'kerajaan_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

5. **Pasang Composer**
   ```bash
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   sudo chmod +x /usr/local/bin/composer
   ```

6. **Pasang wkhtmltopdf**
   ```bash
   # Ubuntu/Debian
   sudo apt install -y wkhtmltopdf
   
   # CentOS/RHEL
   sudo yum install -y xorg-x11-fonts-75dpi xorg-x11-fonts-Type1
   sudo yum install -y https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox-0.12.6-1.centos7.x86_64.rpm
   ```

7. **Muat Turun dan Pasang Aplikasi**
   ```bash
   cd /var/www/html
   sudo git clone https://github.com/username/kerajaan.git
   cd kerajaan
   sudo composer install --no-dev --optimize-autoloader
   ```

8. **Konfigurasi Hak Milik Folder dan Fail**
   ```bash
   sudo chown -R www-data:www-data /var/www/html/kerajaan  # Ubuntu/Debian
   # ATAU
   sudo chown -R apache:apache /var/www/html/kerajaan  # CentOS/RHEL
   
   sudo chmod -R 755 /var/www/html/kerajaan
   sudo chmod -R 775 /var/www/html/kerajaan/app/pdf
   sudo chmod -R 775 /var/www/html/kerajaan/app/uploads
   ```

9. **Konfigurasi Aplikasi**
   ```bash
   sudo cp app/config/config.example.php app/config/config.php
   sudo nano app/config/config.php  # Kemaskini tetapan
   sudo cp app/config/database.example.php app/config/database.php
   sudo nano app/config/database.php  # Kemaskini tetapan pangkalan data
   ```

10. **Import Pangkalan Data**
    ```bash
    sudo mysql -u kerajaan_user -p kerajaan < database/kerajaan.sql
    ```

11. **Konfigurasi Virtual Host Apache**
    ```bash
    # Ubuntu/Debian
    sudo nano /etc/apache2/sites-available/kerajaan.conf
    ```
    
    Atau
    
    ```bash
    # CentOS/RHEL
    sudo nano /etc/httpd/conf.d/kerajaan.conf
    ```
    
    Tambah konfigurasi berikut:
    
    ```apache
    <VirtualHost *:80>
        ServerName domain-anda.com
        ServerAdmin webmaster@domain-anda.com
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

12. **Aktifkan Konfigurasi dan Mod Rewrite**
    ```bash
    # Ubuntu/Debian
    sudo a2ensite kerajaan.conf
    sudo a2enmod rewrite
    sudo systemctl restart apache2
    
    # CentOS/RHEL
    sudo systemctl restart httpd
    ```

13. **Periksa Konfigurasi Firewall**
    ```bash
    # Ubuntu/Debian
    sudo ufw allow 80/tcp
    sudo ufw allow 443/tcp
    
    # CentOS/RHEL
    sudo firewall-cmd --permanent --add-service=http
    sudo firewall-cmd --permanent --add-service=https
    sudo firewall-cmd --reload
    ```

14. **Penyelenggaraan Rutin**
    - Jadualkan backup pangkalan data:
      ```bash
      sudo crontab -e
      ```
      
      Tambah baris berikut untuk backup harian pada jam 2 pagi:
      ```
      0 2 * * * mysqldump -u kerajaan_user -p'kata_laluan_selamat' kerajaan > /var/backups/kerajaan_$(date +\%Y\%m\%d).sql
      ```
      
    - Jadualkan task untuk membersihkan fail sementara setiap minggu:
      ```
      0 3 * * 0 find /var/www/html/kerajaan/app/pdf/temp -type f -mtime +7 -delete
      ```

15. **Pemantauan dan Keselamatan**
    - Pantau log sistem dan aplikasi secara berkala:
      ```bash
      sudo tail -f /var/log/apache2/kerajaan-error.log  # Ubuntu/Debian
      # ATAU
      sudo tail -f /var/log/httpd/kerajaan-error.log  # CentOS/RHEL
      ```
    
    - Pastikan sistem sentiasa dikemaskini:
      ```bash
      sudo apt update && sudo apt upgrade -y  # Ubuntu/Debian
      # ATAU
      sudo yum update -y  # CentOS/RHEL
      ```

Jika aplikasi memerlukan HTTPS, gunakan Let's Encrypt untuk mendapatkan sijil SSL percuma:
```bash
# Ubuntu/Debian
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d domain-anda.com

# CentOS/RHEL
sudo yum install -y certbot python3-certbot-apache
sudo certbot --apache -d domain-anda.com
```

## 👥 Peranan dan Tanggungjawab

### Pengguna Biasa
- Membuat permohonan keluar
- Melihat status permohonan
- Muat turun borang yang diluluskan

### Ketua Jabatan/Unit
- Menyemak permohonan staf jabatan
- Memberi sokongan atau menolak permohonan
- Menulis catatan jika perlu

### Pengarah
- Memberi kelulusan muktamad
- Menyemak permohonan yang telah disokong
- Memantau pergerakan staf

### Admin Sistem
- Menguruskan akaun pengguna
- Menetapkan peranan pengguna
- Memantau penggunaan sistem

## 📱 Penggunaan Sistem

### Cara Membuat Permohonan
1. Log masuk ke sistem
2. Klik "Permohonan Baru"
3. Isi maklumat yang diperlukan
4. Semak dan hantar permohonan
5. Tunggu kelulusan dari Ketua dan Pengarah

### Cara Menyemak Status
1. Log masuk ke sistem
2. Lihat senarai permohonan di dashboard
3. Klik pada permohonan untuk butiran lanjut
4. Status akan dipaparkan dengan warna berbeza:
   - 🟡 Menunggu Kelulusan
   - 🔵 Diluluskan oleh Ketua
   - 🟢 Diluluskan
   - 🔴 Ditolak

## 📞 Sokongan

Jika menghadapi sebarang masalah atau memerlukan bantuan:
- Hubungi Admin Sistem di ext. XXXX
- Emel: admin@ipgkbm.edu.my
- Bilik ICT, Aras 1, Blok A

## 🔄 Pengemaskinian

Sistem ini akan dikemaskini dari semasa ke semasa untuk penambahbaikan. Sebarang cadangan boleh diajukan kepada pihak ICT untuk pertimbangan.

## 📜 Lesen

Hak Cipta Terpelihara © 2024 Institut Pendidikan Guru Kampus Bahasa Melayu
Dibangunkan oleh Unit ICT, IPG KBM 