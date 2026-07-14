CREATE DATABASE IF NOT EXISTS helpdesk_testing
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON helpdesk_testing.* TO 'helpdesk'@'%';
FLUSH PRIVILEGES;
