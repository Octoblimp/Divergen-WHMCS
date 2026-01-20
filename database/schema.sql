-- OpenWHM Database Schema
-- Version 1.0.0

SET FOREIGN_KEY_CHECKS = 0;

-- Admins Table
CREATE TABLE IF NOT EXISTS `owh_admins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `role` ENUM('super_admin', 'admin', 'support') DEFAULT 'admin',
    `two_factor_secret` VARCHAR(255) NULL,
    `two_factor_enabled` TINYINT(1) DEFAULT 0,
    `last_login` DATETIME NULL,
    `last_login_ip` VARCHAR(45) NULL,
    `active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clients Table
CREATE TABLE IF NOT EXISTS `owh_clients` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `firstname` VARCHAR(100) NOT NULL,
    `lastname` VARCHAR(100) NOT NULL,
    `company` VARCHAR(255) NULL,
    `address1` VARCHAR(255) NULL,
    `address2` VARCHAR(255) NULL,
    `city` VARCHAR(100) NULL,
    `state` VARCHAR(100) NULL,
    `postcode` VARCHAR(20) NULL,
    `country` CHAR(2) DEFAULT 'US',
    `phone` VARCHAR(50) NULL,
    `currency` CHAR(3) DEFAULT 'USD',
    `language` VARCHAR(10) DEFAULT 'en',
    `credit` DECIMAL(10,2) DEFAULT 0.00,
    `tax_exempt` TINYINT(1) DEFAULT 0,
    `tax_id` VARCHAR(50) NULL,
    `notes` TEXT NULL,
    `status` ENUM('active', 'inactive', 'closed') DEFAULT 'active',
    `email_verified` TINYINT(1) DEFAULT 0,
    `email_verified_at` DATETIME NULL,
    `two_factor_secret` VARCHAR(255) NULL,
    `two_factor_enabled` TINYINT(1) DEFAULT 0,
    `last_login` DATETIME NULL,
    `last_login_ip` VARCHAR(45) NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_email` (`email`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Groups Table
CREATE TABLE IF NOT EXISTS `owh_product_groups` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `headline` VARCHAR(255) NULL,
    `tagline` TEXT NULL,
    `description` TEXT NULL,
    `features` TEXT NULL,
    `order_form` VARCHAR(100) DEFAULT 'standard',
    `sort_order` INT DEFAULT 0,
    `hidden` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products Table
CREATE TABLE IF NOT EXISTS `owh_products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group_id` INT UNSIGNED NOT NULL,
    `type` ENUM('hosting', 'domain', 'addon', 'other') DEFAULT 'hosting',
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `features` TEXT NULL,
    `welcome_email` INT UNSIGNED NULL,
    `stock_control` TINYINT(1) DEFAULT 0,
    `stock_quantity` INT DEFAULT 0,
    `pay_type` ENUM('free', 'onetime', 'recurring') DEFAULT 'recurring',
    `price_monthly` DECIMAL(10,2) DEFAULT 0.00,
    `price_quarterly` DECIMAL(10,2) DEFAULT 0.00,
    `price_semiannually` DECIMAL(10,2) DEFAULT 0.00,
    `price_annually` DECIMAL(10,2) DEFAULT 0.00,
    `price_biennially` DECIMAL(10,2) DEFAULT 0.00,
    `price_triennially` DECIMAL(10,2) DEFAULT 0.00,
    `setup_fee` DECIMAL(10,2) DEFAULT 0.00,
    `module` VARCHAR(100) NULL,
    `server_group_id` INT UNSIGNED NULL,
    `config_option1` TEXT NULL,
    `config_option2` TEXT NULL,
    `config_option3` TEXT NULL,
    `config_option4` TEXT NULL,
    `config_option5` TEXT NULL,
    `config_option6` TEXT NULL,
    `custom_fields` JSON NULL,
    `sort_order` INT DEFAULT 0,
    `hidden` TINYINT(1) DEFAULT 0,
    `retired` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    FOREIGN KEY (`group_id`) REFERENCES `owh_product_groups`(`id`) ON DELETE CASCADE,
    INDEX `idx_group` (`group_id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Servers Table
