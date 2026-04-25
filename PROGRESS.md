# zslab shop — 작업 진행 현황

---
## 작업 방식 (고정)
- 작업 시작 전 스텝 목록 먼저 PROGRESS.md에 기록
- 각 스텝 완료 시마다 즉시 [x] 업데이트
- 스텝 형식:
  - [ ] STEP N: 작업 내용 (시작 전)
  - [x] STEP N: 작업 내용 (완료 후)
- 전체 완료 후 요약 보고
---

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

## 완료된 작업 (GitHub Push 준비)

### STEP 44: README.md 전면 재작성
- TOC 추가, 뱃지 보강 (Zustand, Socket.io, Caddy 추가)
- API Overview 35개 엔드포인트 전체 나열
- CI/CD 테이블 정리, Docker 네트워크 테이블 추가
- 디렉토리 구조 상세화

### STEP 43: GitHub Push 준비
- .gitignore 보완: `.claude/`, `.claude.json`, `.docker/`, `.zshrc`, `.zsh_history` 추가
- 커밋: feat: 고객 사이트 프론트 완성 + API 마무리 + 버그 수정 (54 files, 5674 insertions)
- remote: https://github.com/zeus721-zslab/zslab-shop.git ✓

## 완료된 작업 (Nginx 마스터 게이트웨이)

### STEP 45: Nginx 마스터 리버스프록시 구성

**아키텍처:**
```
인터넷 (80/443)
└── gateway_nginx (Nginx 1.27, /home/gateway/)
    ├── zslab.duckdns.org      → portfolio_app:80 (HTTP, 내부)
    ├── zslab-shop.duckdns.org → zslab_caddy:8080 (HTTP, 내부)
    └── zslab-stg.duckdns.org  → zslab_caddy:8080 (HTTP, 내부)
```

**주요 변경:**
- `/home/gateway/` 생성 (docker alpine를 통해 root 소유)
  - `docker-compose.yml`: nginx:1.27-alpine, gateway_net 생성
  - `nginx/nginx.conf`: TLS 종료, resolver 127.0.0.11, proxy_pass 변수
  - `certs/`: 기존 Caddy 인증서 재활용 (유효 2026-07-19)
- `portfolio_app`: 포트 80/443 제거, `SERVER_NAME: http://:80`, gateway_net 연결
  - `docker run` 직접 재생성 (docker compose .env 권한 문제 우회)
- `zslab_caddy`: portfolio_portfolio_net 제거, gateway_net 연결
- `zslab docker-compose.yml`: portfolio_portfolio_net → gateway_net 으로 교체
- `shop.caddyfile`: 내용 비움 (Nginx가 라우팅 담당)

**검증 결과:**
- HTTP → HTTPS 301 리다이렉트 ✓
- https://zslab-shop.duckdns.org → 200 OK ✓
- https://zslab-shop.duckdns.org/api/health → {"status":"ok"} ✓
- https://zslab.duckdns.org → 200 OK ✓
- portfolio_app 80/443 외부 바인딩 없음 ✓
- gateway_nginx 80/443 점유 ✓

**인증서 갱신 주의:**
- 현재 인증서: /home/gateway/certs/ (Caddy에서 추출, 유효 2026-07-19)
- 갱신 방법: certbot 또는 Caddy를 이용해 재발급 후 /home/gateway/certs/ 업데이트 필요

## 완료된 작업 (관리자 패널)

### STEP 46: 관리자 패널 (AdminLTE 4 기반)
- URL: https://zslab-shop.duckdns.org/zslab-manage/
- 로그인: admin@zslab.com / [비밀번호는 .env 참고]

**백엔드 (Laravel Web 세션 기반):**
- `routes/admin.php` — 21개 라우트 (`zslab-manage` prefix)
- `bootstrap/app.php` — admin 라우트 등록 (`then:` 콜백) + `admin.auth` 미들웨어 alias 추가
- `AdminAuth.php` — 세션 인증 미들웨어 (role=admin 체크)
- Controllers: Auth/Dashboard/Product/Order/Member/Category

**뷰 (AdminLTE 4 + Bootstrap 5 + Bootstrap Icons):**
- `admin/layouts/app.blade.php` — 공통 레이아웃 (사이드바, 네비바)
- `admin/auth/login.blade.php` — 로그인 페이지
- `admin/dashboard/index.blade.php` — KPI 카드 + 최근 주문/회원
- `admin/products/` — index / create / edit / _form
- `admin/orders/` — index / show (상태 변경)
- `admin/members/index.blade.php` — 등급 변경 + 활성/정지 토글
- `admin/categories/index.blade.php` — 대/소분류 목록 + 수정 모달

**인프라:**
- `docker/caddy/Caddyfile` — `/zslab-manage*`, `/storage/*` → Laravel 라우팅 추가
- `AdminSeeder` — admin@zslab.com 계정 생성 (updateOrCreate)

**검증:**
- 로그인 → 302 → 대시보드 200 ✓
- 상품/주문/회원/카테고리 관리 각 200 ✓
- API 기존 동작 정상 ✓

## 완료된 작업 (관리자 패널 데모 계정)

### STEP 47: 읽기 전용 데모 계정 구현
- URL: https://zslab-shop.duckdns.org/zslab-manage/
- 데모 로그인: demo@zslab.com / demo1234!

**백엔드:**
- `DemoSeeder` — demo@zslab.com 계정 생성 (role=demo, .env에서 읽음)
- `AdminAuth.php` — role=admin 또는 role=demo 접근 허용
- `AuthController` — login/showLogin에 demo role 허용 추가
- `DemoGuard.php` — role=demo 시 GET 외 요청 차단 + 플래시 메시지
- `bootstrap/app.php` — `demo.guard` alias 추가
- `routes/admin.php` — 인증 필요 그룹에 `demo.guard` 미들웨어 추가
- `.env` / `.env.example` — DEMO_EMAIL, DEMO_PASSWORD 추가

