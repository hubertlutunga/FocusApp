CREATE TABLE IF NOT EXISTS starlink_subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    line_label VARCHAR(160) NOT NULL,
    subscription_number VARCHAR(80) NULL,
    plan_name VARCHAR(120) NULL,
    start_date DATE NULL,
    end_date DATE NOT NULL,
    monthly_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    reminder_days INT UNSIGNED NOT NULL DEFAULT 7,
    status ENUM('active', 'expired', 'cancelled') NOT NULL DEFAULT 'active',
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_starlink_subscriptions_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_starlink_subscriptions_user FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE INDEX idx_starlink_subscriptions_end_date ON starlink_subscriptions(end_date);
CREATE INDEX idx_starlink_subscriptions_status ON starlink_subscriptions(status);