CREATE TABLE IF NOT EXISTS `owh_servers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `hostname` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `assigned_ips` TEXT NULL,
    `port` INT DEFAULT 2087,
    `secure` TINYINT(1) DEFAULT 1,
    `username` VARCHAR(255) NULL,
    `password` TEXT NULL,
    `access_hash` TEXT NULL,
    `module` VARCHAR(100) NOT NULL,
    `active` TINYINT(1) DEFAULT 1,
    `disabled` TINYINT(1) DEFAULT 0,
    `max_accounts` INT DEFAULT 0,
    `monthly_cost` DECIMAL(10,2) DEFAULT 0.00,
    `nameserver1` VARCHAR(255) NULL,
    `nameserver2` VARCHAR(255) NULL,
    `nameserver3` VARCHAR(255) NULL,
    `nameserver4` VARCHAR(255) NULL,
    `nameserver1_ip` VARCHAR(45) NULL,
    `nameserver2_ip` VARCHAR(45) NULL,
    `nameserver3_ip` VARCHAR(45) NULL,
    `nameserver4_ip` VARCHAR(45) NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_module` (`module`),
    INDEX `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Server Groups Table
CREATE TABLE IF NOT EXISTS `owh_server_groups` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `fill_type` ENUM('fill', 'round_robin') DEFAULT 'fill',
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Server Group Relations Table
CREATE TABLE IF NOT EXISTS `owh_server_group_relations` (
    `server_id` INT UNSIGNED NOT NULL,
    `group_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`server_id`, `group_id`),
    FOREIGN KEY (`server_id`) REFERENCES `owh_servers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`group_id`) REFERENCES `owh_server_groups`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services (Hosting Accounts) Table
CREATE TABLE IF NOT EXISTS `owh_services` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT UNSIGNED NOT NULL,
    `order_id` INT UNSIGNED NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `server_id` INT UNSIGNED NULL,
    `domain` VARCHAR(255) NULL,
    `username` VARCHAR(100) NULL,
    `password` TEXT NULL,
    `billing_cycle` ENUM('monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially', 'onetime', 'free') DEFAULT 'monthly',
    `amount` DECIMAL(10,2) DEFAULT 0.00,
    `first_payment_amount` DECIMAL(10,2) DEFAULT 0.00,
    `registration_date` DATE NULL,
    `next_due_date` DATE NULL,
    `next_invoice_date` DATE NULL,
    `termination_date` DATE NULL,
    `completed_date` DATE NULL,
    `status` ENUM('pending', 'active', 'suspended', 'terminated', 'cancelled', 'fraud') DEFAULT 'pending',
    `suspend_reason` VARCHAR(255) NULL,
    `override_suspend` TINYINT(1) DEFAULT 0,
    `override_auto_terminate` TINYINT(1) DEFAULT 0,
    `dedicated_ip` VARCHAR(45) NULL,
    `assigned_ips` TEXT NULL,
    `disk_usage` BIGINT DEFAULT 0,
    `disk_limit` BIGINT DEFAULT 0,
    `bandwidth_usage` BIGINT DEFAULT 0,
    `bandwidth_limit` BIGINT DEFAULT 0,
    `last_update` DATETIME NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    FOREIGN KEY (`client_id`) REFERENCES `owh_clients`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `owh_products`(`id`),
    FOREIGN KEY (`server_id`) REFERENCES `owh_servers`(`id`) ON DELETE SET NULL,
    INDEX `idx_client` (`client_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_next_due` (`next_due_date`),
    INDEX `idx_domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Domains Table
