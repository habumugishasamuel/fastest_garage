# API Documentation

## Authentication

All API endpoints require authentication using JWT tokens.

### Get Token
```
POST /api/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123"
}
```

## Customer Endpoints

### Get Customer Profile
```
GET /api/customers/{id}
Authorization: Bearer {token}
```

### Update Customer Profile
```
PUT /api/customers/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890"
}
```

## Service Endpoints

### Get Available Services
```
GET /api/services
Authorization: Bearer {token}
```

### Book a Service
```
POST /api/services/book
Authorization: Bearer {token}
Content-Type: application/json

{
    "service_id": 1,
    "vehicle_id": 1,
    "appointment_date": "2024-04-30 10:00:00"
}
```

## Invoice Endpoints

### Get Customer Invoices
```
GET /api/invoices
Authorization: Bearer {token}
```

### Pay Invoice
```
POST /api/invoices/{id}/pay
Authorization: Bearer {token}
Content-Type: application/json

{
    "payment_method": "credit_card",
    "amount": 150.00
}
```

## Vehicle Endpoints

### Get Customer Vehicles
```
GET /api/vehicles
Authorization: Bearer {token}
```

### Add Vehicle
```
POST /api/vehicles
Authorization: Bearer {token}
Content-Type: application/json

{
    "make": "Toyota",
    "model": "Camry",
    "year": 2020,
    "license_plate": "ABC123"
}
```

## Error Responses

All error responses follow this format:
```json
{
    "error": {
        "code": "ERROR_CODE",
        "message": "Error description"
    }
}
```

Common error codes:
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 500: Internal Server Error 