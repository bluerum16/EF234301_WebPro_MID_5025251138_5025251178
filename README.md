# myBook

**by Farrel & Fazli**

myBook is a library management website built for the **Midterm Exam of Web Programming (EF234301), Class D**, Institut Teknologi Sepuluh Nopember, 2026.

## Team

| Name | NRP |
|---|---|
| Farrel Satria Mukti | 5025251138 |
| Fazli Irham Ramadhan Abdillah | 5025251178 |

## About

myBook supports the daily operations of a library. It has three user roles:

- **Library Administrator** manages librarian and member accounts, book categories, and views the dashboard and reports.
- **Librarian** manages book data, records walk-in loans, processes online reservations, confirms pickups, and records returns.
- **Library Member** browses the catalog, makes online reservations, and tracks their reservations and borrowings.

### Borrowing Schemes

1. **Walk-In Borrowing**: the librarian records the loan directly from the dashboard. The loan is immediately marked as `Borrowed`.
2. **Online Reservation**: a member reserves a book on the website (`Pending`). The librarian approves or rejects it. Once approved, the book is `Ready for Pickup`, and the loan is created when the member collects the book.

## Features

| Role | Features |
|---|---|
| General | Login, Registration, Logout |
| Administrator | Librarian Management, Member Management, Book Category Management, Library Dashboard, Library Reports |
| Librarian | Book Management, Walk-In Borrowing, Reservation Management, Pickup Confirmation, Return Processing, Loan Monitoring |
| Member | Book Catalog, Book Search and Filter, Book Details, Online Reservation, Reservation Tracking, Reservation Cancellation, My Borrowings, Borrowing History |

## Tech Stack

- **Front-End:** Blade, Livewire, Tailwind CSS, JavaScript
- **Back-End:** Laravel, PHP
- **Database:** PostgreSQL
- **Version Control:** Git, GitHub

## Design References

- **Book catalog (member pages):** [Gramedia.com](https://www.gramedia.com/categories/buku), for the book grid, category filter, and search layout.
- **Dashboard (administrator and librarian pages):** "Shopeers" dashboard by [Dipa Inhouse on Dribbble](https://dribbble.com/dipainhouse), for the sidebar, summary cards, and data tables.

These references are used for layout inspiration only. myBook uses its own name, colors, and content.

## Getting Started

Requirements: PHP 8.4+, Composer, Node.js, and Docker (for PostgreSQL).

```bash
git clone https://github.com/farrelsatria/EF234301_WebPro_MID_5025251138_5025251178.git
cd EF234301_WebPro_MID_5025251138_5025251178

composer install
npm install
cp .env.example .env
php artisan key:generate
```

Start PostgreSQL:

```bash
docker compose up -d
```

Set the database connection in `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=mybook
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

Then run the migrations and start the app:

```bash
php artisan migrate --seed
composer run dev
```

## Links

- **Repository:** https://github.com/farrelsatria/EF234301_WebPro_MID_5025251138_5025251178
- **Website:** _coming soon_
