<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Sekawan Final Project - Clothing Store

This project is a clothing store application built with Laravel and React, supporting dark mode and JWT authentication.

## Getting Started

### Prerequisites

- PHP >= 7.4
- Composer
- Node.js & npm

### Installation

1. Clone the repository:

    ```sh
    git clone https://github.com/RadjaShiqnals/SekawanFinalProject-ClothingStore.git
    cd SekawanFinalProject-ClothingStore
    ```

2. Install Composer dependencies:

    ```sh
    composer install
    ```

3. Install npm dependencies:

    ```sh
    npm install
    ```

4. Generate JWT secret:

    ```sh
    php artisan jwt:secret
    ```

5. Run the migrations and seed the database:

    ```sh
    php artisan migrate --seed
    ```

6. (Optional) Seed an admin account (this step assumes you have created an admin seeder):

    ```sh
    php artisan db:seed --class=AdminSeeder
    ```

### Running the Application

1. Start the Laravel development server:

    ```sh
    php artisan serve
    ```

2. Start the npm development server:

    ```sh
    npm run dev
    ```

### API Documentation

The API documentation is available on Postman: [Postman Documentation](https://documenter.getpostman.com/view/28791552/2sAY4x9M7v)

## Contributing

Thank you for considering contributing to this project! Please follow the [contribution guide](https://laravel.com/docs/contributions).

## Code of Conduct

Please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct) to ensure a welcoming community.

## Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).