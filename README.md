# zslab shop

풀스택 쇼핑몰 프로젝트

| 구분 | 기술 |
|------|------|
| 프론트엔드 | Next.js 15 (App Router) + TypeScript + TailwindCSS |
| 백엔드 | Laravel 13 (API 모드) + PHP 8.4 + FrankenPHP |
| DB | MariaDB 10.11 |
| 캐시/큐 | Redis 7.x |
| 검색 | Elasticsearch 8.x |
| 실시간 | Node.js 20 + Socket.io |
| 인프라 | Docker + Docker Compose |
| 리버스 프록시 | Caddy 2 (자동 HTTPS) |
| CI/CD | GitHub Actions |

## 도메인

| 환경 | 프론트 | API |
|------|--------|-----|
| 운영 | `zslab-shop.duckdns.org` | `api.zslab-shop.duckdns.org` |
| 스테이징 | `zslab-stg.duckdns.org` | `api.zslab-stg.duckdns.org` |

## 빠른 시작

```bash
# 1. 환경변수 설정
cp .env.example .env
# .env 파일을 열어 DB/Redis 비밀번호 등을 채우세요.

# 2. 운영 스택 기동
./scripts/up.sh

# 3. 마이그레이션
./scripts/migrate.sh

# 4. 헬스 체크
curl https://api.zslab-shop.duckdns.org/api/health
# → {"status":"ok","service":"zslab-api"}
```

## 주요 스크립트

| 스크립트 | 설명 |
|----------|------|
| `scripts/up.sh` | 운영 스택 빌드 & 기동 |
| `scripts/down.sh` | 전체 스택 중지 |
| `scripts/shell-api.sh` | Laravel 컨테이너 쉘 진입 |
| `scripts/shell-front.sh` | Next.js 컨테이너 쉘 진입 |
| `scripts/migrate.sh` | 마이그레이션 실행 (`--fresh` 옵션 지원) |

## 디렉토리 구조

```
/home/zslab/
├── frontend/          # Next.js 15 앱
├── backend/           # Laravel 13 API
├── socket/            # Socket.io 서버
├── docker/            # 서비스별 Docker 설정
│   ├── caddy/         # Caddyfile (리버스 프록시)
│   ├── frankenphp/    # PHP 설정
│   ├── mariadb/       # MariaDB 설정
│   ├── redis/
│   └── elasticsearch/
├── scripts/           # 운영 헬퍼 스크립트
├── .github/workflows/ # CI/CD 파이프라인
├── docker-compose.yml
├── docker-compose.stg.yml
└── .env.example
```

## CI/CD

- `develop` 브랜치 push → GitHub Actions → 스테이징 자동 배포
- `main` 브랜치 push → GitHub Actions → 운영 자동 배포

### 필요한 GitHub Secrets

```
PROD_SSH_HOST / PROD_SSH_USER / PROD_SSH_KEY
STG_SSH_HOST  / STG_SSH_USER  / STG_SSH_KEY
```

## 개발 환경 설정

```bash
# Laravel 로컬 개발
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate

# Next.js 로컬 개발
cd frontend
npm install
npm run dev

# Socket 로컬 개발
cd socket
npm install
npm run dev
```
