-- Migration: create frontpage table
-- Run this in your cPanel MySQL (phpMyAdmin) or via CLI to create the table that stores the canonical front page JSON.

CREATE TABLE IF NOT EXISTS frontpage (
  id INT PRIMARY KEY,
  content LONGTEXT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert an initial empty row so SELECT returns a 200 response with '{}'
INSERT IGNORE INTO frontpage (id, content) VALUES (1, '{}');
