<div align="center">
    <h1>Oikos: Personal Finance & Smart Assistant</h1>
    <p>A powerful personal finance, nutrition, and lifestyle management system with an integrated AI assistant. Automates transaction syncing, tracks assets, and manages daily life with advanced data visualization.</p>
    <img src="https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&labelColor=111827" alt="Laravel">
    <img src="https://img.shields.io/badge/Vue.js-3.0-4FC08D?style=for-the-badge&logo=vue.js&labelColor=111827" alt="Vue.js">
    <img src="https://img.shields.io/badge/Tailwind_CSS-3.0-38B2AC?style=for-the-badge&logo=tailwind-css&labelColor=111827" alt="Tailwind CSS">
    <img src="https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge&color=34d399&labelColor=111827" alt="License">
    <br/>
    <a href="https://github.com/sponsors/klpod221">
        <img src="https://img.shields.io/badge/Sponsor-GitHub-ea4aaa?style=for-the-badge&logo=github&labelColor=111827" alt="GitHub Sponsors">
    </a>
</div>

## 📝 Description

**Oikos** is a comprehensive lifestyle management platform designed to bring order to your finances, health, and daily productivity. It combines automated financial tracking through Gmail integration, precious metals monitoring, and detailed nutrition planning into a single, cohesive dashboard.

At its core is a smart AI Assistant capable of managing transactions, generating meal plans, and providing insights into your spending habits. Whether you're tracking improved net worth or optimizing your workout schedule, Oikos serves as your central hub for personal growth.

## 🚀 Table Of Content

- [📝 Description](#-description)
- [🚀 Table Of Content](#-table-of-content)
- [✨ Features](#-features)
  - [💰 Financial Management](#-financial-management)
  - [🤖 AI Assistant](#-ai-assistant)
  - [🥗 Nutrition \& Health](#-nutrition--health)
  - [💪 Workout Tracking](#-workout-tracking)
  - [📧 Integration Services](#-integration-services)
- [🚀 Installation](#-installation)
  - [🐳 Using Docker (Recommended)](#-using-docker-recommended)
  - [🛠️ Manual Installation](#️-manual-installation)
- [🚀 Development](#-development)
  - [Key Technologies](#key-technologies)
- [🤝 Contributing](#-contributing)
- [📝 License](#-license)
- [👤 Author](#-author)

## ✨ Features

### 💰 Financial Management
- **Transactions & Wallets**: Track spending across multiple wallets (Cash, Bank, Savings) with automatic categorization.
- **Gmail Sync**: Automatically parses bank emails to record transactions in real-time.
- **Asset Tracking**: Monitor Gold/Silver prices and calculate total asset value alongside fiat currency.
- **Savings Goals**: Set and track progress towards financial targets.
- **Statistics**: Visual reports on income, expenses, and net worth trends.

### 🤖 AI Assistant
- **Chat Interface**: Natural language interaction to query data or perform actions.
- **Smart Tools**: The AI can creating transactions, updating wallets, logging workouts, and more.
- **Context Aware**: Understands your financial history and health data to provide personalized advice.

### 🥗 Nutrition & Health
- **Meal Planning**: Generate weekly meal plans based on dietary preferences.
- **Ingredient Tracking**: Manage pantry inventory and shopping lists.
- **Recipes**: Store and organize favorite recipes with nutritional information.

### 💪 Workout Tracking
- **Routine Management**: Schedule and track workouts.
- **Exercises Library**: Database of exercises with instruction and muscle targeting.

### 📧 Integration Services
- **Google OAuth**: Secure integration with Gmail for data syncing.
- **Background Jobs**: Automated tasks for keeping data up-to-date without manual intervention.

## 🚀 Installation

### 🐳 Using Docker (Recommended)

The easiest way to get Oikos running is using Docker Compose.

1. Clone the repository
```bash
git clone https://github.com/klpod221/oikos.git
cd oikos
```

2. Start the services
```bash
docker compose up -d
```

3. Run migrations and seed data
```bash
docker compose exec backend php artisan migrate --seed
```

4. Access the application
- Frontend: http://localhost:3000
- Backend API: http://localhost:8080

### 🛠️ Manual Installation

1. Clone the repository
```bash
git clone https://github.com/klpod221/oikos.git
cd oikos
```

2. Setup Backend (Laravel)
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

3. Setup Frontend (Vue 3)
```bash
cd frontend
npm install
npm run dev
```

## 🚀 Development

### Key Technologies

| Layer | Technology | Purpose |
|-------|-----------|---------|
| Frontend | Vue 3 + Tailwind CSS | Responsive and reactive User Interface |
| Backend | Laravel 12 | Robust API and application logic |
| Database | PostgreSQL | Relational data storage |
| Cache | Redis | High-performance caching and queue management |
| AI | OpenAI / LLM Integration | Smart assistant capabilities |
| Icons | Ant Design Vue | Comprehensive UI component library |

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👤 Author

**Bùi Thanh Xuân (klpod221)**

- Website: [klpod221.com](https://klpod221.com)
- GitHub: [@klpod221](https://github.com/klpod221)
- Email: [klpod221@gmail.com](mailto:klpod221@gmail.com)

<div align="center">
    <p>Made with ❤️ by klpod221</p>
    <p>⭐ Star this repository if you find it helpful!</p>
</div>
