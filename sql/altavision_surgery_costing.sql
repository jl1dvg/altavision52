-- AltaVision Surgery Costing (standalone migration)
-- Apply manually on target database.

CREATE TABLE IF NOT EXISTS surgery_day (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  surgery_date DATE NOT NULL,
  facility_id INT NULL,
  room_name VARCHAR(80) NULL,
  notes TEXT NULL,
  created_by INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_surgery_day_date (surgery_date),
  INDEX idx_surgery_day_facility (facility_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS surgery_case (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  surgery_day_id BIGINT NOT NULL,
  pid BIGINT NOT NULL,
  encounter_id BIGINT NULL,
  surgeon_id INT NOT NULL,
  procedure_code VARCHAR(40) NULL,
  procedure_name VARCHAR(255) NOT NULL,
  specialty ENUM('retina','catarata','glaucoma','pterigion','otro') NOT NULL DEFAULT 'otro',
  complexity_points DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  start_time DATETIME NULL,
  end_time DATETIME NULL,
  status ENUM('planned','done','cancelled') NOT NULL DEFAULT 'planned',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (surgery_day_id) REFERENCES surgery_day(id),
  INDEX idx_surgery_case_day (surgery_day_id),
  INDEX idx_surgery_case_surgeon (surgeon_id),
  INDEX idx_surgery_case_specialty (specialty),
  INDEX idx_surgery_case_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inventory_item (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(60) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  unit VARCHAR(20) NOT NULL,
  avg_cost DECIMAL(12,4) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  INDEX idx_inventory_item_active (is_active)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inventory_issue (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  surgery_day_id BIGINT NOT NULL,
  case_id BIGINT NULL,
  item_id BIGINT NOT NULL,
  qty DECIMAL(12,4) NOT NULL,
  unit_cost DECIMAL(12,4) NOT NULL,
  total_cost DECIMAL(12,4) GENERATED ALWAYS AS (qty * unit_cost) STORED,
  usage_type ENUM('direct_case','shared_batch','shared_day') NOT NULL,
  specialty_scope ENUM('all','retina','catarata','glaucoma','pterigion','otro') NOT NULL DEFAULT 'all',
  allocation_method ENUM('none','equal_cases','by_minutes','by_points','manual') NOT NULL DEFAULT 'none',
  notes VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (surgery_day_id) REFERENCES surgery_day(id),
  FOREIGN KEY (case_id) REFERENCES surgery_case(id),
  FOREIGN KEY (item_id) REFERENCES inventory_item(id),
  INDEX idx_inventory_issue_day (surgery_day_id),
  INDEX idx_inventory_issue_case (case_id),
  INDEX idx_inventory_issue_usage (usage_type),
  INDEX idx_inventory_issue_scope (specialty_scope)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cost_allocation (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  issue_id BIGINT NOT NULL,
  case_id BIGINT NOT NULL,
  allocated_qty DECIMAL(12,4) NOT NULL,
  allocated_cost DECIMAL(12,4) NOT NULL,
  rule_used ENUM('equal_cases','by_minutes','by_points','manual') NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (issue_id) REFERENCES inventory_issue(id),
  FOREIGN KEY (case_id) REFERENCES surgery_case(id),
  UNIQUE KEY uq_issue_case (issue_id, case_id),
  INDEX idx_cost_alloc_case (case_id)
) ENGINE=InnoDB;
