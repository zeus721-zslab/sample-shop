<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    private array $products = [
        // 패션/의류
        ['name' => '클래식 슬림핏 셔츠',     'slug' => 'classic-slim-shirt',         'cat' => 'mens-clothing',  'price' => 39000,  'sale' => 29000],
        ['name' => '스트레치 치노 팬츠',      'slug' => 'stretch-chino-pants',        'cat' => 'mens-clothing',  'price' => 55000,  'sale' => 42000],
        ['name' => '울 혼방 코트',            'slug' => 'wool-blend-coat',            'cat' => 'mens-clothing',  'price' => 180000, 'sale' => 145000],
        ['name' => '린넨 와이드 팬츠',        'slug' => 'linen-wide-pants',           'cat' => 'womens-clothing','price' => 49000,  'sale' => null],
        ['name' => '플로럴 맥시 원피스',      'slug' => 'floral-maxi-dress',          'cat' => 'womens-clothing','price' => 68000,  'sale' => 52000],
        ['name' => '캐시미어 터틀넥 니트',    'slug' => 'cashmere-turtleneck',        'cat' => 'womens-clothing','price' => 89000,  'sale' => null],
        // 전자제품
        ['name' => '무선 이어폰 프로 2세대',  'slug' => 'wireless-earphone-pro2',     'cat' => 'smartphones',    'price' => 250000, 'sale' => 199000],
        ['name' => '스마트폰 케이스 투명',    'slug' => 'phone-case-clear',           'cat' => 'smartphones',    'price' => 15000,  'sale' => 9900],
        ['name' => 'PD 65W 고속 충전기',      'slug' => 'pd-65w-charger',             'cat' => 'smartphones',    'price' => 35000,  'sale' => 27000],
        ['name' => '기계식 키보드 텐키리스',  'slug' => 'mechanical-keyboard',        'cat' => 'laptops',        'price' => 120000, 'sale' => 99000],
        ['name' => '27인치 4K 모니터',        'slug' => '27inch-4k-monitor',          'cat' => 'laptops',        'price' => 480000, 'sale' => 420000],
        ['name' => '노트북 스탠드 알루미늄',  'slug' => 'laptop-stand-aluminium',     'cat' => 'laptops',        'price' => 42000,  'sale' => null],
        ['name' => '블루투스 스피커 포터블',  'slug' => 'bluetooth-speaker-portable', 'cat' => 'audio',          'price' => 98000,  'sale' => 75000],
        ['name' => '노이즈캔슬링 헤드폰',     'slug' => 'noise-cancelling-headphone', 'cat' => 'audio',          'price' => 320000, 'sale' => 280000],
        // 뷰티/헬스
        ['name' => '비타민C 세럼 30ml',       'slug' => 'vitamin-c-serum-30ml',       'cat' => 'skincare',       'price' => 45000,  'sale' => 36000],
        ['name' => '히알루론산 수분크림',     'slug' => 'hyaluronic-moisture-cream',  'cat' => 'skincare',       'price' => 38000,  'sale' => null],
        ['name' => 'SPF50 선크림 50ml',       'slug' => 'spf50-sunscreen-50ml',       'cat' => 'skincare',       'price' => 28000,  'sale' => 22000],
        ['name' => '롱래스팅 립스틱 세트',   'slug' => 'longlasting-lipstick-set',   'cat' => 'makeup',         'price' => 32000,  'sale' => null],
        ['name' => '파운데이션 쿠션 리필',    'slug' => 'cushion-foundation-refill',  'cat' => 'makeup',         'price' => 24000,  'sale' => 19000],
        ['name' => '셀프 네일 젤 스타터킷',  'slug' => 'gel-nail-starter-kit',       'cat' => 'makeup',         'price' => 39000,  'sale' => null],
        ['name' => '오메가3 120캡슐',         'slug' => 'omega3-120caps',             'cat' => 'health-food',    'price' => 29000,  'sale' => 22000],
        ['name' => '유산균 프리미엄 60포',    'slug' => 'probiotics-premium-60pack',  'cat' => 'health-food',    'price' => 42000,  'sale' => null],
        ['name' => '멀티비타민 90정',         'slug' => 'multivitamin-90tabs',        'cat' => 'health-food',    'price' => 25000,  'sale' => 19800],
        ['name' => '프로틴 초코맛 1kg',       'slug' => 'protein-chocolate-1kg',      'cat' => 'health-food',    'price' => 48000,  'sale' => 38000],
        // 식품
        ['name' => '제주 한라봉 3kg',         'slug' => 'jeju-hallabong-3kg',         'cat' => 'fresh-food',     'price' => 28000,  'sale' => null],
        ['name' => '국내산 한우 등심 500g',   'slug' => 'hanwoo-sirloin-500g',        'cat' => 'fresh-food',     'price' => 55000,  'sale' => null],
        ['name' => '유기농 채소 꾸러미',      'slug' => 'organic-veggie-box',         'cat' => 'fresh-food',     'price' => 18000,  'sale' => 14900],
        ['name' => '비건 즉석 카레 10팩',    'slug' => 'vegan-curry-10pack',         'cat' => 'ready-to-eat',   'price' => 19800,  'sale' => 15900],
        ['name' => '냉동 만두 왕교자 1kg',    'slug' => 'frozen-dumpling-1kg',        'cat' => 'ready-to-eat',   'price' => 13900,  'sale' => null],
        ['name' => '콜드브루 원두커피 500ml', 'slug' => 'cold-brew-coffee-500ml',     'cat' => 'beverages',      'price' => 8900,   'sale' => null],
        ['name' => '유기농 그린티 20티백',    'slug' => 'organic-greentea-20bags',    'cat' => 'beverages',      'price' => 12000,  'sale' => 9500],
        ['name' => '드립 커피 핸드밀 세트',  'slug' => 'drip-coffee-handmill-set',   'cat' => 'beverages',      'price' => 58000,  'sale' => 48000],
        // 스포츠/레저
        ['name' => '요가매트 6mm 논슬립',     'slug' => 'yoga-mat-6mm-nonslip',       'cat' => 'fitness',        'price' => 35000,  'sale' => 28000],
        ['name' => '덤벨 세트 5~20kg',        'slug' => 'dumbbell-set-5-20kg',        'cat' => 'fitness',        'price' => 89000,  'sale' => null],
        ['name' => '폼롤러 33cm',             'slug' => 'foam-roller-33cm',           'cat' => 'fitness',        'price' => 22000,  'sale' => 17000],
        ['name' => '스마트 체중계 체지방',    'slug' => 'smart-scale-body-fat',       'cat' => 'fitness',        'price' => 55000,  'sale' => 43000],
        ['name' => '경량 트레킹 배낭 30L',    'slug' => 'lightweight-trekking-bag',   'cat' => 'outdoor',        'price' => 72000,  'sale' => 58000],
        ['name' => '방수 등산화 고어텍스',    'slug' => 'waterproof-hiking-boots',    'cat' => 'outdoor',        'price' => 148000, 'sale' => 120000],
        ['name' => '캠핑 폴딩 체어',          'slug' => 'camping-folding-chair',      'cat' => 'outdoor',        'price' => 42000,  'sale' => 33000],
        ['name' => '자전거 헬멧 경량',        'slug' => 'cycling-helmet-light',       'cat' => 'cycling',        'price' => 45000,  'sale' => null],
        ['name' => '사이클링 글러브',         'slug' => 'cycling-gloves',             'cat' => 'cycling',        'price' => 25000,  'sale' => 19000],
        ['name' => '수영 고글 미러 코팅',     'slug' => 'swim-goggles-mirror',        'cat' => 'swimming',       'price' => 18000,  'sale' => null],
        // 가구/인테리어
        ['name' => '인체공학 오피스 체어',    'slug' => 'ergonomic-office-chair',     'cat' => 'sofa-chair',     'price' => 280000, 'sale' => 235000],
        ['name' => '패브릭 1인 소파',         'slug' => 'fabric-single-sofa',         'cat' => 'sofa-chair',     'price' => 195000, 'sale' => null],
        ['name' => 'LED 스탠드 조명 스마트',  'slug' => 'led-stand-light-smart',      'cat' => 'lighting',       'price' => 68000,  'sale' => 55000],
        ['name' => '무드등 간접조명 세트',    'slug' => 'mood-light-set',             'cat' => 'lighting',       'price' => 32000,  'sale' => null],
        ['name' => '대나무 도마 세트',        'slug' => 'bamboo-cutting-board-set',   'cat' => 'household',      'price' => 24000,  'sale' => 18000],
        ['name' => '진공 밀폐 컨테이너 5p',  'slug' => 'vacuum-container-5pack',     'cat' => 'household',      'price' => 29000,  'sale' => null],
        ['name' => '극세사 이불 킹사이즈',    'slug' => 'microfiber-blanket-king',    'cat' => 'household',      'price' => 68000,  'sale' => 52000],
        ['name' => '천연 아로마 디퓨저',      'slug' => 'natural-aroma-diffuser',     'cat' => 'household',      'price' => 35000,  'sale' => 28000],
    ];

    public function run(): void
    {
        $categoryMap = Category::all()->keyBy('slug');

        foreach ($this->products as $i => $data) {
            $category = $categoryMap->get($data['cat']);
            if (! $category) continue;

            $stock  = rand(0, 200);
            $status = $stock === 0 ? 'soldout' : 'active';

            Product::create([
                'category_id'  => $category->id,
                'seller_id'    => null,
                'name'         => $data['name'],
                'slug'         => $data['slug'],
                'description'  => "{$data['name']}. 합리적인 가격에 고품질 상품을 제공합니다.",
                'price'        => $data['price'],
                'sale_price'   => $data['sale'],
                'stock'        => $stock,
                'status'       => $status,
                'images'       => [
                    "https://picsum.photos/seed/p{$i}a/600/600",
                    "https://picsum.photos/seed/p{$i}b/600/600",
                ],
                'rating_avg'   => round(rand(30, 50) / 10, 1),
                'order_count'  => rand(0, 300),
                'view_count'   => rand(10, 3000),
            ]);
        }
    }
}
