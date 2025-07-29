# VersAI - AI Agents Business Platform

## Overview
VersAI is a Laravel-based web application that provides AI agent services for businesses. The platform offers intelligent automation and AI-powered solutions for various business needs.

## Features
- AI Agent Management
- Blog System with Comments
- Multi-language Support (Turkish, English, French, Arabic)
- Modern Dashboard Interface
- Responsive Design

## Technology Stack
- **Backend**: Laravel (PHP)
- **Frontend**: HTML, CSS, JavaScript
- **Styling**: Tailwind CSS
- **Icons**: Feather Icons, Material Design Icons

## Project Structure
```
VERSEAI/
├── app/
│   ├── Console/Commands/     # Artisan commands
│   ├── Http/Controllers/     # Web controllers
│   ├── Models/              # Eloquent models
│   └── Providers/           # Service providers
├── database/
│   ├── migrations/          # Database migrations
│   ├── seeders/            # Database seeders
│   └── factories/          # Model factories
├── public/                 # Public assets
├── resources/
│   ├── views/              # Blade templates
│   ├── css/                # Stylesheets
│   └── js/                 # JavaScript files
└── routes/                 # Route definitions
```

## Installation
1. Clone the repository
2. Install dependencies: `composer install`
3. Copy `.env.example` to `.env` and configure
4. Run migrations: `php artisan migrate`
5. Seed the database: `php artisan db:seed`

## Development
- Start development server: `php artisan serve`
- Run tests: `php artisan test`

## License
This project is proprietary software developed for VersAI business platform.

---
*Last updated: $(date)*
