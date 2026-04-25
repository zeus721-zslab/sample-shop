# ZSLAB SHOP — 프로젝트 전체 정리

> 에디토리얼 큐레이션 기반 풀스택 커머스 플랫폼 + 범용 실시간 채팅 솔루션

---

## 1. 프로젝트 개요

| 항목 | 내용 |
|------|------|
| 서비스명 | ZSLAB SHOP |
| 컨셉 | 잡지풍 에디토리얼 큐레이션 커머스 (29CM 스타일) |
| 운영 URL | https://zslab-shop.duckdns.org |
| 스테이징 URL | https://zslab-stg.duckdns.org |
| 관리자 패널 | https://zslab-shop.duckdns.org/zslab-manage/ |
| GitHub | https://github.com/zeus721-zslab/sample-shop |
| 개발 기간 | 2026.04 |
| 개발 방식 | 1인 풀스택 (기획 → 인프라 → 백엔드 → 프론트엔드 → 운영) |

---

## 2. 기술 스택

### 프론트엔드
| 항목 | 기술 |
|------|------|
| 프레임워크 | Next.js 15 (App Router, standalone) |
| 언어 | TypeScript |
| 스타일링 | TailwindCSS v4 |
| 상태관리 | Zustand |
| 애니메이션 | Framer Motion |
| 실시간 | Socket.io Client |

### 백엔드
| 항목 | 기술 |
|------|------|
| 프레임워크 | Laravel 13 (API 모드) |
| 언어 | PHP 8.5 |
| 서버 | FrankenPHP (Worker 모드) |
| 인증 | Laravel Sanctum |

### 데이터 / 검색 / 실시간
| 항목 | 기술 | 용도 |
|------|------|------|
| MariaDB 10 | RDB | 메인 데이터베이스 |
| Redis 7 | 캐시 | 장바구니, 세션, pub/sub |
| Elasticsearch 8 | 검색엔진 | 상품 검색 + 로그 분석 |
| Socket.io (Node.js) | WebSocket | 실시간 1:1 채팅 |

### 인프라
| 항목 | 기술 |
|------|------|
| 컨테이너 | Docker + Docker Compose |
| 게이트웨이 | Nginx 1.27 (마스터 리버스프록시) |
| 내부 프록시 | Caddy 2 (path-based 라우팅) |
| CI/CD | GitHub Actions |
| SSL | Let's Encrypt (certbot, 자동갱신) |
| 모니터링 | ELK Stack (Logstash + Kibana + Filebeat) |

---

## 3. 시스템 아키텍처

```
인터넷 (HTTPS 443 / HTTP 80)
          │
          ▼
┌──────────────────────────────────────┐
│   Nginx 1.27 (Master Gateway)        │  TLS 종료 · 도메인별 라우팅
│   /home/gateway/                     │  (zslab-shop / zslab-stg / zslab)
└───────────┬──────────────────────────┘
            │  /chat/* → zslab_chat:3001  (WebSocket 직결, 1-hop)
            │  /* → zslab_caddy:8080
            ▼
┌──────────────────────────────────────┐
│   Caddy (Internal Reverse Proxy)     │  path-based 라우팅 (내부망)
│   :8080                              │
└────┬──────────────┬──────────────────┘
     │  /*          │  /api/* /zslab-manage/*
     ▼              ▼
┌─────────┐  ┌───────────────────────────────────┐
│ Next.js │  │  Laravel API (FrankenPHP)          │
│  :3000  │  │  Worker 모드                       │
└─────────┘  └──────┬────────────────────────────┘
                    │
       ┌────────────┼────────────┬────────────┐
       ▼            ▼            ▼            ▼
   MariaDB       Redis     Elasticsearch  Logstash
    :3306         :6379        :9200         :5044
                                │              │
                            Kibana         Filebeat
                             :5601    (Laravel 로그 수집)

zslab_chat (Node.js + Socket.io) :3001
  └── Redis Adapter (멀티 인스턴스 대비)
  └── MariaDB (chat_rooms, chat_participants, chat_messages)
```

