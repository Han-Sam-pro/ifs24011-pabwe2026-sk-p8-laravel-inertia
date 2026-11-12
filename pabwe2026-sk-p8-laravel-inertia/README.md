# Aplikasi Blog — PABWE 2026 SK-P8 (Laravel 13 + Inertia + React)

Blog dengan tiga tingkat akses: **Publik**, **Anggota (penulis)**, dan **Admin**.

## Fitur

- **Publik**: beranda, daftar artikel dengan pencarian dan filter kategori, detail artikel (TOAST UI Viewer), tanpa sidebar.
- **Anggota**: Dashboard Penulis (`/dashboard/posts`) dengan CRUD artikel, publikasi/draf, sampul (upload & hapus), tampilan list dengan kebab menu, ekspor/impor Excel, dan generate draf dengan OpenAI.
- **Admin**: Panel Admin (`/admin/users`, `/admin/posts`) untuk kelola pengguna dan monitoring artikel (read-only), termasuk ekspor Excel.

## Persyaratan

- PHP 8.4+ (lihat catatan di bawah), Composer, Node.js 20+ dan npm
- PostgreSQL 14+

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Buat database:

```bash
psql -U postgres -c "CREATE DATABASE pabwe_p8_blog;"
```

Sesuaikan `.env` (`DB_*`) dan isi `OPENAI_API_KEY` jika ingin memakai fitur AI.

```bash
php artisan migrate --seed
npm install
npm run build
```

Akun seeder (password: `password`):

| Username   | Peran    |
|------------|----------|
| `admin`    | Admin    |
| `penulis1` | Anggota  |
| `penulis2` | Anggota  |
| `penulis3` | Anggota  |

## Menjalankan

Dua terminal:

```bash
php artisan serve
npm run dev
```

Atau `composer run dev` (menjalankan server, queue, dan Vite sekaligus).

## Format impor Excel

Baris pertama berisi header. Kolom wajib: **Judul**, **Kategori**. Opsional: **Ringkasan**, **Konten**, **Status** (`Publik` / `Draf`).
Kategori: Teknologi, Pendidikan, Gaya Hidup, Bisnis, Opini, Umum (nilai atau label tidak sensitif huruf besar/kecil).
Baris yang tidak valid dilaporkan beserta alasannya; baris valid disimpan dalam satu transaksi database.

## Menjalankan test

```bash
php artisan test
php artisan test --coverage --min=100   # butuh driver Xdebug atau PCOV
composer audit
```

Test memakai SQLite in-memory (lihat `phpunit.xml`), sehingga tidak menyentuh database PostgreSQL.

## Struktur (feature-first)

```
app/Features/
├── Admin/       Controllers, Services
├── Ai/          Contracts, DTO, Services (OpenAI)
├── Auth/        Actions, Controllers
├── Post/        Contracts, Controllers, Enums, Models, Policies, Repositories, Requests, Services
└── Profile/     Controllers, Services
```

Alur: **Controller → Service/Action → Repository → Model**. Controller hanya bergantung pada kontrak (`PostRepositoryInterface`, `AiClientInterface`) yang dipasang di `AppServiceProvider`.

## Catatan

- Kunci API OpenAI hanya di `.env` (`OPENAI_API_KEY`).
- Sampul disimpan di disk `public`; jalankan `php artisan storage:link` sekali.
- `composer.lock` tidak disertakan; jalankan `composer install` (atau `composer update`) agar lock sesuai PHP di mesin Anda.
