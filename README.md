# Fastest Garage - Customer Service Module

A comprehensive garage management system with a focus on customer service functionality.

## Features

- Customer Dashboard
- Service Management
- Appointment Scheduling
- Invoice Management
- Vehicle Tracking
- Payment Processing

## Installation

1. Clone the repository:
```bash
git clone https://github.com/habumugishasamuel/fastest_garage.git
```

2. Set up the database:
```bash
cd fastest_garage
php config/init_database.php
```

3. Configure your environment:
- Copy `.env.example` to `.env`
- Update database credentials
- Set up your mail configuration

4. Install dependencies:
```bash
composer install
```

## Development Workflow

- `main` - Production branch
- `dev` - Development branch
- `feature/*` - Feature branches
- `hotfix/*` - Hotfix branches

## Security Features

- Password hashing
- CSRF protection
- Input validation
- SQL injection prevention
- XSS protection

## API Documentation

The system provides RESTful APIs for:
- Customer management
- Service booking
- Invoice generation
- Vehicle tracking

See [API Documentation](docs/api.md) for details.

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Contact

Project Maintainer: [Your Name](mailto:your.email@example.com) 