**뷰:**
- `layouts/app.blade.php` — 데모 배너(노란색) + page-actions 숨김
- `products/index.blade.php` — 수정/삭제 버튼 → "조회 전용" 텍스트
- `orders/show.blade.php` — 상태 변경 폼 → lock 아이콘 + 안내 메시지
- `members/index.blade.php` — 등급변경/정지 버튼 → "조회 전용" 텍스트
- `categories/index.blade.php` — 추가 폼 패널 숨김 + 수정/삭제 버튼 숨김
- `auth/login.blade.php` — "데모로 체험하기" 버튼 (자동 입력 + 제출)

**검증:**
- demo@zslab.com 로그인 → 대시보드 200 ✓
- 읽기 전용 배너 표시 ✓
- 상품 목록 "조회 전용" 표시 ✓
- 주문 상세 상태 변경 차단 메시지 ✓
- "데모로 체험하기" 버튼 렌더링 ✓

## 완료된 작업 (프론트엔드 애니메이션)

### STEP 48: Framer Motion 애니메이션 적용

**설치:** `framer-motion` npm 패키지

**공통 컴포넌트 (`src/components/motion/`):**
- `FadeIn.tsx` — 마운트 시 opacity + y 페이드인 (delay 지원)
- `ScrollReveal.tsx` — 스크롤 진입 시 페이드인 (useInView)
- `StaggerList.tsx` — 자식 순차 등장 (stagger, inView 연동)
- `PageTransition.tsx` — 페이지 전환 fade+y 효과

**적용 내역:**
- `layout.tsx` — PageTransition으로 모든 페이지 진입 애니메이션
- `Header.tsx` — 스크롤 시 그림자 전환, 검색 오버레이 AnimatePresence 슬라이드
- `page.tsx` (홈) — 히어로 텍스트 순차 FadeIn, 카테고리/에디토리얼/신상품/인기 StaggerList/ScrollReveal
- `ProductCard.tsx` — whileHover y:-2, 이미지 scale(1.05), 정보 슬라이드업, `'use client'` 전환, ProductCardSkeleton 추가
- `AddToCartButton.tsx` — 수량 숫자 bounce, 버튼 whileHover/whileTap, 성공 플로팅 뱃지 AnimatePresence
- `products/page.tsx` — StaggerList 그리드
- `category/[slug]/page.tsx` — StaggerList 그리드
- `cart/page.tsx` — AnimatePresence 아이템 슬라이드 in/out, skeleton-shimmer 로딩
- `globals.css` — skeleton-shimmer keyframe 추가

**디자인 원칙:** duration 0.3~0.5s, easeOut, subtle 효과

**검증:** 빌드 성공 ✓ / 전 페이지 200 ✓

## 완료된 작업 (푸터 메뉴 페이지 구성)

### STEP 49: 공지사항/FAQ/정적 페이지 구현 (2026-04-24)

**Backend:**
- DB 마이그레이션: notices, faqs 테이블 생성 ✓
- Model: Notice, Faq ✓
- Seeder: 공지사항 6개, FAQ 카테고리별 4개 (총 16개) ✓
- API: GET /api/notices, GET /api/notices/{id}, GET /api/faqs ✓
- 관리자 Controller: Admin\NoticeController, Admin\FaqController (CRUD) ✓
- 관리자 Views: notices/{index,create,edit,_form}, faqs/{index,create,edit,_form} ✓
- 사이드바: 공지사항/FAQ 관리 메뉴 추가 ✓

**Frontend:**
- Types: Notice, Faq 추가 ✓
- API: noticeApi, faqApi 추가 ✓
- /notice — 공지사항 목록 (핀고정 강조, 카테고리 뱃지) ✓
- /notice/[id] — 공지사항 상세 (잡지 아티클 레이아웃) ✓
- /faq — 카테고리 탭 + Framer Motion 아코디언 ✓
- /shipping — 배송 정책 카드 그리드 + 스텝 다이어그램 ✓
- /about — 히어로 + 브랜드 스토리 + 핵심 가치 + 카운트업 애니메이션 ✓
- /terms, /privacy — 준비 중 페이지 (아이콘 + hover 애니메이션) ✓
- Footer 링크 실제 경로로 업데이트 ✓

**검증:**
- GET /api/notices → 200 (공지 6건) ✓
- GET /api/notices/1 → 200 ✓
- GET /api/faqs → 200 (카테고리별 그룹 반환) ✓
- 7개 프론트엔드 페이지 모두 HTTP 200 ✓
- 관리자 /zslab-manage/notices, /zslab-manage/faqs → 200 ✓

## 완료된 작업 (zslab-chat 쇼핑몰 연동)

### STEP 50: zslab-chat 실시간 1:1 문의 채팅 연동 (2026-04-24)

**인프라:**
- `docker-compose.yml`: zslab-chat 서비스 추가 (node:20-alpine, port 3001, zslab_net)
  - 볼륨: `./zslab-chat:/app` (로컬 채팅 서버 소스)
  - 환경변수: DB_HOST/PORT/DATABASE/USERNAME/PASSWORD, REDIS_URL, JWT_SECRET
- `docker/caddy/Caddyfile`: `/chat/*` → zslab-chat:3001 WebSocket 프록시 추가

**채팅 서버 (`./zslab-chat/`):**
- `package.json`: socket.io, express, mysql2, ioredis, jsonwebtoken
- `server/index.js`: Socket.io 서버
  - JWT 인증 미들웨어 (HS256)
  - 이벤트: join_room / send_message / mark_read / typing_start / typing_stop
  - Redis pub/sub 브로드캐스트 (다중 인스턴스 대비)
  - DB: MariaDB (chat_rooms, chat_participants, chat_messages)
  - 헬스체크: GET /health

**DB 마이그레이션:**
- `2026_04_24_000010_create_chat_tables.php`: chat_rooms, chat_participants, chat_messages 테이블

**Backend API (Laravel):**
- `ChatController.php`: token / findOrCreateRoom / messages / unreadCount
- `config/chat.php`: CHAT_JWT_SECRET 환경변수 바인딩
- `routes/api.php`: POST /api/chat/token, POST /api/chat/rooms, GET /api/chat/rooms/{id}/messages, GET /api/chat/unread

