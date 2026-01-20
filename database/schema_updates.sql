<?php
/**
 * Updated Database Schema with new tables for:
 * - Pages (page builder)
 * - Manual payment methods
 * - Fraud detection
 * - Email logs
 */

// Run after initial schema.sql

$additionalTables = <<<SQL

-- Pages for Page Builder
CREATE TABLE IF NOT EXISTS `{PREFIX}pages` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL UNIQUE,
    `content` longtext,
    `blocks` json,
    `meta_title` varchar(255),
    `meta_description` text,
    `template` varchar(50) DEFAULT 'default',
    `status` enum('draft','published') DEFAULT 'draft',
    `show_in_menu` tinyint(1) DEFAULT 0,
    `menu_order` int(11) DEFAULT 0,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `slug` (`slug`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Manual Payment Methods
CREATE TABLE IF NOT EXISTS `{PREFIX}manual_payment_methods` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `type` varchar(50) NOT NULL,
    `icon` varchar(100) DEFAULT 'fas fa-money-bill',
    `instructions` text NOT NULL,
    `fields` json,
    `active` tinyint(1) DEFAULT 1,
    `sort_order` int(11) DEFAULT 0,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payment Log for tracking manual payments
CREATE TABLE IF NOT EXISTS `{PREFIX}payment_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `invoice_id` int(11) NOT NULL,
    `gateway` varchar(50) NOT NULL,
    `method` varchar(50),
    `transaction_id` varchar(255),
    `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
    `amount` decimal(10,2),
    `data` json,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `invoice_id` (`invoice_id`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fraud Checks Log
CREATE TABLE IF NOT EXISTS `{PREFIX}fraud_checks` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11),
    `client_id` int(11),
    `email` varchar(255),
    `ip_address` varchar(45),
    `score` int(11) DEFAULT 0,
    `action` enum('allow','review','reject') DEFAULT 'allow',
    `risks` json,
    `details` json,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `order_id` (`order_id`),
    KEY `client_id` (`client_id`),
    KEY `score` (`score`),
    KEY `action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fraud Records (blacklist)
CREATE TABLE IF NOT EXISTS `{PREFIX}fraud_records` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11),
    `client_id` int(11),
    `email` varchar(255),
    `ip_address` varchar(45),
    `reason` text,
    `status` enum('fraud','cleared') DEFAULT 'fraud',
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `email` (`email`),
    KEY `ip_address` (`ip_address`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Email Log
CREATE TABLE IF NOT EXISTS `{PREFIX}email_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `client_id` int(11),
    `to_email` varchar(255) NOT NULL,
    `cc` varchar(255),
    `bcc` varchar(255),
    `subject` varchar(255) NOT NULL,
    `body` text NOT NULL,
    `status` enum('pending','sent','failed') DEFAULT 'pending',
    `error` text,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `sent_at` datetime,
    PRIMARY KEY (`id`),
    KEY `client_id` (`client_id`),
    KEY `status` (`status`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Announcements
CREATE TABLE IF NOT EXISTS `{PREFIX}announcements` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `content` text NOT NULL,
    `status` enum('draft','published') DEFAULT 'draft',
    `published_at` datetime,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `status` (`status`),
    KEY `published_at` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Knowledgebase Categories
CREATE TABLE IF NOT EXISTS `{PREFIX}kb_categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text,
    `parent_id` int(11),
    `sort_order` int(11) DEFAULT 0,
    `hidden` tinyint(1) DEFAULT 0,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Knowledgebase Articles
CREATE TABLE IF NOT EXISTS `{PREFIX}kb_articles` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `category_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `content` text NOT NULL,
    `views` int(11) DEFAULT 0,
    `helpful_yes` int(11) DEFAULT 0,
    `helpful_no` int(11) DEFAULT 0,
    `status` enum('draft','published') DEFAULT 'draft',
    `sort_order` int(11) DEFAULT 0,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `category_id` (`category_id`),
    KEY `status` (`status`),
    FULLTEXT KEY `search` (`title`, `content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin Activity Log
CREATE TABLE IF NOT EXISTS `{PREFIX}admin_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `admin_id` int(11) NOT NULL,
    `action` varchar(100) NOT NULL,
    `description` text,
    `ip_address` varchar(45),
    `user_agent` varchar(255),
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `admin_id` (`admin_id`),
    KEY `action` (`action`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Client Notes
CREATE TABLE IF NOT EXISTS `{PREFIX}client_notes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `client_id` int(11) NOT NULL,
    `admin_id` int(11) NOT NULL,
    `note` text NOT NULL,
    `sticky` tinyint(1) DEFAULT 0,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- API Credentials (for client API access)
CREATE TABLE IF NOT EXISTS `{PREFIX}api_credentials` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `client_id` int(11) NOT NULL,
    `identifier` varchar(100) NOT NULL UNIQUE,
    `secret_hash` varchar(255) NOT NULL,
    `permissions` json,
    `allowed_ips` text,
    `last_access` datetime,
    `active` tinyint(1) DEFAULT 1,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `client_id` (`client_id`),
    KEY `identifier` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add fraud columns to orders table
ALTER TABLE `{PREFIX}orders` 
    ADD COLUMN IF NOT EXISTS `fraud_status` enum('pending','cleared','fraud') DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS `fraud_score` int(11) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `fraud_notes` text;

-- Add payment method to invoices
ALTER TABLE `{PREFIX}invoices`
    ADD COLUMN IF NOT EXISTS `payment_method` varchar(100);

-- Insert default pages
INSERT INTO `{PREFIX}pages` (`title`, `slug`, `content`, `template`, `status`, `show_in_menu`, `menu_order`) VALUES
('About Us', 'about', '<h2>About Our Company</h2><p>We are a leading provider of web hosting and domain services. With years of experience, we offer reliable, secure, and affordable solutions for businesses and individuals.</p><h3>Our Mission</h3><p>To provide exceptional hosting services that empower our customers to succeed online.</p><h3>Why Choose Us?</h3><ul><li>99.9% Uptime Guarantee</li><li>24/7 Expert Support</li><li>Affordable Pricing</li><li>Easy-to-use Control Panel</li></ul>', 'default', 'published', 1, 1),
('Terms of Service', 'terms', '<h2>Terms of Service</h2><p>Last updated: [Date]</p><h3>1. Acceptance of Terms</h3><p>By accessing and using our services, you agree to be bound by these Terms of Service.</p><h3>2. Services</h3><p>We provide web hosting, domain registration, and related services as described on our website.</p><h3>3. Payment Terms</h3><p>All services are billed in advance. Invoices are due upon receipt unless otherwise specified.</p><h3>4. Acceptable Use</h3><p>You agree not to use our services for any illegal or harmful purposes.</p><h3>5. Termination</h3><p>We reserve the right to terminate services for violation of these terms.</p>', 'default', 'published', 1, 2),
('Privacy Policy', 'privacy', '<h2>Privacy Policy</h2><p>Last updated: [Date]</p><h3>Information We Collect</h3><p>We collect information you provide when registering for our services, including name, email, address, and payment information.</p><h3>How We Use Information</h3><p>We use your information to provide and improve our services, process payments, and communicate with you.</p><h3>Data Security</h3><p>We implement industry-standard security measures to protect your data.</p><h3>Your Rights</h3><p>You have the right to access, correct, or delete your personal information.</p>', 'default', 'published', 1, 3),
('Contact Us', 'contact', '<h2>Contact Us</h2><p>We''d love to hear from you! Get in touch using any of the methods below.</p><h3>Support</h3><p>For technical support, please <a href="/client/tickets/open">open a support ticket</a>.</p><h3>Sales</h3><p>For sales inquiries, email us at sales@example.com</p><h3>Office Hours</h3><p>Monday - Friday: 9:00 AM - 6:00 PM EST</p>', 'default', 'published', 1, 4)
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Insert default KB categories
INSERT INTO `{PREFIX}kb_categories` (`name`, `description`, `sort_order`) VALUES
('Getting Started', 'Basic guides to help you get started with our services', 1),
('Hosting', 'Web hosting related articles', 2),
('Domains', 'Domain registration and management', 3),
('Billing', 'Payment and billing information', 4),
('Technical', 'Technical guides and tutorials', 5)
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Insert default announcement
INSERT INTO `{PREFIX}announcements` (`title`, `content`, `status`, `published_at`) VALUES
('Welcome to Our Hosting Platform!', 'We are excited to welcome you to our hosting platform. Explore our services and don''t hesitate to contact support if you need any assistance.', 'published', NOW())
ON DUPLICATE KEY UPDATE `id` = `id`;

SQL;
