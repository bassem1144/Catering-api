-- Drop the database if it exists
DROP DATABASE IF EXISTS Catering_api;

-- Recreate the database
CREATE DATABASE Catering_api;

-- Use the database
USE Catering_api;

-- Create Locations table
CREATE TABLE Locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL,
    zip_code VARCHAR(10) NOT NULL,
    country_code VARCHAR(10) NOT NULL,
    phone_number VARCHAR(20) NOT NULL
);

-- Create Tags table
CREATE TABLE Tags (
    tag_id INT AUTO_INCREMENT PRIMARY KEY,
    tag_name VARCHAR(255) UNIQUE NOT NULL
);

-- Create Facilities table
CREATE TABLE Facilities (
    facility_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    creation_date DATE DEFAULT CURRENT_DATE,
    location_id INT,
    CONSTRAINT fk_location FOREIGN KEY (location_id) REFERENCES Locations(location_id)
);

-- Create Facility_tags junction table for many-to-many relationship
CREATE TABLE Facility_tags (
    facility_id INT,
    tag_id INT,
    PRIMARY KEY (facility_id, tag_id),
    CONSTRAINT fk_facility FOREIGN KEY (facility_id) REFERENCES Facilities(facility_id),
    CONSTRAINT fk_tag FOREIGN KEY (tag_id) REFERENCES Tags(tag_id)
);

-- Insert dummy data into Locations table
INSERT INTO Locations (city, address, zip_code, country_code, phone_number)
VALUES
    ('Amsterdam', 'Street 1', '1234HE', '31', '1234567890'),
    ('Utrecht', 'Street 2', '5432WC', '31', '4567890123'),
    ('Rotterdam', 'Street 3', '9876AB', '31', '7890123456'),
    ('Groningen', 'Street 4', '2345PO', '31', '2345678901'),
    ('Maastricht', 'Street 5', '6789BA', '31', '3456789012'),
    ('Eindhoven', 'Street 6', '1234AH', '31', '4567890123'),
    ('Haarlem', 'Street 7', '5432AB', '31', '5678901234'),
    ('Amersfoort', 'Street 8', '9876BT', '31', '6789012345'),
    ('Breda', 'Street 9', '2345MT', '31', '7890123456'),
    ('Leeuwarden', 'Street 10', '6789HA', '31', '8901234567');

-- Insert dummy data into Tags table
INSERT INTO Tags (tag_name) VALUES ('Tag1'), ('Tag2'), ('Tag3'), ('Tag4'), ('Tag5');

-- Insert dummy data into Facilities table
INSERT INTO Facilities (name, creation_date, location_id)
VALUES
    ('Facility 1', '2022-01-01', 1),
    ('Facility 2', '2022-01-02', 2),
    ('Facility 3', '2022-01-03', 3),
    ('Facility 4', '2022-01-04', 4),
    ('Facility 5', '2022-01-05', 5),
    ('Facility 6', '2022-01-06', 6),
    ('Facility 7', '2022-01-07', 7),
    ('Facility 8', '2022-01-08', 8),
    ('Facility 9', '2022-01-09', 9),
    ('Facility 10', '2022-01-10', 10);

-- Insert dummy data into Facility_tags table
INSERT INTO Facility_tags (facility_id, tag_id)
VALUES
    (1, 1),
    (1, 2),
    (2, 3),
    (2, 4),
    (3, 5),
    (3, 1),
    (4, 2),
    (4, 3),
    (5, 4),
    (5, 5),
    (6, 1),
    (6, 2),
    (7, 3),
    (7, 4),
    (8, 5),
    (8, 1),
    (9, 2),
    (9, 3),
    (10, 4),
    (10, 5);
