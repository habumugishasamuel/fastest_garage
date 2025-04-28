<?php
// Basic routing
$request = $_SERVER['REQUEST_URI'];
$basepath = '/check';

// Remove basepath from request
$request = str_replace($basepath, '', $request);

// Route to the appropriate file
switch ($request) {
    case '/':
    case '':
        require __DIR__ . '/customer/dashboard.php';
        break;
    case '/services':
        require __DIR__ . '/customer/services.php';
        break;
    case '/appointments':
        require __DIR__ . '/customer/appointments.php';
        break;
    case '/invoices':
        require __DIR__ . '/customer/invoices.php';
        break;
    case '/vehicles':
        require __DIR__ . '/customer/vehicles.php';
        break;
    default:
        http_response_code(404);
        require __DIR__ . '/404.php';
        break;
} 