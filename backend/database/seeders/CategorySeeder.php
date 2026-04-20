<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            ['name' => '패션/의류',    'slug' => 'fashion', 'children' => [
                ['name' => '남성 의류',   'slug' => 'mens-clothing'],
                ['name' => '여성 의류',   'slug' => 'womens-clothing'],
                ['name' => '아동 의류',   'slug' => 'kids-clothing'],
                ['name' => '속옷/양말',   'slug' => 'underwear'],
            ]],
            ['name' => '전자제품',     'slug' => 'electronics', 'children' => [
                ['name' => '스마트폰',    'slug' => 'smartphones'],
                ['name' => '노트북/PC',   'slug' => 'laptops'],
                ['name' => '태블릿',      'slug' => 'tablets'],
                ['name' => '오디오',      'slug' => 'audio'],
                ['name' => '카메라',      'slug' => 'cameras'],
            ]],
            ['name' => '뷰티/헬스',    'slug' => 'beauty', 'children' => [
                ['name' => '스킨케어',    'slug' => 'skincare'],
                ['name' => '메이크업',    'slug' => 'makeup'],
                ['name' => '헤어케어',    'slug' => 'haircare'],
                ['name' => '건강식품',    'slug' => 'health-food'],
            ]],
            ['name' => '식품',         'slug' => 'food', 'children' => [
                ['name' => '신선식품',    'slug' => 'fresh-food'],
                ['name' => '간편식',      'slug' => 'ready-to-eat'],
                ['name' => '음료',        'slug' => 'beverages'],
                ['name' => '과자/스낵',   'slug' => 'snacks'],
            ]],
            ['name' => '스포츠/레저',  'slug' => 'sports', 'children' => [
                ['name' => '운동용품',    'slug' => 'fitness'],
                ['name' => '아웃도어',    'slug' => 'outdoor'],
                ['name' => '자전거',      'slug' => 'cycling'],
                ['name' => '수영',        'slug' => 'swimming'],
            ]],
            ['name' => '가구/인테리어','slug' => 'furniture', 'children' => [
                ['name' => '소파/의자',   'slug' => 'sofa-chair'],
                ['name' => '침대/매트리스','slug' => 'bed-mattress'],
                ['name' => '조명',        'slug' => 'lighting'],
                ['name' => '생활용품',    'slug' => 'household'],
            ]],
        ];

        foreach ($tree as $i => $cat) {
            $parent = Category::create([
                'name'       => $cat['name'],
                'slug'       => $cat['slug'],
                'sort_order' => $i,
                'is_active'  => true,
            ]);

            foreach ($cat['children'] as $j => $child) {
                Category::create([
                    'parent_id'  => $parent->id,
                    'name'       => $child['name'],
                    'slug'       => $child['slug'],
                    'sort_order' => $j,
                    'is_active'  => true,
                ]);
            }
        }
    }
}
