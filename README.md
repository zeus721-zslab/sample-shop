# zslab shop

**에디토리얼 큐레이션 기반 종합 커머스 플랫폼**

[![Live Demo](https://img.shields.io/badge/Live%20Demo-zslab--shop.duckdns.org-black?style=for-the-badge)](https://zslab-shop.duckdns.org)

---

## Tech Stack

**Frontend**
![Next.js](https://img.shields.io/badge/Next.js_15-black?style=flat-square&logo=next.js)
![TypeScript](https://img.shields.io/badge/TypeScript-3178C6?style=flat-square&logo=typescript&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)

**Backend**
![Laravel](https://img.shields.io/badge/Laravel_13-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP_8.5-777BB4?style=flat-square&logo=php&logoColor=white)
![FrankenPHP](https://img.shields.io/badge/FrankenPHP-6C41A1?style=flat-square)

**Database / Cache / Search**
![MariaDB](https://img.shields.io/badge/MariaDB-003545?style=flat-square&logo=mariadb&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-DC382D?style=flat-square&logo=redis&logoColor=white)
![Elasticsearch](https://img.shields.io/badge/Elasticsearch_8-005571?style=flat-square&logo=elasticsearch&logoColor=white)

**Infra**
![Docker](https://img.shields.io/badge/Docker-2496ED?style=flat-square&logo=docker&logoColor=white)
![GitHub Actions](https://img.shields.io/badge/GitHub_Actions-2088FF?style=flat-square&logo=github-actions&logoColor=white)
![Let's Encrypt](https://img.shields.io/badge/Let's_Encrypt-003A70?style=flat-square&logo=letsencrypt&logoColor=white)

---

## 주요 기능

| 기능 | 설명 |
|------|------|
| 에디토리얼 홈 피드 | 비대칭 그리드 + 텍스트 오버레이 카드 중심의 잡지풍 레이아웃 |
| 상품 검색 | Elasticsearch ngram 분석기 기반 한국어 부분 문자열 매칭, DB fallback |
| 실시간 장바구니 | Redis 기반 세션 장바구니, 수량·삭제·금액 실시간 반영 |
| 재고 Race Condition 방지 | `stock_version` 낙관적 락 + 3회 백오프 재시도 |
| 멤버십 등급제 | 등급별 할인율 + 적립금 시스템 |
| 실시간 문의 채팅 | Socket.io + Redis pub/sub 기반 1:1 채팅 (예정) |
| SHOP_MODE 전환 | 환경변수 한 줄로 단독몰 ↔ 마켓플레이스 전환 |
| 자동 HTTPS | Caddy + Let's Encrypt 자동 인증서 발급·갱신 |

---

## System Architecture

```
인터넷 (443/80)
     │
     ▼
┌─────────────────────────────┐
│  FrankenPHP (포트폴리오 서버) │  ← TLS 종료 + 도메인 분기
│  portfolio_portfolio_net    │
└────────────┬────────────────┘
             │ proxy  zslab_caddy:8080
             ▼
┌─────────────────────────────┐
│       zslab Caddy           │  ← 내부 HTTP 8080, path-based 라우팅
│       zslab_zslab_net       │
└────┬───────────────┬────────┘
     │ /*            │ /api/*
     ▼               ▼
┌─────────┐   ┌──────────────┐
│ Next.js │   │ Laravel API  │  (FrankenPHP Worker 모드)
│  :3000  │   │   :8000      │
└─────────┘   └──────┬───────┘
                     │
          ┌──────────┼──────────┐
          ▼          ▼          ▼
      MariaDB      Redis   Elasticsearch
       :3306       :6379      :9200

                Socket.io :3001
                (Redis pub/sub 브릿지)
```

**네트워크 구조**
- `zslab_zslab_net` — zslab 서비스 내부 통신
- `portfolio_portfolio_net` — FrankenPHP ↔ zslab Caddy 연결 (external)

---

## 화면 구성

### 고객 사이트 (완성)

| 경로 | 페이지 |
|------|--------|
| `/` | 홈 — 에디토리얼 피드, 카테고리 퀵탭, 신상품·인기상품 |
| `/products` | 상품 목록 — 카테고리 사이드바, 정렬, 페이지네이션 |
| `/products/[slug]` | 상품 상세 — 이미지, 가격, 리뷰, 위시리스트 |
| `/category/[slug]` | 카테고리 — 브레드크럼, 서브카테고리 탭, 상품 그리드 |
| `/search` | 검색 결과 — Elasticsearch, 정렬, empty state |
| `/cart` | 장바구니 — 수량 조절, 삭제, 결제 요약 |
| `/checkout` | 결제 — 배송지 입력, 쿠폰, 주문 생성 |
| `/order/complete` | 주문 완료 — 주문번호, 상품 목록, 배송지 확인 |
| `/login` | 로그인 |
| `/register` | 회원가입 |
| `/my` | 마이페이지 — 프로필 / 주문 내역 / 위시리스트 / 내 리뷰 |

### 관리자·셀러 패널 (예정)

---

## Quick Start

**필요 환경:** Docker 24+, Docker Compose v2

```bash
# 1. 환경변수 설정
cp .env.example .env
# .env 파일에서 DB/Redis 비밀번호, APP_KEY, 도메인 등 수정

# 2. 스택 기동
./scripts/up.sh

# 3. DB 마이그레이션
./scripts/migrate.sh

# 4. 상품 Elasticsearch 인덱싱
./scripts/shell-api.sh
php artisan products:index

# 5. 헬스 체크
curl https://zslab-shop.duckdns.org/api/health
# → {"status":"ok","service":"zslab-api"}
```

### 헬퍼 스크립트

| 스크립트 | 설명 |
|----------|------|
| `scripts/up.sh` | 스택 빌드 + 기동 |
| `scripts/down.sh` | 전체 스택 중지 |
| `scripts/shell-api.sh` | Laravel 컨테이너 쉘 |
| `scripts/shell-front.sh` | Next.js 컨테이너 쉘 |
| `scripts/migrate.sh` | 마이그레이션 (`--fresh` 옵션 지원) |
| `scripts/restart-api.sh` | FrankenPHP Worker 재시작 + 캐시 클리어 |

---

## Directory Structure

```
/home/zslab/
├── frontend/                  # Next.js 15 (App Router)
│   └── src/
│       ├── app/               # 페이지 라우트
│       ├── components/        # 공통 컴포넌트
│       ├── hooks/             # 커스텀 훅
│       ├── lib/               # API 클라이언트, 유틸
│       ├── store/             # Zustand 상태 관리
│       └── types/             # 타입 정의
├── backend/                   # Laravel 13 API
│   ├── app/
│   │   ├── Console/Commands/  # Artisan 커맨드
│   │   ├── Contracts/         # 인터페이스 (결제 게이트웨이 등)
│   │   ├── Http/Controllers/  # API 컨트롤러
│   │   ├── Models/            # Eloquent 모델
│   │   └── Services/          # 비즈니스 로직
│   ├── database/migrations/
│   └── routes/api.php
├── socket/                    # Socket.io 서버 (Node.js 20)
├── docker/
│   ├── caddy/                 # Caddyfile (내부 라우팅)
│   ├── frankenphp/            # PHP 설정
│   └── mariadb/               # MariaDB 설정
├── scripts/                   # 운영 헬퍼
├── .github/workflows/         # CI/CD (PR 테스트 + 자동 배포)
├── docker-compose.yml         # 운영 스택
├── docker-compose.stg.yml     # 스테이징 스택
└── .env.example
```

---

## API Overview (35 endpoints)

```
Health      GET  /api/health
Auth        POST /api/auth/register|login|logout  GET /api/auth/me
Category    GET  /api/categories  /api/categories/{slug}
Product     GET  /api/products  /api/products/{slug}
Search      GET  /api/search?q=
Cart        GET|POST|PATCH|DELETE  /api/cart
Order       POST /api/orders  GET /api/orders/{id}  PATCH /api/orders/{id}/status
            POST /api/orders/{id}/cancel
Wishlist    GET|POST|DELETE  /api/wishlist
Review      GET|POST|DELETE  /api/products/{id}/reviews
My          GET|PATCH  /api/my/profile  GET /api/my/orders|reviews|wishlist
```

---

## CI/CD

- `develop` 브랜치 push → 스테이징 자동 배포
- `main` 브랜치 push → 운영 자동 배포

**필요한 GitHub Secrets**

```
PROD_SSH_HOST / PROD_SSH_USER / PROD_SSH_KEY
STG_SSH_HOST  / STG_SSH_USER  / STG_SSH_KEY
```

---

## License & Author

MIT License

**zeus721-zslab** — [zslab-shop.duckdns.org](https://zslab-shop.duckdns.org)
