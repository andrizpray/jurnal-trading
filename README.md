# 📈 Trading Journal Pro

**A Professional Trading Journal & Plan Generator for Forex Traders**

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.x-7952B3?style=flat&logo=bootstrap)](https://getbootstrap.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)](https://php.net)
[![PWA](https://img.shields.io/badge/PWA-Ready-5A0FC8?style=flat&logo=pwa)](https://web.dev/progressive-web-apps/)

## 🚀 Features

### 📊 **Trading Plan Generator**
- 30-day compound interest trading plans
- Real-time calculation preview
- Multiple trader types (Beginner, Intermediate, Advanced)
- Risk management calculator
- Automatic lot size calculation

### 📈 **Analytics Dashboard**
- Win rate & profit factor analysis
- Trading consistency scoring
- Emotional control metrics
- Monthly progress tracking
- Personalized recommendations

### 🔔 **Notification System**
- Daily challenge reminders
- Trading plan updates
- Performance milestones
- Email & in-app notifications
- Real-time notification bell

### 📱 **Mobile PWA**
- Installable progressive web app
- Offline functionality
- Service worker caching
- Mobile-responsive design
- Push notifications ready

### 🎯 **Export & Reporting**
- Excel export with formatting
- PDF reports with charts
- Performance summaries
- Trading history export
- Printable formats

## 🛠️ Tech Stack

**Backend:**
- Laravel 11.x
- PHP 8.2+
- MySQL 8.0
- Redis (caching)

**Frontend:**
- Bootstrap 5.x
- Tailwind CSS
- Alpine.js
- Chart.js
- Bootstrap Icons

**DevOps:**
- Vite build system
- Service Workers (PWA)
- Database indexing
- Caching layer

## 📦 Installation

### 1. Clone Repository
```bash
git clone https://github.com/yourusername/trading-journal-pro.git
cd trading-journal-pro
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

### Production Setup
```bash
# Build for production
npm run build

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Setup queue worker (for notifications)
php artisan queue:work --daemon

# Setup cron job
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Docker Deployment
```dockerfile
# Coming soon
```

## 📁 Project Structure

```
trading-journal-pro/
├── app/
│   ├── Http/Controllers/     # MVC Controllers
│   ├── Services/             # Business logic
│   ├── Notifications/        # Notification classes
│   ├── Exports/              # Excel/PDF exports
│   └── Models/               # Eloquent models
├── resources/
│   ├── views/                # Blade templates
│   ├── js/                   # JavaScript files
│   └── css/                  # Stylesheets
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Sample data
├── routes/                   # Web & API routes
├── tests/                    # PHPUnit tests
└── public/                   # Public assets
```

## 🔧 Development

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test
./vendor/bin/phpunit tests/PolishingTest.php
```

### Development Server
```bash
# Frontend hot reload
npm run dev

# Backend server
php artisan serve
```

### Code Quality
```bash
# Check PHP syntax
php -l app/Services/TradingCalculatorService.php

# Clear caches
php artisan optimize:clear
```

## 📈 Features Roadmap

### ✅ **Completed (v1.0)**
- [x] Trading plan calculator
- [x] Real-time preview API
- [x] Analytics dashboard
- [x] Notification system
- [x] PWA mobile app
- [x] Export functionality
- [x] Performance optimization

### 🚧 **Planned (v2.0)**
- [ ] AI trade pattern analysis
- [ ] Social features & leaderboards
- [ ] Live market data integration
- [ ] Advanced backtesting
- [ ] Mobile app (React Native)
- [ ] Multi-language support

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Authors

- **Andriz** - *Initial work* - [@Andrizzz](https://github.com/Andrizzz)
- **Kael** - *Development & Polish* - AI Assistant

## 🙏 Acknowledgments

- Laravel community
- Bootstrap team
- Trading community feedback
- Open source contributors

---

**⭐ If you find this project useful, please give it a star!**