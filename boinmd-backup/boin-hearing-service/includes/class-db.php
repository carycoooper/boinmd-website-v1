<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BHS_DB {
    const DB_VERSION = '0.4.0';

    public static function sessions_table() {
        global $wpdb;
        return $wpdb->prefix . 'boin_hearing_sessions';
    }

    public static function results_table() {
        global $wpdb;
        return $wpdb->prefix . 'boin_hearing_results';
    }

    public static function requests_table() {
        global $wpdb;
        return $wpdb->prefix . 'boin_service_requests';
    }

    public static function logs_table() {
        global $wpdb;
        return $wpdb->prefix . 'boin_hearing_logs';
    }

    public static function install() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset = $wpdb->get_charset_collate();

        dbDelta( "CREATE TABLE " . self::sessions_table() . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            session_uuid VARCHAR(64) NOT NULL,
            session_token VARCHAR(128) NOT NULL,
            phone VARCHAR(32) NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'created',
            current_ear VARCHAR(16) NULL,
            current_frequency INT NULL,
            current_level INT NULL,
            completed_steps INT NOT NULL DEFAULT 0,
            total_steps INT NOT NULL DEFAULT 12,
            device_type VARCHAR(100) NULL,
            browser VARCHAR(100) NULL,
            operating_system VARCHAR(100) NULL,
            user_agent TEXT NULL,
            headphone_confirmed TINYINT NOT NULL DEFAULT 0,
            volume_confirmed TINYINT NOT NULL DEFAULT 0,
            ip_hash VARCHAR(128) NULL,
            interrupt_reason VARCHAR(255) NULL,
            started_at DATETIME NULL,
            completed_at DATETIME NULL,
            interrupted_at DATETIME NULL,
            expires_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY session_uuid (session_uuid),
            KEY status (status),
            KEY created_at (created_at),
            KEY phone (phone)
        ) $charset;" );

        dbDelta( "CREATE TABLE " . self::results_table() . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id BIGINT UNSIGNED NOT NULL,
            ear VARCHAR(16) NOT NULL,
            frequency INT NOT NULL,
            relative_level INT NULL,
            result_status VARCHAR(32) NULL,
            answer_count INT NOT NULL DEFAULT 0,
            replay_count INT NOT NULL DEFAULT 0,
            raw_steps_json LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY session_freq (session_id, ear, frequency),
            KEY session_id (session_id)
        ) $charset;" );

        dbDelta( "CREATE TABLE " . self::requests_table() . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            request_uuid VARCHAR(64) NOT NULL,
            phone VARCHAR(32) NOT NULL,
            device_id BIGINT UNSIGNED NULL,
            device_name_snapshot VARCHAR(255) NULL,
            hearing_session_id BIGINT UNSIGNED NULL,
            main_problem VARCHAR(255) NULL,
            usage_scene VARCHAR(255) NULL,
            ear_description VARCHAR(255) NULL,
            feedback_options_json LONGTEXT NULL,
            description LONGTEXT NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'pending',
            admin_note LONGTEXT NULL,
            wecom_status VARCHAR(32) NULL,
            wecom_sent_at DATETIME NULL,
            wecom_error TEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY request_uuid (request_uuid),
            KEY phone (phone),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset;" );

        dbDelta( "CREATE TABLE " . self::logs_table() . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            log_type VARCHAR(64) NOT NULL,
            level VARCHAR(24) NOT NULL DEFAULT 'info',
            message TEXT NOT NULL,
            context_json LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY log_type (log_type),
            KEY created_at (created_at)
        ) $charset;" );

        update_option( 'boin_hearing_service_db_version', self::DB_VERSION, false );
    }
}
