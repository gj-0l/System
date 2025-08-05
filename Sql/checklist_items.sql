-- جدول الفحوصات المرتبطة بكل معدة
CREATE TABLE checklist_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    test_name VARCHAR(255) NOT NULL,
    initial_action TEXT,
    default_status ENUM('accepted', 'rejected') DEFAULT 'accepted',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
);