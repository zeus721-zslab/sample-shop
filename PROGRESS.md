# zslab shop — 작업 진행 현황

## 완료된 STEP
- [x] STEP 1: PROGRESS.md 생성 및 초기화
- [x] STEP 2: 디렉토리 구조 생성
- [x] STEP 3: Docker Compose 구성 (docker-compose.yml, docker-compose.stg.yml, Caddyfile, MariaDB/PHP 설정)
- [x] STEP 4: Laravel 13 초기 설치 — composer create-project + Sanctum + /api/health 엔드포인트 + Dockerfile
- [x] STEP 5: Next.js 15 초기 설치 — create-next-app (TS + Tailwind + App Router + src-dir) + standalone output + Dockerfile
- [x] STEP 6: Socket.io 서버 초기 설정 — package.json, server.js (Redis pub/sub 브릿지, cart/order/user rooms), Dockerfile
- [x] STEP 7: .env.example 작성 — SHOP_MODE 포함 전체 환경변수 정의
- [x] STEP 8: 헬퍼 스크립트 생성 — up.sh / down.sh / shell-api.sh / shell-front.sh / migrate.sh
- [x] STEP 9: GitHub Actions CI/CD — ci.yml (PR 테스트), deploy-staging.yml (develop→STG), deploy-production.yml (main→PROD)
- [x] STEP 10: README.md 작성

## 현재 진행 중
- [x] STEP 11: 전체 기동 및 확인 — 완료

## STEP 11 상태
zslab 계정이 docker 그룹에 포함되어 있지 않아 자동 실행 불가.
아래 명령을 root 또는 권한 있는 계정으로 실행 필요:

```bash
# 1. zslab 계정을 docker 그룹에 추가 (root 계정에서)
usermod -aG docker zslab
newgrp docker

# 2. /home/zslab 에서 기동
cd /home/zslab
./scripts/up.sh

# 3. 헬스 체크 (DNS 전파 후)
curl https://api.zslab-shop.duckdns.org/api/health
# 예상 응답: {"status":"ok","service":"zslab-api"}
```

## 생성된 파일 목록
```
/home/zslab/
├── README.md
├── PROGRESS.md
├── docker-compose.yml
├── docker-compose.stg.yml
├── .env.example
├── .env                         ← 개발용 임시 비밀번호로 생성됨 (교체 필요)
├── frontend/
│   ├── Dockerfile
│   ├── next.config.ts           ← standalone output 설정
│   └── (create-next-app 기본 파일 전체)
├── backend/
│   ├── Dockerfile
│   ├── routes/api.php           ← /api/health 엔드포인트 추가
│   ├── app/Models/User.php      ← HasApiTokens trait 추가
│   └── (Laravel 13 + Sanctum 설치 완료)
├── socket/
│   ├── Dockerfile
│   ├── package.json
│   └── server.js
├── docker/
│   ├── caddy/
│   │   ├── Dockerfile
│   │   ├── Caddyfile            ← 운영 도메인 라우팅
│   │   └── Caddyfile.stg        ← 스테이징 도메인 라우팅
│   ├── frankenphp/
│   │   ├── Caddyfile
│   │   └── php.ini
│   └── mariadb/
│       └── my.cnf
├── scripts/
│   ├── up.sh
│   ├── down.sh
│   ├── shell-api.sh
│   ├── shell-front.sh
│   └── migrate.sh
└── .github/workflows/
    ├── ci.yml
    ├── deploy-staging.yml
    └── deploy-production.yml
```

## 완료된 작업 (추가)
- [x] STEP 12: GitHub 레포 연결 (zeus721-zslab/zslab-shop) — git init + 초기 커밋 완료
  - 푸시 대기: GitHub PAT 또는 SSH 키 설정 필요
- [x] STEP 13: Phase 2 — 핵심 커머스 기반
  - DB: users(확장) + categories + products + orders + order_items + wishlists + reviews + coupons
  - Middleware: ShopMode (single/marketplace 분기)
  - API: GET /api/categories (트리/flat), GET /api/products (필터/정렬), GET /api/products/{slug}
  - Seeder: 6대분류 + 24소분류 + 상품 50개
  - APP_KEY 생성 완료 (.env)

## 완료된 작업 (Phase 2 마무리 + Phase 3)

### Phase 2 마무리 (백엔드)
- [x] STEP 14: 장바구니 API (Redis 기반) — CartController + CartService
- [x] STEP 15: 주문 API (낙관적 락) — OrderService (stock_version 체크 + 3회 재시도)
  - migration: 2026_04_21_000010_add_stock_version_to_products.php
  - 재시도 시 usleep(50ms * attempt) 백오프
  - 취소 시 재고 복원
