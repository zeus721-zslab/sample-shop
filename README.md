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
![Framer Motion](https://img.shields.io/badge/Framer_Motion-0055FF?style=flat-square&logo=framer&logoColor=white)

**Backend**

![Laravel](https://img.shields.io/badge/Laravel_13-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP_8.5-777BB4?style=flat-square&logo=php&logoColor=white)
![FrankenPHP](https://img.shields.io/badge/FrankenPHP-6C41A1?style=flat-square)
![Node.js](https://img.shields.io/badge/Node.js_20-339933?style=flat-square&logo=node.js&logoColor=white)

**Data / Search / Realtime**

![MariaDB](https://img.shields.io/badge/MariaDB_10-003545?style=flat-square&logo=mariadb&logoColor=white)
![Redis](https://img.shields.io/badge/Redis_7-DC382D?style=flat-square&logo=redis&logoColor=white)
![Elasticsearch](https://img.shields.io/badge/Elasticsearch_8-005571?style=flat-square&logo=elasticsearch&logoColor=white)
![Logstash](https://img.shields.io/badge/Logstash_8-005571?style=flat-square&logo=logstash&logoColor=white)
![Kibana](https://img.shields.io/badge/Kibana_8-005571?style=flat-square&logo=kibana&logoColor=white)
![Socket.io](https://img.shields.io/badge/Socket.io-010101?style=flat-square&logo=socket.io&logoColor=white)

**Infra / CI**

![Docker](https://img.shields.io/badge/Docker-2496ED?style=flat-square&logo=docker&logoColor=white)
![Nginx](https://img.shields.io/badge/Nginx_1.27-009639?style=flat-square&logo=nginx&logoColor=white)
![Caddy](https://img.shields.io/badge/Caddy_2-1F88C0?style=flat-square&logo=caddy&logoColor=white)
![GitHub Actions](https://img.shields.io/badge/GitHub_Actions-2088FF?style=flat-square&logo=github-actions&logoColor=white)
![Let's Encrypt](https://img.shields.io/badge/Let's_Encrypt-003A70?style=flat-square&logo=letsencrypt&logoColor=white)

---

## 주요 기능

| 기능 | 설명 |
|------|------|
| **에디토리얼 홈 피드** | 비대칭 12컬럼 그리드 + 텍스트 오버레이 카드 중심의 잡지풍 레이아웃, Framer Motion 스크롤 애니메이션 |
| **한국어 상품 검색** | Elasticsearch ngram 분석기(min:2, max:10) 기반 부분 문자열 매칭, ES 다운 시 DB LIKE fallback |
| **맞춤 상품 추천** | 구매 이력 기반 카테고리 추천 + 협업 필터링 + 성별·나이 세그먼트 추천, 비로그인 시 인기 상품 fallback |
| **Redis 장바구니** | 세션 기반 Redis 장바구니, 수량·삭제·금액 실시간 반영 |
| **재고 Race Condition 방지** | `stock_version` 낙관적 락 + 최대 3회 지수 백오프 재시도 |
| **쿠폰 발행·적용·복원** | 정액/정률 할인, 최소 주문금액, 최대 할인 상한, 중복 방지, 주문 취소 시 자동 복원 |
| **멤버십 등급제** | Newbie / Silver / Gold / VIP — DB 기반 등급 기준·적립률 동적 관리, 12개월 구매액 자동 재산정 |
| **적립금 시스템** | 배송 완료 시 등급별 자동 적립, 주문 시 차감, 취소 시 환원, 이력 관리 |
| **실시간 1:1 문의 채팅** | Socket.io + Redis pub/sub 브릿지, JWT 인증, 관리자 실시간 응답 패널 |
| **ELK 통계 대시보드** | Filebeat → Logstash → Elasticsearch 로그 파이프라인, Kibana 인덱스 패턴, 관리자 Chart.js 통계 |
| **SHOP_MODE 전환** | 환경변수 한 줄(`single` \| `marketplace`)로 운영 모드 전환 |
| **자동 HTTPS** | Nginx 게이트웨이 TLS 종료 + Let's Encrypt 인증서 |

---

## System Architecture

```
인터넷 (HTTPS 443 / HTTP 80)
          │
          ▼
┌──────────────────────────────────────┐
│   Nginx 1.27 (Master Gateway)        │  TLS 종료 · 도메인별 라우팅
│   /home/gateway/                     │  (zslab-shop / zslab / zslab-stg)
└───────────┬──────────────────────────┘
            │  /chat/* → zslab_chat:3001  (WebSocket 직결)
            │  /* → zslab_caddy:8080
            ▼
┌──────────────────────────────────────┐
│   Caddy (Internal Reverse Proxy)     │  path-based 라우팅 (내부망)
│   :8080                              │  /api/* /zslab-manage/* /kibana/*
└────┬──────────────┬──────────────────┘
     │  /*          │  /api/*  /zslab-manage/*
     ▼              ▼
┌─────────┐  ┌───────────────────────────────────┐
│ Next.js │  │  Laravel API                      │
│  :3000  │  │  (FrankenPHP Worker 모드)          │
└─────────┘  └──────┬────────────────────────────┘
                    │
       ┌────────────┼────────────┬────────────┐
       ▼            ▼            ▼            ▼
   MariaDB       Redis     Elasticsearch  Logstash
    :3306         :6379        :9200         :5044
                                │              │
                            Kibana         Filebeat
                             :5601    (Laravel 로그 수집)

zslab-chat (Socket.io)  :3001
  ├── Redis pub/sub (다중 인스턴스 대비)
  └── MariaDB (chat_rooms / chat_messages)
```

**Docker 네트워크**

| 네트워크 | 위치 | 역할 |
|----------|------|------|
| `gateway_net` | `/home/gateway/` | Nginx ↔ Caddy ↔ zslab-chat 외부 연결 |
| `zslab_net` | `/home/zslab/` | 서비스 내부 통신 (Caddy ↔ Next.js ↔ Laravel ↔ DB ↔ ELK) |

---

## 페이지 구성

### 고객 사이트 (18페이지)

| 경로 | 설명 |
|------|------|
| `/` | 홈 — 에디토리얼 피드 · 카테고리 퀵탭 · 신상품 · 인기상품 · 맞춤 추천 |
| `/products` | 상품 목록 — 카테고리 사이드바 · 정렬 · 페이지네이션 |
| `/products/[slug]` | 상품 상세 — 이미지 · 가격 · 리뷰 · 위시리스트 |
| `/category/[slug]` | 카테고리 — 브레드크럼 · 서브카테고리 탭 · 상품 그리드 |
| `/search` | 검색 결과 — Elasticsearch · 정렬 · empty state |
| `/cart` | 장바구니 — 수량 조절 · 삭제 · 결제 요약 |
| `/checkout` | 결제 — 배송지 입력 · 쿠폰 · 적립금 사용 · 주문 생성 |
| `/order/complete` | 주문 완료 — 주문번호 · 상품 목록 · 배송지 확인 |
| `/login` | 로그인 |
| `/register` | 회원가입 — 성별·출생연도 입력 · 웰컴 쿠폰 팝업 |
| `/my` | 마이페이지 — 프로필 / 주문 내역 / 위시리스트 / 내 리뷰 / 등급·적립금 |
| `/notice` | 공지사항 목록 — 핀고정 강조 · 카테고리 뱃지 |
| `/notice/[id]` | 공지사항 상세 — 잡지 아티클 레이아웃 |
| `/faq` | FAQ — 카테고리 탭 · Framer Motion 아코디언 |
| `/shipping` | 배송 정책 — 카드 그리드 · 스텝 다이어그램 |
| `/about` | 브랜드 소개 — 히어로 · 카운트업 애니메이션 |
| `/terms` | 이용약관 |
| `/privacy` | 개인정보처리방침 |

### 관리자 패널 (`/zslab-manage/`)

| 경로 | 설명 |
|------|------|
| `/zslab-manage/login` | 관리자 로그인 (admin / demo 계정) |
| `/zslab-manage/dashboard` | KPI 카드 + 최근 주문·회원 요약 |
| `/zslab-manage/stats` | 통계 — Chart.js 매출 추이·주문 상태·시간대·TOP10 상품 + ELK 상태 |
| `/zslab-manage/products` | 상품 관리 — 목록 · 등록 · 수정 · 삭제 |
| `/zslab-manage/orders` | 주문 관리 — 목록 · 상세 · 상태 변경 |
| `/zslab-manage/members` | 회원 관리 — 등급·적립금 표시 · 수동 조정 · 활성/정지 |
| `/zslab-manage/membership` | 멤버십 설정 — 등급별 기준 금액·적립률 수정 |
| `/zslab-manage/categories` | 카테고리 관리 — 대/소분류 · 인라인 수정 모달 |
| `/zslab-manage/coupons` | 쿠폰 관리 — 정액/정률 · 유효기간 · 사용 현황 |
| `/zslab-manage/notices` | 공지사항 관리 — CRUD · 핀고정 |
| `/zslab-manage/faqs` | FAQ 관리 — CRUD · 카테고리 · 정렬 |
| `/zslab-manage/inquiries` | 1:1 문의 — 실시간 채팅 관리 패널 |

> **데모 계정**: `demo@zslab.com / demo1234!` — GET 전용 읽기 모드

---

## API Overview

50개 엔드포인트, 모두 `/api/*` 경로, JSON 응답, Sanctum 토큰 인증

```
Health        GET    /api/health

Auth          POST   /api/auth/register          (gender, birth_year 포함, welcome_coupon 반환)
              POST   /api/auth/login
              POST   /api/auth/logout
              GET    /api/auth/me
              GET    /api/auth/social/{provider}/redirect
              GET    /api/auth/social/{provider}/callback

Category      GET    /api/categories
              GET    /api/categories/{slug}

Product       GET    /api/products
              GET    /api/products/{slug}

Search        GET    /api/search?q=

Recommend     GET    /api/recommendations         (로그인 시 개인화, 비로그인 시 인기 상품)

Notice        GET    /api/notices
              GET    /api/notices/{id}

FAQ           GET    /api/faqs

Cart          GET    /api/cart
              POST   /api/cart
              PATCH  /api/cart/{itemId}
              DELETE /api/cart/{itemId}
              DELETE /api/cart

Order         POST   /api/orders                  (use_points 파라미터 지원)
              GET    /api/orders
              GET    /api/orders/{id}
              POST   /api/orders/{id}/confirm
              POST   /api/orders/{id}/cancel
              PATCH  /api/orders/{id}/status

Coupon        POST   /api/coupons/validate

Wishlist      GET    /api/wishlist
              POST   /api/wishlist/{productId}
              DELETE /api/wishlist/{productId}
              GET    /api/wishlist/check/{productId}

Review        GET    /api/products/{id}/reviews
              POST   /api/products/{id}/reviews
              DELETE /api/reviews/{id}

My            GET    /api/my/profile
              PATCH  /api/my/profile
              GET    /api/my/orders
              GET    /api/my/reviews
              GET    /api/my/wishlist
              GET    /api/my/points               (등급 정보 + 적립금 잔액 + 이력)

Chat          POST   /api/chat/token
              POST   /api/chat/rooms
              GET    /api/chat/rooms/{id}/messages
              GET    /api/chat/unread
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
docker compose exec api php artisan products:index

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
/home/gateway/                       # Nginx 마스터 게이트웨이
├── docker-compose.yml               # Nginx 1.27 컨테이너
├── nginx/nginx.conf                 # TLS 종료 + 도메인 라우팅
└── certs/                           # Let's Encrypt 인증서

/home/zslab/
├── frontend/                        # Next.js 15 (App Router, standalone)
│   └── src/
│       ├── app/                     # 페이지 라우트 (18개)
│       ├── components/              # 공통 컴포넌트 (ProductCard, ChatWidget, RecommendationSection 등)
│       ├── components/motion/       # Framer Motion 래퍼 (FadeIn, ScrollReveal, StaggerList, PageTransition)
│       ├── hooks/                   # 커스텀 훅 (useChat 등)
│       ├── lib/                     # API 클라이언트 (api.ts), 유틸
│       ├── store/                   # Zustand 전역 상태 (auth)
│       └── types/                   # TypeScript 타입 정의
│
├── backend/                         # Laravel 13 (API + Admin 모드)
│   └── app/
│       ├── Console/Commands/        # Artisan 커맨드 (products:index)
│       ├── Contracts/               # 인터페이스 (PaymentGatewayInterface)
│       ├── Http/Controllers/        # API 컨트롤러 (14개)
│       ├── Http/Controllers/Admin/  # 관리자 컨트롤러 (12개)
│       ├── Http/Middleware/         # AdminAuth, DemoGuard, ShopMode
│       ├── Models/                  # Eloquent 모델 (15개)
│       └── Services/                # 비즈니스 로직 (Cart, Order, Membership, Search, Review)
│
├── zslab-chat/                      # Socket.io 채팅 서버 (Node.js 20)
│   └── server/
│       ├── handlers/                # chat, room, typing 핸들러
│       ├── middleware/              # JWT 인증
│       └── models/                  # Message, Room
│
├── docker/
│   ├── caddy/                       # Caddyfile (내부 path-based 라우팅)
│   ├── elasticsearch/               # ES 설정
│   ├── filebeat/                    # filebeat.yml (Laravel 로그 수집, multiline)
│   ├── frankenphp/                  # PHP 설정, Worker 모드
│   ├── kibana/                      # kibana.yml (basePath=/kibana)
│   ├── logstash/                    # Grok 파이프라인 (Laravel → ES 인덱스)
│   ├── mariadb/                     # MariaDB 설정
│   └── redis/                       # Redis 설정
│
├── scripts/                         # 운영 헬퍼 스크립트
├── .github/workflows/               # CI/CD 파이프라인 (ci / deploy-staging / deploy-production)
├── docker-compose.yml               # 운영 스택 (18개 서비스)
├── docker-compose.stg.yml           # 스테이징 스택
└── .env.example                     # 환경변수 템플릿
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
# deploy test
