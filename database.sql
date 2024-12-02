CREATE TABLE `folders` (
    `folder_id` INT PRIMARY KEY AUTO_INCREMENT,
    `folder_name` VARCHAR(255) NOT NULL
); 

ALTER TABLE `exams`
ADD COLUMN `folder_id` INT,
ADD FOREIGN KEY (`folder_id`) REFERENCES `folders`(`folder_id`); 