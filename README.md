# zslab shop

**에디토리얼 큐레이션 기반 종합 커머스 플랫폼**

[![Live Demo](https://img.shields.io/badge/Live%20Demo-zslab--shop.duckdns.org-000?style=for-the-badge&logo=vercel)](https://zslab-shop.duckdns.org)
[![GitHub](https://img.shields.io/badge/GitHub-zeus721--zslab%2Fzslab--shop-181717?style=for-the-badge&logo=github)](https://github.com/zeus721-zslab/zslab-shop)

---

## Table of Contents

- [Tech Stack](#tech-stack)
- [주요 기능](#주요-기능)
- [System Architecture](#system-architecture)
- [페이지 구성](#페이지-구성)
- [API Overview](#api-overview)
- [Quick Start](#quick-start)
- [Directory Structure](#directory-structure)
- [CI/CD](#cicd)
- [License](#license)

---

## Tech Stack

**Frontend**

![Next.js](https://img.shields.io/badge/Next.js_15-000?style=flat-square&logo=next.js&logoColor=white)
![TypeScript](https://img.shields.io/badge/TypeScript-3178C6?style=flat-square&logo=typescript&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS_v4-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)
![Zustand](https://img.shields.io/badge/Zustand-443E38?style=flat-square)

**Backend**

![Laravel](https://img.shields.io/badge/Laravel_13-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP_8.5-777BB4?style=flat-square&logo=php&logoColor=white)
![FrankenPHP](https://img.shields.io/badge/FrankenPHP-6C41A1?style=flat-square)

**Data / Search / Realtime**

![MariaDB](https://img.shields.io/badge/MariaDB_10-003545?style=flat-square&logo=mariadb&logoColor=white)
![Redis](https://img.shields.io/badge/Redis_7-DC382D?style=flat-square&logo=redis&logoColor=white)
![Elasticsearch](https://img.shields.io/badge/Elasticsearch_8-005571?style=flat-square&logo=elasticsearch&logoColor=white)
![Socket.io](https://img.shields.io/badge/Socket.io-010101?style=flat-square&logo=socket.io&logoColor=white)

**Infra / CI**

![Docker](https://img.shields.io/badge/Docker-2496ED?style=flat-square&logo=docker&logoColor=white)
![Caddy](https://img.shields.io/badge/Caddy_2-1F88C0?style=flat-square&logo=caddy&logoColor=white)
![GitHub Actions](https://img.shields.io/badge/GitHub_Actions-2088FF?style=flat-square&logo=github-actions&logoColor=white)
![Let's Encrypt](https://img.shields.io/badge/Let's_Encrypt-003A70?style=flat-square&logo=letsencrypt&logoColor=white)

---

## 주요 기능

| 기능 | 설명 |
|------|------|
| **에디토리얼 홈 피드** | 비대칭 12컬럼 그리드 + 텍스트 오버레이 카드 중심의 잡지풍 레이아웃 |
| **한국어 상품 검색** | Elasticsearch ngram 분석기(min:2, max:10) 기반 부분 문자열 매칭, ES 다운 시 DB LIKE fallback |
| **Redis 장바구니** | 세션 기반 Redis 장바구니, 수량·삭제·금액 실시간 반영 |
| **재고 Race Condition 방지** | `stock_version` 낙관적 락 + 최대 3회 지수 백오프 재시도 |
| **멤버십 등급제** | 등급별 할인율 + 적립금 시스템 |
| **실시간 1:1 문의 채팅** | Socket.io + Redis pub/sub 브릿지 (구현 예정) |
| **SHOP_MODE 전환** | 환경변수 한 줄(`single` \| `marketplace`)로 운영 모드 전환 |
| **자동 HTTPS** | Caddy + Let's Encrypt TLS 자동 발급·갱신 |

---

## System Architecture

```
인터넷 (HTTPS 443 / HTTP 80)
          │
          ▼
┌──────────────────────────────────┐
│          Caddy (Reverse Proxy)   │  TLS 종료 · path-based 라우팅
│          Let's Encrypt 자동 HTTPS │
└────────┬─────────────┬───────────┘
         │  /*         │  /api/*
         ▼             ▼
   ┌──────────┐  ┌───────────────────────────┐
   │ Next.js  │  │  Laravel API              │
   │  :3000   │  │  (FrankenPHP Worker 모드)  │
   └──────────┘  └──────────┬────────────────┘
                            │
              ┌─────────────┼─────────────┐
              ▼             ▼             ▼
          MariaDB         Redis     Elasticsearch
           :3306          :6379        :9200

                    Socket.io  :3001
                  (Redis pub/sub 브릿지)
```

**Docker 네트워크**

| 네트워크 | 역할 |
|----------|------|
| `zslab_zslab_net` | 서비스 내부 통신 (Caddy ↔ Next.js ↔ Laravel ↔ DB) |

---

## 페이지 구성

### 고객 사이트 (11페이지 완성)

| 경로 | 설명 |
|------|------|
| `/` | 홈 — 에디토리얼 피드 · 카테고리 퀵탭 · 신상품 · 인기상품 |
| `/products` | 상품 목록 — 카테고리 사이드바 · 정렬 · 페이지네이션 |
| `/products/[slug]` | 상품 상세 — 이미지 · 가격 · 리뷰 · 위시리스트 |
| `/category/[slug]` | 카테고리 — 브레드크럼 · 서브카테고리 탭 · 상품 그리드 |
| `/search` | 검색 결과 — Elasticsearch · 정렬 · empty state |
| `/cart` | 장바구니 — 수량 조절 · 삭제 · 결제 요약 |
| `/checkout` | 결제 — 배송지 입력 · 쿠폰 · 주문 생성 |
| `/order/complete` | 주문 완료 — 주문번호 · 상품 목록 · 배송지 확인 |
| `/login` | 로그인 |
| `/register` | 회원가입 |
| `/my` | 마이페이지 — 프로필 / 주문 내역 / 위시리스트 / 내 리뷰 |

### 관리자 · 셀러 패널 (예정)

---

## API Overview

35개 엔드포인트, 모두 `/api/*` 경로, JSON 응답, Sanctum 토큰 인증

```
Health    GET  /api/health

Auth      POST /api/auth/register
          POST /api/auth/login
          POST /api/auth/logout
          GET  /api/auth/me

Category  GET  /api/categories
          GET  /api/categories/{slug}

Product   GET  /api/products
          GET  /api/products/{slug}

Search    GET  /api/search?q=

Cart      GET    /api/cart
          POST   /api/cart
          PATCH  /api/cart/{itemId}
          DELETE /api/cart/{itemId}
          DELETE /api/cart

Order     POST   /api/orders
          GET    /api/orders/{id}
          PATCH  /api/orders/{id}/status
          POST   /api/orders/{id}/cancel

Wishlist  GET    /api/wishlist
          POST   /api/wishlist/{productId}
          DELETE /api/wishlist/{productId}
          GET    /api/wishlist/check/{productId}

Review    GET    /api/products/{id}/reviews
          POST   /api/products/{id}/reviews
          DELETE /api/reviews/{id}

My        GET    /api/my/profile
          PATCH  /api/my/profile
          GET    /api/my/orders
          GET    /api/my/reviews
          GET    /api/my/wishlist
```

---

## Quick Start

**필요 환경:** Docker 24+, Docker Compose v2

```bash
# 1. 환경변수 설정
cp .env.example .env
# APP_KEY, DB/Redis 비밀번호, 도메인 등 수정

# 2. 스택 빌드 & 기동
./scripts/up.sh

# 3. DB 마이그레이션 + 시더
./scripts/migrate.sh

# 4. Elasticsearch 상품 인덱싱
./scripts/shell-api.sh
# 컨테이너 쉘 진입 후:
php artisan products:index

# 5. 헬스 체크
curl https://zslab-shop.duckdns.org/api/health
# → {"status":"ok","service":"zslab-api"}
```

**헬퍼 스크립트**

| 스크립트 | 설명 |
|----------|------|
| `scripts/up.sh` | 스택 빌드 + 기동 |
| `scripts/down.sh` | 전체 스택 중지 |
| `scripts/shell-api.sh` | Laravel 컨테이너 쉘 진입 |
| `scripts/shell-front.sh` | Next.js 컨테이너 쉘 진입 |
| `scripts/migrate.sh` | 마이그레이션 실행 (`--fresh` 옵션 지원) |
| `scripts/restart-api.sh` | FrankenPHP Worker 재시작 + 캐시 클리어 |

---

## Directory Structure

```
/home/zslab/
├── frontend/                    # Next.js 15 (App Router, standalone)
│   └── src/
│       ├── app/                 # 페이지 라우트 (11개)
│       ├── components/          # 공통 컴포넌트
│       ├── hooks/               # 커스텀 훅
│       ├── lib/                 # API 클라이언트, 유틸
│       ├── store/               # Zustand 전역 상태
│       └── types/               # TypeScript 타입 정의
│
├── backend/                     # Laravel 13 (API 모드)
│   └── app/
│       ├── Console/Commands/    # Artisan 커맨드 (products:index 등)
│       ├── Contracts/           # 인터페이스 (PaymentGateway 등)
│       ├── Http/Controllers/    # 8개 API 컨트롤러
│       ├── Models/              # Eloquent 모델
│       └── Services/            # 비즈니스 로직 (Cart, Order, Search 등)
│
├── socket/                      # Socket.io 서버 (Node.js 20)
│
├── docker/
│   ├── caddy/                   # Caddyfile (path-based 라우팅)
│   ├── frankenphp/              # PHP 설정, Worker 모드
│   └── mariadb/                 # MariaDB 설정
│
├── scripts/                     # 운영 헬퍼 스크립트
├── .github/workflows/           # CI/CD 파이프라인
├── docker-compose.yml           # 운영 스택
├── docker-compose.stg.yml       # 스테이징 스택
└── .env.example                 # 환경변수 템플릿
```

---

## CI/CD

| 브랜치 | 트리거 | 대상 |
|--------|--------|------|
| `main` | push | 운영 서버 자동 배포 |
| `develop` | push | 스테이징 서버 자동 배포 |
| 모든 PR | open / sync | 린트 + 테스트 실행 |

**GitHub Secrets 필요 항목**

```
PROD_SSH_HOST / PROD_SSH_USER / PROD_SSH_KEY
STG_SSH_HOST  / STG_SSH_USER  / STG_SSH_KEY
```

---

## License

MIT License © [zeus721-zslab](https://github.com/zeus721-zslab)
