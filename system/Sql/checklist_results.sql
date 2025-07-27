CREATE TABLE checklist_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checklist_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('accepted', 'rejected') DEFAULT 'rejected',
    FOREIGN KEY (checklist_id) REFERENCES checklist_items(id) ON DELETE CASCADE
);