# 📈 Trading Journal

**A Professional Trading Journal & Plan Generator for Forex Traders**

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4-06B6D4?style=flat&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![PWA](https://img.shields.io/badge/PWA-Ready-5A0FC8?style=flat&logo=pwa)](https://web.dev/progressive-web-apps/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## 🚀 Features

### 📓 **Trading Journal**
- CRUD journal entries untuk catatan trading harian
- Tracking currency pair, trade type (buy/sell), hasil (win/loss/break even)
- P&L tracking dengan format angka Indonesia
- Emotion score & market condition logging
- Pagination & responsive table

### 📊 **Trading Plan Generator**
- 30-day compound interest trading plans
- Real-time calculation preview
- Multiple trader types (Beginner, Intermediate, Advanced)
- Risk management calculator
- Automatic lot size calculation
- Export plan ke Excel & PDF

### 📈 **Analytics Dashboard**
- Total entries, win rate, total P&L, avg P&L per trade
- Win/Loss ratio tracking
- Performance overview charts (Chart.js)
- Personalized insights

### 🏆 **30-Day Challenges**
- Daily trading challenges
- Progress tracking per hari
- Performance chart per challenge
- Reset & start challenge baru

### 🔔 **Notification System**
- Real-time notification bell
- Mark as read / clear all
- In-app notification panel

### 📱 **Mobile PWA**
- Installable progressive web app
- Offline fallback page
- Service worker caching
- Mobile-first responsive design

### 🎨 **Multi-Theme System**
- 5 tema tersedia: **Light**, **Dark**, **Ocean**, **Forest**, **Neubrutalism**
- Theme switcher di header
- Tema tersimpan di localStorage
- Chart.js otomatis adapt ke tema (dark/light)

### 🎯 **Export & Reporting**
- Excel export (Maatwebsite Excel) — 30 hari terakhir
- PDF export (DomPDF) — 30 hari terakhir
- Trading plan export ke Excel & PDF

### ⚙️ **User Settings**
- Profile management
- Password change
- Default capital setting

## 🛠️ Tech Stack

**Backend:**
- Laravel 11.x (Blade Templating)
- PHP 8.3
- MySQL 8.0
- Database cache driver

**Frontend:**
- Tailwind CSS 3.4
- Alpine.js 3.14
- Chart.js 4.4
- Bootstrap Icons 1.13
- Font Awesome 6.4
- Vite 5.x (build system)

**Packages:**
- Maatwebsite Excel 3.1 (export Excel)
- Barryvdh DomPDF 2.0 (export PDF)

## 📦 Installation

### 1. Clone Repository
```bash
git clone https://github.com/andrizpray/jurnal-trading.git
cd jurnal-trading
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=trading_journal
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-password
```

### 4. Setup Database
```bash
php artisan migrate
php artisan db:seed  # Optional: sample data
```

### 5. Build Assets
```bash
npm run build
```

### 6. Run Application
```bash
php artisan serve
```

Visit: http://localhost:8000

## 🚀 Deployment

### Nginx Reverse Proxy (Production)
```bash
# Build for production
npm run build

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Setup cron job
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Example Nginx config:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path-to-project/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 📁 Project Structure

```
jurnal-trading/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/                  # API Controllers (notifications, calculator)
│   │   ├── Auth/                 # Authentication (login, register)
│   │   ├── ChallengeController   # 30-day challenges
│   │   ├── DashboardController   # Analytics dashboard
│   │   ├── JournalController     # Trading journal CRUD
│   │   ├── SettingsController    # User settings
│   │   └── TradingPlanController # Trading plan generator
│   └── Models/
│       ├── User
│       ├── JournalEntry
│       ├── TradingPlan
│       ├── Challenge
│       └── ChallengeDay
├── resources/
│   ├── views/
│   │   ├── layouts/              # Main layout (app.blade.php) + themes
│   │   ├── auth/                 # Login & register pages
│   │   ├── journal/              # Journal CRUD pages
│   │   ├── dashboard/            # Dashboard & analytics
│   │   ├── trading-plan/         # Trading plan generator
│   │   ├── challenge/            # 30-day challenges
│   │   ├── settings/             # User settings
│   │   └── notifications/        # Notification panel
│   ├── css/                      # Tailwind + custom CSS
│   └── js/                       # Alpine.js + custom JS
├── database/
│   ├── migrations/               # Database schema
│   └── seeders/                  # Sample data
├── routes/
│   ├── web.php                   # Web routes
│   └── api.php                   # API routes
├── public/                       # Public assets + PWA
├── tests/                        # PHPUnit tests
└── public/
    ├── manifest.json             # PWA manifest
    └── sw.js                     # Service worker
```

## 🎨 Themes

| Theme | Description |
|-------|-------------|
| ☀️ Light | Clean white background, blue accents |
| 🌙 Dark | Dark gray background, cyan/green gradients |
| 🌊 Ocean | Deep blue tones, aquatic feel |
| 🌲 Forest | Green nature-inspired palette |
| ⚡ Neubrutalism | Bold colors, thick black borders, offset shadows |

## 🔧 Development

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test
./vendor/bin/phpunit tests/Feature/JournalTest.php
```

### Development Server
```bash
# Frontend hot reload
npm run dev

# Backend server
php artisan serve
```

### Clear Caches
```bash
php artisan optimize:clear
```

## 📈 Roadmap

### ✅ Completed (v1.0)
- [x] Trading journal CRUD
- [x] Trading plan calculator with compound interest
- [x] Real-time preview API
- [x] Analytics dashboard with Chart.js
- [x] 30-day challenges
- [x] Notification system
- [x] PWA support
- [x] Export Excel & PDF
- [x] Multi-theme system (5 themes)
- [x] Mobile-first responsive design
- [x] User authentication & settings

### 🚧 Planned (v2.0)
- [ ] AI trade pattern analysis
- [ ] Social features & leaderboards
- [ ] Live market data integration
- [ ] Advanced backtesting
- [ ] Multi-language support (i18n)

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Authors

- **Andriz** — *Owner & Initial work* — [@andrizpray](https://github.com/andrizpray)

---

**⭐ If you find this project useful, please give it a star!**
