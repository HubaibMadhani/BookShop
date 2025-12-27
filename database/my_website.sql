CREATE DATABASE my_website;
USE my_website;
CREATE TABLE users(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100),email VARCHAR(100),password VARCHAR(255),role VARCHAR(10));
CREATE TABLE contact_messages(
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	email VARCHAR(100) NOT NULL,
	message TEXT NOT NULL,
	is_read TINYINT(1) NOT NULL DEFAULT 0,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE books(
	id INT AUTO_INCREMENT PRIMARY KEY,
	title VARCHAR(255) NOT NULL,
	author VARCHAR(255),
	description TEXT,
	price DECIMAL(8,2) NOT NULL DEFAULT 0.00,
	image VARCHAR(255) DEFAULT 'assets/images/placeholder.png',
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders(
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255),
	email VARCHAR(255),
	total DECIMAL(10,2),
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE password_resets(
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	token_hash VARCHAR(255) NOT NULL,
	expires_at DATETIME NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_items(
	id INT AUTO_INCREMENT PRIMARY KEY,
	order_id INT NOT NULL,
	book_id INT NOT NULL,
	quantity INT NOT NULL,
	price DECIMAL(8,2) NOT NULL,
	FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
	FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- Sample books
INSERT INTO books (title,author,description,price,image) VALUES
('The Modern PHP Handbook','Jane Doe','A practical guide to modern PHP development.',19.99,'assets/images/book1.jpg'),
('Clean Code for Web','John Smith','Principles and best practices for writing clean code.',24.50,'assets/images/book2.jpg'),
('Designing Interfaces','Alex Roe','User interface patterns and design techniques.',29.99,'assets/images/book3.jpg');