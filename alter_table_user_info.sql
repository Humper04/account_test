-- Add role column to user_info table
ALTER TABLE user_info ADD role ENUM('admin','user') NOT NULL DEFAULT 'user';

-- Add unique key to email column (optional, uncomment if needed)
-- ALTER TABLE user_info ADD UNIQUE KEY (email);
