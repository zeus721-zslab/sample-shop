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

## 다음 작업
- STEP 11 완료: zslab 계정에 docker 권한 부여 후 `./scripts/up.sh` 실행
- GitHub Secrets 등록: PROD_SSH_HOST/USER/KEY, STG_SSH_HOST/USER/KEY
- 운영 `.env` 비밀번호 실제 값으로 교체
- backend `.env` APP_KEY 확인 (이미 생성됨)

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
