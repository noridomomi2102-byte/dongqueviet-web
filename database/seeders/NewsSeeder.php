<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        $categories = [
            ['name' => 'Thời sự', 'slug' => 'thoi-su', 'description' => 'Tin tức trong nước và quốc tế', 'sort_order' => 1],
            ['name' => 'Kinh tế', 'slug' => 'kinh-te', 'description' => 'Thị trường, doanh nghiệp, tài chính', 'sort_order' => 2],
            ['name' => 'Công nghệ', 'slug' => 'cong-nghe', 'description' => 'Công nghệ và đổi mới số', 'sort_order' => 3],
            ['name' => 'Thể thao', 'slug' => 'the-thao', 'description' => 'Tin thể thao trong và ngoài nước', 'sort_order' => 4],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(['slug' => $data['slug']], $data + ['is_active' => true]);
        }

        $samples = [
            ['category' => 'thoi-su', 'title' => 'Hội nghị cấp cao bàn giải pháp phát triển bền vững'],
            ['category' => 'kinh-te', 'title' => 'Thị trường chứng khoán ghi nhận phiên tăng mạnh'],
            ['category' => 'cong-nghe', 'title' => 'Xu hướng AI tạo sinh thay đổi ngành truyền thông'],
            ['category' => 'the-thao', 'title' => 'Đội tuyển chuẩn bị cho giải đấu khu vực'],
            ['category' => 'thoi-su', 'title' => 'Chính sách mới hỗ trợ doanh nghiệp vừa và nhỏ'],
            ['category' => 'cong-nghe', 'title' => 'Startup Việt gọi vốn thành công vòng Series A'],
        ];

        foreach ($samples as $i => $item) {
            $category = Category::where('slug', $item['category'])->first();
            $slug = Str::slug($item['title']);

            Post::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category->id,
                    'user_id' => $admin?->id,
                    'title' => $item['title'],
                    'excerpt' => 'Đây là đoạn tóm tắt mẫu cho bài viết: ' . $item['title'] . '. Nội dung chi tiết sẽ được cập nhật từ trang quản trị.',
                    'content' => '<p>Đây là nội dung mẫu cho bài viết <strong>' . e($item['title']) . '</strong>.</p><p>Bạn có thể chỉnh sửa hoặc thêm bài mới tại trang quản trị Filament.</p>',
                    'status' => 'published',
                    'published_at' => now()->subHours($i + 1),
                    'meta_title' => $item['title'],
                    'meta_description' => Str::limit($item['title'], 150),
                ]
            );
        }
    }
}
