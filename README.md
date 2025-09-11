<p align="center"> <a href="https://github.com/<your-username>/SavEats" target="_blank"> <img src="https://i.imgur.com/ijM3nQm.png" width="400" alt="SavEats Logo"> </a> </p> <p align="center"> <a href="#"><img src="https://img.shields.io/badge/build-passing-brightgreen" alt="Build Status"></a> <a href="#"><img src="https://img.shields.io/badge/license-MIT-blue" alt="License"></a> <a href="#"><img src="https://img.shields.io/badge/Laravel-11.x-ff2d20?logo=laravel" alt="Laravel"></a> <a href="#"><img src="https://img.shields.io/badge/PostgreSQL-NeonDB-336791?logo=postgresql" alt="PostgreSQL"></a> </p>
🍽️ About SavEats

SavEats is a one-stop platform that helps reduce food waste while making food more accessible and affordable.
It connects consumers, establishments, and food banks in a shared mission to save food, save money, and save the planet 🌍.

SavEats provides:

Discounted food listings from partner establishments

A way for businesses to sell surplus food

A channel for donations to food banks

User dashboards to track savings, rewards, and impact

✨ Features

👤 Consumers – browse listings, track orders, earn badges

🏪 Establishments – manage surplus food sales, view analytics

🏢 Food Banks – receive food donations, manage inventory

🔑 Admins – oversee users, transactions, and reports

🛠️ Tech Stack

Backend: Laravel (PHP)

Frontend: Blade templates, JavaScript, CSS

Database: PostgreSQL (hosted on NeonDB)

Deployment Ready: Docker / Render

⚙️ Installation

Clone the repository

git clone https://github.com/<your-username>/SavEats.git
cd SavEats


Install dependencies

composer install
npm install && npm run dev


Copy .env.example → .env and configure your DB (Neon/Postgres).

Run migrations

php artisan migrate


Start local server

php artisan serve
