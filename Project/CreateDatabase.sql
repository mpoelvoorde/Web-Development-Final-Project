DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS employee_types;
DROP TABLE IF EXISTS price_modifications;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS room_types;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS states;
DROP TABLE IF EXISTS countries;

CREATE TABLE IF NOT EXISTS countries(
	country_id CHAR(3) NOT NULL,
	country_name VARCHAR(50) NOT NULL,
	PRIMARY KEY (country_id)
);

CREATE TABLE IF NOT EXISTS states(
	state_id CHAR(2) NOT NULL,
	country_id CHAR(3) NOT NULL,
	state_name VARCHAR(50) NOT NULL,
	PRIMARY KEY (state_id),
	FOREIGN KEY (country_id) REFERENCES countries(country_id)
);

CREATE TABLE IF NOT EXISTS customers(
	customer_id INT NOT NULL AUTO_INCREMENT,
	first_name VARCHAR(30) NOT NULL,
	last_name VARCHAR(30) NOT NULL,
	email VARCHAR(100) NOT NULL,
	password VARCHAR(40) NOT NULL,
	street_address VARCHAR(200) NOT NULL,
	city VARCHAR(100) NOT NULL,
	state_id CHAR(2),
	country_id CHAR(3) NOT NULL,
	PRIMARY KEY (customer_id),
	FOREIGN KEY (state_id) REFERENCES states(state_id),
	FOREIGN KEY (country_id) REFERENCES countries(country_id)
);

ALTER TABLE customers AUTO_INCREMENT = 1001;

CREATE TABLE IF NOT EXISTS room_types(
	room_type CHAR(1) NOT NULL,
	room_type_description VARCHAR(15) NOT NULL,
	beds TINYINT NOT NULL,
	min_occupancy TINYINT NOT NULL,
	max_occupancy TINYINT NOT NULL,
	description VARCHAR(1000) NOT NULL,
	PRIMARY KEY (room_type)
);

CREATE TABLE IF NOT EXISTS rooms(
	room_number SMALLINT NOT NULL,
	room_type CHAR(1),
	base_price INT NOT NULL,
	PRIMARY KEY (room_number),
	FOREIGN KEY (room_type) REFERENCES room_types(room_type)
);

CREATE TABLE IF NOT EXISTS reservations(
	reservation_id INT NOT NULL AUTO_INCREMENT,
	customer_id INT NOT NULL,
	room_number SMALLINT NOT NULL,
	start_date DATE NOT NULL,
	end_date DATE NOT NULL,
	reservation_date TIMESTAMP NOT NULL,
	price INT NOT NULL,
	PRIMARY KEY (reservation_id),
	FOREIGN KEY (room_number) REFERENCES rooms(room_number),
	FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);

ALTER TABLE reservations AUTO_INCREMENT = 1000001;

CREATE TABLE IF NOT EXISTS employees(
	employee_id INT NOT NULL AUTO_INCREMENT,
	first_name VARCHAR(30) NOT NULL,
	last_name VARCHAR(30) NOT NULL,
	email VARCHAR(100) NOT NULL,
	password VARCHAR(40) NOT NULL,
	street_address VARCHAR(200) NOT NULL,
	city VARCHAR(100) NOT NULL,
	state_id CHAR(2),
	country_id CHAR(3),
	PRIMARY KEY (employee_id),
	FOREIGN KEY (state_id) REFERENCES states(state_id),
	FOREIGN KEY (country_id) REFERENCES countries(country_id)
);

ALTER TABLE employees AUTO_INCREMENT = 101;

-- To log into database:
--Database:	poelvoo_fp
--Host:	localhost
--Username:	poelvoo_fp
--Password:	mypassword