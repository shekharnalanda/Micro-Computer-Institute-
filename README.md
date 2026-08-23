# Micro Computer Institute

Laravel website and institute management system for Micro Computer Institute, MCI Campus, Bihar Sharif, Nalanda.

- Production domain: https://mciedu.com
- Website author: Sujit Shekhar
- Email: mcieducationalgroup@gmail.com
- Contact: 9334779133, 7004773247

## Main modules

- Bilingual public website with page-specific hero and content images
- Online and offline admission management
- Course management and detailed course pages
- Course-wise PDF and link-based online study materials
- Student portal, attendance, assignments and assessments
- Fees, receipts, ID cards, marksheets and certificates
- Enquiry management and notifications
- Gallery, notices, jobs and career guidance
- Secure admin dashboard and CMS

## Server setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

On BigRock, use PHP 8.3, point `mciedu.com` to the repository's `public/` directory, keep `.env` outside Git, and make `storage/` plus `bootstrap/cache/` writable.