**Frontend (Next.js):**
- `frontend/package.json`: socket.io-client ^4.7.5 추가
- `src/hooks/useChat.ts`: Socket.io 훅 (연결/메시지/타이핑/읽음 처리)
- `src/components/chat/ChatWidget.tsx`: 우하단 고정 채팅 버튼 + 미읽음 뱃지
- `src/components/chat/ChatWindow.tsx`: 채팅창 UI (말풍선, 타이핑 인디케이터)
- `src/app/layout.tsx`: `<ChatWidget />` 추가 (로그인 유저만 표시)

**관리자 패널 (AdminLTE):**
- `Admin\InquiryController.php`: 문의 목록 + 관리자 JWT 발급
- `resources/views/admin/inquiries/index.blade.php`: 실시간 채팅 관리 패널
  - 문의 목록 사이드바 (미읽음 뱃지, 마지막 메시지 미리보기)
  - 관리자 채팅 영역 (Socket.io CDN 직접 연결)
  - 읽음 처리 관리자에게만 표시
- `routes/admin.php`: GET /zslab-manage/inquiries 라우트
- `admin/layouts/app.blade.php`: 사이드바 1:1 문의 메뉴 추가

**검증:**
- GET /api/health → {"status":"ok"} ✓
- GET /chat/health → {"status":"ok","service":"zslab-chat"} ✓
- zslab_chat 컨테이너 Up (MariaDB+Redis 연결 ✓)
- https://zslab-shop.duckdns.org/ → 200 ✓ (ChatWidget 포함)
- https://zslab-shop.duckdns.org/zslab-manage/inquiries → 200 ✓

## 완료된 작업 (zslab-chat GitHub clone 연동)

### STEP 51: zslab-chat GitHub 레포에서 clone 후 연동 (2026-04-24)
- 기존 /home/zslab/zslab-chat (Docker root 소유 node_modules) → alpine 컨테이너로 삭제
- git clone https://github.com/zeus721-zslab/zslab-chat.git ✓
- .env 생성: DB_HOST=mariadb, DB_DATABASE=zslab_shop, REDIS_URL (쇼핑몰 동일 DB/Redis)
- docker-compose.yml 볼륨 `./zslab-chat:/app` — 경로 일치 ✓
- docker compose down zslab-chat → up -d zslab-chat ✓
- npm install 128 packages (dotenv, @socket.io/redis-adapter 포함)
- Redis adapter connected ✓
- GET /health → `{"status":"ok","service":"zslab-chat"}` ✓

**새 레포 구조 (모듈화):**
- server/config.js, db.js
- server/handlers/{chat,room,typing}.js
- server/middleware/auth.js
- server/models/{Message,Room}.js

## 완료된 작업 (채팅 위젯 UI 개선)

### STEP 52: 채팅 위젯 애니메이션 + 쇼핑몰 톤앤매너 개선 (2026-04-24)

**변경 파일:**
- `frontend/src/components/chat/ChatWidget.tsx` — Framer Motion 적용 전면 개편
- `frontend/src/components/chat/ChatWindow.tsx` — Tailwind + 에디토리얼 디자인 전환
- `frontend/src/app/globals.css` — `chatBounce` 키프레임 + `.chat-bounce` 클래스 추가

**ChatWidget 개선:**
- AnimatePresence + motion.div로 채팅창 슬라이드업 (y:16→0, scale:0.96→1, duration:0.22s)
- motion.button: whileHover scale(1.06), whileTap scale(0.94)
- 아이콘 전환 애니메이션: 열기(채팅 아이콘) ↔ 닫기(X) — rotate 45도 페이드 교차
- 미읽음 뱃지: spring scale 팝인/아웃 (AnimatePresence)
- 버튼 색상: `#4f46e5 purple` → `#111 black` (쇼핑몰 에디토리얼 톤)

