-- Migration: add customer fields to orders table
USE coffee_pos;

ALTER TABLE orders
ADD COLUMN IF NOT EXISTS customer_name VARCHAR(150) NULL,
ADD COLUMN IF NOT EXISTS customer_phone VARCHAR(50) NULL,
ADD COLUMN IF NOT EXISTS customer_note TEXT NULL;

-- Note: If your MySQL version doesn't support 'IF NOT EXISTS' on ADD COLUMN,
-- run the following statements manually (one per column) if necessary:
-- ALTER TABLE orders ADD COLUMN customer_name VARCHAR(150) NULL;
-- ALTER TABLE orders ADD COLUMN customer_phone VARCHAR(50) NULL;
-- ALTER TABLE orders ADD COLUMN customer_note TEXT NULL;