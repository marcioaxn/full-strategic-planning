<p align="center">
  <a href="https://laravel.com" target="_blank" rel="noopener noreferrer">
    <img src="public/img/_0403eb0b-de95-4131-87cd-5c705ae95535.png" width="320" alt="Laravel Starter Kit Logo">
  </a>
</p>

<h1 align="center">Laravel Jetstream + Livewire + Bootstrap Starter Kit</h1>

<p align="center">
  <strong>Production-ready Laravel 12 starter kit with modern UX/UI, complete authentication, and dark mode support</strong>
</p>

<p align="center">
  <a href="#-quick-start">Quick Start</a> •
  <a href="#-features">Features</a> •
  <a href="#-installation">Installation</a> •
  <a href="#-usage">Usage</a> •
  <a href="#-customization">Customization</a> •
  <a href="#-troubleshooting">Troubleshooting</a>
</p>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Requirements](#-requirements)
- [Quick Start](#-quick-start)
- [Detailed Installation](#-detailed-installation)
- [Project Structure](#-project-structure)
- [Usage Guide](#-usage)
- [Theme System](#-theme-system)
- [Authentication](#-authentication)
- [Exception Handling](#-exception-handling)
- [Customization](#-customization)
- [Development](#-development)
- [Production Deployment](#-production-deployment)
- [Troubleshooting](#-troubleshooting)
- [FAQ](#-faq)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🎯 Overview

This is a **professional, production-ready starter kit** for Laravel applications featuring:

- ✅ **Complete Authentication System** - Powered by Laravel Jetstream with Livewire
- ✅ **Modern Bootstrap 5 UI** - No Tailwind, pure Bootstrap with custom styling
- ✅ **Dark/Light/System Theme** - Automatic theme switching with localStorage persistence
- ✅ **Advanced UX/UI** - Modern, responsive design with smooth animations
- ✅ **Leads Management CRUD** - Full-featured example with filters, pagination, and modals
- ✅ **Strong Password Validation** - Real-time validation with visual feedback
- ✅ **Smart Exception Handling** - User-friendly error pages and redirects
- ✅ **UUID Primary Keys** - Enhanced security and distributed system support
- ✅ **Ready for Production** - Optimized, tested, and documented

**Perfect for:**
- Developers starting a new Laravel project
- Teams needing a solid foundation with modern UI
- Projects requiring authentication out of the box
- Applications that need dark mode support
- Startups looking to accelerate development

---

## ✨ Features

### 🔐 Authentication & Security

- **Laravel Jetstream 5.3** - Complete authentication scaffolding
- **Livewire 3.6.4** - Reactive components without page refreshes
- **Laravel Sanctum** - API token authentication ready
- **UUID Primary Keys** - Enhanced security and scalability
- **CSRF Protection** - Built-in with user-friendly error handling
- **Session Management** - Automatic expiration with clear messaging
- **Password Strength Validator** - Real-time validation requiring:
  - Minimum 12 characters
  - At least 1 uppercase letter
  - At least 1 number
  - At least 2 special characters
- **Show/Hide Password Toggle** - On both password fields

### 🎨 Modern UX/UI

- **Bootstrap 5.3.3** - Latest stable version with SCSS customization
- **Dark/Light/System Theme** - Three-way theme switcher with:
  - System preference detection
  - localStorage persistence across pages
  - Smooth transitions
  - Consistent styling across all pages
- **Responsive Design** - Mobile-first approach, works on all devices
- **Modern Components**:
  - Glassmorphism effects on modals
  - Gradient buttons with hover animations
  - Floating labels and icons
  - Progress bars and strength indicators
  - Toast notifications and alerts
- **Custom Welcome Page** - Fully redesigned landing page with:
  - Animated hero section
  - Feature showcases
  - Technology stack display
  - Testimonials section
  - Contact information
  - Smooth scroll navigation

### 📊 Leads Management (Example CRUD)

- **Full CRUD Operations** - Create, Read, Update, Delete
- **Real-time Search** - Debounced search with Livewire
- **Status Filters** - Filter by lead status (New, In Progress, Won, Lost)
- **Pagination** - Efficient data loading
- **CSV Export** - Download leads as CSV file
- **Responsive Tables** - Desktop table view, mobile card view
- **Modern Modals** - Create/Edit forms in beautiful modals
- **Confirmation Dialogs** - Safe delete operations
- **Loading States** - Visual feedback during operations
- **Empty States** - Helpful messages when no data

### 🛠️ Developer Experience

- **Vite 7.0.7** - Lightning-fast HMR (Hot Module Replacement)
- **SCSS Support** - Customize Bootstrap variables easily
- **Alpine.js 3.15.2** - Minimal JavaScript framework (included with Livewire)
- **Bootstrap Icons** - 1,800+ icons included
- **Concurrently** - Run multiple dev servers simultaneously
- **Clear Code Structure** - Well-organized, commented, and documented
- **No Build Required** - Assets pre-compiled for quick start

### 🚨 Smart Exception Handling

Automatically handles common errors and redirects users appropriately:

| Exception Type | User Status | Redirect To | Message |
|---------------|-------------|-------------|---------|
| Session Expired | Was Logged In | `/login` | "Your session has expired. Please sign in again." |
| Unauthenticated | Not Logged In | `/welcome` | "You need to be authenticated to access this page." |
| CSRF Token Mismatch | Not Logged In | `/welcome` | "Your session has expired. Please try again." |
| CSRF Token Mismatch | Logged In | `/login` | "Your session token has expired. Please sign in again." |
| 403 Forbidden | Not Logged In | `/welcome` | "You do not have permission to access this resource." |
| 403 Forbidden | Logged In | `/dashboard` | Error message shown |
| 404 Not Found | Not Logged In | `/welcome` | "The page you are looking for could not be found." |
| 404 Not Found | Logged In | Standard 404 | Stays in authenticated area |
| 500 Server Error | Not Logged In | `/welcome` | "An unexpected error occurred. Please try again later." |
| 500 Server Error | Logged In | Standard Error | Laravel handles normally |

---

## 🛠️ Tech Stack

### Backend
| Package | Version | Purpose |
|---------|---------|---------|
| **Laravel** | 12.x | PHP framework |
| **Jetstream** | 5.3 | Authentication scaffolding |
| **Livewire** | 3.6.4 | Reactive components |
| **Sanctum** | 4.x | API authentication |
| **Spatie HTML** | 3.13 | HTML form helpers |

### Frontend
| Package | Version | Purpose |
|---------|---------|---------|
| **Bootstrap** | 5.3.3 | CSS framework |
| **Bootstrap Icons** | 1.11.x | Icon library |
| **Alpine.js** | 3.15.2 | JavaScript framework |
| **Alpine Mask** | 3.15.x | Input masking |
| **Sortable.js** | 1.15.x | Drag & drop |
| **Axios** | 1.x | HTTP client |
| **Vite** | 7.0.7 | Build tool |

### Development Tools
| Tool | Version | Purpose |
|------|---------|---------|
| **PHP** | 8.2+ | Required |
| **Composer** | 2.x | PHP dependencies |
| **Node.js** | 20 LTS | JavaScript runtime |
| **PostgreSQL** | 13+ | Database (recommended) |
| **Pest** | 3.x | Testing framework (optional) |

---

## 📦 Requirements

### Minimum Requirements

Before starting, ensure you have:

1. **PHP 8.2 or higher**
   - Extensions: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`
   - Check with: `php -v` and `php -m`

2. **Composer 2.x**
   - Download from: https://getcomposer.org/download/
   - Check with: `composer --version`

3. **Node.js 20 LTS** (or 18+)
   - Download from: https://nodejs.org/
   - Check with: `node -v` and `npm -v`

4. **Database**
   - **PostgreSQL 13+** (recommended) - configured in this project
   - OR **MySQL 8.0+** (requires `.env` changes)
   - OR **SQLite** (for development only)

5. **Web Server** (one of):
   - Apache 2.4+ with mod_rewrite
   - Nginx 1.18+
   - PHP built-in server (development only)

### Recommended Setup (Optional but Helpful)

- **Git** - For version control
- **Redis** - For caching and queues (production)
- **Supervisor** - For queue workers (production)
- **SSL Certificate** - For HTTPS (production)

---

## 🚀 Quick Start

**For experienced developers - Get running in 3 minutes:**

```bash
# 1. Clone and navigate
git clone <your-repository-url> my-project
cd my-project

# 2. Install dependencies
composer install && npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env (edit with your credentials)
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=your_database
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# 5. Run migrations with demo data
php artisan migrate --seed

# 6. Build assets and start server
npm run build
php artisan serve
```

**Done!** Open http://localhost:8000 and login with:
- Email: `user_adm@user_adm.com`
- Password: `1352@765@1452`

---

## 📖 Detailed Installation

### Step 1: Clone the Repository

```bash
# Using HTTPS
git clone https://github.com/yourusername/your-repo.git my-project

# OR using SSH
git clone git@github.com:yourusername/your-repo.git my-project

# Navigate to project directory
cd my-project
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

**What this does:**
- Downloads Laravel and all required PHP packages
- Sets up autoloading
- Prepares the vendor directory

**Troubleshooting:**
- If you get memory errors: `php -d memory_limit=-1 /path/to/composer install`
- If extensions are missing: Install required PHP extensions for your OS

### Step 3: Install JavaScript Dependencies

```bash
npm install
```

**What this does:**
- Downloads Node.js packages (Bootstrap, Vite, Alpine.js, etc.)
- Creates `node_modules` directory
- Prepares for asset compilation

**Troubleshooting:**
- If you get permission errors on Linux/Mac: `sudo chown -R $USER:$USER .`
- If npm is slow: Try `npm install --legacy-peer-deps`

### Step 4: Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

**Now edit the `.env` file:**

```env
# Application
APP_NAME="My Application"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (PostgreSQL example)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# For MySQL, use:
# DB_CONNECTION=mysql
# DB_PORT=3306
```

**Important Notes:**
- **Never commit `.env` to version control** - it contains sensitive data
- Change `APP_ENV=production` and `APP_DEBUG=false` in production
- Update `APP_URL` to your actual domain in production

### Step 5: Database Setup

**Create the database first:**

```bash
# PostgreSQL
createdb your_database_name

# OR in psql:
psql -U postgres
CREATE DATABASE your_database_name;
\q

# MySQL
mysql -u root -p
CREATE DATABASE your_database_name;
exit;
```

**Run migrations and seeders:**

```bash
php artisan migrate --seed
```

**What this does:**
- Creates all required tables
- Sets up UUID primary keys
- Creates default admin user: `user_adm@user_adm.com` / `1352@765@1452`
- Generates 25 sample leads with English team names

**To reset database (WARNING: Deletes all data):**
```bash
php artisan migrate:fresh --seed
```

### Step 6: Storage Link (For File Uploads)

```bash
php artisan storage:link
```

**What this does:**
- Creates symbolic link from `public/storage` to `storage/app/public`
- Required for user avatars and file uploads to work

### Step 7: Build Assets

**Development build (with source maps):**
```bash
npm run dev
```

**Production build (minified and optimized):**
```bash
npm run build
```

**What this does:**
- Compiles SCSS to CSS
- Bundles JavaScript
- Optimizes images
- Creates versioned filenames for cache busting

### Step 8: Start Development Server

**Option A: PHP Built-in Server (Simple)**
```bash
php artisan serve
```
Access: http://localhost:8000

**Option B: Concurrent Development (Recommended)**
```bash
composer run dev
```
This runs in parallel:
- `php artisan serve` (http://localhost:8000)
- `php artisan queue:listen` (background jobs)
- `npm run dev` (hot module replacement)

**Option C: Apache/Nginx (Production-like)**
- Point document root to `/public` directory
- Ensure mod_rewrite (Apache) or proper nginx config
- Example nginx config in `docs/nginx.conf` (if you create one)

---

## 📁 Project Structure

```
starter-kit-laravel-jetstream-livewire-bootstrap/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/          # HTTP controllers (minimal, mostly Livewire)
│   │   └── Middleware/           # Custom middleware
│   ├── Livewire/                 # Livewire components
│   │   └── LeadsTable.php        # Example CRUD component
│   ├── Models/                   # Eloquent models
│   │   ├── User.php              # User model with UUID
│   │   └── Lead.php              # Lead model example
│   └── Providers/                # Service providers
│
├── bootstrap/
│   └── app.php                   # Application bootstrap with exception handlers
│
├── config/                       # Configuration files
│   ├── app.php                   # Application config
│   ├── database.php              # Database config
│   └── livewire.php              # Livewire config
│
├── database/
│   ├── factories/
│   │   ├── UserFactory.php       # User factory
│   │   └── LeadFactory.php       # Lead factory (English)
│   ├── migrations/               # Database migrations (UUID setup)
│   └── seeders/
│       └── DatabaseSeeder.php    # Seeds admin user + 25 leads
│
├── public/                       # Public web root
│   ├── index.php                 # Entry point
│   ├── img/                      # Images
│   └── build/                    # Compiled assets (auto-generated)
│
├── resources/
│   ├── js/
│   │   ├── app.js                # Main JS entry (theme system)
│   │   └── bootstrap.js          # Axios & Bootstrap bundle
│   ├── scss/
│   │   └── app.scss              # Main SCSS entry (Bootstrap customization)
│   └── views/
│       ├── welcome.blade.php     # Landing page (modern UX)
│       ├── dashboard.blade.php   # Dashboard (modern UX)
│       ├── auth/
│       │   ├── login.blade.php   # Login page (modern UX)
│       │   └── register.blade.php # Register page (strong password validation)
│       ├── components/           # Blade components
│       │   ├── modal.blade.php   # Base modal (glassmorphism)
│       │   ├── dialog-modal.blade.php
│       │   └── confirmation-modal.blade.php
│       ├── layouts/
│       │   ├── app.blade.php     # Main layout (with sidebar)
│       │   └── guest.blade.php   # Guest layout (modern dark mode)
│       └── livewire/
│           └── leads-table.blade.php # Leads CRUD UI
│
├── routes/
│   ├── web.php                   # Web routes (with 'welcome' named route)
│   └── api.php                   # API routes (Sanctum ready)
│
├── storage/                      # Storage directory
│   ├── app/
│   ├── framework/
│   └── logs/
│
├── tests/                        # Pest/PHPUnit tests (optional)
│
├── .env.example                  # Environment template
├── artisan                       # Artisan CLI
├── composer.json                 # PHP dependencies
├── package.json                  # Node dependencies
├── vite.config.js                # Vite configuration
└── README.md                     # This file
```

---

## 📚 Usage

### Default User Credentials

After running `php artisan migrate --seed`:

```
Email: user_adm@user_adm.com
Password: 1352@765@1452
```

**IMPORTANT:** Change this password in production!

### Accessing the Application

1. **Home/Landing Page**: http://localhost:8000
   - Modern welcome page with features showcase
   - Theme switcher in navbar
   - Login/Register buttons

2. **Login**: http://localhost:8000/login
   - Modern login form
   - Password show/hide toggle
   - Session expiration handling

3. **Register**: http://localhost:8000/register
   - Real-time password strength validation
   - Password requirements checklist
   - Password match indicator

4. **Dashboard** (after login): http://localhost:8000/dashboard
   - Modern stats cards
   - Gradient header
   - Quick actions

5. **Leads Management**: http://localhost:8000/leads
   - Full CRUD operations
   - Search and filters
   - Export to CSV
   - Modern modals

### Theme Switcher

The theme switcher appears in:
- **Navbar** on welcome page
- **Top-right corner** on login/register pages
- **Topbar** on dashboard and authenticated pages

**Three modes:**
1. 🌞 **Light** - Traditional light theme
2. 🌙 **Dark** - Dark theme for reduced eye strain
3. ⚙️ **System** - Follows OS preference

**How it works:**
- Click the theme button to cycle through modes
- Preference is saved in `localStorage` as `app.theme`
- Persists across all pages and sessions
- Smooth transitions between themes

### Creating a New Lead

1. Navigate to `/leads`
2. Click **"New Lead"** button
3. Fill in the form:
   - **Name** (required) - Lead/company name
   - **Team** (optional) - e.g., Sales, Marketing
   - **Status** (required) - New, In Progress, Won, Lost
   - **Email** (optional) - Contact email
   - **Phone** (optional) - Contact number
   - **Notes** (optional) - Additional information
4. Click **"Save"**
5. Lead appears in the table immediately

### Editing a Lead

1. Find the lead in the table
2. Click the **pencil icon** (Edit button)
3. Modify the fields
4. Click **"Save"**
5. Changes reflect immediately

### Deleting a Lead

1. Find the lead in the table
2. Click the **trash icon** (Delete button)
3. Confirm deletion in the modal
4. Click **"Delete"**
5. Lead is removed from the table

### Filtering Leads

- **Search bar**: Type to search by name, email, or phone (debounced)
- **Status dropdown**: Select a status to filter
- **Active filters**: Show as removable tags below the filters
- **Clear button**: Removes all active filters at once

### Exporting Leads

1. Click **"Export"** button
2. CSV file downloads automatically with all leads
3. File name: `leads_YYYY-MM-DD.csv`

---

## 🎨 Theme System

### How the Theme System Works

The starter kit includes a sophisticated three-mode theme system:

**1. LocalStorage Key**: `app.theme`
   - Stores user preference: `'light'`, `'dark'`, or `'system'`

**2. Theme Detection**:
   ```javascript
   const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
   const currentTheme = localStorage.getItem('app.theme') || 'system';

   const resolvedTheme = currentTheme === 'system'
       ? (prefersDark.matches ? 'dark' : 'light')
       : currentTheme;

   document.documentElement.setAttribute('data-bs-theme', resolvedTheme);
   ```

**3. CSS Implementation**:
   ```css
   /* Light mode (default) */
   .card {
       background: #ffffff;
       color: #212529;
   }

   /* Dark mode override */
   [data-bs-theme="dark"] .card {
       background: rgba(255, 255, 255, 0.05);
       color: rgba(255, 255, 255, 0.95);
   }
   ```

### Theme Persistence

- Saved in `localStorage` across all pages
- Works on:
  - Welcome page
  - Login page
  - Register page
  - Dashboard
  - Leads page
  - All authenticated pages

### Customizing Theme Colors

**Bootstrap Variables** (`resources/scss/app.scss`):

```scss
// Override Bootstrap variables BEFORE importing
$primary: #0d6efd;
$secondary: #6c757d;
$success: #198754;
$danger: #dc3545;

// Dark mode specific (using CSS custom properties)
[data-bs-theme="dark"] {
    --bs-primary-rgb: 77, 148, 255; // Lighter blue for dark mode
    --bs-body-bg: #1a1d29;
    --bs-body-color: #f8f9fa;
}

@import 'bootstrap/scss/bootstrap';
```

**After changes:**
```bash
npm run build  # Rebuild assets
```

### Adding Dark Mode to New Components

**Best practices:**

```css
/* 1. Use CSS custom properties */
.my-component {
    background: var(--bs-body-bg);
    color: var(--bs-body-color);
    border-color: var(--bs-border-color);
}

/* 2. Add dark-specific overrides when needed */
[data-bs-theme="dark"] .my-component {
    background: rgba(255, 255, 255, 0.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

/* 3. Use semi-transparent colors with CSS variables */
.my-component-highlight {
    background: rgba(var(--bs-primary-rgb), 0.1);
    border: 1px solid rgba(var(--bs-primary-rgb), 0.3);
}
```

---

## 🔐 Authentication

### Registration Process

**Password Requirements** (enforced client-side and server-side):
- ✅ Minimum 12 characters
- ✅ At least 1 uppercase letter (A-Z)
- ✅ At least 1 number (0-9)
- ✅ At least 2 special characters (!@#$%^&*...)

**Real-time Validation:**
- Password strength bar (Very Weak → Strong)
- Visual checklist that turns green when requirements are met
- Password match indicator

**After Registration:**
- User is automatically logged in
- Redirected to dashboard
- Email verification (if enabled in config)

### Login Process

**Features:**
- Remember me checkbox
- Forgot password link
- Session management
- Show/hide password toggle
- CSRF protection

**Session Expiration:**
- Default: 120 minutes (2 hours)
- Configurable in `config/session.php`
- User sees: "Your session has expired. Please sign in again."
- Redirected to login page

### Password Reset

1. Click **"Forgot password?"** on login page
2. Enter email address
3. Receive reset link via email
4. Click link and set new password
5. Same strength requirements apply

### Profile Management

**Available in Dashboard:**
- Update name and email
- Change password
- Upload profile photo (if enabled)
- Two-factor authentication (if enabled)
- Browser sessions management
- Delete account

### API Tokens (Sanctum)

**If you need API access:**

1. Go to **Dashboard** → **API Tokens**
2. Click **"Create New Token"**
3. Enter token name and select permissions
4. Copy the token (shown only once!)
5. Use in API requests:
   ```bash
   curl -H "Authorization: Bearer YOUR_TOKEN" \
        http://localhost:8000/api/user
   ```

---

## 🚨 Exception Handling

### How It Works

**File**: `bootstrap/app.php` (lines 22-116)

The starter kit includes intelligent exception handling that:
1. Detects the exception type
2. Checks if user is authenticated
3. Shows appropriate message
4. Redirects to correct page

### Exception Flow

```
Exception Occurs
    ↓
Is user authenticated?
    ↓
├─ NO → Redirect to /welcome with error message
│
└─ YES → Handle based on exception:
          - 404: Show standard 404 page
          - 403: Redirect to dashboard with error
          - Session expired: Redirect to /login
          - CSRF: Redirect to /login
```

### Testing Exception Handling

**Test Unauthenticated 404:**
```
Visit: http://localhost:8000/page-that-does-not-exist
Expected: Redirect to / with message "The page you are looking for could not be found."
```

**Test Session Expiration:**
```
1. Login to dashboard
2. Clear cookies or wait for session timeout
3. Try to access /dashboard
4. Expected: Redirect to /login with "Your session has expired. Please sign in again."
```

**Test CSRF Token:**
```
1. Open login form
2. Wait 2+ hours (or manually clear _token)
3. Try to submit
4. Expected: Redirect to /welcome with "Your session has expired. Please try again."
```

### Customizing Error Messages

**Edit** `bootstrap/app.php`:

```php
// Example: Change 404 message
$exceptions->render(function (NotFoundHttpException $e, Request $request) {
    if (!auth()->check()) {
        return redirect()
            ->route('welcome')
            ->with('error', 'YOUR CUSTOM MESSAGE HERE');
    }
    return null;
});
```

### Production Error Pages

**In production** (`APP_ENV=production`, `APP_DEBUG=false`):
- Users see friendly error messages
- Technical details are hidden
- Errors are logged to `storage/logs/laravel.log`
- Optionally send to error tracking service (Sentry, Bugsnag, etc.)

---

## 🎨 Customization

### Changing the Logo

**Replace the logo image:**
```bash
public/img/_0403eb0b-de95-4131-87cd-5c705ae95535.png
```

**Update in views:**
```blade
{{-- resources/views/components/authentication-card-logo.blade.php --}}
<img src="{{ asset('img/your-new-logo.png') }}" alt="Logo">
```

### Customizing Colors

**Edit** `resources/scss/app.scss`:

```scss
// Define your brand colors BEFORE importing Bootstrap
$primary: #your-color;
$secondary: #your-color;
$success: #your-color;
$danger: #your-color;
$info: #your-color;
$warning: #your-color;

// Import Bootstrap
@import 'bootstrap/scss/bootstrap';

// Add custom styles AFTER importing
.your-custom-class {
    // Your styles
}
```

**Rebuild:**
```bash
npm run build
```

### Adding a New Page

**1. Create route** (`routes/web.php`):
```php
Route::get('/my-page', function () {
    return view('my-page');
})->name('my-page');
```

**2. Create view** (`resources/views/my-page.blade.php`):
```blade
<x-app-layout>
    <x-slot name="header">
        <h2>My New Page</h2>
    </x-slot>

    <div class="py-4">
        <div class="container">
            <div class="card">
                <div class="card-body">
                    Your content here
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

**3. Add to navigation** (`resources/views/navigation-menu.blade.php`):
```blade
<li>
    <a href="{{ route('my-page') }}" class="nav-link">
        My Page
    </a>
</li>
```

### Creating a Livewire Component

**1. Generate component:**
```bash
php artisan make:livewire MyComponent
```

**2. Creates:**
- `app/Livewire/MyComponent.php` - Logic
- `resources/views/livewire/my-component.blade.php` - View

**3. Use in a view:**
```blade
<livewire:my-component />
```

**Example component:**
```php
// app/Livewire/MyComponent.php
namespace App\Livewire;

use Livewire\Component;

class MyComponent extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return view('livewire.my-component');
    }
}
```

```blade
{{-- resources/views/livewire/my-component.blade.php --}}
<div>
    <h3>Count: {{ $count }}</h3>
    <button wire:click="increment" class="btn btn-primary">
        Increment
    </button>
</div>
```

### Customizing the Welcome Page

**File**: `resources/views/welcome.blade.php`

**Sections you can edit:**
- Hero section (lines ~102-140)
- About section (lines ~145-200)
- Features section (lines ~205-290)
- Technology section (lines ~295-360)
- Testimonials (lines ~365-420)
- Contact section (lines ~425-480)
- Footer (lines ~485-540)

**Example: Change hero title:**
```blade
<h1 class="hero-title">
    Your Custom
    <span class="hero-title-gradient">Amazing Title</span>
</h1>
```

### Adding Custom Middleware

**1. Create middleware:**
```bash
php artisan make:middleware MyMiddleware
```

**2. Edit** `app/Http/Middleware/MyMiddleware.php`:
```php
public function handle($request, Closure $next)
{
    // Your logic here

    return $next($request);
}
```

**3. Register in** `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'my-middleware' => \App\Http\Middleware\MyMiddleware::class,
    ]);
})
```

**4. Use in routes:**
```php
Route::get('/my-route', function () {
    //
})->middleware('my-middleware');
```

---

## 💻 Development

### Available Commands

**PHP/Laravel:**
```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Fresh migrations with seeds
php artisan migrate:fresh --seed

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate IDE helper (if installed)
php artisan ide-helper:generate
php artisan ide-helper:models

# List all routes
php artisan route:list
```

**Node/NPM:**
```bash
# Development build with HMR
npm run dev

# Production build
npm run build

# Watch for changes
npm run watch
```

**Composer:**
```bash
# Run all dev services (artisan serve + queue + vite)
composer run dev

# Run tests (if configured)
composer test

# Update dependencies
composer update

# Dump autoload
composer dump-autoload
```

### Running Tests (Optional)

**This starter kit includes Pest** but tests are optional:

```bash
# Run all tests
php artisan test

# OR with Pest directly
./vendor/bin/pest

# Run specific test
php artisan test --filter=ExampleTest

# Run with coverage (requires xdebug)
php artisan test --coverage
```

**Writing tests:**
```php
// tests/Feature/ExampleTest.php
test('welcome page works', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Laravel');
});
```

### Debugging

**Enable debug mode** (`.env`):
```env
APP_DEBUG=true
```

**View logs:**
```bash
tail -f storage/logs/laravel.log
```

**Common debugging tools:**
```php
// Dump and die
dd($variable);

// Dump
dump($variable);

// Laravel debugbar (install separately)
composer require barryvdh/laravel-debugbar --dev

// Ray (install separately)
composer require spatie/laravel-ray --dev
```

### Database Management

**Tinker (Laravel REPL):**
```bash
php artisan tinker

# Examples:
>>> User::count()
>>> Lead::factory()->count(10)->create()
>>> DB::table('leads')->where('status', 'new')->count()
```

**Database commands:**
```bash
# Show database info
php artisan db:show

# Show tables
php artisan db:table leads

# Wipe database (WARNING: Deletes all data)
php artisan db:wipe
```

### Performance Optimization

**Development:**
```bash
# Route caching (faster route resolution)
php artisan route:cache

# Config caching
php artisan config:cache

# View caching
php artisan view:cache
```

**Production:**
```bash
# Optimize everything
php artisan optimize

# OR individually:
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Build production assets
npm run build
```

---

## 🚀 Production Deployment

### Pre-Deployment Checklist

- [ ] Change `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Update `APP_URL` to your domain
- [ ] Change default admin password
- [ ] Configure real database credentials
- [ ] Set up email service (SMTP, Mailgun, etc.)
- [ ] Configure queue driver (Redis, database)
- [ ] Set up cache driver (Redis recommended)
- [ ] Enable HTTPS/SSL
- [ ] Run `php artisan optimize`
- [ ] Run `npm run build`
- [ ] Set proper file permissions
- [ ] Configure supervisor for queues
- [ ] Set up backups
- [ ] Configure error monitoring (Sentry, Bugsnag)

### Environment Configuration

**Minimum production `.env`:**

```env
APP_NAME="Your App Name"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_secure_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Server Requirements

**Recommended server setup:**
- Ubuntu 22.04 LTS or similar
- Nginx or Apache
- PHP 8.2+ with required extensions
- PostgreSQL 13+ or MySQL 8+
- Redis 6+ for caching/queues
- Composer installed globally
- Node.js 20 LTS
- Supervisor for queue workers
- SSL certificate (Let's Encrypt)

### Deployment Steps

**1. Clone repository on server:**
```bash
cd /var/www
git clone https://github.com/yourusername/your-repo.git yourapp
cd yourapp
```

**2. Install dependencies:**
```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

**3. Set permissions:**
```bash
sudo chown -R www-data:www-data /var/www/yourapp
sudo chmod -R 755 /var/www/yourapp
sudo chmod -R 775 /var/www/yourapp/storage
sudo chmod -R 775 /var/www/yourapp/bootstrap/cache
```

**4. Environment setup:**
```bash
cp .env.example .env
nano .env  # Edit with your production values
php artisan key:generate
```

**5. Run migrations:**
```bash
php artisan migrate --force
```

**6. Optimize:**
```bash
php artisan optimize
php artisan storage:link
```

**7. Configure web server:**

**Nginx example** (`/etc/nginx/sites-available/yourapp`):
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/yourapp/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
}
```

**8. Set up queue worker** (Supervisor):

**File**: `/etc/supervisor/conf.d/yourapp-worker.conf`
```ini
[program:yourapp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/yourapp/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/yourapp/storage/logs/worker.log
stopwaitsecs=3600
```

**Apply:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start yourapp-worker:*
```

**9. Set up scheduled tasks** (Cron):
```bash
crontab -e
```

Add:
```
* * * * * cd /var/www/yourapp && php artisan schedule:run >> /dev/null 2>&1
```

**10. Enable SSL** (Let's Encrypt):
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### Continuous Deployment

**Using Git hooks** (example):

**File**: `.git/hooks/post-receive`
```bash
#!/bin/bash
cd /var/www/yourapp
git --work-tree=/var/www/yourapp --git-dir=/var/www/yourapp/.git pull
composer install --optimize-autoloader --no-dev
npm install
npm run build
php artisan migrate --force
php artisan optimize
php artisan queue:restart
sudo supervisorctl restart yourapp-worker:*
```

**Or use deployment tools:**
- Laravel Forge
- Laravel Envoyer
- Deployer
- GitHub Actions
- GitLab CI/CD

### Monitoring & Maintenance

**Log monitoring:**
```bash
tail -f storage/logs/laravel.log
```

**Queue monitoring:**
```bash
php artisan queue:monitor
```

**Schedule testing:**
```bash
php artisan schedule:test
```

**Health check endpoint:**
```
https://yourdomain.com/up
```

---

## 🔧 Troubleshooting

### Common Issues

#### Issue: "Class 'Livewire\Component' not found"
**Solution:**
```bash
composer dump-autoload
php artisan optimize:clear
```

#### Issue: "SQLSTATE[HY000] [2002] Connection refused"
**Solution:**
- Check database is running
- Verify `.env` database credentials
- Test connection: `php artisan db:show`

#### Issue: "Mix version hash not found"
**Solution:**
```bash
npm run build
php artisan view:clear
```

#### Issue: "The stream or file could not be opened"
**Solution:**
```bash
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

#### Issue: "419 Page Expired" on form submit
**Solution:**
- Clear browser cache
- Check CSRF token in form: `@csrf`
- Session driver working correctly
- Cookies enabled in browser

#### Issue: Dark mode not persisting
**Solution:**
- Clear browser cache and cookies
- Check browser console for JavaScript errors
- Verify `resources/js/app.js` compiled correctly
- Run `npm run build`

#### Issue: "Route [welcome] not defined"
**Solution:**
```bash
# Make sure routes/web.php has:
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

# Then clear cache:
php artisan route:clear
```

#### Issue: Vite not working / Assets not loading
**Solution:**
```bash
# Kill any running Vite processes
pkill -f vite

# Remove and reinstall
rm -rf node_modules package-lock.json
npm install

# Rebuild
npm run build

# For development:
npm run dev
```

### Debug Mode

**Enable detailed errors** (development only):

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

**Check logs:**
```bash
tail -f storage/logs/laravel.log
```

### Cache Issues

**Clear all caches:**
```bash
php artisan optimize:clear

# Or individually:
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
```

### Permission Issues (Linux/Mac)

**Fix storage and cache permissions:**
```bash
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Database Issues

**Reset database completely:**
```bash
php artisan migrate:fresh --seed
```

**Check migrations status:**
```bash
php artisan migrate:status
```

**Show database info:**
```bash
php artisan db:show
```

### Performance Issues

**Optimize for production:**
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

**Clear optimizations (development):**
```bash
php artisan optimize:clear
composer dump-autoload
```

---

## ❓ FAQ

### General Questions

**Q: Is this starter kit free to use?**
A: Yes, it's open-source under the MIT license. Use it for personal or commercial projects.

**Q: Can I use MySQL instead of PostgreSQL?**
A: Yes, just change `DB_CONNECTION=mysql` in `.env` and update database credentials.

**Q: Does this work with Tailwind CSS?**
A: This starter kit uses Bootstrap 5. For Tailwind, you would need to remove Bootstrap and install Tailwind separately.

**Q: Can I remove the Leads example?**
A: Yes! Delete:
- `app/Livewire/LeadsTable.php`
- `app/Models/Lead.php`
- `resources/views/livewire/leads-table.blade.php`
- `database/migrations/*_create_leads_table.php`
- Route in `routes/web.php`

**Q: Is this production-ready?**
A: Yes, but you should:
- Change default admin password
- Review security settings
- Set up proper error monitoring
- Configure backups
- Test thoroughly

**Q: Can I use this for API-only applications?**
A: Yes, Laravel Sanctum is already configured. Remove web views and use API routes in `routes/api.php`.

### Technical Questions

**Q: Why UUID instead of auto-increment IDs?**
A: UUIDs provide:
- Better security (non-sequential, unpredictable)
- Easier data merging across databases
- No ID conflicts in distributed systems
- Simpler offline data generation

**Q: How do I change the session timeout?**
A: Edit `config/session.php`:
```php
'lifetime' => 120, // minutes
```

**Q: Can I add two-factor authentication?**
A: Yes, Jetstream includes 2FA. Enable in `config/fortify.php`:
```php
Features::twoFactorAuthentication([
    'confirm' => true,
    'confirmPassword' => true,
]),
```

**Q: How do I add more authentication features?**
A: Check Jetstream docs: https://jetstream.laravel.com/

**Q: Where are passwords hashed?**
A: Laravel uses bcrypt by default. See `config/hashing.php` to change.

**Q: How do I add email verification?**
A: Enable in Jetstream config and add `verified` middleware to routes.

**Q: Can I use this with Docker?**
A: Yes! Create a `Dockerfile` and `docker-compose.yml`. Example in Laravel docs.

**Q: How do I add more languages?**
A: Laravel supports i18n. Add language files in `lang/` directory.

### Customization Questions

**Q: How do I change the primary color?**
A: Edit `resources/scss/app.scss`:
```scss
$primary: #your-color;
```
Then run `npm run build`.

**Q: Can I add more themes besides light/dark?**
A: Yes, but requires custom JavaScript and CSS. The current system uses Bootstrap's `data-bs-theme` attribute.

**Q: How do I add a custom font?**
A: Add font in `resources/scss/app.scss`:
```scss
@import url('https://fonts.googleapis.com/css2?family=Your+Font&display=swap');

$font-family-base: 'Your Font', sans-serif;
```

**Q: Can I change the layout?**
A: Yes, edit `resources/views/layouts/app.blade.php` and `guest.blade.php`.

### Deployment Questions

**Q: What hosting do you recommend?**
A: Options:
- **Beginner**: Laravel Forge + DigitalOcean
- **Managed**: Laravel Vapor (serverless)
- **Self-managed**: AWS, DigitalOcean, Linode
- **Simple**: Shared hosting with SSH access

**Q: Do I need Redis?**
A: Not required for basic usage, but recommended for production (caching, queues, sessions).

**Q: How do I set up automated backups?**
A: Use Laravel Backup package:
```bash
composer require spatie/laravel-backup
```

**Q: Can I use this on shared hosting?**
A: Yes, if the hosting supports:
- PHP 8.2+
- Composer
- Database access
- SSH access (recommended)

**Q: How do I enable HTTPS?**
A: Use Let's Encrypt with Certbot (free SSL certificates).

---

## 🤝 Contributing

We welcome contributions! Here's how you can help:

### Reporting Bugs

**Before submitting:**
1. Check existing issues
2. Test on latest version
3. Provide clear reproduction steps

**Submit an issue with:**
- Laravel version
- PHP version
- Browser (if UI bug)
- Steps to reproduce
- Expected vs actual behavior
- Error messages/screenshots

### Suggesting Features

**Create an issue with:**
- Clear feature description
- Use case/benefits
- Proposed implementation (optional)

### Pull Requests

**Process:**
1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes
4. Test thoroughly
5. Commit: `git commit -m 'Add amazing feature'`
6. Push: `git push origin feature/amazing-feature`
7. Open a Pull Request

**Guidelines:**
- Follow existing code style
- Update README if needed
- Add tests if applicable
- Keep changes focused

### Code Style

**PHP:**
- Follow PSR-12
- Use meaningful variable names
- Add comments for complex logic

**JavaScript:**
- Use ES6+ features
- Use `const`/`let` (not `var`)
- Add comments for clarity

**CSS/SCSS:**
- Use Bootstrap utilities when possible
- Keep custom CSS minimal
- Organize styles logically

---

## 📄 License

This project is open-source software licensed under the [MIT License](LICENSE).

**What this means:**
- ✅ Use for personal projects
- ✅ Use for commercial projects
- ✅ Modify as needed
- ✅ Distribute freely
- ❌ No warranty provided
- ❌ Author not liable for issues

### Credits

**Created by:** Marcio Alessandro Xavier Neto

**Built with assistance from:**
- Codex GPT-5
- Gemini 2.5 PRO / 3
- Claude Sonnet 4.5

**Open-source packages used:**
- Laravel Framework
- Laravel Jetstream
- Laravel Livewire
- Bootstrap
- Alpine.js
- And many others (see `composer.json` and `package.json`)

**Special thanks to:**
- Laravel community
- Bootstrap team
- Livewire team
- All contributors

---

## 📞 Support & Contact

### Documentation

- **Laravel**: https://laravel.com/docs
- **Jetstream**: https://jetstream.laravel.com
- **Livewire**: https://livewire.laravel.com
- **Bootstrap**: https://getbootstrap.com/docs

### Community

- **Laravel Discord**: https://discord.gg/laravel
- **Laracasts**: https://laracasts.com
- **Stack Overflow**: Tag your questions with `laravel`, `livewire`, `bootstrap`

### Issues & Questions

- **Bug Reports**: Open an issue on GitHub
- **Feature Requests**: Open an issue with [Feature Request] tag
- **Questions**: Use GitHub Discussions

### Stay Updated

- ⭐ Star this repo to follow updates
- 👁️ Watch for new releases
- 🍴 Fork to create your own version

---

## 🎉 Thank You!

Thank you for using this Laravel Jetstream Livewire Bootstrap Starter Kit!

If you find it helpful:
- ⭐ **Star the repository**
- 🐛 **Report bugs** to help improve it
- 💡 **Suggest features** you'd like to see
- 🤝 **Contribute** if you can
- 📣 **Share** with others who might benefit

**Happy coding!** 🚀

---

<p align="center">
  <sub>Built with ❤️ using Laravel, Jetstream, Livewire, and Bootstrap</sub>
</p>
