-- Vulnerable SecureCart Seed Data
USE vuln_securecart;

-- Insert Seed Users with standard hashed passwords for compatibility
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

-- Insert Sample Reviews (Includes an XSS payload demo)
INSERT INTO reviews (user_id, product_id, rating, review_text) VALUES
(2, 1, 5, 'Great key! <script>alert("XSS Vulnerability Executed on Port 8001!")</script> Very handy.'),
(3, 1, 4, 'Solid build quality and seamless browser integration. Highly recommended for 2FA.'),
(2, 2, 5, 'Physical PIN pad works great.');

-- Insert Sample Order for IDOR Demonstration
INSERT INTO orders (id, user_id, total_amount, shipping_name, shipping_address, phone, status) VALUES
(1001, 1, 12999.00, 'System Admin', '123 Secret Admin Way, Tech City', '9998887770', 'Completed'),
(1002, 3, 1499.00, 'Alice Smith', '456 Garden St, Metro Area', '9876543210', 'Processing');

INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1001, 3, 1, 12999.00),
(1002, 4, 1, 1499.00);
