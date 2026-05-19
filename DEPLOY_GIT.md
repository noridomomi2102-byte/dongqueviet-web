# Đưa dự án lên dongqueviet.com (Git + PhpStorm)

Dự án nằm tại: `C:\Users\admin\PhpstormProjects\tin-tuc`

## 1. Tình trạng Git hiện tại (quan trọng)

Repo đang trỏ **remote Laravel gốc** (`laravel/laravel`), HEAD **detached** tại tag `v12.12.2` — **chưa phải** repo riêng của Đồng Quê Việt.

Bạn cần tạo repo mới (GitHub / GitLab / Bitbucket) ví dụ: `dongqueviet-web`, rồi đổi remote và push code của mình.

---

## 2. Chuẩn bị repo trên GitHub (hoặc GitLab)

1. Tạo repository **trống** (không tick “Add README”).
2. Copy URL, ví dụ:
   - HTTPS: `https://github.com/TEN-BAN/dongqueviet-web.git`
   - SSH: `git@github.com:TEN-BAN/dongqueviet-web.git`

---

## 3. Thiết lập Git trong PhpStorm (một lần)

### Bước A — Tạo nhánh `main` và commit toàn bộ code

**Terminal** trong PhpStorm (`Alt+F12`), chạy lần lượt:

```powershell
cd C:\Users\admin\PhpstormProjects\tin-tuc

git checkout -b main

git add .
git status
```

Kiểm tra **không** có file `.env` trong danh sách commit (đã nằm trong `.gitignore`).

```powershell
git commit -m "Dự án Đồng Quê Việt: frontend, admin Filament, crawl tin tức"
```

### Bước B — Đổi remote sang repo của bạn

```powershell
git remote remove origin
git remote remove composer
git remote add origin https://github.com/TEN-BAN/dongqueviet-web.git
git remote -v
```

Thay `TEN-BAN/dongqueviet-web` bằng repo thật của bạn.

### Bước C — Push lần đầu

```powershell
git push -u origin main
```

**Hoặc dùng giao diện PhpStorm:**

1. **Git → Commit** (`Ctrl+K`): chọn file → message → **Commit**.
2. **Git → Push** (`Ctrl+Shift+K`): chọn remote `origin` → **Push**.
3. Nếu chưa có remote: **Git → Manage Remotes…** → `+` → Name: `origin`, URL repo của bạn.

---

## 4. Làm việc hàng ngày (bạn + AI chỉnh code)

1. Sửa code (trong Cursor / PhpStorm).
2. **Git → Commit** (hoặc `Ctrl+K`).
3. **Git → Push** (hoặc `Ctrl+Shift+K`).

Trên server (hoặc CI) sẽ `git pull` để cập nhật.

---

## 5. Triển khai lên server dongqueviet.com

### Yêu cầu hosting

- PHP **8.2+**
- Extension: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `intl`, `zip`
- Composer trên server (hoặc deploy từ máy local kèm `vendor`)
- MySQL/MariaDB (khuyến nghị production) hoặc SQLite (chỉ demo nhỏ)
- **Document root** trỏ vào thư mục **`public`** (bắt buộc với Laravel)

Cấu trúc trên VPS thường là:

```text
/home/user/dongqueviet.com/     ← clone Git (toàn bộ project)
/home/user/dongqueviet.com/public/  ← document root domain
```

### Lần đầu trên server (SSH)

```bash
cd /đường/dẫn/đến/site
git clone https://github.com/TEN-BAN/dongqueviet-web.git .
# hoặc: git pull origin main  nếu đã clone sẵn

cp .env.example .env
nano .env   # chỉnh theo mục 6 bên dưới

composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Quyền ghi** (Linux):

```bash
chmod -R ug+rwx storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

(Đổi `www-data` theo user PHP-FPM/nginx của host.)

### Mỗi lần bạn `git push` — cập nhật server

```bash
cd /đường/dẫn/đến/site
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Hoặc chạy script có sẵn: `bash scripts/deploy-server.sh`

### Ảnh bài crawl

Ảnh crawl nằm trong `storage/app/public/` (không đưa lên Git). Sau deploy:

- Chạy lại crawl trên server, **hoặc**
- Copy thư mục `storage/app/public/posts` từ máy local lên server.

```bash
php artisan news:crawl phap-luat --limit=50 --force
php artisan news:crawl doi-song --limit=50 --force
php artisan news:crawl tin-gia --limit=15 --force
```

---

## 6. File `.env` trên production (demo — chỉnh theo host thật)

```env
APP_NAME="Đồng Quê Việt"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://dongqueviet.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dongqueviet
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

Tạo database trên hosting, import hoặc `php artisan migrate --force`.

**SSL:** bật HTTPS trên panel host; `APP_URL` phải là `https://dongqueviet.com`.

---

## 7. cPanel / shared hosting (không SSH)

1. Upload code (hoặc **Git Version Control** trong cPanel nếu có).
2. Document root = `public`.
3. Đặt `.env` trên server (không commit).
4. Chạy Composer qua **Terminal** cPanel hoặc upload cả thư mục `vendor` từ máy local (`composer install --no-dev`).
5. Trỏ subdomain/path tới `public/index.php`.

---

## 8. Checklist trước khi go-live

- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] Document root = `public`
- [ ] `php artisan storage:link`
- [ ] Đổi mật khẩu admin Filament (`/admin`)
- [ ] HTTPS + `APP_URL` đúng domain
- [ ] Cron (tuỳ chọn): `* * * * * php /path/to/artisan schedule:run`

---

## 9. Lỗi thường gặp

| Lỗi | Cách xử lý |
|-----|------------|
| 500 sau deploy | `storage` / `bootstrap/cache` chưa ghi được; xem `storage/logs/laravel.log` |
| CSS/JS lỗi | Chạy `php artisan storage:link`; kiểm tra `public/images` |
| Ảnh bài không hiện | Thiếu file trong `storage/app/public` — crawl lại hoặc copy từ local |
| Git push bị từ chối | Kiểm tra quyền repo; dùng Personal Access Token (HTTPS) hoặc SSH key |

---

## 10. Liên hệ workflow với Cursor / PhpStorm

- **Cursor** chỉnh code → bạn **Commit + Push** trong PhpStorm.
- Server **pull** + chạy `scripts/deploy-server.sh`.
- Không commit file `.env`, `vendor`, `node_modules`, `database/database.sqlite` (nếu dùng SQLite local).
