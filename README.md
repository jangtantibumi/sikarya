# SIKARYA ERP (Enterprise Resource Planning)

A modern, multi-tenant enterprise resource planning system built with Laravel 11. Designed to centralize business operations across multiple companies or branches seamlessly, focusing on performance, modularity, and scalability.

## Key Features

1. **Multi-Tenant Architecture**
   - Single database, multiple companies.
   - Data is scoped tightly via `BelongsToCompany` global scope, ensuring no data leaks between branches/tenants.

2. **Command Center (Master Portal)**
   - Designed exclusively for top-level executives (CEOs).
   - Real-time global dashboard monitoring cross-module metrics.
   - Module Controls: Switch modules ON/OFF instantly per tenant.

3. **Employee Portal (Operational Hub)**
   - Role-based access control (Manager, HRD, Staff, PPIC, Finance).
   - Features shift assignments, attendance workflows, and peer-to-peer integrated chat systems.

4. **Integrated ERP Modules**
   - **CRM & Sales:** Lead management, Pos Sales, and pipeline tracking.
   - **Purchasing & Inventory:** Purchase Requisitions (PR) -> Purchase Orders (PO) -> Goods Receipt (GR), and automated stock backflushing.
   - **Production (BOM):** Bill of Materials management and Work Order tracking for manufacturing pipelines.
   - **Finance & Accounting Engine:** Fully automated Double-Entry Bookkeeping system handling Assets, Liabilities, Equity, and P&L. Supports Manual Journal Entries.
   - **HRIS & Organizational Hierarchy:** Resignation workflows, document sharing, shift management, and hierarchical approvals.
   - **AI Integrations (Gemini):** Encrypted server-side API Key storage per tenant, granting AI assistance explicitly mapped to user roles context.

## Requirements

- **PHP**: ^8.2 (Tested on PHP 8.3.30)
- **Composer**: ^2.0
- **Database**: SQLite (Default) or MySQL/PostgreSQL
- **Web Server**: Apache/Nginx (via Laragon on Windows recommended)

## Installation Guide (Local / Laragon)

1. **Clone or Extract the Project**
   Place the project folder inside your Laragon `www` directory (e.g., `C:\laragon\www\suba-erp`).

2. **Install Dependencies**
   Open terminal inside the project directory and run:
   ```bash
   composer install
   ```

3. **Environment Setup**
   Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
   Generate the application key:
   ```bash
   php artisan key:generate
   ```

4. **Database Migration & Seeding**
   Ensure your `.env` is set to SQLite (default for development):
   ```env
   DB_CONNECTION=sqlite
   ```
   Run migrations and seed the initial tenant data (Company, Roles, CEO, Employees):
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Run the Development Server**
   If you prefer not to use Laragon's auto-virtual host, you can run:
   ```bash
   php artisan serve --port=8081
   ```

## Demo Access

After running `php artisan serve --port=8081`, you can access the system at:
* **Base URL:** `http://127.0.0.1:8081/master-demo/login`

**Default Credentials (From Seeder):**
1. **CEO (Executive Access)**
   - Role: CEO
   - Hub: Master Portal (`/master-demo/app`)
2. **Manager (Operational)**
   - Role: Manager
   - Hub: Employee Portal (`/master-demo/employee`)
3. **Staff (Operational)**
   - Role: Staff
   - Hub: Employee Portal (`/master-demo/employee`)

*(Note: In a true production environment, seeders would populate real users, or users would be invited via email).*

## Testing

This project comes with a comprehensive suite of Feature and Unit tests covering:
- Complex multi-role resignation approvals.
- Accounting double-entry validity.
- Security boundary scoping between tenants.
- Database retention workflows.

To run the test suite:
```bash
php artisan test
```

## Maintenance & Support
For further technical documentation and architectural blueprints, please refer to the internal documentation repository.
