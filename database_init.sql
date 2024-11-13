-- Create the database
CREATE DATABASE IF NOT EXISTS task1 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci;

-- Use the newly created database
USE task1;

-- Create the users table
CREATE TABLE users (
    sequence_number INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    fname VARCHAR(50) DEFAULT NULL,
    lname VARCHAR(50) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    uimg VARCHAR(255) DEFAULT NULL,
    verified TINYINT(1) NOT NULL,
    otp CHAR(6) DEFAULT NULL,
    PRIMARY KEY (sequence_number),
    UNIQUE KEY (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
