<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryStructureSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        $tree = [
            [
                'name' => 'Thời sự',
                'slug' => 'thoi-su',
                'description' => 'Tin tức, sự kiện và các vấn đề thời sự trong nước.',
                'sort_order' => 1,
                'children' => [
                    [
                        'name' => 'Tin tức & Sự kiện',
                        'slug' => 'tin-tuc-su-kien',
                        'description' => 'Tin tức, sự kiện thời sự (nguồn cand.vn).',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Vấn đề hôm nay',
                        'slug' => 'van-de-hom-nay',
                        'description' => 'Phân tích, bình luận các vấn đề thời sự (nguồn cand.vn).',
                        'sort_order' => 2,
                    ],
                ],
            ],
            [
                'name' => 'Góc nhìn & Phản biện',
                'slug' => 'goc-nhin-phan-bien',
                'description' => 'Chuyên mục cốt lõi — phân tích, phản biện và đấu tranh với thông tin sai lệch.',
                'sort_order' => 2,
                'children' => [
                    [
                        'name' => 'Đấu tranh phản bác',
                        'slug' => 'dau-tranh-phan-bac',
                        'description' => 'Các bài viết trực tiếp đập tan luận điệu sai trái.',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Bình luận chuyên sâu',
                        'slug' => 'binh-luan-chuyen-sau',
                        'description' => 'Phân tích bối cảnh, mục đích và cơ chế lan truyền của tin giả.',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Chống diễn biến hòa bình',
                        'slug' => 'chong-dien-bien-hoa-binh',
                        'description' => 'Đấu tranh phản bác các luận điệu, thông tin sai lệch về "diễn biến hòa bình".',
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'name' => 'Kiểm chứng thông tin',
                'slug' => 'kiem-chung-thong-tin',
                'description' => 'Fact-checking — kiểm chứng, làm rõ sự thật.',
                'sort_order' => 3,
                'children' => [
                    [
                        'name' => 'Tin giả',
                        'slug' => 'tin-gia',
                        'description' => 'Kiểm chứng, phản bác tin giả, thông tin sai lệch.',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Giải mã tin đồn',
                        'slug' => 'giai-ma-tin-don',
                        'description' => 'Bóc tách trắng đen các tin đồn đang lan truyền.',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Hồ sơ sự thật',
                        'slug' => 'ho-so-su-that',
                        'description' => 'Cung cấp chứng cứ, tài liệu gốc để đối chiếu.',
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'name' => 'Nhận diện & Cảnh báo',
                'slug' => 'nhan-dien-canh-bao',
                'description' => 'Nhận diện mối đe dọa và cảnh báo cộng đồng.',
                'sort_order' => 4,
                'children' => [
                    [
                        'name' => 'Cảnh báo mạng',
                        'slug' => 'canh-bao-mang',
                        'description' => 'Danh sách trang web, hội nhóm, tài khoản phát tán tin độc.',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Kỹ năng phòng chống',
                        'slug' => 'ky-nang-phong-chong',
                        'description' => 'Công cụ OSINT cơ bản, nhận biết lừa đảo, deepfake.',
                        'sort_order' => 2,
                    ],
                ],
            ],
            [
                'name' => 'Pháp luật',
                'slug' => 'phap-luat',
                'description' => 'Tin tức pháp luật, hồ sơ vụ án, chính sách mới.',
                'sort_order' => 10,
                'show_in_menu' => false,
                'children' => [
                    ['name' => 'Hồ sơ vụ án', 'slug' => 'ho-so-vu-an', 'sort_order' => 1],
                    ['name' => 'Pháp đình', 'slug' => 'phap-dinh', 'sort_order' => 2],
                    ['name' => 'Việt Nam và luật pháp quốc tế', 'slug' => 'viet-nam-luat-quoc-te', 'sort_order' => 3],
                    ['name' => 'Chính sách mới', 'slug' => 'chinh-sach-moi', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Đời sống',
                'slug' => 'doi-song',
                'description' => 'Du lịch, ẩm thực, sức khỏe và đời sống hàng ngày.',
                'sort_order' => 11,
                'show_in_menu' => false,
                'children' => [
                    ['name' => 'Du lịch', 'slug' => 'du-lich', 'sort_order' => 1],
                    ['name' => 'Ẩm thực', 'slug' => 'am-thuc', 'sort_order' => 2],
                    ['name' => 'Sức khỏe', 'slug' => 'suc-khoe', 'sort_order' => 3],
                    ['name' => 'Gia đình', 'slug' => 'gia-dinh', 'sort_order' => 4],
                ],
            ],
        ];

        $validSlugs = [];

        foreach ($tree as $parentData) {
            $children = $parentData['children'] ?? [];
            unset($parentData['children']);

            $parent = Category::updateOrCreate(
                ['slug' => $parentData['slug']],
                $parentData + [
                    'parent_id' => null,
                    'is_active' => true,
                    'show_in_menu' => $parentData['show_in_menu'] ?? true,
                ]
            );
            $validSlugs[] = $parent->slug;

            foreach ($children as $childData) {
                $child = Category::updateOrCreate(
                    ['slug' => $childData['slug']],
                    $childData + [
                        'parent_id' => $parent->id,
                        'is_active' => true,
                        'show_in_menu' => true,
                        'description' => $childData['description'] ?? null,
                    ]
                );
                $validSlugs[] = $child->slug;
            }
        }

        $removeIds = Category::whereNotIn('slug', $validSlugs)->pluck('id');
        if ($removeIds->isNotEmpty()) {
            Post::whereIn('category_id', $removeIds)->delete();
            Category::whereIn('id', $removeIds)->delete();
        }

    }
}
