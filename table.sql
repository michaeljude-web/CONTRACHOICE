SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS recommendations;
DROP TABLE IF EXISTS questionnaire_responses;
DROP TABLE IF EXISTS contraceptive_methods;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admin;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    user_id    INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50) UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,
    email      VARCHAR(100) NULL,
    age_range  VARCHAR(20) NOT NULL,
    address    VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE admin (
    admin_id   INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50) UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE questionnaire_responses (
    response_id       INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT NOT NULL,
    sexually_active   ENUM('yes','no','prefer_not_to_say') NOT NULL,
    wants_children    ENUM('yes','no','unsure') NOT NULL,
    children_when     ENUM('within_1yr','1_to_3yrs','3yrs_plus','not_applicable') NOT NULL DEFAULT 'not_applicable',
    health_conditions SET('none','hypertension','migraines','diabetes','blood_clots','liver_disease','depression') NOT NULL DEFAULT 'none',
    is_smoker         ENUM('yes','no') NOT NULL,
    is_breastfeeding  ENUM('yes','no') NOT NULL,
    hormone_free_pref ENUM('very_important','somewhat','not_important') NOT NULL,
    delivery_pref     ENUM('daily_pill','weekly_patch','monthly_injection','long_term','barrier','natural') NOT NULL,
    budget_pref       ENUM('low','medium','high') NOT NULL,
    used_before       ENUM('yes','no') NOT NULL,
    previous_method   VARCHAR(100) NULL,
    submitted_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE contraceptive_methods (
    method_id              INT AUTO_INCREMENT PRIMARY KEY,
    name                   VARCHAR(100) NOT NULL,
    category               ENUM('hormonal','barrier','long_term','natural','emergency') NOT NULL,
    effectiveness          DECIMAL(4,1) NOT NULL COMMENT 'Percentage e.g. 99.7',
    delivery               ENUM('daily_pill','weekly_patch','monthly_injection','long_term','barrier','natural') NOT NULL,
    is_hormone_free        TINYINT(1) DEFAULT 0,
    cost_level             ENUM('low','medium','high') NOT NULL,
    suitable_smoker        TINYINT(1) DEFAULT 1,
    suitable_breastfeeding TINYINT(1) DEFAULT 1,
    contraindications      TEXT NULL COMMENT 'Comma-separated: hypertension,migraines,etc.',
    description            TEXT NOT NULL,
    side_effects           TEXT NULL,
    created_at             DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE recommendations (
    recommendation_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT NOT NULL,
    response_id       INT NOT NULL,
    method_id         INT NOT NULL,
    score             INT NOT NULL COMMENT 'Match score out of 100',
    rank              TINYINT NOT NULL,
    created_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user     (user_id),
    INDEX idx_response (response_id),
    INDEX idx_method   (method_id),
    FOREIGN KEY (user_id)     REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (response_id) REFERENCES questionnaire_responses(response_id) ON DELETE CASCADE,
    FOREIGN KEY (method_id)   REFERENCES contraceptive_methods(method_id) ON DELETE CASCADE
);