- [x] STEP 16: 배송 상태 API — PATCH /api/orders/{id}/status
  - 허용 전환: paid→shipping→delivered, paid/pending→cancelled
- [x] STEP 17: PaymentGatewayInterface + MockGateway + PortoneGateway + AppServiceProvider 바인딩
- [x] STEP 18: AuthController (register/login/logout/me)

### Phase 3 (Next.js 프론트)
- [x] STEP 19: 공통 레이아웃 (Header, Footer, AuthProvider)
- [x] STEP 20: 홈 페이지 (히어로, 카테고리 그리드, 인기/신상품 섹션)
- [x] STEP 21: 상품 목록 페이지 (/products) — 카테고리 사이드바, 정렬, 페이지네이션
- [x] STEP 22: 상품 상세 페이지 (/products/[slug]) — 이미지, 가격, AddToCartButton
- [x] STEP 23: 장바구니 페이지 (/cart) — 수량 조절, 삭제, 결제 요약
- [x] STEP 24: 로그인/회원가입 페이지 (/login, /register)

## 완료된 작업 (홈 퍼블리싱)

### STEP 25: 홈 29CM 스타일 에디토리얼 리디자인
- globals.css: Tailwind v4 + 다크모드 비활성화 + scrollbar-none + product-img-wrap 호버 + link-underline
- layout.tsx: Noto Sans KR + Inter (next/font) + Footer 링크 4열 그리드
- Header.tsx: 검색 오버레이(모바일), 인라인 검색바(md+), GNB 8개 카테고리, 로그아웃 API 호출
- ProductCard.tsx: portrait/square/landscape ratio prop, 에디토리얼 타이포, 품절 오버레이
- page.tsx 완전 재설계:
  - HeroBanner: full-bleed 블랙, clamp 타이포, 배경 giant-text 장식
  - CategoryQuickMenu: 보더 하단 퀵탭
  - EditorialFeed: 12컬럼 비대칭 그리드 (7:5 / 2행), 텍스트 오버레이 카드, 보조 3열
  - EditorialBand: bg-[#f7f7f5] 브랜드 카피
  - NewArrivals: 가로 스크롤 카드
  - PopularProducts: 2×4 그리드
  - 모든 async 섹션 try/catch — API 다운 시 graceful empty 처리
- docker compose build + up 완료
- 외부 접근: https://zslab-shop.duckdns.org/ → HTTP 200 ✓

## 완료된 작업 (미완료 API 마무리)

### STEP 26: 인증 API 완성
- POST /api/auth/register ✓ (기존)
- POST /api/auth/login ✓ (기존)
- POST /api/auth/logout ✓ (기존)
- GET /api/auth/me ✓ (기존)
- GET /api/auth/social/{provider}/redirect ✓ (라우트 설계, 501 stub)
- GET /api/auth/social/{provider}/callback ✓ (라우트 설계, 501 stub)
- migration: 2026_04_21_000011_add_social_fields_to_users (social_provider, social_id, avatar)

### STEP 27: 위시리스트 API
- GET /api/wishlist ✓
- POST /api/wishlist/{productId} (토글) ✓
- DELETE /api/wishlist/{productId} ✓
- GET /api/wishlist/check/{productId} ✓

### STEP 28: 리뷰 API
- GET /api/products/{productId}/reviews ✓ (비로그인 가능)
- POST /api/products/{productId}/reviews ✓ (구매 확인 시 is_verified=true)
- DELETE /api/reviews/{id} ✓ (본인만)
- ReviewService: 리뷰 작성/삭제 후 rating_avg 자동 재계산

### STEP 29: 주문 API 마무리
- POST /api/orders/{id}/cancel ✓ (재고 복원 포함)
- GET /api/orders ✓ (기존)
- GET /api/orders/{id} ✓ (기존)

### STEP 30: 마이페이지 API
- GET /api/my/profile ✓
- PATCH /api/my/profile ✓ (비밀번호 변경 포함)
- GET /api/my/orders ✓
- GET /api/my/reviews ✓
- GET /api/my/wishlist ✓

### STEP 31: 검색 API (Elasticsearch 연동)
- GET /api/search?q= ✓
- SearchService: ES multi_match (name^3/category^2/desc^1) + fuzziness AUTO
- DB fallback (ES 다운 시 LIKE 쿼리 자동 전환)
- ensureIndex: 인덱스 없으면 자동 생성 (Korean custom analyzer)
- Artisan: php artisan products:index (--fresh 옵션)
- 상품 50개 인덱싱 완료 ✓

### 추가 작업
- scripts/restart-api.sh: FrankenPHP Worker 재시작 + 캐시 클리어
- FrankenPHP Worker 모드 캐시 이슈 발견: 코드 변경 후 restart 필요

### 전체 라우트 (35개)
- Health, Auth(6), Category(2), Product(2+2), Search(1), Cart(5), Order(5), Wishlist(4), Review(3), My(5)

## 완료된 작업 (Phase 4)

### STEP 32: /checkout 페이지
- 배송지 입력 폼 (수취인/연락처/우편번호/주소/상세주소)
- 로그인 유저명 자동 채우기
- 쿠폰 코드 입력란
- 카트 아이템 요약 (이미지/이름/수량/금액)
- 배송비 계산 (5만원 이상 무료)
- orderApi.create 호출 → /order/complete?id=... 리다이렉트
- 비로그인 시 /login?redirect=/checkout 리다이렉트
- 빈 카트면 /cart로 리다이렉트

### STEP 33: /order/complete 페이지
- 주문 완료 체크마크 UI
- 주문번호, 상품 목록, 금액, 배송지, 상태 표시
- useSearchParams로 주문 ID 수신 → Suspense 래핑
- 주문 내역 보기 (/my?tab=orders) / 쇼핑 계속하기 링크

### STEP 34: /my 마이페이지
- 4개 탭: 프로필 / 주문 내역 / 위시리스트 / 내 리뷰
- URL 쿼리파라미터 (?tab=...) 로 탭 상태 관리
- 프로필 탭: 이름/연락처 수정, 비밀번호 변경
- 주문 내역 탭: 목록 (페이지네이션), 주문 취소 기능
- 위시리스트 탭: 상품 그리드, 개별 삭제
- 내 리뷰 탭: 리뷰 목록, 개별 삭제
- Suspense 래핑 (useSearchParams 사용)
- 빌드 + docker compose up -d 완료, HTTP 200 확인

## 완료된 작업 (Phase 4 마무리)

### STEP 35: 검색 페이지 (/search)
- Elasticsearch 연동 (DB fallback 포함)
- 검색어 재검색 폼 + 정렬 탭
- 빈 결과 empty state
- 인기상품 fallback (검색어 없을 때)

### STEP 36: 카테고리 페이지 (/category/[slug])
- 브레드크럼 (홈 > 대분류 > 소분류)
- 서브카테고리 탭 (있을 때 표시)
- 사이드바: 형제/하위 카테고리 네비게이션
- 정렬 + 상품 그리드 + 페이지네이션
- notFound() 처리

### STEP 37: 버그 수정 (백엔드)
- bootstrap/app.php: API 인증 실패 시 JSON 401 반환 (Route[login] 에러 해결)
- OrderService: paid_amount=0 기본값 추가 (DB constraint 에러 해결)
- MockPaymentGateway: route() 제거 → next_action=none으로 즉시 paid 처리
- OrderService: next_action=none일 때 auto-confirm (status=paid)
- ReviewController: productId 타입 int→string (TypeError 해결)

### STEP 38: 버그 수정 (프론트엔드 API)
- categoryApi.list(): { data: [...] } 언래핑
- categoryApi.get(): { data: {...} } 언래핑
- productApi.get(): { data: {...} } 언래핑
- Header GNB: /products?category=... → /category/... 링크 수정
- 검색 제출: /products?search=... → /search?q=... 수정
- 홈 카테고리 퀵탭: /category/... 링크 수정

### STEP 39: 전체 플로우 검증 완료
- 회원가입 → 로그인 → 상품조회 → 장바구니 → 주문 생성 → paid 상태 확인 ✓
- 모든 API 정상 응답 확인
- 12개 페이지 전부 HTTP 200 확인

## 완료된 작업 (장바구니/구매 플로우 검증 및 버그 수정)

### STEP 40: 장바구니 + 구매 플로우 E2E 검증
**수정 내용:**
- AddToCartButton: "바로구매" 버튼이 카트 추가 없이 /cart 이동하던 버그 수정
  → cartApi.add() 호출 후 /checkout으로 이동, 로딩 상태 추가
- AuthProvider: `isLoaded` 상태 추가 (localStorage 복원 완료 신호)
  → auth store에 `isLoaded` 필드 추가
- cart/checkout/my/order-complete 페이지: `isLoaded` 확인 후 리다이렉트
  → 로그인된 사용자가 새로고침 시 login으로 잘못 이동하는 버그 수정
- CartService::all(): 빈 카트 시 `[]` 반환 → `{items:[], count:0, subtotal:0}` 수정
  → 프론트에서 CartData 타입 파싱 실패 방지
- 상품 상세 카테고리 링크: /products?category= → /category/ 수정

**E2E 검증 결과:**
- 상품 상세 조회 ✓ (stock/price 정상)
- 장바구니 담기 ✓ (cart_item_id 반환)
- 수량 변경 ✓ (2→3, subtotal 87,000)
- 주문 생성 ✓ (status: paid, 금액 정확)
- 주문 상세 조회 ✓ (items, shipping_address 포함)
- 주문 후 장바구니 자동 비움 ✓ ({items:[], count:0})
- 전체 11개 페이지 HTTP 200 ✓

## 완료된 작업 (CORS 버그 수정)

### STEP 41: POST /api/cart → 302 리다이렉트 + CORS 오류 해결
**원인 분석:**
1. `api.ts`의 `request()` 함수에서 `...init` 스프레드가 병합된 `headers` 객체를 덮어쓰는 버그
   → `Content-Type: application/json`이 POST 요청에서 누락됨 → Laravel이 요청을 파싱 못해 302 리다이렉트
   → 수정: `const { headers: initHeaders, ...restInit } = init ?? {}` 패턴으로 해결
2. Caddy Caddyfile의 `handle /api/*` 블록에서 `Access-Control-Allow-Origin` 헤더 추가
   → Laravel HandleCors 미들웨어도 동일 헤더 추가 → 브라우저가 중복 헤더 거부 → CORS TypeError
   → 수정: `/home/zslab/docker/caddy/Caddyfile`에서 CORS 헤더 블록 제거, Laravel CORS만 유지

**검증:**
- OPTIONS preflight: ACAO 헤더 1개 (`*`) ✓
- POST /api/cart: HTTP 201 ✓ (단일 ACAO 헤더)
- GET /api/cart: 아이템 목록 정상 반환 ✓

## 완료된 작업 (검색 버그 수정)

### STEP 42: Elasticsearch 부분 검색 버그 수정
**원인:**
- `ensureIndex()`가 `standard` tokenizer 사용 → "멀티비타민"이 단일 토큰으로 처리
- `fuzziness: AUTO`는 부분 문자열 매칭 불가 (편집 거리 기반)

**수정 내용:**
- `ngram` tokenizer 추가 (min_gram:2, max_gram:10, token_chars: letter+digit)
- 인덱싱 analyzer: `korean_ngram` (ngram tokenizer + lowercase) — 모든 부분 문자열 토큰화
- 검색 analyzer: `korean_search` (standard tokenizer + lowercase) — 검색어 원형 그대로 매칭
- `max_ngram_diff: 9` 설정 (ES 8 필수, max-min 이상)
- 매핑에서 `boost` 제거 (ES 8에서 미지원 → 쿼리 fields `name^3` 에서 처리)
- `fuzziness: AUTO` 제거 (ngram이 부분 매칭 담당)

**검증:**
- "멀티" 검색 → "멀티비타민 90정" 1위 노출 ✓
- "비타민" 검색 → "멀티비타민 90정" 1위 + "비타민C 세럼 30ml" 노출 ✓

## 다음 작업
- GitHub PAT 설정 후 push
- GitHub Secrets 등록: PROD_SSH_HOST/USER/KEY, STG_SSH_HOST/USER/KEY
- 소셜 로그인 (Google/Kakao) 실제 연동

## 오류 기록
- STEP 11 자동 실행 불가: zslab 계정이 docker 그룹에 미포함
  → `usermod -aG docker zslab` 후 재로그인 필요

## 아키텍처 변경 이력
- DuckDNS 서브도메인 미지원 → path-based 라우팅으로 변경
  - zslab-shop.duckdns.org/api/* → Laravel API
  - zslab-shop.duckdns.org/* → Next.js
- 포트 충돌 해결: 포트폴리오 FrankenPHP(80/443 마스터) + 쇼핑몰 내부 Caddy(HTTP 8080)
  - portfolio_app이 TLS 처리 후 zslab_caddy:8080으로 프록시
  - /home/portfolio/docker/frankenphp/Caddyfile.d/shop.caddyfile 소스 파일로 영구 저장
  - /home/portfolio/docker-compose.yml: Caddyfile.d 볼륨 마운트 추가, zslab_zslab_net external 네트워크 추가
  - zslab docker-compose.yml: caddy 외부 포트 제거, portfolio_portfolio_net 공유
  - 재빌드 시에도 shop.caddyfile 유지됨
