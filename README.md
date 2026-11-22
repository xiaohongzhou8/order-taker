# Order Taker

Order Taker is a Laravel application for creating and managing food orders. It can print customer receipts and kitchen tickets, including Chinese menu names, through configured CUPS printer queues.

## Requirements

Install the following software before setting up the project:

- PHP `^8.3`
- Composer
- Node.js and npm
- A database: SQLite is the default; MySQL and PostgreSQL are also supported
- CUPS and the `lp` command, plus configured printer queues, if you want to print orders

## Installing Prerequisites

On Debian or Ubuntu, use repositories that provide PHP 8.3 or later. Install PHP CLI and the extensions used by the app, then install Composer:

```bash
sudo apt update
sudo apt install curl php-cli php-curl php-mbstring php-sqlite3 php-xml unzip
sudo apt install composer
```

Verify the installed versions before continuing:

```bash
php -v
composer --version
```

Install Node.js 22 and its bundled npm using NodeSource:

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
```

Vite requires Node.js `^20.19` or `>=22.12`. Verify Node.js and npm with `node --version` and `npm --version`. Alternatively, use the [official Node.js download page](https://nodejs.org/en/download).

For printing, install CUPS and its command-line client:

```bash
sudo apt install cups cups-client
```

For MySQL or PostgreSQL, install and configure the database server and the matching PHP extension (`php-mysql` or `php-pgsql`) before running setup. Confirm PHP and Node.js versions with `php -v` and `node --version`.

## Installation

From the project directory, run:

```bash
composer setup
```

The setup script installs Composer dependencies, creates `.env` if it does not exist, generates the application key, runs migrations, installs npm dependencies, and builds the frontend assets. SQLite is used by default. To use MySQL or PostgreSQL, create and configure `.env` before running setup so the migrations use the intended database.

To populate the database with the starter user and menu items, run:

```bash
php artisan db:seed
```

This creates `test@example.com` with the password `password` and synchronizes menu items from `database/seeders/menu-items.json`, removing menu items that are not in that file. Do not use the default credentials in production or run this seeder against a production database.

## Printer Configuration

Printing requires CUPS queues on the same host as the application. List available queues with:

```bash
lpstat -p
```

Set the queue names in `.env`:

```dotenv
PRINTER_QUEUE=receipt-queue
KITCHEN_PRINTER_QUEUE=kitchen-queue
```

If `KITCHEN_PRINTER_QUEUE` is omitted, kitchen tickets use `PRINTER_QUEUE`. Chinese text rendering depends on the selected queue's CUPS driver and printer support.

## Development

Start the local development environment with:

```bash
composer dev
```

To run only the Vite development server with Hot Module Replacement:

```bash
npm run dev
```

## Testing

Run the PHPUnit test suite with:

```bash
composer test
```

Automated tests do not send real printer jobs, so verify receipt layout on the target CUPS queue.

