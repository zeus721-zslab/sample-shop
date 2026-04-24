<?php

namespace Database\Seeders;

use App\Models\Notice;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{
    public function run(): void
    {
        $notices = [
            [
                'title'     => '[공지] zslab shop 오픈 안내',
                'content'   => "<h2>zslab shop이 정식 오픈했습니다!</h2>\n<p>안녕하세요, zslab shop을 찾아주신 고객 여러분께 감사드립니다.</p>\n<p>패션부터 라이프스타일까지, 취향을 발견하는 공간 zslab shop이 정식 오픈하였습니다.</p>\n<p>오픈 기념으로 <strong>전 상품 무료 배송</strong> 이벤트를 진행합니다. 많은 이용 부탁드립니다.</p>\n<ul>\n<li>이벤트 기간: 2026.04.24 ~ 2026.05.31</li>\n<li>대상: 전 회원</li>\n<li>혜택: 전 상품 무료 배송</li>\n</ul>",
                'category'  => 'event',
                'is_pinned' => true,
            ],
            [
                'title'     => '[공지] 개인정보처리방침 개정 안내',
                'content'   => "<h2>개인정보처리방침이 개정됩니다.</h2>\n<p>zslab shop은 더 나은 서비스 제공과 개인정보 보호를 위해 개인정보처리방침을 개정합니다.</p>\n<p>주요 변경 내용:</p>\n<ul>\n<li>개인정보 수집 항목 명확화</li>\n<li>제3자 제공 범위 조정</li>\n<li>보관 기간 업데이트</li>\n</ul>\n<p>개정 시행일: 2026년 5월 1일</p>",
                'category'  => 'policy',
                'is_pinned' => true,
            ],
            [
                'title'     => '[공지] 배송 지연 안내 (2026년 4월)',
                'content'   => "<h2>일부 상품 배송 지연 안내</h2>\n<p>물류 센터 점검으로 인해 4월 25일 ~ 26일 주문 건의 경우 배송이 1~2일 지연될 수 있습니다.</p>\n<p>불편을 드려 대단히 죄송합니다. 빠른 시일 내 정상화될 수 있도록 최선을 다하겠습니다.</p>\n<p>문의사항은 고객센터(help@zslab.com)로 연락 주시기 바랍니다.</p>",
                'category'  => 'delivery',
                'is_pinned' => false,
            ],
            [
                'title'     => '[이벤트] 회원가입 혜택 안내',
                'content'   => "<h2>신규 회원 가입 혜택</h2>\n<p>zslab shop에 가입하시면 아래 혜택을 드립니다.</p>\n<ul>\n<li>웰컴 쿠폰 5,000원 즉시 지급</li>\n<li>첫 구매 시 추가 10% 할인</li>\n<li>생일 월 특별 혜택</li>\n</ul>\n<p>지금 바로 가입하고 혜택을 누려보세요!</p>",
                'category'  => 'event',
                'is_pinned' => false,
            ],
            [
                'title'     => '[공지] 서비스 점검 안내 (2026.05.01)',
                'content'   => "<h2>정기 서비스 점검 안내</h2>\n<p>더 나은 서비스 제공을 위해 정기 점검을 실시합니다.</p>\n<p>점검 일시: 2026년 5월 1일 (금) 새벽 2:00 ~ 5:00 (3시간)</p>\n<p>점검 내용:</p>\n<ul>\n<li>서버 인프라 업그레이드</li>\n<li>데이터베이스 최적화</li>\n<li>보안 패치 적용</li>\n</ul>\n<p>점검 시간 중에는 서비스 이용이 불가합니다. 양해 부탁드립니다.</p>",
                'category'  => 'system',
                'is_pinned' => false,
            ],
            [
                'title'     => '[안내] 반품/교환 정책 변경 안내',
                'content'   => "<h2>반품 및 교환 정책이 변경됩니다.</h2>\n<p>고객님의 쇼핑 편의를 위해 반품/교환 정책을 개선합니다.</p>\n<p>변경 내용 (2026.05.01 부터 적용):</p>\n<ul>\n<li>반품 신청 기간: 수령 후 7일 → 14일로 연장</li>\n<li>단순 변심 반품 배송비: 3,000원</li>\n<li>상품 하자 시 무료 교환/반품</li>\n</ul>",
                'category'  => 'policy',
                'is_pinned' => false,
            ],
        ];

        foreach ($notices as $notice) {
            Notice::updateOrCreate(['title' => $notice['title']], $notice);
        }
    }
}
