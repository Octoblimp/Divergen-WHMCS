# OpenWHM - Divergen Hosting Management System

A complete, WHMCS-like billing and hosting management platform built with PHP. OpenWHM provides all the features you need to run a web hosting business, including client management, invoicing, support tickets, domain registration, and hosting provisioning.

## Features

### Core Features
- **Client Management** - Full client lifecycle management with profiles, credit, and activity logging
- **Billing & Invoicing** - Automated invoicing, multiple payment gateways, recurring billing
- **Support Tickets** - Departmental ticketing system with priorities and statuses
- **Order Management** - Shopping cart, checkout, order processing and fraud prevention
- **Product Management** - Flexible product configuration with multiple billing cycles
- **Frontend Website** - Beautiful public-facing website with product showcase and shopping cart

### Integrations
- **NameSilo Registrar** - Full domain registration, transfers, renewals, DNS management
- **HestiaCP Server Module** - Automated hosting provisioning, suspension, termination
- **Payment Gateways** - PayPal, Stripe, Bank Transfer (extensible)

### Extension System
- Hook-based extensibility with 40+ hooks
- Easy plugin development framework
- Settings management for extensions

## Requirements

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- cURL extension
- OpenSSL extension

## Installation

1. **Upload Files**
   Upload all files to your web server.

2. **Configure Database**
   - Create a MySQL database
   - Import `database/schema.sql`
   ```bash
   mysql -u username -p database_name < database/schema.sql
   ```

3. **Configure Application**
   Edit `config/config.php` with your settings:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_database');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   
   define('BASE_URL', 'https://divergen.io');
   define('COMPANY_NAME', 'Divergen Hosting');
   
   // NameSilo API
   define('NAMESILO_API_KEY', 'your-api-key');
   
   // HestiaCP
   define('HESTIACP_HOST', 'https://your-server:8083');
   define('HESTIACP_USER', 'admin');
   define('HESTIACP_PASSWORD', 'your-password');
   ```

4. **Configure Web Server**
   
   **Apache (.htaccess)**
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [QSA,L]
   ```

5. **Access Admin Panel**
   - URL: `https://yourdomain.com/admin`
   - Default: `admin@example.com` / `admin123`
   - **Change this immediately!**

## Directory Structure

```
├── config/                 # Configuration
├── controllers/            # Controllers
│   ├── Admin/             # Admin panel
│   ├── Client/            # Client area
│   ├── HomeController     # Frontend
│   └── CartController     # Shopping cart
├── core/                   # Framework classes
├── database/              # SQL schema
├── extensions/            # Extensions/plugins
├── models/                # Data models
├── modules/
│   ├── gateways/         # Payment gateways (PayPal, Stripe, Bank Transfer)
│   ├── registrars/       # Domain registrars (NameSilo)
│   └── servers/          # Hosting modules (HestiaCP)
├── templates/
│   ├── admin/            # Admin views
│   ├── client/           # Client area views
│   └── frontend/         # Public website
└── index.php              # Entry point
```

## URLs

- **Frontend**: `/` - Public website, hosting plans, domains
- **Cart**: `/cart` - Shopping cart and checkout
- **Client Area**: `/client` - Client dashboard, services, invoices
- **Admin Panel**: `/admin` - Full administration

## Payment Gateways

- **PayPal** - Full PayPal checkout integration
- **Stripe** - Stripe Checkout Sessions
- **Bank Transfer** - Manual offline payments

## Hooks Available

```
ClientAdd, ClientEdit, ClientDelete, ClientLogin
OrderCreated, OrderAccepted, OrderCancelled, OrderFraud
InvoiceCreated, InvoicePaid, InvoiceCancelled
ServiceCreate, ServiceSuspend, ServiceUnsuspend, ServiceTerminate
TicketOpen, TicketReply, TicketClose
DomainRegister, DomainRenew, DomainTransfer
```

## Creating Extensions

1. Create `extensions/your-extension/`
2. Add `extension.json`:
   ```json
   {
       "name": "Your Extension",
       "version": "1.0.0",
       "author": "Your Name"
   }
   ```
3. Add main PHP file

## License

Exclusively for Divergen.io Hosting