### Docker 네트워크
| 네트워크 | 역할 |
|----------|------|
| `zslab_zslab_net` | 쇼핑몰 내부 서비스 통신 |
| `gateway_net` | Nginx 게이트웨이 ↔ 서비스 연결 |

---

## 4. 구현된 기능 전체 목록

### 4-1. 고객 사이트 (18페이지)

| 경로 | 설명 |
|------|------|
| `/` | 홈 — 에디토리얼 피드, 카테고리 퀵탭, 맞춤 추천, 신상품, 인기상품 |
| `/products` | 상품 목록 — 카테고리 사이드바, 정렬, 페이지네이션 |
| `/products/[slug]` | 상품 상세 — 이미지, 가격, 리뷰, 위시리스트 |
| `/category/[slug]` | 카테고리 — 브레드크럼, 서브카테고리 탭, 상품 그리드 |
| `/search` | 검색 결과 — Elasticsearch 연동, 자동완성, 정렬 |
| `/cart` | 장바구니 — 수량 조절, 삭제, 결제 요약 |
| `/checkout` | 결제 — 배송지 입력, 쿠폰 적용, 적립금 사용, 주문 생성 |
| `/order/complete` | 주문 완료 — 주문번호, 상품 목록, 배송지 확인 |
| `/my` | 마이페이지 — 프로필 / 주문 / 위시리스트 / 리뷰 / 멤버십/적립금 |
| `/login` | 로그인 |
| `/register` | 회원가입 (성별/출생연도 입력, 웰컴쿠폰 자동 발급) |
| `/notice` | 공지사항 목록 |
| `/notice/[id]` | 공지사항 상세 |
| `/faq` | FAQ — 카테고리 탭, 아코디언 |
| `/shipping` | 배송 정책 |
| `/about` | 브랜드 소개 |
| `/terms` | 이용약관 |
| `/privacy` | 개인정보처리방침 |

### 4-2. 관리자 패널 (12페이지) — `/zslab-manage/`

| 경로 | 설명 |
|------|------|
| `/dashboard` | 대시보드 — KPI 카드 |
| `/stats` | 통계 — 매출/주문/방문자 차트 (ELK 연동) |
| `/products` | 상품 관리 — CRUD |
| `/orders` | 주문 관리 — 목록, 상세, 상태 변경 |
| `/members` | 회원 관리 — 등급/적립금 표시, 수동 조정, 정지 처리 |
| `/categories` | 카테고리 관리 — 트리 구조 CRUD |
| `/notices` | 공지사항 관리 |
| `/faqs` | FAQ 관리 |
| `/coupons` | 쿠폰 관리 — 발행/수정/삭제 |
| `/membership` | 멤버십 설정 — 등급 기준/적립률 동적 관리 |
| `/chat` | 1:1 문의 — 채팅 목록, 관리자 답변 |

### 4-3. 관리자 사이드바 그룹 구조
```
대시보드
  - 대시보드
  - 통계

커머스 관리
  - 상품 관리
  - 주문 관리
  - 쿠폰 관리
  - 멤버십 설정
  - 카테고리 관리

고객 관리
  - 회원 관리
  - 1:1 문의

콘텐츠 관리
  - 공지사항 관리
  - FAQ 관리
```

---

## 5. API 전체 목록 (50개 엔드포인트)

