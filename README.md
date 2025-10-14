<p align="center">
  <a href="https://github.com/<your-username>/SavEats" target="_blank">
    <img src="https://classroomclipart.com/images/gallery/Animations/boy-eating-hamburger-holding-soft-drink-animated-clipart-crca.gif" width="200" alt="SavEats Logo">
  </a>
</p>

<p align="center">
<a href="#"><img src="https://img.shields.io/badge/build-passing-brightgreen" alt="Build Status"></a>
<a href="#"><img src="https://img.shields.io/badge/Laravel-11.x-ff2d20?logo=laravel" alt="Laravel"></a>
<a href="#"><img src="https://img.shields.io/badge/PostgreSQL-NeonDB-336791?logo=postgresql" alt="PostgreSQL"></a>
</p>

---

##  About SavEats  

**SavEats** is a one-stop platform that helps reduce food waste while making food more accessible and affordable.  
It connects **consumers**, **establishments**, and **food banks** in a shared mission to **save food, save money, and save the planet** 🌍.  

SavEats provides:  
- Discounted **food listings** from partner establishments  
- A way for businesses to **sell surplus food**  
- A channel for **donations to food banks**  
- **User dashboards** to track savings, rewards, and impact  

---

##  Features  

-  **Consumers** – browse listings, track orders, earn badges  
-  **Establishments** – manage surplus food sales, view analytics  
-  **Food Banks** – receive food donations, manage inventory  
-  **Admins** – oversee users, transactions, and reports  

---

##  Tech Stack  

- **Backend**: Laravel (PHP)  
- **Frontend**: Blade templates, JavaScript, CSS  
- **Database**: PostgreSQL (hosted on NeonDB)  
- **Deployment Ready**: Docker / Render  

---

##  Installation  

1. Clone the repository  
   ```bash
   git clone https://github.com/RosalDaniel/SavEats.git
   cd SavEats
2. Install dependencies
   ```bash
   composer install
   npm install && npm run dev

4. Copy .env.example → .env and configure your DB (Neon/Postgres).

5. Run migrations
    ```bash
    php artisan migrate

7. Start local server
    ```bash
    php artisan serve
