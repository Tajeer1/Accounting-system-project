# Event Plus Mobile

تطبيق الهاتف لنظام Event Plus المحاسبي — React Native + Expo.

## البنية

```
mobile/
├── app/                    # expo-router file-based routes
│   ├── _layout.js         # root layout (auth gate + stack)
│   ├── login.js           # شاشة تسجيل الدخول
│   ├── (tabs)/            # التبويبات السفلية
│   │   ├── _layout.js
│   │   ├── index.js       # لوحة التحكم
│   │   ├── purchases.js   # قائمة المشتريات
│   │   ├── invoices.js    # قائمة الفواتير
│   │   ├── projects.js    # المشاريع
│   │   └── more.js        # حسابات + إعدادات + خروج
│   ├── purchases/
│   │   ├── [id].js        # تفاصيل عملية
│   │   └── new.js         # إضافة عملية
│   └── invoices/
│       ├── [id].js
│       └── new.js
└── src/
    ├── api/client.js      # axios + interceptors + endpoints
    ├── context/AuthContext.js
    ├── components/        # Card, StatCard, Button, Input, Picker, Badge, EmptyState
    ├── theme/colors.js
    └── utils/format.js
```

## الإعداد

### 1) اضبط عنوان الـ API

في [app.json](app.json) عدّل `extra.apiUrl` ليشير إلى سيرفر Laravel.

- **المحاكي (Android emulator):** `http://10.0.2.2:8000/api`
- **محاكي iOS:** `http://localhost:8000/api`
- **جهاز حقيقي على نفس الشبكة:** `http://<IP-الكمبيوتر>:8000/api` (مثال: `http://192.168.1.10:8000/api`)

### 2) شغّل الـ Laravel backend

من المجلد الرئيسي للمشروع:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

`--host=0.0.0.0` مهم حتى يصل الجهاز الحقيقي للسيرفر.

### 3) شغّل التطبيق

```bash
cd mobile
npm start
```

ثم امسح QR code بتطبيق **Expo Go** على الجوال، أو اضغط `a` لفتح محاكي Android، أو `i` لـ iOS.

## بيانات تسجيل الدخول الافتراضية

```
البريد: admin@eventplus.com
كلمة المرور: password
```

## نظرة على المزايا

- **المصادقة:** Laravel Sanctum (tokens محفوظة في AsyncStorage).
- **التنقل:** expo-router (file-based routing).
- **الـ API client:** axios مع interceptor لإضافة الـ token تلقائيًا.
- **RTL:** تصميم كامل من اليمين لليسار.
- **Pull to refresh** في كل القوائم.
- **الشاشات:**
  - لوحة تحكم بإحصائيات ورسم بياني ومعرض آخر العمليات
  - قوائم المشتريات والفواتير مع بحث وفلترة
  - تفاصيل عملية مع القيد المحاسبي المنشأ تلقائيًا
  - إضافة عملية شراء وفاتورة جديدة
  - قائمة المشاريع بنسبة الربح والتقدم
  - الحسابات البنكية وتسجيل الخروج

## الـ Endpoints المستخدمة من Laravel

كل الـ routes تحت `/api/*` ومحمية بـ `auth:sanctum` (عدا `/login`):

- `POST /api/login` · `POST /api/logout` · `GET /api/me`
- `GET /api/dashboard`
- `GET|POST /api/purchases` · `GET|PUT|DELETE /api/purchases/{id}`
- `GET|POST /api/invoices` · `GET|PUT|DELETE /api/invoices/{id}` · `POST /api/invoices/{id}/mark-paid`
- `GET /api/bank-accounts` · `GET /api/projects` · `GET /api/categories` · `GET /api/chart-of-accounts`
