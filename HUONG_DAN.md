# Site tin tức Laravel — Đồng Quê Việt

Dự án tại `C:\Users\admin\PhpstormProjects\tin-tuc`.

**Đưa lên dongqueviet.com + Git (PhpStorm):** xem [DEPLOY_GIT.md](DEPLOY_GIT.md).

## Yêu cầu

- PHP 8.2+ (XAMPP)
- Extension: `zip`, `intl` (đã bật trong `php.ini`)
- Composer (`composer.phar` tại `PhpstormProjects` hoặc cài global)

## Chạy lần đầu

```bash
cd C:\Users\admin\PhpstormProjects\tin-tuc

# Tạo DB SQLite (nếu chưa có)
# file: database/database.sqlite

php artisan migrate --force
php artisan db:seed --force
php artisan storage:link

php artisan serve
```

- **Trang chủ:** http://localhost:8000
- **Admin Filament:** http://localhost:8000/admin

## Tài khoản admin mẫu

| | |
|---|---|
| Email | `admin@tin-tuc.test` |
| Mật khẩu | `password` |

Đổi mật khẩu sau khi đăng nhập lần đầu.

## Tạo admin mới

```bash
php artisan make:filament-user
```

## Chuyển sang MySQL (XAMPP)

Sửa file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tin_tuc
DB_USERNAME=root
DB_PASSWORD=
```

Tạo database `tin_tuc` trong phpMyAdmin, rồi chạy `php artisan migrate --force`.

## Cấu trúc chính

- `app/Models/Category.php`, `Post.php` — dữ liệu tin tức
- `app/Filament/Resources/` — quản trị danh mục & bài viết
- `app/Http/Controllers/` — trang chủ, danh mục, chi tiết, tìm kiếm
- `resources/views/frontend/` — giao diệen người đọc

## Crawl tin từ cand.vn

Chuyên mục **Chống diễn biến hòa bình** (con của *Góc nhìn & Phản biện*) lấy bài từ:
https://cand.vn/chong-dien-bien-hoa-binh/

```bash
# Crawl tối đa 30 bài (mặc định)
php artisan cand:crawl chong-dien-bien-hoa-binh

# Crawl nhiều hơn / cập nhật lại
php artisan cand:crawl chong-dien-bien-hoa-binh --limit=50
php artisan cand:crawl chong-dien-bien-hoa-binh --force
```

Mỗi bài lưu `source_url` gốc và ghi **Nguồn: cand.vn** cuối bài.

## Crawl tin giả / Pháp luật / Đời sống

```bash
php artisan news:crawl tin-gia --limit=15 --force
php artisan news:crawl phap-luat --month=5 --year=2026 --limit=50
php artisan news:crawl doi-song --month=5 --year=2026 --limit=50
```

## Lệnh hữu ích

```bash
php artisan migrate:fresh --seed   # reset DB + dữ liệu mẫu
php artisan optimize:clear
```
