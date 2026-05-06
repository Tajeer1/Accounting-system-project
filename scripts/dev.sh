#!/usr/bin/env bash
# سكربت تشغيل التطوير لمشروع Event Plus
# يفحص MySQL والـmigrations قبل تشغيل السيرفر

set -e

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

cd "$(dirname "$0")/.."

log()    { echo -e "${BLUE}▸${NC} $1"; }
ok()     { echo -e "${GREEN}✓${NC} $1"; }
warn()   { echo -e "${YELLOW}!${NC} $1"; }
err()    { echo -e "${RED}✗${NC} $1"; }

# 1) فحص .env
if [ ! -f .env ]; then
    err "ملف .env غير موجود"
    log "إنشاء .env من .env.example..."
    cp .env.example .env
    php artisan key:generate
    ok "تم إنشاء .env"
fi

# 2) فحص vendor/
if [ ! -d vendor ]; then
    log "vendor/ غير موجود — تشغيل composer install..."
    composer install
fi

# 3) فحص node_modules/
if [ ! -d node_modules ]; then
    log "node_modules/ غير موجود — تشغيل npm install..."
    npm install
fi

# 4) فحص اتصال MySQL
log "فحص اتصال قاعدة البيانات..."
if ! php artisan migrate:status >/dev/null 2>&1; then
    err "MySQL غير متاح أو الـmigrations لم تنفذ"
    echo ""
    warn "افتح XAMPP Control Panel ثم اضغط Start بجانب MySQL"
    warn "أو شغّل MySQL من سطر الأوامر:"
    echo "    sudo /Applications/XAMPP/xamppfiles/xampp startmysql"
    echo ""
    read -p "بعد تشغيل MySQL، اضغط Enter للمتابعة (أو Ctrl+C للخروج)..."

    if ! php artisan migrate:status >/dev/null 2>&1; then
        err "ما زال الاتصال فاشل. تحقق من إعدادات DB_* في .env"
        exit 1
    fi
fi
ok "اتصال قاعدة البيانات يعمل"

# 5) تشغيل migrations لو فيه جديد
PENDING=$(php artisan migrate:status 2>/dev/null | grep -c "Pending" || true)
if [ "$PENDING" -gt 0 ]; then
    log "يوجد $PENDING migration معلّق — تشغيل php artisan migrate..."
    php artisan migrate --force
    ok "تم تنفيذ الـmigrations"
else
    ok "كل الـmigrations مطبّقة"
fi

# 6) تشغيل الخدمات بشكل متوازي
echo ""
ok "كل شيء جاهز. تشغيل خدمات التطوير..."
echo -e "${BLUE}─────────────────────────────────────${NC}"
echo -e "  ${GREEN}server${NC}  http://localhost:8000"
echo -e "  ${GREEN}queue${NC}   php artisan queue:listen"
echo -e "  ${GREEN}logs${NC}    php artisan pail"
echo -e "  ${GREEN}vite${NC}    npm run dev"
echo -e "${BLUE}─────────────────────────────────────${NC}"
echo ""

exec npx concurrently \
    -c "#93c5fd,#c4b5fd,#fb7185,#fdba74" \
    "php artisan serve" \
    "php artisan queue:listen --tries=1 --timeout=0" \
    "php artisan pail --timeout=0" \
    "npm run dev" \
    --names=server,queue,logs,vite \
    --kill-others
