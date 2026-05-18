# JingleWorks

JingleWorks is a PHP and MySQL web application for a custom audio production studio. It includes a public marketing site, an audio gallery, news, user registration and login, profile management, appointment scheduling, and administration screens for users, gallery items, news, and appointments.

This repository contains Version 2 of the original `trabajo_JS` project. Version 1 was mainly a static HTML/CSS/JavaScript site. Version 2 keeps the original visual idea and some existing pages, but adds PHP templates, a MySQL database, user sessions, role-based navigation, and admin CRUD screens.

The project is intentionally built as a simple page-based PHP application. It does not use an MVC framework; each page contains the PHP needed for that screen and shares common layout through `includes/header.php` and `includes/footer.php`.

## Version 2 Highlights

- Replaced the old static `.html` entry points with PHP pages.
- Added a reusable header, footer, and database connection include.
- Added MySQL tables for users, login credentials, gallery items, news, and appointments.
- Added user registration, login, logout, remembered email, and profile editing.
- Added role-based navigation for guests, normal users, and admin users.
- Added admin screens to manage users, appointments, gallery content, and news.
- Moved gallery and news content from static/front-end data toward database-backed records.
- Kept some Version 1 pages mostly intact, especially `contacto` and `presupuesto`, while integrating them into the PHP layout.

## Features

- Public home page with audio-service content and latest news.
- Gallery page that lists audio work from the database.
- News page backed by database records.
- Contact page with a Leaflet/OpenStreetMap map.
- Quote request page with client-side validation and live price estimation.
- User registration, login, logout, and profile editing.
- User appointment scheduling and appointment management.
- Admin dashboard pages for:
  - users
  - appointments
  - gallery items
  - news

## Tech Stack

- PHP with `mysqli` for server-side pages and database access.
- MySQL / MariaDB for persistent application data.
- Native PHP sessions for authentication state and role handling.
- Password hashing with PHP's `password_hash()` / `password_verify()`.
- HTML, CSS, and vanilla JavaScript for the front end.
- Lightbox2 for gallery image previews.
- Leaflet and OpenStreetMap tiles on the contact page.
- XAMPP-compatible local development setup.

## Application Flow

The app has three main user states:

- Guests can visit the home page, gallery, news, contact page, quote page, login page, and register page.
- Registered users can log in, edit their profile, and schedule or manage their own appointments.
- Admin users can manage appointments, gallery items, users, and news.

After login, the app stores the user's ID, role, name, and email in the PHP session. The shared header reads the session and changes the navigation depending on whether the visitor is logged in and whether the user role is `user` or `admin`.

## Content Flow

News and gallery content are database-driven in Version 2.

- News records are stored in the `noticias` table.
- The public news page lists news ordered by date.
- The home page shows the latest three news items.
- Admin users can create, edit, and delete news from `views/noticias-administracion.php`.
- News images are expected to live in `images/news/`.

Gallery records are stored in the `gallery_items` table.

- The public gallery page reads gallery records from MySQL.
- Each gallery item can include a title, summary, details, tags, image, and audio file.
- Admin users can create, edit, and delete gallery items from `views/galeria-administracion.php`.
- Gallery images are expected to live in `images/gallery/`.
- Audio files are expected to live in `audio/`.

## Pages Kept Close To Version 1

Some pages were kept close to their Version 1 behavior because they already worked as mostly front-end pages:

- `views/contacto.php` keeps the contact/map page behavior and wraps it in the shared PHP layout.
- `views/presupuesto.php` keeps the quote form and JavaScript price calculation. It currently calculates an estimate on the client side and does not save quote requests to the database.

## Project Structure

```text
.
|-- audio/                     Audio files used by gallery items
|-- css/                       Page-specific stylesheets
|-- images/                    Gallery, news, and social images
|-- includes/
|   |-- db.php                 Database connection
|   |-- header.php             Shared navigation/header
|   `-- footer.php             Shared footer
|-- js/                        Page-specific JavaScript
|-- views/                     Public, user, and admin PHP pages
|-- xml/                       XML news data
|-- index.php                  Home page
`-- jingleworks_db_starter.sql Starter database with demo admin user
```

## Requirements

- XAMPP, WAMP, MAMP, or another PHP/MySQL environment
- PHP with `mysqli` enabled
- MySQL or MariaDB

## Local Setup

1. Place the project folder inside your web server document root. The folder name can be anything.

   For XAMPP on Windows, a typical location is:

   ```text
   C:\xampp\htdocs\your-project-folder
   ```

2. Start Apache and MySQL from the XAMPP control panel.

3. Create and import the database.

   Recommended for a demo/local install:

   ```sql
   SOURCE C:/xampp/htdocs/your-project-folder/jingleworks_db_starter.sql;
   ```

   You can also import `jingleworks_db_starter.sql` through phpMyAdmin.

4. Check the database credentials in `includes/db.php`.

   The current default values are:

   ```php
   $dbHost = '127.0.0.1';
   $dbUser = 'root';
   $dbPass = 'test';
   $dbName = 'jingleworks_db';
   ```

   Update `$dbUser` and `$dbPass` if your local MySQL credentials are different.

5. Open the app in your browser.

   ```text
   http://localhost/your-project-folder/index.php
   ```

   Replace `your-project-folder` with the actual folder name used on that machine.

## Demo Login

The starter database creates one admin user:

```text
Username: admin
Password: admin123
```

Change this password after importing the database if the project will be used outside a local demo environment.

## Main Pages

- `index.php` - home page
- `views/productos.php` - gallery
- `views/noticias.php` - news
- `views/contacto.php` - contact
- `views/presupuesto.php` - quote request
- `views/register.php` - register
- `views/login.php` - login
- `views/profile.php` - profile
- `views/citaciones.php` - user appointment scheduling
- `views/citas-administracion.php` - admin appointment management
- `views/galeria-administracion.php` - admin gallery management
- `views/noticias-administracion.php` - admin news management
- `views/usuarios-administracion.php` - admin user management

## Database Notes

The application uses the `jingleworks_db` database. The main tables are:

- `users_data`
- `users_login`
- `gallery_items`
- `noticias`
- `citas`

Use `jingleworks_db_starter.sql` for a ready-to-run local database with the schema and demo admin account.

## Asset Notes

Gallery images should be placed in `images/gallery/`, news images in `images/news/`, and audio files in `audio/`. The admin gallery and news screens use the files available in these folders.

## Development Notes

- Keep shared layout changes in `includes/header.php` and `includes/footer.php`.
- Keep page-specific styles in the matching file under `css/`.
- Keep page-specific scripts in the matching file under `js/`.
- Avoid committing local test media files; `.gitignore` already excludes `audio/test.mp3`, `images/test.webp`, and `xml/news copy.xml`.