```
Health      GET  /api/health

Auth        POST /api/auth/register          (gender, birth_year 포함)
            POST /api/auth/login
            POST /api/auth/logout
            GET  /api/auth/me
            GET  /api/auth/social/{provider}/redirect  (stub)
            GET  /api/auth/social/{provider}/callback  (stub)

Category    GET  /api/categories
            GET  /api/categories/{slug}

Product     GET  /api/products
            GET  /api/products/{slug}

Search      GET  /api/search?q=
            GET  /api/search/suggest?q=      (자동완성, 최대 5개)

Cart        GET    /api/cart
            POST   /api/cart
            PATCH  /api/cart/{itemId}
            DELETE /api/cart/{itemId}
            DELETE /api/cart

Order       POST   /api/orders               (use_points 포함)
            GET    /api/orders/{id}
            PATCH  /api/orders/{id}/status
            POST   /api/orders/{id}/cancel

Wishlist    GET    /api/wishlist
            POST   /api/wishlist/{productId}
            DELETE /api/wishlist/{productId}
            GET    /api/wishlist/check/{productId}

Review      GET    /api/products/{id}/reviews
            POST   /api/products/{id}/reviews
            DELETE /api/reviews/{id}

Coupon      POST   /api/coupons/validate

My          GET    /api/my/profile
            PATCH  /api/my/profile
            GET    /api/my/orders
            GET    /api/my/reviews
            GET    /api/my/wishlist
            GET    /api/my/points            (적립금 잔액 + 이력)

Recommend   GET    /api/recommendations      (로그인: 개인화 / 비로그인: 인기)

Notice      GET    /api/notices
            GET    /api/notices/{id}

FAQ         GET    /api/faqs

Chat        POST   /api/chat/token
            POST   /api/chat/rooms
            GET    /api/chat/rooms/{id}/messages
            GET    /api/chat/unread

Admin Stats GET    /zslab-manage/api/stats/summary
            GET    /zslab-manage/api/stats/sales
            GET    /zslab-manage/api/stats/orders-by-hour
            GET    /zslab-manage/api/stats/top-products
```

---

## 6. 핵심 기술 구현 상세

### 6-1. 상품 검색 (Elasticsearch)
- `search_as_you_type` 필드 기반 자동완성 (300ms debounce)
- ngram 분석기 (min:2, max:10) 기반 부분 문자열 매칭
- multi_match: name^3 / category^2 / description^1 가중치
- fuzziness AUTO (오타 허용)
- ES 다운 시 MariaDB LIKE 쿼리 자동 fallback

### 6-2. 장바구니 (Redis)
- 세션 기반 Redis 장바구니
- 수량/삭제/금액 실시간 반영
- N+1 쿼리 → whereIn+keyBy 일괄 조회로 개선

### 6-3. 재고 Race Condition 방지
- `stock_version` 낙관적 락(Optimistic Lock)
- 최대 3회 지수 백오프 재시도 로직

### 6-4. 쿠폰 시스템
- 정액(fixed) / 정률(percent) 두 가지 타입
- 최소 주문금액, 최대 할인금액, 사용횟수 제한
- 주문 취소 시 coupon_usages 이력 삭제 + used_count 자동 복원
- 테스트 쿠폰: WELCOME10 / SAVE5000 / SUMMER20 / VIP3000

### 6-5. 멤버십 등급제
- 4단계: Newbie → Silver → Gold → VIP
- 등급 기준/적립률 DB(membership_configs) 관리 — 하드코딩 없음
- 관리자 패널에서 기준 금액/적립률 실시간 변경 가능

| 등급 | 최근 12개월 구매액 | 적립률 |
|------|-------------------|--------|
| Newbie | 신규 가입 | 0% |
| Silver | 30만원 이상 | 1.5% |
| Gold | 100만원 이상 | 2% |
| VIP | 300만원 이상 | 3% |

- 주문 확정(delivered) 시 자동 적립금 지급 + 등급 재산정
- 주문 취소 시 적립금 자동 환원
- 회원가입 시 Newbie 쿠폰(WELCOME10) 자동 발급

### 6-6. 맞춤 상품 추천 (Elasticsearch)
- 구매 이력 기반: 구매한 카테고리 중심 연관 상품 추출
- 협업 필터링: 같은 상품 구매 유저들이 함께 구매한 상품
- 성별/나이 세그먼트: gender + birth_year 기반 동일 세그먼트 인기 상품
- 비로그인 시 인기 상품 fallback

### 6-7. 실시간 1:1 채팅 (zslab-chat)
- Socket.io + Redis Adapter (멀티 인스턴스 대비)
- JWT 인증 (userType: admin/user 권한 분리)
- 이벤트: join_room / send_message / mark_read / typing_start / typing_stop
- Nginx → zslab_chat 1-hop 직접 연결 (WebSocket 2-hop 프록시 문제 해결)
- 사용자: 우측 하단 고정 위젯 + 슬라이드업 채팅창 (Framer Motion)

