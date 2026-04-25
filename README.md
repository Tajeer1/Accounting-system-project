# Event Plus — نظام محاسبي متكامل

نظام إداري محاسبي داخلي لشركة Event Plus (معارض/فعاليات) يتكوّن من:

- **الويب (Laravel 13):** لوحة تحكم ويب + REST API — في جذر المشروع.
- **الموبايل (React Native + Expo):** تطبيق جوال متصل بالـ API — في [mobile/](mobile/).

الاثنان يتشاركان **نفس قاعدة البيانات** و**نفس منطق العمل** عبر `App\Services\AccountingService`.

## الهيكلة العامة

```
Accounting system project/
├── app/                    # كود Laravel (Models, Controllers, Services)
│   ├── Http/Controllers/
│   │   └── Api/            # API controllers للموبايل (Sanctum-protected)
│   ├── Models/             # Eloquent models
│   ├── Services/           # AccountingService — منطق القيود المركزي
│   └── Support/helpers.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── database.sqlite
├── resources/views/        # Blade templates (الويب)
├── routes/
│   ├── web.php            # الراوتس للويب
│   └── api.php            # الراوتس للموبايل
└── mobile/                # تطبيق React Native Expo
    ├── app/               # شاشات expo-router
    └── src/               # مكونات + API client + theme
```

## التشغيل

### 1) تشغيل الـ Backend (Laravel)

```bash
# من جذر المشروع
php artisan serve --host=0.0.0.0 --port=8000
```

افتح `http://127.0.0.1:8000` للوحة التحكم الويب.

### 2) تشغيل تطبيق الموبايل

```bash
cd mobile
npm start
```

ثم امسح QR code بتطبيق **Expo Go** على جوالك (تأكّد أن الجوال والكمبيوتر على نفس شبكة Wi-Fi).

⚠️ قبل التشغيل لأول مرة، عدّل `extra.apiUrl` في [mobile/app.json](mobile/app.json) ليشير لعنوان IP جهازك:

```json
"extra": { "apiUrl": "http://192.168.1.10:8000/api" }
```

## تسجيل الدخول الافتراضي

```
admin@eventplus.com / password
```

## الوحدات (Modules)

| الوحدة | الويب | الموبايل | API |
|---|---|---|---|
| لوحة التحكم | ✅ | ✅ | ✅ |
| المشتريات | CRUD كامل | قائمة/تفاصيل/إضافة/حذف | ✅ |
| الفواتير | CRUD كامل | قائمة/تفاصيل/إضافة/تحديد كمدفوعة | ✅ |
| الحسابات البنكية | CRUD + تحويلات | عرض | ✅ |
| القيود اليومية | CRUD مع توازن | (مولّدة تلقائيًا) | — |
| شجرة الحسابات | CRUD شجرة | عرض | ✅ |
| المشاريع | CRUD + ربحية | عرض مع تقدم وإحصاءات | ✅ |
| الإعدادات | تبويبات كاملة | عرض | — |

## الربط المحاسبي التلقائي

أي مشتريات أو فاتورة تُنشئ **قيدًا محاسبيًا** تلقائيًا عبر `AccountingService` — سواء من الويب أو الموبايل:

- **مشتريات مدفوعة:** مدين مصاريف، دائن نقدية/بنك
- **مشتريات معلقة:** مدين مصاريف، دائن موردين
- **فاتورة مبيعات مدفوعة:** مدين نقدية، دائن إيراد
- **فاتورة مبيعات غير مدفوعة:** مدين عملاء، دائن إيراد

أرصدة الحسابات البنكية تُعاد حسابتها تلقائيًا عند أي تغيير.

## التقنيات

**Backend:** Laravel 13 · PHP 8.3 · SQLite · Sanctum · Blade · Tailwind 4  
**Mobile:** React Native 0.81 · Expo SDK 54 · expo-router · axios · AsyncStorage

## ملاحظات تطويرية

- لاختبار الـ API يدويًا:
  ```bash
  curl -X POST http://127.0.0.1:8000/api/login \
    -H "Accept: application/json" -H "Content-Type: application/json" \
    -d '{"email":"admin@eventplus.com","password":"password"}'
  ```
- قاعدة البيانات تستخدم SQLite افتراضيًا؛ لتغييرها عدّل `.env`.
- لإعادة بناء البيانات التجريبية: `php artisan migrate:fresh --seed`.
# Accounting-system-project
