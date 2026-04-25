#!/bin/bash
# /home/zslab/scripts/certbot-renew.sh
# 매월 1일 새벽 3시 실행 — certbot 갱신 + Nginx 인증서 자동 업데이트

set -e
LOG="/var/log/certbot-renew.log"

echo "[$(date)] certbot 갱신 시작" >> "$LOG"

# certbot renew (webroot, 30일 이상 남으면 skip)
docker run --rm \
  -v /etc/letsencrypt:/etc/letsencrypt \
  -v /home/gateway/webroot:/var/www/certbot \
  certbot/certbot renew \
  --webroot \
  -w /var/www/certbot \
  --non-interactive \
  --quiet \
  2>> "$LOG"

# 갱신된 인증서 복사 (/home/gateway/certs/)
CERTS_DIR="/home/gateway/certs"
LE_DIR="/etc/letsencrypt/live"

docker run --rm \
  -v /etc/letsencrypt:/etc/letsencrypt \
  -v "$CERTS_DIR":/certs \
  alpine sh -c "
for pair in 'zslab.duckdns.org:zslab.duckdns.org' 'zslab-shop.duckdns.org:zslab-shop.duckdns.org' 'zslab-stg.duckdns.org-0001:zslab-stg.duckdns.org'; do
  src=\${pair%%:*}; dst=\${pair##*:}
  if [ -d /etc/letsencrypt/live/\$src ]; then
    cp -L /etc/letsencrypt/live/\$src/fullchain.pem /certs/\$dst/fullchain.crt
    cp -L /etc/letsencrypt/live/\$src/privkey.pem   /certs/\$dst/privkey.key
    chmod 600 /certs/\$dst/privkey.key
    echo \"[\$(date)] \$dst 인증서 복사 완료\"
  fi
done
" >> "$LOG" 2>&1

# Nginx 무중단 리로드
docker exec gateway_nginx nginx -s reload >> "$LOG" 2>&1

echo "[$(date)] certbot 갱신 완료" >> "$LOG"
