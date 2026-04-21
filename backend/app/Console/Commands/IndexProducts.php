<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\SearchService;
use Illuminate\Console\Command;

class IndexProducts extends Command
{
    protected $signature   = 'products:index {--fresh : 인덱스 삭제 후 재생성}';
    protected $description = 'Elasticsearch에 상품 데이터를 인덱싱합니다.';

    public function handle(SearchService $search): int
    {
        if ($this->option('fresh')) {
            $this->info('기존 인덱스를 삭제하고 재생성합니다...');
            $host  = rtrim(config('services.elasticsearch.host'), '/');
            $index = config('services.elasticsearch.index');
            \Illuminate\Support\Facades\Http::delete("{$host}/{$index}");
        }

        $this->info('Elasticsearch 인덱스 확인/생성 중...');
        $search->ensureIndex();

        $total = Product::count();
        $this->info("상품 {$total}개를 인덱싱합니다...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        Product::with('category')->chunk(100, function ($products) use ($search, $bar) {
            foreach ($products as $product) {
                $search->indexProduct($product);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('인덱싱 완료!');

        return Command::SUCCESS;
    }
}
