# ParkSmart Parking Management System

ParkSmart is a web-based parking management platform that streamlines on-site operations for drivers and administrators. Drivers can locate slots, monitor active sessions, and request exit approvals, while administrators oversee occupancy, manage users, and review revenue insights in real time.

## Features
- **Driver tools**
  - Real-time slot availability and parking session tracking
  - Parking history with fee breakdowns and statuses
  - Cash-based exit requests managed through the admin workflow
- **Administrator tools**
  - Interactive dashboard with live occupancy metrics powered by `admin/dashboard.php`
  - Slot lifecycle management (add, edit, delete, toggle maintenance) via `backend/api/admin/`
  - Driver account management with status controls and detailed profiles
  - Earnings analytics, pending payment reviews, and activity monitoring
- **Platform capabilities**
  - Modular PHP backend organized by controllers, APIs, and config files
  - Modern UI with responsive styling under `css/` and interactive behavior in `js/`
  - SQL schema provided in `database/parking_system_structure_only.sql`

## Tech Stack & Libraries
- **Backend:** PHP 8+, PDO, REST-style endpoints under `backend/api/`
- **Frontend:**
  - HTML5 & CSS3
  - Vanilla JavaScript modules under `js/`
  - [Font Awesome 6](https://cdnjs.com/libraries/font-awesome) via CDN for icons
  - [Chart.js 4](https://www.jsdelivr.com/package/npm/chart.js) (admin dashboard analytics)
- **Database:** MySQL / MariaDB accessed via PDO
- **Environment:** WAMP/XAMPP or comparable PHP-enabled stack

## Getting Started
- **Clone the repository**
  - Place the project inside your web root (e.g., `wamp64/www/` or `xampp/htdocs/`)
- **Install dependencies**
  - No package manager is required; ensure PHP extensions for PDO MySQL are enabled
- **Database setup**
  - Create a database (default name: `parking_system_db`)
  - Import `database/parking_system_structure_only.sql`
  - After importing `database/parking_system_db.sql` (or seeding your data), the default admin credentials are:
    - Username: `admin@parksmart.com`
    - Password: `Parkadmin123`
  - Update credentials in `backend/config/db.php` if they differ from your environment
- **Serve the application**
  - Start Apache and MySQL from your stack (WAMP/XAMPP)
  - Navigate to `http://localhost/parking-system/index.php`

## Project Structure
- **`admin/`** — Admin-facing pages such as `dashboard.php`
- **`driver/`** — Driver dashboard, parking, and history pages
- **`backend/`**
  - `controllers/` — Authentication and domain logic (e.g., `auth_controller.php`)
  - `api/` — REST endpoints grouped for admin/driver features
  - `config/` — Database configuration (`db.php`)
- **`css/`** and **`js/`** — Styling and interactive behavior split by user role
- **`modals/`** — Reusable UI components loaded dynamically
- **`database/`** — SQL schema and seed artifacts
- **Root pages** — Landing page (`index.php`), `login.php`, `register.php`

## Usage Notes
- Admin authentication guard is enforced via `backend/controllers/auth_guard.php`
- Ensure PHP sessions are enabled; login actions post to `backend/controllers/auth_controller.php`
- Driver-facing assets are production-ready; modify only admin or shared resources as needed

## Troubleshooting
- **Blank pages or warnings:** Enable PHP error reporting and confirm database credentials
- **API failures:** Check network requests in browser dev tools and PHP error logs under your stack
- **Styles/scripts missing:** Verify correct virtual host or directory path when serving the project

## Contributing
- Follow existing directory conventions when adding admin features
- Keep driver-specific functionality untouched unless explicitly required
- Submit changes with concise descriptions and reference affected files

## License
This project does not currently include an explicit license. Add one if you intend to publish or share the system publicly.