### 6-8. ELK 통계 대시보드
- Filebeat → Laravel 로그 수집 → Logstash → Elasticsearch
- 관리자 통계 페이지: 매출 추이(30일), 주문 상태 비율, 시간대별 주문 분포, KPI 카드
- Elasticsearch 단일 인스턴스에서 검색용 인덱스(products)와 로그용 인덱스(logs-*) 분리

### 6-9. 보안
- Rate Limiting: 로그인 5회/분, 회원가입 10회/분 (throttle 미들웨어)
- 정지 계정 로그인 차단 + 정지 시 Sanctum 토큰 전체 폐기
- 일반 유저 배송 상태 변경 차단 (admin/seller만 가능)
- payment_raw 민감 데이터 API 응답 제외
- JWT 생성 로직 JwtService로 통합 (중복 제거)
- DB::raw 직접 변수 삽입 → 바인딩 파라미터 방식으로 변경
- 누락 인덱스 5개 추가

### 6-10. CI/CD
- GitHub Actions 3개 워크플로우
  - ci.yml: PR 시 테스트 실행
  - deploy-staging.yml: develop → 스테이징 자동 배포
  - deploy-production.yml: main → 프로덕션 자동 배포
- Docker 이미지 빌드 (frontend / backend / socket 병렬)
- SSH 배포 (PROD_SSH_HOST / USER / KEY GitHub Secrets 등록)

### 6-11. SSL 자동 갱신
- certbot webroot 방식으로 3개 도메인 관리
  - zslab-shop.duckdns.org
  - zslab-stg.duckdns.org
  - zslab.duckdns.org
- crontab 매월 1·15일 03:00 자동 갱신
- 갱신 후 /home/gateway/certs/ 복사 → nginx -s reload 무중단 적용

---

## 7. zslab-chat — 범용 실시간 채팅 솔루션

### 개요
쇼핑몰과 완전히 분리된 독립 백엔드 솔루션. GitHub clone으로 어느 웹서비스에나 연동 가능.

- GitHub: https://github.com/zeus721-zslab/zslab-chat
- 순수 백엔드 서버 (프론트엔드 없음)

### 기술 스택
| 항목 | 기술 |
|------|------|
| 런타임 | Node.js 20 |
| 프레임워크 | Express |
| 실시간 | Socket.io |
| 어댑터 | Redis (REDIS_URL 설정 시 자동 활성화) |
| 데이터베이스 | MySQL / MariaDB |
| 인증 | JWT (HS256) |

### 제공 API
| 엔드포인트 | 설명 |
|-----------|------|
| `GET /health` | 헬스체크 |
| `POST /api/token` | 개발용 JWT 발급 |

### Socket.io 이벤트
| 이벤트 | 방향 | 설명 |
|--------|------|------|
| `join_room` | Client→Server | 채팅방 입장 |
| `send_message` | Client→Server | 메시지 전송 |
| `mark_read` | Client→Server | 읽음 처리 |
| `typing_start` | Client→Server | 타이핑 시작 |
| `typing_stop` | Client→Server | 타이핑 종료 (5초 자동 해제) |

### 권한 정책
- `userType: user` — 자신이 참여한 방만 접근
- `userType: admin` — 전체 방 접근, 최초 입장 시 자동 등록

### 쇼핑몰 연동 구조
```
사용자 브라우저
  │  Socket.io (JWT)
  ▼
Nginx (WebSocket 직결, 1-hop)
  │
  ▼
zslab_chat :3001
  │
  ├── Redis Adapter
  └── MariaDB (chat_rooms, chat_participants, chat_messages)
```

### Nginx WebSocket 설정 (필수)
```nginx
location /chat/ {
    proxy_pass http://zslab_chat:3001/;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection $connection_upgrade;
}
```

