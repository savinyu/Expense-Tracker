# 💸 Multi-Currency Expense Tracker

A robust, enterprise-grade expense tracking application built for seamless multi-currency management. This project was developed as a hackathon submission, focusing on real-time currency conversion, reactive data visualization, and a highly customizable user experience.

## ✨ Key Features

* **Dual-Currency Tracking & Live Conversion:** 
  Log expenses in any currency (from receipts or manual entry). The app automatically fetches live exchange rates, stores the original amount, and calculates a unified `base_amount` in the user's default currency for accurate analytics.
* **Intelligent API Caching:** 
  Integrates with `open.er-api.com` using a smart 1-hour cache layer to prevent rate-limiting and ensure lightning-fast dashboard loads.
* **Interactive Data Visualization:** 
  Features dynamic, reactive pie charts built with Chart.js and Alpine.js. Engineered with a "stateless" architecture to prevent reactivity memory leaks while maintaining real-time UI updates.
* **Dynamic High-Spend Alerts:** 
  Automatically highlights expensive transactions. The system dynamically scales the alert threshold based on currency denomination (e.g., $100 for USD vs. ¥10,000 for JPY), or users can set a custom threshold in their profile settings.
* **Advanced Data Filtering:** 
  Slice and dice expense data via a URL-bookmarkable, GET-parameter filter panel (Date Range, Category, and Original Currency) that instantly syncs with the dashboard charts.

## 🛠️ Tech Stack

* **Backend:** Laravel (PHP), MySQL
* **Frontend:** Blade, Tailwind CSS, Alpine.js
* **Data Visualization:** Chart.js
* **Environment:** Docker (Laravel Sail)

## 🚀 Getting Started

### Prerequisites
Make sure you have [Docker](https://www.docker.com/) installed and running on your machine.

### Installation

1. **Clone the repository:**
   ```bash
   git clone [https://github.com/YOUR-USERNAME/expense-tracker-hackathon.git](https://github.com/YOUR-USERNAME/expense-tracker-hackathon.git)
   cd expense-tracker-hackathon
2. **Set up your environment file:**

    ```Bash
    cp .env.example .env


3. **Install PHP dependencies (via Docker):**
   ```bash
   docker run --rm \
       -u "$(id -u):$(id -g)" \
       -v $(pwd):/var/www/html \
       -w /var/www/html \
       laravelsail/php8.2-composer:latest \
       composer install --ignore-platform-reqs
4. Start the Docker containers:

    ```Bash
    ./vendor/bin/sail up -d
# Or if using raw Docker Compose: docker compose up -d
Generate the application key:

    docker compose exec app php artisan key:generate
    Run Migrations and Seed the Database:
This will create the necessary tables and populate the app with realistic, multi-currency dummy data for testing.

    docker compose exec app php artisan migrate:fresh --seed
Compile Frontend Assets:

    
    npm install
    npm run dev
⚙️ Configuration
Exchange Rate API:
This project uses a free, open API for live exchange rates. By default, it requires no API key. If you experience rate limits, or wish to upgrade to a provider requiring a key, add it to your .env file:

Code snippet
EXCHANGE_RATE_API_KEY=your_key_here
Note: If you make changes to the .env file, ensure you clear the config cache:
    
    docker compose exec app php artisan config:clear

🧹 Useful Commands
If you need to force the app to fetch fresh exchange rates (bypassing the 1-hour cache), run:
    
    docker compose exec app php artisan cache:clear
