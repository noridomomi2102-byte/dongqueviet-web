<?php

namespace App\Console\Commands;

use App\Services\ExternalNewsCrawler;
use Illuminate\Console\Command;

class CrawlExternalNewsCommand extends Command
{
    protected $signature = 'news:crawl
                            {category : Slug chuyên mục (tin-gia, phap-luat, doi-song)}
                            {--limit=50 : Số bài tối đa}
                            {--force : Cập nhật lại bài đã crawl}
                            {--month=5 : Lọc bài theo tháng (1-12)}
                            {--year=2026 : Lọc bài theo năm}
                            {--no-fallback : Không lấy bài mới nhất khi tingia không có bài đúng tháng}';

    protected $description = 'Crawl tin từ tingia.gov.vn và dantri.com.vn theo chuyên mục';

    public function handle(ExternalNewsCrawler $crawler): int
    {
        $slug = $this->argument('category');
        $force = (bool) $this->option('force');
        $month = (int) $this->option('month');
        $year = (int) $this->option('year');
        $limit = (int) $this->option('limit');
        if ($slug === 'tin-gia' && $limit === 50) {
            $limit = 15;
        }

        $sources = [
            'tin-gia' => 'tingia.gov.vn/linh-vuc',
            'phap-luat' => 'dantri.com.vn/phap-luat',
            'doi-song' => 'dantri.com.vn/doi-song',
        ];

        if (! isset($sources[$slug])) {
            $this->error('Chuyên mục không hỗ trợ. Dùng: '.implode(', ', array_keys($sources)));

            return self::FAILURE;
        }

        if ($slug === 'tin-gia') {
            $this->info('Tin giả: lấy tối đa '.$limit.' bài mới nhất từ tingia.gov.vn (mọi tháng).');
        } else {
            $this->info("Đang crawl {$slug} (tháng {$month}/{$year}, tối đa {$limit} bài)...");
        }

        try {
            $stats = $crawler->crawlCategory(
                $slug,
                $limit,
                $force,
                $month,
                $year,
                $slug === 'tin-gia' || ! $this->option('no-fallback'),
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Kết quả', 'Số lượng'],
            [
                ['Đã nhập / cập nhật', $stats['imported']],
                ['Bỏ qua', $stats['skipped']],
                ['Lỗi', $stats['failed']],
            ]
        );

        return self::SUCCESS;
    }
}