> ⚠️ 2-hop 프록시(Nginx → Caddy → zslab-chat) 구조에서 WebSocket 헤더 손실 문제 발생.
> 반드시 Nginx에서 zslab-chat으로 직접 1-hop 연결 필요.

> ⚠️ Next.js standalone 빌드에서 NEXT_PUBLIC_* 환경변수는 빌드 타임에 embed됨.
> 런타임 환경변수(docker-compose.yml)로는 반영 안 됨. frontend/.env.production에 직접 기재 후 재빌드 필요.

---

## 8. 운영 정보

### 서버 디렉토리 구조
```
/home/
├── gateway/        # Nginx 마스터 게이트웨이 (포트 80/443)
│   └── nginx/nginx.conf
├── zslab/          # 쇼핑몰 솔루션
│   ├── frontend/   # Next.js 15
│   ├── backend/    # Laravel 13
│   ├── zslab-chat/ # 채팅 솔루션 (GitHub clone)
│   ├── docker/     # Caddy, MariaDB, ELK 설정
│   └── scripts/    # 운영 헬퍼 스크립트
├── zslab-chat/     # 채팅 솔루션 독립 레포 (참고용)
└── portfolio/      # 포트폴리오 사이트
```

### 헬퍼 스크립트
| 스크립트 | 설명 |
|----------|------|
| `scripts/up.sh` | 스택 빌드 + 기동 |
| `scripts/down.sh` | 전체 스택 중지 |
| `scripts/shell-api.sh` | Laravel 컨테이너 쉘 진입 |
| `scripts/shell-front.sh` | Next.js 컨테이너 쉘 진입 |
| `scripts/migrate.sh` | 마이그레이션 실행 |
| `scripts/restart-api.sh` | FrankenPHP Worker 재시작 + 캐시 클리어 |
| `scripts/certbot-renew.sh` | SSL 인증서 갱신 스크립트 |

### 읽기 전용 데모 계정
| 구분 | 이메일 | 비고 |
|------|--------|------|
| 사용자 | demo@zslab.com | 조회 전용 |
| 관리자 | admin@zslab.com | 관리자 패널 |

---

## 9. 미완료 / 향후 과제

| 항목 | 상태 | 비고 |
|------|------|------|
| 소셜 로그인 (Google/Kakao) | 미완료 | 라우트 stub만 구현 |
| 셀러 패널 | 미완료 | ShopMode 미들웨어만 구현 |
| gateway 레포 분리 | 예정 | zeus721-zslab/gateway 레포 생성 |
| 한국어 자모 분리 자동완성 | 미완료 | `멀ㅌ` → `멀티비타민` 매칭 |
| 이메일 알림 | 미완료 | 주문 확인, 배송 상태 변경 알림 |
| 상품 이미지 직접 업로드 | 미완료 | 현재 URL 입력 방식 |

---

## 10. 주요 트러블슈팅 기록

| 문제 | 원인 | 해결 |
|------|------|------|
| WebSocket 연결 실패 | Nginx→Caddy→zslab-chat 2-hop에서 Upgrade 헤더 손실 | Nginx→zslab-chat 1-hop 직결로 변경 |
| 채팅 소켓 미연결 | Next.js standalone에서 NEXT_PUBLIC_CHAT_URL 빌드 타임 embed 누락 | frontend/.env.production에 직접 기재 후 재빌드 |
| CI 테스트 실패 | brianium/paratest 미설치로 --parallel 옵션 오류 | ci.yml에서 --parallel 옵션 제거 |
| Dockerfile 빌드 실패 | API 모드에서 view:cache 실행 시 뷰 경로 없음 | Dockerfile에서 php artisan view:cache 제거 |
| 적립금 미적립 | Admin OrderController가 OrderService 우회하여 직접 update() 호출 | Admin OrderController → OrderService.updateStatus() 호출로 수정 |
| N+1 쿼리 (CartService) | 아이템 루프 내 개별 상품 조회 | whereIn+keyBy 일괄 조회로 개선 |
| 관리자 쿠폰 메뉴 미노출 | 브라우저 캐시 | 강제 새로고침으로 해결 |

---

*문서 작성: 2026-04-26*
