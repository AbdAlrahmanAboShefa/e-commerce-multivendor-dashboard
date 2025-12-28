# Multi-Vendor Marketplace - Laravel & Vue.js

This is a **Multi-Vendor Marketplace** project built with Laravel (backend) and Vue.js (frontend).  
It allows sellers to manage products, track orders, and monitor earnings through a simple dashboard.

**Features & Usage:**
- Seller Dashboard:
  - Total products
  - Total orders
  - Total earnings (calculated only from delivered items)
- Product management for sellers
- Each order item has its own status: `pending`, `delivered`, `canceled`
- Backend: Laravel API
- Frontend: Vue.js (Vue Shop)

**Requirements:**
- PHP >= 8.x, Laravel >= 10
- Node.js >= 18, npm or yarn
- MySQL or any supported database

**Installation & Running the Project:**
1. Clone the project:
```bash
git clone https://github.com/AbdAlrahmanAboShefa/e-commerce-multivendor-dashboard.git
cd e-commerce-multivendor-dashboard
Setup backend:
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
Setup frontend:
cd vue-shop
npm install
npm run dev
Frontend usually runs at http://localhost:5173 (Vite default)

Backend API runs at http://127.0.0.1:8000

Notes:

Earnings are counted only for delivered items

Do not commit node_modules (already in .gitignore)

Frontend changes require npm install to update dependencies

Future Improvements:

Improve Seller Dashboard design

Add filters (day/week/month)

Add notifications for sellers

Admin Dashboard for monitoring all sellers and orders
