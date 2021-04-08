DROP DATABASE IF EXISTS patissien;
CREATE DATABASE IF NOT EXISTS patissien;

DROP USER IF EXISTS 'Webgebruiker'@'localhost';

CREATE USER 'Webgebruiker'@'localhost';
GRANT ALL PRIVILEGES ON patissien.* To 'Webgebruiker'@'localhost' IDENTIFIED BY 'Labo2020';

USE patissien;

DROP TABLE IF EXISTS postalCodes;
DROP TABLE IF EXISTS orderDetails;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS address;

CREATE TABLE postalCodes
(
	postalCode INT(4) UNSIGNED NOT NULL UNIQUE,
	municipName VARCHAR(30),
	PRIMARY KEY (postalCode)
);

CREATE TABLE address
(
	addressID SMALLINT UNSIGNED NOT NULL UNIQUE AUTO_INCREMENT,
	postalCode INT(4) UNSIGNED NOT NULL,
	streetName TINYTEXT NOT NULL,
	streetNumber INT(5) NOT NULL,
	PRIMARY KEY (addressID),
	FOREIGN KEY (postalCode) REFERENCES postalCodes(postalCode)

);

CREATE TABLE users
(
	userID SMALLINT UNSIGNED NOT NULL UNIQUE AUTO_INCREMENT,
	password TINYTEXT NOT NULL,
	email TINYTEXT NOT NULL,
	fname VARCHAR(30),
	lname VARCHAR(30),
	admin BIT NOT NULL DEFAULT 0 CHECK (admin IN (1,0)),
	PRIMARY KEY (userID)
);

CREATE TABLE categories
(
	categoryID INT(1) UNSIGNED NOT NULL UNIQUE AUTO_INCREMENT,
	categoryName VARCHAR(20),
	PRIMARY KEY (categoryID)
);

CREATE TABLE products
(
	productID SMALLINT UNSIGNED NOT NULL UNIQUE AUTO_INCREMENT,
	productName VARCHAR(30) NOT NULL,
	productPrice DECIMAL(6,2) NOT NULL,
	productPicture TINYTEXT NOT NULL,
	productDescription TEXT NOT NULL,
	availableAmount INT(2) NOT NULL,
	categoryID INT(1) UNSIGNED,
	PRIMARY KEY(productID),
	FOREIGN KEY (categoryID) REFERENCES categories(categoryID)
);

CREATE TABLE orders
(
	orderID SMALLINT UNSIGNED NOT NULL UNIQUE AUTO_INCREMENT,
	userID SMALLINT UNSIGNED,
	orderPrice DECIMAL(5,2) NOT NULL,
	orderDate DATE NOT NULL,
	structRef CHAR(20) NOT NULL,
	paymentStatus BIT DEFAULT 0,
	addressID SMALLINT UNSIGNED NOT NULL,
	PRIMARY KEY(orderID),
	FOREIGN KEY(userID) REFERENCES users(userID),
	FOREIGN KEY(addressID) REFERENCES address(addressID)
);

CREATE TABLE orderDetails
(
	orderID SMALLINT UNSIGNED NOT NULL,
	productID SMALLINT UNSIGNED NOT NULL,
	amount SMALLINT UNSIGNED NOT NULL,
	PRIMARY KEY(orderID,productID),
	FOREIGN KEY(orderID) REFERENCES orders(orderID),
	FOREIGN KEY(productID) REFERENCES products(productID)
);

INSERT INTO postalCodes (postalCode, municipName) VALUES ('2910', 'Essen'),('2920', 'Kalmthout');
INSERT INTO categories (categoryName) VALUES ("Taarten"),("Koeken"),("Chocolade"),("Broden");
INSERT INTO 
`products`(`productName`, `productPrice`, `productPicture`, `productDescription`, `availableAmount`, `categoryID`) 
VALUES
('Tijgerbrood','3.99','./Images/Tijgerbrood.jpg', 'Raawrrr...',0,4),
('Plat brood','3.99','./Images/Platbrood.jpg', 'Speciaal voor de &#34;flat earthers&#34; onder ons',4,4),
('Honingbrood','3.99','./Images/Tijgerbrood.jpg', 'Een brood met een enorme b(u)zz-factor',3,4),
('Kersttaart','8.99','./Images/Kersttaart.jpg', 'Fijne kerstdagen zonder deze taart?!',9,1),
('KitKat-M&M taart','11.99','./Images/Kitkat-M&M-taart.jpg', 'Een feestje om te zien en een nog groter feest om op te eten',1,1),
('Fruittaart','7.99','./Images/Fruittaart.png', 'Fruittaart crème de la crème',5,1),
('Chocoladebiscuit','9.99','./Images/Chocoladebiscuit.jpg', 'Overheerlijke chocoladebiscuit met chocomousse en chocolade',3,1),
('Orangettes','5.99','./Images/Orangettes.jpg', '100g geconfijte sinaasappelschillen met een heerlijk laagje chocolade',20,3);
INSERT INTO `users` (`userID`, `password`, `email`, `fname`, `lname`, `admin`) VALUES ('1', '$2y$10$kwEselaiXpxXrHnlFjWeZ.lzFqne5x/URTy3G.e6NOaXHOuQi8RkC', 'admin@patissien.be', 'admin', 'admin', b'1'); 
/* email : admin@patissien.be ; password : adminadmin */