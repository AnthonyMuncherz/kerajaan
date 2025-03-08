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