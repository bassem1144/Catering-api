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
    creation_date DATE NOT NULL,
    location_id INT,
    CONSTRAINT fk_location FOREIGN KEY (location_id) REFERENCES Locations(location_id)
);

-- Create FacilityTags junction table for many-to-many relationship
CREATE TABLE FacilityTags (
    facility_id INT,
    tag_id INT,
    PRIMARY KEY (facility_id, tag_id),
    CONSTRAINT fk_facility FOREIGN KEY (facility_id) REFERENCES Facilities(facility_id),
    CONSTRAINT fk_tag FOREIGN KEY (tag_id) REFERENCES Tags(tag_id)
);
