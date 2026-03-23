# Scandiweb Fullstack Test Task

eCommerce SPA with product listing and cart functionality.

## Stack
- **Backend:** PHP 8.1+, GraphQL, MySQL
- **Frontend:** React 19, Vite, TailwindCSS

## Requirements
- PHP 8.1+
- Composer
- MySQL 5.6+
- Node.js 18+
- npm

## Installation

### 1. Clone the repository
git clone https://github.com/samanta-rukmane/scandiweb-fullstack.git
cd scandiweb-fullstack

### 2. Backend setup
cd backend
composer install
cp .env.example .env

Open `.env` and fill in your database credentials:
DB_HOST=localhost
DB_NAME=scandiweb
DB_USER=root
DB_PASS=your_password

### 3. Create database
mysql -u root -p
CREATE DATABASE scandiweb;
EXIT;

### 4. Import data
php import.php

### 5. Start backend server
cd public
php -S localhost:8000

### 6. Frontend setup
Open a new terminal:
cd frontend
npm install
npm run dev

## Usage
- Frontend: http://localhost:5173
- Backend GraphQL: http://localhost:8000/graphql