CREATE TABLE IF NOT EXISTS `owh_domains` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT UNSIGNED NOT NULL,
    `order_id` INT UNSIGNED NULL,
    `domain` VARCHAR(255) NOT NULL,
    `registrar` VARCHAR(100) NOT NULL,
    `registration_date` DATE NULL,
    `expiry_date` DATE NULL,
    `next_due_date` DATE NULL,
    `next_invoice_date` DATE NULL,
    `registration_period` INT DEFAULT 1,
    `recurring_amount` DECIMAL(10,2) DEFAULT 0.00,
    `first_payment_amount` DECIMAL(10,2) DEFAULT 0.00,
    `status` ENUM('pending', 'pending_transfer', 'active', 'expired', 'cancelled', 'fraud', 'transferred_away') DEFAULT 'pending',
    `is_premium` TINYINT(1) DEFAULT 0,
    `id_protection` TINYINT(1) DEFAULT 0,
    `dns_management` TINYINT(1) DEFAULT 0,
    `email_forwarding` TINYINT(1) DEFAULT 0,
    `do_not_renew` TINYINT(1) DEFAULT 0,
    `nameserver1` VARCHAR(255) NULL,
    `nameserver2` VARCHAR(255) NULL,
    `nameserver3` VARCHAR(255) NULL,
    `nameserver4` VARCHAR(255) NULL,
    `nameserver5` VARCHAR(255) NULL,
    `registrant_id` INT UNSIGNED NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    FOREIGN KEY (`client_id`) REFERENCES `owh_clients`(`id`) ON DELETE CASCADE,
    INDEX `idx_client` (`client_id`),
    INDEX `idx_domain` (`domain`),
    INDEX `idx_expiry` (`expiry_date`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Domain Pricing Table
CREATE TABLE IF NOT EXISTS `owh_domain_pricing` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tld` VARCHAR(50) NOT NULL,
    `registrar` VARCHAR(100) NOT NULL,
    `register_1yr` DECIMAL(10,2) NULL,
    `register_2yr` DECIMAL(10,2) NULL,
    `register_3yr` DECIMAL(10,2) NULL,
    `register_4yr` DECIMAL(10,2) NULL,
    `register_5yr` DECIMAL(10,2) NULL,
    `register_6yr` DECIMAL(10,2) NULL,
    `register_7yr` DECIMAL(10,2) NULL,
    `register_8yr` DECIMAL(10,2) NULL,
    `register_9yr` DECIMAL(10,2) NULL,
    `register_10yr` DECIMAL(10,2) NULL,
    `renew_1yr` DECIMAL(10,2) NULL,
    `renew_2yr` DECIMAL(10,2) NULL,
    `renew_3yr` DECIMAL(10,2) NULL,
    `renew_4yr` DECIMAL(10,2) NULL,
    `renew_5yr` DECIMAL(10,2) NULL,
    `renew_6yr` DECIMAL(10,2) NULL,
    `renew_7yr` DECIMAL(10,2) NULL,
    `renew_8yr` DECIMAL(10,2) NULL,
    `renew_9yr` DECIMAL(10,2) NULL,
    `renew_10yr` DECIMAL(10,2) NULL,
    `transfer` DECIMAL(10,2) NULL,
    `grace_period` INT DEFAULT 0,
    `grace_period_fee` DECIMAL(10,2) DEFAULT 0.00,
    `redemption_period` INT DEFAULT 0,
    `redemption_fee` DECIMAL(10,2) DEFAULT 0.00,
    `epp_required` TINYINT(1) DEFAULT 1,
    `id_protection` DECIMAL(10,2) NULL,
    `dns_management` DECIMAL(10,2) NULL,
    `email_forwarding` DECIMAL(10,2) NULL,
    `enabled` TINYINT(1) DEFAULT 1,
    `auto_register` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    UNIQUE KEY `unique_tld_registrar` (`tld`, `registrar`),
    INDEX `idx_tld` (`tld`),
    INDEX `idx_enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders Table
CREATE TABLE IF NOT EXISTS `owh_orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(50) NOT NULL UNIQUE,
    `client_id` INT UNSIGNED NOT NULL,
    `invoice_id` INT UNSIGNED NULL,
    `promo_code` VARCHAR(100) NULL,
    `promo_id` INT UNSIGNED NULL,
    `amount` DECIMAL(10,2) DEFAULT 0.00,
    `payment_method` VARCHAR(100) NULL,
    `status` ENUM('pending', 'active', 'fraud', 'cancelled') DEFAULT 'pending',
    `ip_address` VARCHAR(45) NULL,
    `fraud_score` INT NULL,
    `fraud_output` TEXT NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    FOREIGN KEY (`client_id`) REFERENCES `owh_clients`(`id`) ON DELETE CASCADE,
    INDEX `idx_client` (`client_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_order_number` (`order_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoices Table
CREATE TABLE IF NOT EXISTS `owh_invoices` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
    `client_id` INT UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `due_date` DATE NOT NULL,
    `date_paid` DATETIME NULL,
    `subtotal` DECIMAL(10,2) DEFAULT 0.00,
    `tax_rate` DECIMAL(5,2) DEFAULT 0.00,
    `tax` DECIMAL(10,2) DEFAULT 0.00,
    `tax_rate2` DECIMAL(5,2) DEFAULT 0.00,
    `tax2` DECIMAL(10,2) DEFAULT 0.00,
    `credit` DECIMAL(10,2) DEFAULT 0.00,
    `total` DECIMAL(10,2) DEFAULT 0.00,
    `payment_method` VARCHAR(100) NULL,
    `status` ENUM('draft', 'unpaid', 'paid', 'cancelled', 'refunded', 'collections') DEFAULT 'unpaid',
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    FOREIGN KEY (`client_id`) REFERENCES `owh_clients`(`id`) ON DELETE CASCADE,
    INDEX `idx_client` (`client_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_due_date` (`due_date`),
    INDEX `idx_invoice_number` (`invoice_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoice Items Table
CREATE TABLE IF NOT EXISTS `owh_invoice_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoice_id` INT UNSIGNED NOT NULL,
    `type` ENUM('hosting', 'domain', 'addon', 'promo', 'other') DEFAULT 'other',
    `related_id` INT UNSIGNED NULL,
    `description` TEXT NOT NULL,
    `amount` DECIMAL(10,2) DEFAULT 0.00,
    `taxed` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME NOT NULL,
    FOREIGN KEY (`invoice_id`) REFERENCES `owh_invoices`(`id`) ON DELETE CASCADE,
    INDEX `idx_invoice` (`invoice_id`),
    INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions Table
CREATE TABLE IF NOT EXISTS `owh_transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT UNSIGNED NULL,
    `invoice_id` INT UNSIGNED NULL,
    `gateway` VARCHAR(100) NOT NULL,
    `transaction_id` VARCHAR(255) NULL,
    `description` VARCHAR(255) NULL,
    `amount_in` DECIMAL(10,2) DEFAULT 0.00,
    `amount_out` DECIMAL(10,2) DEFAULT 0.00,
    `fees` DECIMAL(10,2) DEFAULT 0.00,
    `exchange_rate` DECIMAL(10,6) DEFAULT 1.000000,
    `currency` CHAR(3) DEFAULT 'USD',
    `created_at` DATETIME NOT NULL,
    FOREIGN KEY (`client_id`) REFERENCES `owh_clients`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`invoice_id`) REFERENCES `owh_invoices`(`id`) ON DELETE SET NULL,
    INDEX `idx_client` (`client_id`),
    INDEX `idx_invoice` (`invoice_id`),
    INDEX `idx_gateway` (`gateway`),
    INDEX `idx_transaction_id` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Support Departments Table
CREATE TABLE IF NOT EXISTS `owh_support_departments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `email` VARCHAR(255) NULL,
    `clients_only` TINYINT(1) DEFAULT 0,
    `pipe_replies_only` TINYINT(1) DEFAULT 0,
    `no_auto_responder` TINYINT(1) DEFAULT 0,
    `hidden` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Support Tickets Table
CREATE TABLE IF NOT EXISTS `owh_tickets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_number` VARCHAR(50) NOT NULL UNIQUE,
    `department_id` INT UNSIGNED NOT NULL,
    `client_id` INT UNSIGNED NULL,
    `name` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `cc` VARCHAR(255) NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('open', 'answered', 'customer_reply', 'on_hold', 'in_progress', 'closed') DEFAULT 'open',
    `priority` ENUM('low', 'medium', 'high') DEFAULT 'medium',
    `admin_id` INT UNSIGNED NULL,
    `service_id` INT UNSIGNED NULL,
    `domain_id` INT UNSIGNED NULL,
    `urgent` TINYINT(1) DEFAULT 0,
    `flagged` TINYINT(1) DEFAULT 0,
    `merged_ticket_id` INT UNSIGNED NULL,
    `last_reply` DATETIME NULL,
    `last_reply_by` ENUM('client', 'admin') NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    FOREIGN KEY (`department_id`) REFERENCES `owh_support_departments`(`id`),
    FOREIGN KEY (`client_id`) REFERENCES `owh_clients`(`id`) ON DELETE SET NULL,
    INDEX `idx_client` (`client_id`),
    INDEX `idx_department` (`department_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_ticket_number` (`ticket_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ticket Replies Table
CREATE TABLE IF NOT EXISTS `owh_ticket_replies` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT UNSIGNED NOT NULL,
    `client_id` INT UNSIGNED NULL,
    `admin_id` INT UNSIGNED NULL,
    `name` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `message` TEXT NOT NULL,
    `attachment` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL,
    FOREIGN KEY (`ticket_id`) REFERENCES `owh_tickets`(`id`) ON DELETE CASCADE,
    INDEX `idx_ticket` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Promotions Table
CREATE TABLE IF NOT EXISTS `owh_promotions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(100) NOT NULL UNIQUE,
    `type` ENUM('percent', 'fixed', 'override', 'free') DEFAULT 'percent',
    `value` DECIMAL(10,2) DEFAULT 0.00,
    `recurring` TINYINT(1) DEFAULT 0,
    `recurring_limit` INT DEFAULT 0,
    `apply_once` TINYINT(1) DEFAULT 1,
    `new_signups_only` TINYINT(1) DEFAULT 0,
    `existing_client` TINYINT(1) DEFAULT 0,
    `once_per_client` TINYINT(1) DEFAULT 1,
    `start_date` DATE NULL,
    `expiry_date` DATE NULL,
    `max_uses` INT DEFAULT 0,
    `uses` INT DEFAULT 0,
    `lifetime_promo` TINYINT(1) DEFAULT 0,
    `applies_to` ENUM('products', 'product_groups', 'domains', 'all') DEFAULT 'all',
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_code` (`code`),
    INDEX `idx_expiry` (`expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Extensions Table
CREATE TABLE IF NOT EXISTS `owh_extensions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `display_name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `version` VARCHAR(20) DEFAULT '1.0.0',
    `author` VARCHAR(255) NULL,
    `type` ENUM('addon', 'payment', 'registrar', 'server', 'notification', 'fraud', 'report', 'widget') DEFAULT 'addon',
    `enabled` TINYINT(1) DEFAULT 0,
    `settings` JSON NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_type` (`type`),
    INDEX `idx_enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Templates Table
CREATE TABLE IF NOT EXISTS `owh_email_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `type` ENUM('general', 'product', 'domain', 'invoice', 'support', 'admin') DEFAULT 'general',
    `name` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `html_message` TEXT NULL,
    `plain_text` TINYINT(1) DEFAULT 0,
    `disabled` TINYINT(1) DEFAULT 0,
    `attachments` TEXT NULL,
    `custom` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_type` (`type`),
    INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Log Table
CREATE TABLE IF NOT EXISTS `owh_activity_log` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `type` VARCHAR(100) NOT NULL,
    `description` TEXT NOT NULL,
    `client_id` INT UNSIGNED NULL,
    `admin_id` INT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_type` (`type`),
    INDEX `idx_client` (`client_id`),
    INDEX `idx_admin` (`admin_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings Table
CREATE TABLE IF NOT EXISTS `owh_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment Gateways Table
CREATE TABLE IF NOT EXISTS `owh_payment_gateways` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `display_name` VARCHAR(255) NOT NULL,
    `type` ENUM('cc', 'bank', 'invoice', 'crypto', 'other') DEFAULT 'other',
    `visible` TINYINT(1) DEFAULT 1,
    `settings` JSON NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Currencies Table
CREATE TABLE IF NOT EXISTS `owh_currencies` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` CHAR(3) NOT NULL UNIQUE,
    `prefix` VARCHAR(10) DEFAULT '',
    `suffix` VARCHAR(10) DEFAULT '',
    `format` INT DEFAULT 1,
    `rate` DECIMAL(10,6) DEFAULT 1.000000,
    `is_default` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tax Rules Table
CREATE TABLE IF NOT EXISTS `owh_tax_rules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `state` VARCHAR(100) NULL,
    `country` CHAR(2) NULL,
    `tax_rate` DECIMAL(5,2) NOT NULL,
    `level` INT DEFAULT 1,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Domain Contacts Table
CREATE TABLE IF NOT EXISTS `owh_domain_contacts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT UNSIGNED NOT NULL,
    `type` ENUM('registrant', 'admin', 'tech', 'billing') DEFAULT 'registrant',
    `firstname` VARCHAR(100) NOT NULL,
    `lastname` VARCHAR(100) NOT NULL,
    `company` VARCHAR(255) NULL,
    `email` VARCHAR(255) NOT NULL,
    `address1` VARCHAR(255) NOT NULL,
    `address2` VARCHAR(255) NULL,
    `city` VARCHAR(100) NOT NULL,
    `state` VARCHAR(100) NULL,
    `postcode` VARCHAR(20) NOT NULL,
    `country` CHAR(2) NOT NULL,
    `phone` VARCHAR(50) NOT NULL,
    `fax` VARCHAR(50) NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    FOREIGN KEY (`client_id`) REFERENCES `owh_clients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Credentials Table
CREATE TABLE IF NOT EXISTS `owh_api_credentials` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT UNSIGNED NULL,
    `admin_id` INT UNSIGNED NULL,
    `identifier` VARCHAR(100) NOT NULL UNIQUE,
    `secret` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) NULL,
    `allowed_ips` TEXT NULL,
    `permissions` JSON NULL,
    `active` TINYINT(1) DEFAULT 1,
    `last_access` DATETIME NULL,
    `last_access_ip` VARCHAR(45) NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_identifier` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default data
INSERT INTO `owh_currencies` (`code`, `prefix`, `suffix`, `format`, `rate`, `is_default`, `created_at`, `updated_at`) VALUES
('USD', '$', '', 1, 1.000000, 1, NOW(), NOW()),
('EUR', '€', '', 1, 0.850000, 0, NOW(), NOW()),
('GBP', '£', '', 1, 0.730000, 0, NOW(), NOW());

INSERT INTO `owh_support_departments` (`name`, `description`, `clients_only`, `sort_order`, `created_at`, `updated_at`) VALUES
('Sales', 'Pre-sales questions and inquiries', 0, 1, NOW(), NOW()),
('Support', 'Technical support requests', 1, 2, NOW(), NOW()),
('Billing', 'Billing and payment inquiries', 1, 3, NOW(), NOW());

INSERT INTO `owh_product_groups` (`name`, `slug`, `headline`, `sort_order`, `created_at`, `updated_at`) VALUES
('Shared Hosting', 'shared-hosting', 'Fast & Reliable Web Hosting', 1, NOW(), NOW()),
('VPS Hosting', 'vps-hosting', 'Powerful Virtual Private Servers', 2, NOW(), NOW()),
('Dedicated Servers', 'dedicated-servers', 'High Performance Dedicated Servers', 3, NOW(), NOW());

INSERT INTO `owh_admins` (`email`, `password`, `name`, `role`, `created_at`, `updated_at`) VALUES
('admin@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4dslBVFfJqLf8lDu', 'Administrator', 'super_admin', NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;
