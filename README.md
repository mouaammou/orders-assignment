# Orders Management App

A simple CRUD Vue.js + Firebase Realtime Database application for managing customer orders.
Includes a PHP seeding script to generate dummy orders.

## Requirements

- Node.js (>= 18)
- PHP (>= 8.0) with Composer (You can easily install PHP using [Laravel Herd](https://herd.laravel.com/))
- Firebase project with Realtime Database enabled
- Service account JSON file (serviceAccount.json)


## Setup & Run (Frontend - Vue.js)

```bash
git clone https://github.com/mouaammou/orders-assignment.git
cd orders-assignment
npm install
npm run dev
```


##  Seeding Orders (PHP Script)

```bash
cd php
composer install
php seedOrders.php 10
```

This will insert 10 random orders into Firebase with status "new".
