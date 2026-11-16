# NEXHIP – Next-Generation Entrepreneurship Experience for HIPMI
## 📌 About The Project

**NEXHIP (Next-Generation Entrepreneurship Experience for HIPMI)** merupakan platform website yang dikembangkan untuk mendukung pengelolaan informasi dan aktivitas organisasi HIPMI PT PalComTech.

Project ini bertujuan untuk menyediakan pusat informasi digital yang memudahkan mahasiswa dalam memperoleh informasi organisasi, mengenal program kerja, melihat struktur kepengurusan, serta mengetahui perkembangan usaha anggota.

Selain itu, NEXHIP menyediakan panel administrasi untuk membantu pengelola dalam mengelola konten dan informasi website secara lebih terstruktur.

Project ini dikembangkan sebagai bagian dari implementasi mata kuliah **Technopreneurship** di Institut Teknologi dan Bisnis PalComTech.

## 🎯 Project Objectives

- Menyediakan pusat informasi digital HIPMI PT PalComTech.
- Mempermudah penyampaian informasi dan program kerja organisasi.
- Memperkenalkan potensi usaha dan UMKM anggota.
- Mendukung pengelolaan konten organisasi secara terpusat.
- Meningkatkan aksesibilitas informasi bagi mahasiswa.

## ✨ Features

### Public Website

- **Home** – Halaman utama yang menampilkan informasi organisasi.
- **About** – Informasi mengenai HIPMI PT PalComTech.
- **Divisions** – Informasi bidang atau divisi organisasi.
- **Organizational Structure** – Informasi struktur kepengurusan.
- **Work Programs** – Informasi program kerja organisasi.
- **Information** – Pusat informasi dan pengumuman.
- **Gallery** – Dokumentasi kegiatan organisasi.
- **Business & MSMEs** – Informasi usaha dan UMKM anggota.
- **Join Us** – Halaman pendaftaran atau pengajuan bergabung.

### Admin Dashboard

- **Overview** – Ringkasan pengelolaan website.
- **Information Management** – Pengelolaan informasi dan pengumuman.
- **Work Program Management** – Pengelolaan program kerja.
- **Gallery Management** – Pengelolaan dokumentasi kegiatan.
- **Business Management** – Pengelolaan informasi usaha anggota.
- **Submission Management** – Pengelolaan pengajuan.
- **Registration Management** – Pengelolaan pendaftaran.
- **Team Management** – Pengelolaan struktur kepengurusan.
- **Account Management** – Pengelolaan akun administrator.
- **Audit Log** – Pencatatan aktivitas administrasi.
- **Settings** – Pengaturan website.
- **Change Password** – Penggantian kata sandi administrator.

## 🛠️ Tech Stack

| Technology      | Description                   |
| --------------- | ----------------------------- |
| PHP 8.2+        | Backend programming language  |
| Laravel 12      | Backend framework             |
| Livewire 3      | Dynamic interface development |
| Tailwind CSS 4  | CSS framework                 |
| Vite            | Frontend build tool           |
| MySQL / MariaDB | Database (if configured)      |
| Composer        | PHP dependency manager        |
| NPM             | JavaScript package manager    |

## 📂 Project Structure

```text
Web-Hipmi-PT-PalComTech/
├── app/
│   └── Livewire/
│       ├── Admin/
│       └── ...
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
│   └── web.php
├── storage/
├── tests/
├── .env.example
├── artisan
├── composer.json
├── package.json
└── README.md
```

## ⚙️ Installation & Setup

Follow these steps to run NEXHIP locally.

### 1. Clone Repository

```bash
git clone https://github.com/Nopal4u/Web-Hipmi-PT-PalComTech.git
```

### 2. Navigate to Project Directory

```bash
cd Web-Hipmi-PT-PalComTech
```

### 3. Install PHP Dependencies

```bash
composer install
```

### 4. Install Node.js Dependencies

```bash
npm install
```

### 5. Configure Environment

Copy `.env.example` and rename the copy to `.env`.

```bash
cp .env.example .env
```

On Windows Command Prompt, you can use:

```cmd
copy .env.example .env
```

### 6. Generate Application Key

```bash
php artisan key:generate
```

### 7. Configure Database

Open the `.env` file and adjust the database configuration according to your local environment.

Example for MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexhip
DB_USERNAME=root
DB_PASSWORD=
```

Create the database before running the migration.

### 8. Run Database Migrations

```bash
php artisan migrate
```

### 9. Run the Application

Open two terminals.

**Terminal 1 – Laravel Development Server**

```bash
php artisan serve
```

**Terminal 2 – Vite Development Server**

```bash
npm run dev
```

Open the application in your browser:

```text
http://127.0.0.1:8000
```

## 👥 Development Team

NEXHIP is developed collaboratively by a team of three students from Institut Teknologi dan Bisnis PalComTech.

| Role    | Responsibility                                           | Team Member |
| ------- | -------------------------------------------------------- | ----------- |
| Hacker  | Backend development, database, and system functionality  | [Name]      |
| Hipster | UI/UX design and frontend interface                      | [Name]      |
| Hustler | Project management, business analysis, and documentation | [Name]      |

## 🎓 Project Information

| Information        | Details                                               |
| ------------------ | ----------------------------------------------------- |
| Project Name       | NEXHIP                                                |
| Full Name          | Next-Generation Entrepreneurship Experience for HIPMI |
| Organization       | HIPMI PT PalComTech                                   |
| Institution        | Institut Teknologi dan Bisnis PalComTech              |
| Course             | Technopreneurship                                     |
| Project Type       | Web-Based Information System                          |
| Development Status | In Development                                        |

## 📄 License

This project is developed for academic purposes as part of the Technopreneurship course at Institut Teknologi dan Bisnis PalComTech.

---

\<p align="center">   \<strong>NEXHIP\</strong>\<br>   Empowering Student Entrepreneurship Through Digital Innovation. \</p>
