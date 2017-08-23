INSERT INTO countries VALUES ('CAN', 'Canada');
INSERT INTO countries VALUES ('USA', 'United States');
INSERT INTO countries VALUES ('GBR', 'United Kingdom');
INSERT INTO countries VALUES ('AUS', 'Australia');
INSERT INTO countries VALUES ('ESP', 'Spain');
INSERT INTO countries VALUES ('GER', 'Germany');
INSERT INTO countries VALUES ('MEX', 'Mexico');

INSERT INTO states VALUES ('ON', 'CAN', 'Ontario');
INSERT INTO states VALUES ('MB', 'CAN', 'Manitoba');
INSERT INTO states VALUES ('QC', 'CAN', 'Quebec');
INSERT INTO states VALUES ('IL', 'USA', 'Illinois');
INSERT INTO states VALUES ('AK', 'USA', 'Alaska');
INSERT INTO states VALUES ('IN', 'USA', 'Indiana');
INSERT INTO states VALUES ('OH', 'USA', 'Ohio');
INSERT INTO states VALUES ('MI', 'USA', 'Michigan');
INSERT INTO states VALUES ('PA', 'USA', 'Pennsylvania');
INSERT INTO states VALUES ('NY', 'USA', 'New York');
INSERT INTO states VALUES ('MA', 'USA', 'Massachusetts');
INSERT INTO states VALUES ('CT', 'USA', 'Connecticut');
INSERT INTO states VALUES ('FL', 'USA', 'Florida');
INSERT INTO states VALUES ('TX', 'USA', 'Texas');
INSERT INTO states VALUES ('CA', 'USA', 'California');
INSERT INTO states VALUES ('DC', 'USA', 'District of Columbia');


-- customers(customer_id, first_name, last_name, email, password, street_address, city, state_id, country_id)


-- room_types(room_type, room_type_description, beds, min_occupancy, max_occupancy, description)
INSERT INTO room_types VALUES ('b', 'Standard', 1, 1, 2, 'Perfect for 1 or 2 guests, a standard room provides the basic ammenities you need to enjoy a comfortable, relaxing, bed-bug-free night at a very affordable price. These rooms are equipped with one queen bed, a TV with free over-the-air television (approximately 25 channels), a bathroom with a shower and single sink, and access to our indoor pool and a free continential breakfast.');
INSERT INTO room_types VALUES ('d', 'Deluxe', 2, 2, 4, 'Perfect for 2 to 4 guests, a deluxe room provides quality and comfort at a price you wouldn''t expect. Perfect for the traveling family or a couple needing separate beds, these rooms feature two queen beds, bathroom with a bathtub, shower, and two sinks, cable TV (approximately 100 channels), free continential breakfast, and free Wi-Fi.');
INSERT INTO room_types VALUES ('s', 'Suite', 2, 2, 4, 'Enjoy luxurious accommodation with our best room package! Suites offer 2 queen beds in separate rooms, both with 60-inch TVs offering the very best in cable TV (over 200 channels), along with daily maid service for the entire length of your stay. Our suites also offer a luxurious bathtub and shower featuring relaxing massage jets, a free continential breakfast and free, unlimited 50 Mbps Wi-Fi.');

-- rooms(room_number, room_type, base_price)
INSERT INTO rooms VALUES (101, 'b', 199);
INSERT INTO rooms VALUES (102, 'd', 269);
INSERT INTO rooms VALUES (103, 's', 349);
INSERT INTO rooms VALUES (104, 'b', 219);
INSERT INTO rooms VALUES (105, 'd', 299);
INSERT INTO rooms VALUES (106, 's', 409);

-- reservations(reservation_id, customer_id, room_number, start_date, end_date, reservation_date)


-- employees(employee_id, first_name, last_name, email, password, street_address, city, state_id, country_id)
