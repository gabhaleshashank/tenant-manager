-- Database schema for Tenant Management System

CREATE DATABASE IF NOT EXISTS tenant_manager
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tenant_manager;

-- Users table: application users (e.g., admin, staff)
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tenants table: tenants being managed
CREATE TABLE IF NOT EXISTS tenants (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(150) DEFAULT NULL,
  address VARCHAR(255) NOT NULL,
  property_name VARCHAR(150) NOT NULL,
  monthly_rent DECIMAL(10,2) NOT NULL,
  deposit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  move_in_date DATE NOT NULL,
  status ENUM('active', 'moved_out') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Payments table: optional payment tracking
CREATE TABLE IF NOT EXISTS payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT UNSIGNED NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_date DATE NOT NULL,
  month TINYINT NOT NULL,
  year SMALLINT NOT NULL,
  status ENUM('paid', 'partial', 'pending') NOT NULL DEFAULT 'paid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payments_tenant
    FOREIGN KEY (tenant_id)
    REFERENCES tenants(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- Rent records per tenant
CREATE TABLE IF NOT EXISTS tenant_rents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT UNSIGNED NOT NULL,
  rent_month TINYINT NOT NULL,
  rent_year SMALLINT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  date_given DATE NOT NULL,
  mode ENUM('online', 'cash') NOT NULL DEFAULT 'cash',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_rents_tenant
    FOREIGN KEY (tenant_id)
    REFERENCES tenants(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- Electricity bill details per tenant
CREATE TABLE IF NOT EXISTS tenant_electricity_bills (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT UNSIGNED NOT NULL,
  bill_month TINYINT NOT NULL,
  bill_year SMALLINT NOT NULL,
  date_given DATE NOT NULL,
  paid_by ENUM('landlord', 'tenant') NOT NULL DEFAULT 'tenant',
  previous_units INT UNSIGNED DEFAULT NULL,
  previous_units_date DATE DEFAULT NULL,
  latest_units INT UNSIGNED DEFAULT NULL,
  latest_units_date DATE DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_electricity_tenant
    FOREIGN KEY (tenant_id)
    REFERENCES tenants(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- Issues raised per tenant
CREATE TABLE IF NOT EXISTS tenant_issues (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT UNSIGNED NOT NULL,
  description TEXT NOT NULL,
  raised_date DATE NOT NULL,
  status ENUM('open', 'in_progress', 'resolved') NOT NULL DEFAULT 'open',
  solved_date DATE DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_issues_tenant
    FOREIGN KEY (tenant_id)
    REFERENCES tenants(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


