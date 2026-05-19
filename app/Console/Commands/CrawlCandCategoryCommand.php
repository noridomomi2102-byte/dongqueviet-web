<?php

namespace App\Console\Commands;

use App\Services\CandVnCrawler;
use Illuminate\Console\Command;

class CrawlCandCategoryCommand extends Command
{
    protected $signature = 'cand:crawl
                            {category=chong-dien-bien-hoa-binh : Slug chuyên mục đích}
                            {--limit=30 : Số bài tối đa}
                            {--force : Cập nhật lại bài đã crawl}
                            {--month= : Lọc bài theo tháng (1-12), dùng kèm --year}
                            {--year= : Lọc bài theo năm (mặc định năm hiện tại)}';

    protected $description = 'Crawl bài viết từ cand.vn theo RSS chuyên mục';

    public function handle(CandVnCrawler $crawler): int
    {
        $slug = $this->argument('category');
        $limit = (int) $this->option('limit');
        $force = (bool) $this->option('force');
        $month = $this->option('month') !== null ? (int) $this->option('month') : null;
        $year = $this->option('year') !== null ? (int) $this->option('year') : null;

        $filterNote = '';
        if ($month) {
            $filterNote = ' (tháng '.$month.'/'.($year ?? date('Y')).')';
        }

        $this->info("Đang crawl chuyên mục: {$slug} (tối đa {$limit} bài){$filterNote}...");

        $stats = $crawler->crawlCategory($slug, $limit, $force, $month, $year);

        $this->table(
            ['Kết quả', 'Số lượng'],
            [
                ['Đã nhập / cập nhật', $stats['imported']],
                ['Bỏ qua (đã có)', $stats['skipped']],
                ['Lỗi', $stats['failed']],
            ]
        );

        return self::SUCCESS;
    }
}
