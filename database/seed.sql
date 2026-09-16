-- SecureCart Seed Data
USE securecart;

-- Insert Seed Users
-- Admin: admin@securecart.com / Password: Admin@123456
-- Customer: user@securecart.com / Password: User@123456
INSERT INTO users (name, email, password_hash, role) VALUES
('System Administrator', 'admin@securecart.com', '$2y$10$/qbNm1R2ezImFGvkUDISlulv8QhX7xIUO3lGPVDgnftWbVtKYDfBu', 'admin'),
('John Doe', 'user@securecart.com', '$2y$10$qcRYQCDKGJ3kW3InZ20SmeKXPlhWGCz/K3XDXEwQi3hNj8QAZLcBS', 'customer'),
('Alice Smith', 'alice@securecart.com', '$2y$10$qcRYQCDKGJ3kW3InZ20SmeKXPlhWGCz/K3XDXEwQi3hNj8QAZLcBS', 'customer');

-- Insert Seed Products
INSERT INTO products (name, description, price, stock, category, image_url) VALUES
('CyberShield Hardware Token', 'FIDO2 & U2F Hardware Security Key providing multi-factor authentication with cryptographic protection.', 2499.00, 25, 'Hardware Security', 'assets/images/yubikey.svg'),
('Encrypted USB Drive 64GB', 'AES-256 bit hardware-encrypted flash drive with PIN keypad protection and zero data trace.', 4999.00, 15, 'Hardware Security', 'assets/images/encrypted_usb.svg'),
('SecurePort Router Pro', 'Enterprise VPN router with automated IDS/IPS, SPI Firewall, and encrypted hardware acceleration.', 12999.00, 10, 'Networking', 'assets/images/router.svg'),
('Privacy Screen Protector (15.6")', 'Anti-peeping magnetic privacy filter preventing side-angle visual eavesdropping.', 1499.00, 50, 'Accessories', 'assets/images/screen_protector.svg'),
('RFID Blocking Wallet', 'Genuine leather bi-fold wallet featuring Signal-Shield Faraday technology against RFID theft.', 1899.00, 40, 'Accessories', 'assets/images/rfid_wallet.svg'),
('Webcam Cover Slider (Pack of 3)', 'Ultra-thin mechanical webcam shield ensuring physical privacy against unauthorized camera access.', 499.00, 100, 'Accessories', 'assets/images/webcam_cover.svg');

-- Insert Sample Reviews
INSERT INTO reviews (user_id, product_id, rating, review_text) VALUES
(2, 1, 5, 'Exceptional security key! Works flawlessly with webauthn and protects all my developer accounts.'),
(3, 1, 4, 'Solid build quality and seamless browser integration. Highly recommended for 2FA.'),
(2, 2, 5, 'The physical PIN pad is extremely responsive. Feel totally safe storing sensitive database backups here.');
