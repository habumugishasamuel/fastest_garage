# Auto Repair Shop Management System

A comprehensive web-based system for managing auto repair shops, including job tracking, customer management, and billing.

## Features

- User Authentication (Admin, Staff, Customers)
- Service & Job Management
- Booking & Job Tracking
- Billing & Payment System
- User Dashboards
- Reports & Analytics

## Technology Stack

- Backend: PHP
- Frontend: HTML, CSS, JavaScript, Bootstrap
- Database: MySQL
- Payment Integration: PayPal/Stripe
- Email Notifications: PHP Mailer

## Installation

1. Clone the repository
2. Import the database schema from `database/schema.sql`
3. Configure database connection in `config/database.php`
4. Set up payment gateway credentials in `config/payment.php`
5. Configure email settings in `config/email.php`

## Project Structure

```
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config/
│   ├── database.php
│   ├── payment.php
│   └── email.php
├── includes/
│   ├── auth.php
│   ├── functions.php
│   └── header.php
├── admin/
├── staff/
├── customer/
└── index.php
```

## License

MIT License 