**ChatWindow 개선:**
- 인라인 style 제거 → Tailwind 클래스 전환
- 헤더: purple → black, 연결 상태 도트 인디케이터 (emerald/gray)
- 메시지 버블: isMine = `#111 bg` + `rounded-br-sm`, admin = `gray-100` + `rounded-bl-sm`
- 타이핑 인디케이터: `chat-bounce` CSS 클래스 (globals.css)
- 입력창: focus:border-[#111] 언더라인, 전송 버튼 아이콘(SVG) 교체
- 빈 메시지 상태: 아이콘 + 안내 문구

**검증:**
- 빌드 성공 ✓
- https://zslab-shop.duckdns.org/ → 200 ✓

## 완료된 작업 (채팅 소켓 연결 버그 수정)

### STEP 53: 채팅 위젯 소켓 연결 "연결 중..." 문제 해결 (2026-04-24)

**원인:**
- `frontend/.env.production`에 `NEXT_PUBLIC_CHAT_URL`이 누락됨
- Next.js standalone 빌드는 `NEXT_PUBLIC_*` 변수를 빌드 타임에 소스에 embed (런타임 docker-compose 환경변수 무시)
- `useChat.ts`에서 `chatUrl = ''` → `if (!token || !chatUrl) return` 조기 리턴 → 소켓 연결 시도 자체 없음
- `connected`가 항상 `false` → 입력창 disabled (거절 아이콘)

**수정 내용:**
- `frontend/.env.production`: `NEXT_PUBLIC_CHAT_URL=https://zslab-shop.duckdns.org` 추가
- `docker compose build --no-cache frontend` 강제 리빌드
- `docker compose up -d frontend` 재시작

**검증:**
- 프론트엔드 빌드 내 `https://zslab-shop.duckdns.org` URL embed 확인 ✓
- `https://zslab-shop.duckdns.org/chat/socket.io/?EIO=4&transport=polling` → 정상 핸드쉐이크 응답 ✓
  ```json
  {"sid":"...","upgrades":["websocket"],"pingInterval":25000,"pingTimeout":20000}
  ```
- zslab_chat 컨테이너 내부 health → `{"status":"ok","service":"zslab-chat"}` ✓
- https://zslab-shop.duckdns.org/ → 200 ✓

**소켓 연결 전체 경로:**
```
브라우저 → HTTPS → Nginx (gateway) → Caddy (:8080) → /chat/* 매치
→ uri strip_prefix /chat → zslab-chat:3001/socket.io → Socket.io v4 서버
```

## 완료된 작업 (WebSocket 업그레이드 문제 해결)

### STEP 54: WebSocket 연결 실패 해결 (2026-04-24)

**원인 분석 (단계별):**

1. **NEXT_PUBLIC_CHAT_URL 누락** (STEP 53에서 수정) — `chatUrl = ''`이라 소켓 연결 시도 자체 없음
2. **Caddy `{http.connection}` 플레이스홀더 이슈** — Nginx hop-by-hop 처리 후 Connection 헤더가 비어
   `header_up Connection {http.connection}` 이 빈 값 전달 → engine.io "Bad handshake method" 400
3. **Caddy 자동 WebSocket 처리 한계** — header_up 제거 후에도 Nginx → Caddy → zslab-chat 경로에서 WebSocket 업그레이드 실패 지속
4. **근본 원인**: 2-hop 프록시(Nginx → Caddy → zslab-chat) 구조에서 hop-by-hop 헤더 손실

**최종 해결 방법 (아키텍처 변경):**
- `docker-compose.yml`: zslab-chat 서비스에 `gateway_net` 추가 (Nginx와 동일 네트워크)
- `docker/caddy/Caddyfile`: /chat/* 핸들러 단순화 (fallback 용도만)
- `/home/gateway/nginx/nginx.conf`:
  - `/chat/` location → `proxy_pass http://zslab_chat:3001/;` **직접 연결** (Caddy 우회)
  - 주의: 변수 없이 URI 직접 지정 → Nginx가 `/chat/` prefix 자동 제거 (`/socket.io/?...` 전달)
  - `proxy_set_header Connection $connection_upgrade` (map 활용, 동적 전환)
  - `proxy_read_timeout 3600s`, `proxy_buffering off`

**검증:**
- polling: `https://zslab-shop.duckdns.org/chat/socket.io/?EIO=4&transport=polling` → 200 ✓
- WebSocket 전체 경로 (TLS → Nginx → zslab_chat:3001): `101 Switching Protocols` ✓
- Nginx access log: `101 0` 확인 ✓
- 사이트 HTTP 200 ✓

**프록시 체인 (변경 전 → 변경 후):**
```
변경 전: Browser → Nginx → Caddy → zslab-chat  (2-hop, WebSocket 실패)
변경 후: Browser → Nginx → zslab-chat           (1-hop, WebSocket 성공)
         (일반 HTTP API/Frontend → Nginx → Caddy 유지)
```

**브라우저 새로고침 필요**: 재연결 시 `connected = true`, 입력창 활성화됨

## 미구현 기능 현황 (2026-04-25 코드 기반 점검)

### 1. 멤버십 등급제 (Newbie/Silver/Gold/VIP)
| 항목 | 상태 | 비고 |
|------|------|------|
| DB grade 컬럼 | ❌ 미구현 | users 테이블 migration에 grade/points 컬럼 없음 |
| 등급 산정 로직 | ❌ 미구현 | 12개월 구매액 기반 산정 로직 없음 |
| 적립금 % 적립 | ❌ 미구현 | OrderService에 포인트 적립 처리 없음 |
| 마이페이지 등급 UI | ❌ 미구현 | /my 페이지에 등급 표시 없음 |

### 2. 쿠폰 발행/관리 (STEP 55에서 완성)
| 항목 | 상태 | 비고 |
|------|------|------|
| DB 쿠폰 스키마 | ✅ 구현됨 | coupons + coupon_usages 테이블 존재 (fixed/percent 타입) |
| 쿠폰 적용/차감 API | ✅ 구현됨 | OrderService.applyCoupon() 완성, 사용이력/used_count 처리 |
| 체크아웃 쿠폰 입력창 | ✅ 구현됨 | validate API 호출, 할인 금액 표시, 최종금액 반영 |
| 관리자 쿠폰 관리 UI | ✅ 구현됨 | /zslab-manage/coupons CRUD 완성 |

### 3. ELK 통계 대시보드
| 항목 | 상태 | 비고 |
|------|------|------|
| Elasticsearch | ✅ 구현됨 | docker-compose에 존재 (검색 전용 용도) |
| Logstash / Kibana | ❌ 미구현 | docker-compose에 서비스 없음 |
| Filebeat 로그 수집 | ❌ 미구현 | 설정 파일 없음 |
| 관리자 통계 페이지 | ❌ 미구현 | 대시보드는 KPI 카드 하드코딩만 (DB 집계 없음) |

### 4. 셀러 패널
| 항목 | 상태 | 비고 |
|------|------|------|
| ShopMode 미들웨어 | ✅ 구현됨 | marketplace 모드 아닌 경우 404 반환 로직 존재 |
| 셀러 라우트 활성화 | ❌ 미구현 | api.php에 `// Route::apiResource('/sellers', ...)` 주석 처리 |
| 셀러 입점/승인 API | ❌ 미구현 | SellerController 없음 |
| 셀러 패널 UI | ❌ 미구현 | /seller/* 페이지 없음 |

## 완료된 작업 (쿠폰 기능 완성)

### STEP 55: 쿠폰 기능 완성 (2026-04-25)

**백엔드:**
- `CouponUsage` 모델 신규 생성
- `OrderService.php`: `applyCoupon()` 메서드 추가
  - 쿠폰 코드 유효성 검증 (존재/만료/사용횟수/시작일)
  - 동일 쿠폰 중복 사용 방지 (coupon_usages 조회)
  - 최소 주문금액 체크
  - 정액(fixed) / 정률(percent, max_discount_amount 상한) 할인 계산
  - 주문 생성 후 coupon_usages 이력 저장 + used_count 증가
- `OrderService.updateStatus()` 취소 시 쿠폰 복원
  - coupon_usages 이력 삭제 + used_count 차감
- `CouponController.php` 신규: `POST /api/coupons/validate`
- `Admin\CouponController.php` 신규: CRUD (index/create/store/edit/update/destroy)
- `routes/api.php`: POST /api/coupons/validate (auth:sanctum 필요)
- `routes/admin.php`: /zslab-manage/coupons CRUD 6개 라우트

**프론트엔드:**
- `api.ts`: `couponApi.validate()` 추가
- `checkout/page.tsx`: 쿠폰 적용 버튼 실제 동작 연결
  - 적용 버튼 → validate API 호출 → 성공 시 녹색 배너 표시
  - 할인 금액 표시, 총 결제금액에서 차감
  - 실패 시 에러 메시지, 적용 취소 기능

**관리자 패널:**
- `admin/coupons/index.blade.php`: 목록 테이블 (코드/할인/유효기간/사용현황/상태)
- `admin/coupons/create.blade.php` + `edit.blade.php`
- `admin/coupons/_form.blade.php`: JS 연동 (정률↔정액 단위 자동 전환)
- `layouts/app.blade.php`: 쿠폰 관리 사이드바 메뉴 추가

**테스트 쿠폰 Seeder:**
- WELCOME10: 신규가입 10% (최대 5,000원, 10,000원 이상)
- SAVE5000: 5,000원 정액 할인 (30,000원 이상, 100회 한도)
- SUMMER20: 여름 시즌 20% (최대 20,000원, 50,000원 이상, 50회)
- VIP3000: VIP 전용 3,000원 (제한 없음)

**검증:**
- POST /api/coupons/validate WELCOME10(50000원) → `{"valid":true,"discount_amount":5000,"final_amount":45000}` ✓
- SAVE5000 최소주문 미달 → `{"message":"30,000원 이상 주문 시 사용 가능한 쿠폰입니다."}` ✓
- 존재하지 않는 코드 → `{"message":"존재하지 않는 쿠폰 코드입니다."}` ✓
- /zslab-manage/coupons → HTTP 200 ✓
- https://zslab-shop.duckdns.org/ → HTTP 200 ✓

## 완료된 작업 (쿠폰 UI 확장)

### STEP 56: 쿠폰 정보 표시 확장 (2026-04-25)
- **관리자 주문 상세** (`/zslab-manage/orders/{id}`): 결제금액 4단계 흐름 (상품금액 → 쿠폰코드+할인 → 최종금액 → 실결제금액), 배송지 키 불일치 버그 수정 (name→recipient, zip→postal_code, address1/2→address+detail)
- **마이페이지 주문 내역** (`/my?tab=orders`): 쿠폰 사용 시 상품금액 + 쿠폰코드 배지 + 할인금액 표시, 미사용 시 숨김
- **주문 완료 페이지** (`/order/complete`): 할인 라벨을 "쿠폰 할인"으로 변경, 쿠폰 코드 배지 추가
- `Order` 타입에 `coupon_code: string | null` 필드 추가

## 완료된 작업 (ELK 통계 대시보드)

### STEP 57: ELK 통계 대시보드 구성 (2026-04-25)

**인프라 (docker-compose.yml):**
- `zslab_logstash`: Logstash 8.13.0, 256m heap, Beats → Grok → ES 파이프라인
- `zslab_kibana`: Kibana 8.13.0, `/kibana/` basePath 설정, 내부망 전용
- `zslab_filebeat`: Filebeat 8.13.0, Laravel 로그 수집 (`backend/storage/logs/`)
  - `--strict.perms=false` 플래그로 파일 소유자 검사 우회

**설정 파일:**
- `docker/logstash/config/logstash.yml`: xpack.monitoring 비활성화
- `docker/logstash/pipeline/main.conf`: Laravel 로그 Grok 파싱, ES 출력 (인덱스: `zslab-logs-laravel-YYYY.MM.dd`)
- `docker/kibana/kibana.yml`: basePath=/kibana, rewriteBasePath=true, security 비활성화
- `docker/filebeat/filebeat.yml`: multiline 설정 (Laravel 멀티라인 스택트레이스 지원)

**Caddy:** `/kibana/*` → `kibana:5601` 프록시 추가

**관리자 통계 페이지 (`/zslab-manage/stats`):**
- `Admin\StatsController.php`: DB 쿼리 4종 (일별매출/주문상태/인기상품/시간대별)
- `admin/stats/index.blade.php`: Chart.js 4.4.0 CDN
  - 최근 30일 매출 추이 (Line, 매출+주문수 이중축)
  - 주문 상태별 비율 (Doughnut)
  - 시간대별 주문 분포 (Bar)
  - 인기 상품 TOP 10 수평 막대 (수량+매출 이중)
  - ELK 상태 테이블 + Kibana 링크
- 사이드바 통계 메뉴 추가

**검증:**
- zslab_logstash: Up ✓
- zslab_kibana: Up ✓
- zslab_filebeat: Up ✓
- StatsController 렌더링 테스트 ✓
- https://zslab-shop.duckdns.org/ → HTTP 200 ✓

## 완료된 작업 (멤버십 등급제 + 맞춤 상품 추천)

### STEP 58: 멤버십 등급제 + 맞춤 상품 추천 (2026-04-25)

**DB 마이그레이션 (4개):**
- `2026_04_25_000010_add_membership_fields_to_users`: grade/points/gender/birth_year 추가
- `2026_04_25_000011_create_point_histories_table`: 적립금 이력 테이블
- `2026_04_25_000012_create_membership_configs_table`: 등급 기준/적립률 DB 관리 (newbie 0% / silver 1.5% / gold 2% / vip 3%)
- `2026_04_25_000013_add_points_to_orders`: orders에 used_points/earned_points 컬럼

**모델:**
- `MembershipConfig`: 등급 설정 모델
- `PointHistory`: 적립금 이력 모델
- `User`: grade/points/gender/birth_year 필드 추가, pointHistories 관계 추가

**서비스 (`MembershipService`):**
- `recalculateGrade()`: 최근 12개월 delivered 주문액 기준, membership_configs 참조 자동 등급 산정
- `earnPoints()`: delivered 전환 시 등급별 적립금 지급 + PointHistory 기록
- `usePoints()`: 주문 생성 시 적립금 차감
- `refundPoints()`: 주문 취소 시 적립금 환원

**`OrderService` 업데이트:**
- `create()`: `use_points` 파라미터 추가 → 쿠폰 차감 후 최종금액에서 적립금 추가 차감
- `updateStatus('delivered')`: `earnPoints()` + `recalculateGrade()` 자동 트리거
- `updateStatus('cancelled')`: `refundPoints()` 자동 트리거

**신규 API:**
- `GET /api/my/points`: 등급 정보 + 다음 등급까지 남은 금액 + 적립금 잔액 + 이력 50건
- `GET /api/recommendations`: 로그인 시 개인화(카테고리+협업필터링+세그먼트), 비로그인 시 인기 상품
- `POST /api/orders`: `use_points` 파라미터 추가
- `POST /api/auth/register`: gender/birth_year 파라미터 추가 + welcome_coupon 반환

**관리자 패널:**
- `Admin\MembershipController`: 등급별 기준 금액/적립률 수정 (GET/PUT)
- `Admin\MemberController`: `adjustPoints()` 수동 적립금 조정 (POST)
- `/zslab-manage/membership`: 멤버십 설정 페이지 (4개 등급 카드 + 등급 흐름 다이어그램)
- `/zslab-manage/members`: 등급/적립금 표시 + 수동 조정 모달
- 사이드바: 커머스 관리 그룹에 멤버십 설정 추가

**프론트엔드:**
- `/register`: 성별/출생연도 선택 필드 추가, 웰컴 쿠폰(WELCOME10) 팝업 표시
- `/my?tab=membership`: 등급 카드 (적립률/다음 등급 프로그레스바) + 적립금 이력 탭
- `/checkout`: 적립금 사용 입력창 + 전액 사용 버튼 + 결제요약 적립금 차감 표시
- 홈 페이지: `<RecommendationSection>` 추가 (로그인 시 "맞춤 추천", 비로그인 시 "지금 인기 있는 상품")

**검증:**
- `POST /api/auth/register` (gender/birth_year) → grade:newbie + welcome_coupon 반환 ✓
- `GET /api/my/points` → grade_info + remaining_amount 정상 반환 ✓
- `GET /api/recommendations` (로그인) → type:personalized, 12개 상품 ✓
- `GET /api/recommendations` (비로그인) → type:popular, 12개 상품 ✓
- `POST /api/orders` (use_points:0) → status:paid ✓
- DB: membership_configs 4개 등급 seeder 완료 ✓
- 프론트 빌드 성공 ✓ / https://zslab-shop.duckdns.org/ → HTTP 200 ✓

**포트폴리오 하이라이트:**
- 멤버십 등급제 (Newbie/Silver/Gold/VIP) — DB 기반 등급 기준/적립률 동적 관리
- Elasticsearch 기반 구매 이력/협업 필터링/성별-나이 세그먼트 맞춤 상품 추천

## 완료된 작업 (README 전면 업데이트)

### README 업데이트 (2026-04-25)
- Tech Stack: Nginx 게이트웨이, ELK 스택, Framer Motion, Node.js 추가
- 주요 기능: 맞춤 추천/쿠폰/멤버십/적립금/채팅/ELK 실제 구현 내용 반영, "(예정)" 문구 전면 제거
- System Architecture: Nginx 마스터 게이트웨이 + Caddy 내부 구조, zslab-chat/ELK 추가, Docker 네트워크 테이블
- 페이지 구성: 고객 18페이지 + 관리자 12페이지 전체 목록
- API Overview: 50개 엔드포인트 전체 (쿠폰/적립금/추천/채팅 포함)
- Directory Structure: zslab-chat/, docker/elk, Nginx 게이트웨이 반영
- GitHub push 완료 (commit: docs: README 전면 업데이트)

## 버그 수정

### 관리자 패널 delivered 처리 시 적립금 미적립 버그 (2026-04-25)

**원인:**
- `Admin\OrderController::updateStatus()`가 `$order->update(['status' => ...])` 직접 호출
- `OrderService::updateStatus()`를 우회 → `MembershipService::earnPoints()`, `recalculateGrade()`, `delivered_at` 타임스탬프 모두 미처리

**수정:**
- `Admin\OrderController`에 `MembershipService` 주입
- `updateStatus()`: `shipped_at` / `delivered_at` 타임스탬프 자동 설정
- `delivered` 전환 시 `earnPoints()` + `recalculateGrade()` 호출 (DB transaction 내)

**해당 주문 보정 (ORD-20260424-UQZLZXMB, order_id=10):**
- `delivered_at` NULL → `now()` 수동 보정
- 사용자 grade = newbie (point_rate 0%) → 적립금 0P (설계 정상)
- Silver(1.5%) 등급 검증: 19,800 × 1.5% = 297P + point_history 1건 생성 확인

**추가 수정 - 고아 포인트 정리:**
- 검증 스크립트 롤백 버그: `$user->update(['points'=>0])` 모델 캐시로 DB 미반영 → 297P 잔존, 이력 0건 불일치
- `DB::table('users')->update(['points'=>0])` 로 직접 초기화, order earned_points 및 histories 정리
- 이후 관리자 패널 delivered 처리 → 이력 정상 생성 (type:earn, description:"[silver] 주문# 적립") 최종 확인

## 완료된 작업 (코드 리뷰 보안/성능 수정)

### STEP 59: 코드 리뷰 지적사항 수정 (2026-04-25)

**🔴 높음**
- Rate Limiting 추가: `POST /api/auth/login` (5회/분), `POST /api/auth/register` (10회/분), `POST /zslab-manage/login` (5회/분) — `throttle` 미들웨어 적용, 초과 시 429 반환 ✓
- 일반 유저 배송 상태 변경 차단: `OrderController::updateStatus()` — shipping/delivered는 admin/seller만 가능, 403 반환 ✓
- 정지 계정 로그인 차단: `AuthController::login()` + `Admin\AuthController::login()` — `is_active` 체크 후 403 반환 ✓
- CartService N+1 쿼리 수정: `CartService::all()` — 루프 내 개별 `Product::find()` → `Product::whereIn()->get()->keyBy()` 일괄 조회로 변경 ✓

**🟡 중간**
- payment_raw 노출 제거: `OrderController::show()` — `select()` 로 민감 필드 제외 ✓
- 정지 시 토큰 폐기: `MemberController::toggle()` — 계정 정지 시 `$user->tokens()->delete()` 호출 ✓
- JWT 중복 구현 통합: `JwtService` 신규 생성 (`app/Services/JwtService.php`) — `ChatController`, `InquiryController`에서 공통 서비스 사용 ✓
- 인덱스 추가: migration `2026_04_25_100001_add_missing_indexes` — orders.created_at, order_items.product_id, reviews.user_id, point_histories.user_id, chat_messages(room_id+sender_type+is_read) ✓
- adjustPoints() 에러 핸들링: `MemberController::adjustPoints()` — try-catch 추가, 포인트 부족 시 flash error 반환 ✓
- DB::raw 안전화: `OrderService::createWithOptimisticLock()` — `(int) $item['quantity']` 명시적 캐스팅 ✓

**검증:**
- Rate Limit: 로그인 6회 시도 → 5회 422, 6회째 429 ✓
- API health: {"status":"ok"} ✓
- Frontend: HTTP 200 ✓
- Migration: add_missing_indexes 완료 ✓

## 완료된 작업 (Dockerfile 수정)

### STEP 61: Dockerfile view:cache 오류 수정 (2026-04-25)
- `backend/Dockerfile:37`: `php artisan view:cache` 라인 제거
- 원인: API 모드로 `resources/views` 미사용 → "View path not found" 빌드 오류
- 커밋: `fix: Dockerfile view:cache 제거` → main push 완료 ✓

## 완료된 작업 (CI 수정)

### STEP 60: GitHub Actions CI 테스트 오류 수정 (2026-04-25)
- `.github/workflows/ci.yml`: `php artisan test --parallel` → `php artisan test`
- 원인: `brianium/paratest` 패키지 미설치로 CI 실패
- 커밋: `fix: CI --parallel 옵션 제거` → main push 완료 ✓

## 완료된 작업 (SSL 인증서 자동 갱신)

### STEP 62: SSL 인증서 자동 갱신 설정 (2026-04-25)

**구성 방식: certbot webroot + docker + crontab**

**발급 결과:**
- zslab.duckdns.org → 만료 2026-07-24 (VALID 89일) ✓
- zslab-shop.duckdns.org → 만료 2026-07-24 (VALID 89일) ✓
- zslab-stg.duckdns.org → 만료 2026-07-24 (VALID 89일) ✓

**변경 파일:**
- `/home/gateway/nginx/nginx.conf`: port 80에 `/.well-known/acme-challenge/` location 추가 (HTTP→HTTPS 리다이렉트 전 처리)
- `/home/gateway/docker-compose.yml`: `./webroot:/var/www/certbot:ro` 볼륨 마운트 추가
- `/home/gateway/webroot/`: certbot webroot 디렉토리 신규 생성
- `/etc/letsencrypt/`: certbot 인증서 저장소 (3개 도메인)
- `/etc/letsencrypt/renewal-hooks/deploy/update-nginx-certs.sh`: 갱신 후 자동 복사+Nginx 리로드 훅
- `/home/zslab/scripts/certbot-renew.sh`: 갱신 전체 스크립트

**crontab (root):**
```
0 3 1,15 * * /home/zslab/scripts/certbot-renew.sh >> /var/log/certbot-renew.log 2>&1
```
매월 1일, 15일 새벽 3시 실행 (만료 30일 이상 남으면 skip)

**dry-run 결과:**
```
Congratulations, all simulated renewals succeeded:
  /etc/letsencrypt/live/zslab-shop.duckdns.org/fullchain.pem (success)
  /etc/letsencrypt/live/zslab-stg.duckdns.org-0001/fullchain.pem (success)
  /etc/letsencrypt/live/zslab.duckdns.org/fullchain.pem (success)
0 renew failure(s)
```

**갱신 플로우:**
```
cron → certbot-renew.sh → certbot/certbot docker renew (webroot challenge)
→ /etc/letsencrypt/live/ 갱신 → /home/gateway/certs/ 복사
→ docker exec gateway_nginx nginx -s reload (무중단)
```


## 완료된 작업 (GlitchTip 에러 트래킹)

### STEP 64: GlitchTip 셀프호스팅 + 에러 트래킹 연동 (2026-04-25)

**인프라 (`/home/glitchtip/docker-compose.yml`):**
- `glitchtip_pg`: Postgres 14-alpine (전용 DB)
- `glitchtip_redis`: Redis 7-alpine (전용 캐시/큐)
- `glitchtip_web`: glitchtip/glitchtip:latest (granian WSGI, 8000)
  - 네트워크: glitchtip_net + gateway_net + zslab_zslab_net
  - ALLOWED_HOSTS=* (내부 호출용)
- `glitchtip_worker`: Celery 비동기 이벤트 처리
- `REDIS_URL` / `CELERY_BROKER_URL` = redis://glitchtip-redis:6379/0
- `GLITCHTIP_DOMAIN` = https://zslab-shop.duckdns.org/errors

**Nginx (`/home/gateway/nginx/nginx.conf`):**
- `location /errors/` → `proxy_pass http://glitchtip_web:8000/;` (prefix 제거, 리터럴 URL)
- `location /static/` → `proxy_pass http://glitchtip_web:8000/static/;` (SPA 정적파일)
- `location /media/` → `proxy_pass http://glitchtip_web:8000/media/;`
- 핵심: proxy_pass 변수 사용 시 prefix 제거 안됨 → 리터럴 URL 사용

**GlitchTip 조직/프로젝트/DSN:**
- 관리자: admin@zslab.com / [비밀번호는 변경 필요]
- 조직: zslab
- Backend 프로젝트 (ID:1): php-laravel
  - DSN: GlitchTip UI > Settings > Projects > php-laravel > Client Keys
- Frontend 프로젝트 (ID:2): javascript-nextjs
  - DSN: GlitchTip UI > Settings > Projects > javascript-nextjs > Client Keys

**Laravel (backend):**
- `sentry/sentry-laravel ^4.25` 설치
- `config/sentry.php` 신규 생성
- `bootstrap/providers.php`: `Sentry\Laravel\ServiceProvider::class` 추가
- `bootstrap/app.php`: `Sentry\captureException()` 예외 리포팅 추가
- `.env`: `SENTRY_LARAVEL_DSN=http://...@glitchtip_web:8000/1` (내부 URL)
- `php artisan sentry:test` → "Test event sent with ID: ..." ✓

**Next.js (frontend):**
- `@sentry/nextjs ^8` 설치 (`--legacy-peer-deps`, Next.js 16 호환)
- `src/instrumentation.ts`: 서버/엣지 런타임 Sentry 초기화 (`onRequestError` 핸들러)
- `src/components/SentryInit.tsx`: 클라이언트 사이드 동적 초기화 (use client)
- `.env.production`: `NEXT_PUBLIC_SENTRY_DSN` + `SENTRY_DSN` 추가
- `withSentryConfig` 미사용 (Next.js 16 미지원) → 수동 초기화 방식

**검증:**
- `POST /errors/api/1/store/` via Nginx → `{"event_id": "...", "task_id": null}` HTTP 200 ✓
- `php artisan sentry:test` → GlitchTip DB에 Issue 수집 확인 ✓
- GlitchTip 총 이슈: 3개 (LaravelException, TestException, 공식 테스트) ✓
- https://zslab-shop.duckdns.org/ → HTTP 200 ✓
- https://zslab-shop.duckdns.org/errors/ → HTTP 200 (GlitchTip UI) ✓

**접근 URL:**
- GlitchTip UI: `https://zslab-shop.duckdns.org/errors/`
- 로그인: admin@zslab.com / [비밀번호는 변경 필요]

---

## 다음 작업
- GitHub Secrets 등록: PROD_SSH_HOST/USER/KEY, STG_SSH_HOST/USER/KEY
- 소셜 로그인 (Google/Kakao) 실제 연동

### STEP 66: GlitchTip 완전 삭제 (2026-04-26)
- [x] STEP 66-1: GlitchTip docker compose down -v (컨테이너/볼륨/네트워크 전체 제거)
- [x] STEP 66-2: /home/glitchtip/ 디렉토리 삭제
- [x] STEP 66-3: Nginx /errors/, /static/, /media/, /_allauth/ location 블록 제거 + 컨테이너 재시작
- [x] STEP 66-4: Laravel .env SENTRY_LARAVEL_DSN 제거
- [x] STEP 66-5: Next.js .env.production SENTRY DSN 2개 제거, docker-compose.yml SENTRY 환경변수 제거
- [x] STEP 66-6: sentry.php / instrumentation.ts / SentryInit.tsx 삭제, providers.php / app.php / layout.tsx 정리
- [x] STEP 66-7: GitHub push

## 완료된 작업 (2026-04-26)

### STEP 65: GlitchTip 로그아웃 리다이렉트 조사
- [x] STEP 65-1: 로그아웃 리다이렉트 상태 점검 + Nginx 재로드 → 정상 작동 확인
  - Nginx sub_filter가 `window.location.href="/login"` → `window.location.href="/errors/login"` 치환 중 ✓
  - 서빙되는 JS 번들(`/static/main-5NRHEHB2.js`) 내 `/errors/login` 확인 ✓
  - base href=`/errors/` HTML 치환도 정상 ✓
  - JS `max-age=86400` 캐시 (upstream 설정) → 24h 이후 만료로 자연 해소
- [x] STEP 65-2: /_allauth/ Nginx 라우팅 추가 + 컨테이너 재시작
  - Angular SPA가 `/_allauth/*` 절대경로로 API 호출 → Nginx에 location 블록 없어서 Next.js로 라우팅(404)됨
  - `/home/gateway/nginx/nginx.conf`에 `location /_allauth/` → `glitchtip_web:8000` 추가
  - 컨테이너 재시작으로 새 config 반영
  - GET `/_allauth/browser/v1/auth/session` → 401 (GlitchTip 정상 응답) ✓
  - DELETE `/_allauth/browser/v1/auth/session` → 403 (CSRF 없음, 정상) ✓
  - https://zslab-shop.duckdns.org/ → 200 ✓
- [ ] STEP 65-3: /errors/login 브라우저 경고 원인 조사 — **보류**
  - 기술적 점검 결과: cert 유효(Let's Encrypt, 만료 2026-07-24), HTTP→HTTPS 301 ✓, mixed content 없음 ✓
  - 브라우저/환경별 문제로 추가 조사 필요 (보류)

---

## 진행 중인 작업 (2026-04-25)

> 📌 작업 방식 변경: 작업 시작 전 스텝 먼저 기록, 완료 시마다 즉시 [x] 업데이트

### STEP 63: 검색 자동완성
- [x] STEP 63-1: ES 인덱스에 search_as_you_type 필드 추가 + 재인덱싱
- [x] STEP 63-2: GET /api/search/suggest?q= 엔드포인트 추가
- [x] STEP 63-3: 프론트엔드 자동완성 UI (debounce + 드롭다운 + 키보드 이동)

### STEP 64: GlitchTip 셀프호스팅 + 에러 트래킹 연동
- [x] STEP 64-1: /home/glitchtip/ Docker Compose 설치 + Nginx 연결
- [x] STEP 64-2: 관리자 계정 + 프로젝트 2개 생성 (backend/frontend DSN 발급)
- [x] STEP 64-3: Laravel sentry/sentry-laravel 패키지 설치 + .env 설정
- [x] STEP 64-4: Next.js @sentry/nextjs 패키지 설치 + 설정 파일
- [x] STEP 64-5: 테스트 에러 발생 → GlitchTip 대시보드 수집 확인
- [x] STEP 64-6: GlitchTip UI 오류 수정 (CSP 인라인 스타일 차단, API 500 에러)
  - Nginx `proxy_hide_header Content-Security-Policy;` 추가 → UI 정상 렌더링
  - OrganizationUserRole role=30 → role=3 수정 → GET /errors/api/0/organizations/ 200
- [x] STEP 64-7: Next.js frontend DSN 최종 연동 (project=2)
  - `.env.production` DSN 업데이트 (hyphen 제거된 정확한 키)
  - `docker-compose.yml` frontend env에 `SENTRY_DSN` + `NEXT_PUBLIC_SENTRY_DSN` 추가 (런타임 주입)
  - 테스트 에러 전송 → GlitchTip project=2 이슈 1건 수집 확인 ✓

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
