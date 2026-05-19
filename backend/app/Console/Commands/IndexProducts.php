<?php

namespace App\Console\Commands;

use App\Models\Product;
use Zslab\Search\Client\ElasticsearchClient;
use Zslab\Search\Index\AnalyzerPresets;
use Zslab\Search\Index\IndexManager;
use Illuminate\Console\Command;

class IndexProducts extends Command
{
    protected $signature   = 'products:index {--fresh : 인덱스 삭제 후 재생성(nori_ngram 재구성 포함)}';
    protected $description = 'Elasticsearch에 상품 데이터를 인덱싱합니다.';

    private array $mapping = [
        'properties' => [
            'id'            => ['type' => 'integer'],
            'name'          => ['type' => 'text', 'analyzer' => 'nori_ngram_analyzer', 'search_analyzer' => 'nori_search_analyzer'],
            'name_suggest'  => ['type' => 'search_as_you_type'],
            'name_jamo'     => ['type' => 'text', 'analyzer' => 'jamo_analyzer', 'search_analyzer' => 'jamo_search_analyzer'],
            'slug'          => ['type' => 'keyword'],
            'description'   => ['type' => 'text', 'analyzer' => 'nori_ngram_analyzer', 'search_analyzer' => 'nori_search_analyzer'],
            'category_name' => ['type' => 'text', 'analyzer' => 'nori_ngram_analyzer', 'search_analyzer' => 'nori_search_analyzer'],
            'price'         => ['type' => 'integer'],
            'sale_price'    => ['type' => 'integer'],
            'status'        => ['type' => 'keyword'],
            'order_count'   => ['type' => 'integer'],
            'rating_avg'    => ['type' => 'float'],
            'images'        => ['type' => 'keyword', 'index' => false],
        ],
    ];

    public function handle(IndexManager $indexManager, ElasticsearchClient $client): int
    {
        $indexName = Product::getSearchIndex();

        if ($this->option('fresh')) {
            $this->freshReindex($indexManager, $client, $indexName);
            return Command::SUCCESS;
        }

        $this->info('Elasticsearch 인덱스 확인/생성 중...');
        $indexManager->ensureIndex($indexName, $this->mapping);
        $this->indexAll($client, $indexName);

        return Command::SUCCESS;
    }

    private function freshReindex(IndexManager $indexManager, ElasticsearchClient $client, string $indexName): void
    {
        $this->info('nori_ngram 분석기로 인덱스 재구성 중...');
        $backupName = $indexName . '_old';

        // 이전 백업 잔재 제거
        if ($client->existsIndex($backupName)) {
            $client->deleteIndex($backupName);
        }
        // 기존 인덱스 백업
        if ($client->existsIndex($indexName)) {
            $client->reindexTo($indexName, $backupName);
            $client->deleteIndex($indexName);
        }

        // 신규 인덱스 생성 (nori_ngram)
        $indexManager->createIndex($indexName, $this->mapping);

        // 전체 재색인 (id를 _source에 포함)
        $this->indexAll($client, $indexName);

        // 백업 삭제
        if ($client->existsIndex($backupName)) {
            $client->deleteIndex($backupName);
        }

        $this->info('재색인 완료!');
    }

    private function indexAll(ElasticsearchClient $client, string $indexName): void
    {
        $total = Product::count();
        $this->info("상품 {$total}개를 인덱싱합니다...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        Product::with('category')->chunk(100, function ($products) use ($client, $indexName, $bar) {
            $batch = [];
            foreach ($products as $product) {
                $doc = $product->toSearchArray(); // id를 _source에 포함
                $batch[] = ['index' => ['_index' => $indexName, '_id' => $doc['id']]];
                $batch[] = $doc;
                $bar->advance();
            }
            $client->bulk($batch);
        });

        $bar->finish();
        $this->newLine();
        $this->info('인덱싱 완료!');
    }
}
