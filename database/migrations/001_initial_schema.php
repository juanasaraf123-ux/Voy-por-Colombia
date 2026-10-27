<?php
declare(strict_types=1);

/**
 * Esquema inicial. Los marcadores {PK}, {FK}, {LONGTEXT} y {ENGINE} se traducen según el motor
 * (MySQL/MariaDB o SQLite) en database/Migrator.php.
 */
return [
    "CREATE TABLE roles (
        id {PK},
        slug VARCHAR(30) NOT NULL UNIQUE,
        name VARCHAR(60) NOT NULL,
        description VARCHAR(255) NULL
    ){ENGINE}",

    "CREATE TABLE users (
        id {PK},
        role_id {FK} NOT NULL,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        username VARCHAR(60) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        is_active TINYINT NOT NULL DEFAULT 1,
        session_version VARCHAR(32) NOT NULL DEFAULT '1',
        last_login_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (role_id) REFERENCES roles(id)
    ){ENGINE}",

    "CREATE TABLE login_attempts (
        id {PK},
        identifier VARCHAR(190) NOT NULL,
        ip VARCHAR(45) NOT NULL,
        success TINYINT NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL
    ){ENGINE}",
    "CREATE INDEX idx_login_attempts_identifier ON login_attempts (identifier, created_at)",
    "CREATE INDEX idx_login_attempts_ip ON login_attempts (ip, created_at)",

    "CREATE TABLE regions (
        id {PK},
        name VARCHAR(80) NOT NULL,
        slug VARCHAR(80) NOT NULL UNIQUE,
        color VARCHAR(9) NOT NULL DEFAULT '#0f9d77',
        summary VARCHAR(400) NULL,
        sort_order INT NOT NULL DEFAULT 0
    ){ENGINE}",

    "CREATE TABLE collections (
        id {PK},
        name VARCHAR(120) NOT NULL,
        slug VARCHAR(140) NOT NULL UNIQUE,
        description VARCHAR(400) NULL,
        sort_order INT NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL
    ){ENGINE}",

    "CREATE TABLE departments (
        id {PK},
        region_id {FK} NULL,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        iso_code VARCHAR(10) NULL,
        capital VARCHAR(100) NULL,
        summary VARCHAR(500) NULL,
        cover_media_id {FK} NULL,
        search_name VARCHAR(120) NOT NULL DEFAULT '',
        FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE SET NULL
    ){ENGINE}",

    "CREATE TABLE municipalities (
        id {PK},
        department_id {FK} NOT NULL,
        name VARCHAR(120) NOT NULL,
        slug VARCHAR(140) NOT NULL,
        lat DECIMAL(10,6) NULL,
        lng DECIMAL(10,6) NULL,
        search_name VARCHAR(140) NOT NULL DEFAULT '',
        UNIQUE (department_id, slug),
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
    ){ENGINE}",
    "CREATE INDEX idx_municipalities_search ON municipalities (search_name)",

    "CREATE TABLE media (
        id {PK},
        kind VARCHAR(12) NOT NULL,
        path VARCHAR(255) NULL,
        mime VARCHAR(80) NULL,
        original_name VARCHAR(190) NULL,
        size_bytes INT NOT NULL DEFAULT 0,
        width INT NULL,
        height INT NULL,
        variants TEXT NULL,
        embed_provider VARCHAR(20) NULL,
        embed_id VARCHAR(40) NULL,
        title VARCHAR(190) NULL,
        alt_text VARCHAR(255) NULL,
        caption VARCHAR(500) NULL,
        credit VARCHAR(190) NULL,
        license VARCHAR(120) NULL,
        source_url VARCHAR(500) NULL,
        department_id {FK} NULL,
        municipality_id {FK} NULL,
        collection_id {FK} NULL,
        gallery_section VARCHAR(20) NULL,
        in_gallery TINYINT NOT NULL DEFAULT 0,
        gallery_featured TINYINT NOT NULL DEFAULT 0,
        is_demo TINYINT NOT NULL DEFAULT 0,
        uploaded_by {FK} NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
        FOREIGN KEY (municipality_id) REFERENCES municipalities(id) ON DELETE SET NULL,
        FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE SET NULL,
        FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
    ){ENGINE}",
    "CREATE INDEX idx_media_gallery ON media (in_gallery, kind, created_at)",

    "CREATE TABLE categories (
        id {PK},
        section VARCHAR(20) NOT NULL,
        name VARCHAR(80) NOT NULL,
        slug VARCHAR(90) NOT NULL,
        icon VARCHAR(30) NULL,
        color VARCHAR(9) NULL,
        sort_order INT NOT NULL DEFAULT 0,
        UNIQUE (section, slug)
    ){ENGINE}",

    "CREATE TABLE sections (
        id {PK},
        section_key VARCHAR(20) NOT NULL UNIQUE,
        title VARCHAR(80) NOT NULL,
        tagline VARCHAR(160) NULL,
        description VARCHAR(500) NULL,
        cover_media_id {FK} NULL,
        sort_order INT NOT NULL DEFAULT 0,
        show_on_home TINYINT NOT NULL DEFAULT 1,
        FOREIGN KEY (cover_media_id) REFERENCES media(id) ON DELETE SET NULL
    ){ENGINE}",

    "CREATE TABLE entries (
        id {PK},
        section VARCHAR(20) NOT NULL,
        title VARCHAR(190) NOT NULL,
        slug VARCHAR(190) NOT NULL,
        summary VARCHAR(400) NULL,
        body {LONGTEXT} NULL,
        status VARCHAR(12) NOT NULL DEFAULT 'draft',
        published_at DATETIME NULL,
        department_id {FK} NULL,
        municipality_id {FK} NULL,
        lat DECIMAL(10,6) NULL,
        lng DECIMAL(10,6) NULL,
        show_on_map TINYINT NOT NULL DEFAULT 1,
        cover_media_id {FK} NULL,
        is_featured TINYINT NOT NULL DEFAULT 0,
        featured_order INT NOT NULL DEFAULT 0,
        is_hidden_gem TINYINT NOT NULL DEFAULT 0,
        event_start DATETIME NULL,
        event_end DATETIME NULL,
        author_name VARCHAR(120) NULL,
        meta TEXT NULL,
        search_text TEXT NULL,
        is_demo TINYINT NOT NULL DEFAULT 0,
        created_by {FK} NULL,
        updated_by {FK} NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE (section, slug),
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
        FOREIGN KEY (municipality_id) REFERENCES municipalities(id) ON DELETE SET NULL,
        FOREIGN KEY (cover_media_id) REFERENCES media(id) ON DELETE SET NULL,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
    ){ENGINE}",
    "CREATE INDEX idx_entries_public ON entries (section, status, published_at)",
    "CREATE INDEX idx_entries_department ON entries (department_id)",
    "CREATE INDEX idx_entries_municipality ON entries (municipality_id)",
    "CREATE INDEX idx_entries_event ON entries (event_start)",

    "CREATE TABLE entry_categories (
        entry_id {FK} NOT NULL,
        category_id {FK} NOT NULL,
        PRIMARY KEY (entry_id, category_id),
        FOREIGN KEY (entry_id) REFERENCES entries(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    ){ENGINE}",

    "CREATE TABLE entry_media (
        entry_id {FK} NOT NULL,
        media_id {FK} NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        PRIMARY KEY (entry_id, media_id),
        FOREIGN KEY (entry_id) REFERENCES entries(id) ON DELETE CASCADE,
        FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
    ){ENGINE}",

    "CREATE TABLE routes (
        id {PK},
        title VARCHAR(190) NOT NULL,
        slug VARCHAR(190) NOT NULL UNIQUE,
        summary VARCHAR(400) NULL,
        body {LONGTEXT} NULL,
        duration VARCHAR(60) NULL,
        cover_media_id {FK} NULL,
        status VARCHAR(12) NOT NULL DEFAULT 'draft',
        is_featured TINYINT NOT NULL DEFAULT 0,
        is_demo TINYINT NOT NULL DEFAULT 0,
        created_by {FK} NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (cover_media_id) REFERENCES media(id) ON DELETE SET NULL,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ){ENGINE}",

    "CREATE TABLE route_stops (
        route_id {FK} NOT NULL,
        entry_id {FK} NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        note VARCHAR(255) NULL,
        PRIMARY KEY (route_id, entry_id),
        FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE,
        FOREIGN KEY (entry_id) REFERENCES entries(id) ON DELETE CASCADE
    ){ENGINE}",

    "CREATE TABLE settings (
        setting_key VARCHAR(80) NOT NULL PRIMARY KEY,
        setting_value TEXT NULL,
        updated_at DATETIME NOT NULL
    ){ENGINE}",

    "CREATE TABLE audit_log (
        id {PK},
        user_id {FK} NULL,
        action VARCHAR(30) NOT NULL,
        entity_type VARCHAR(30) NOT NULL,
        entity_id INT NULL,
        summary VARCHAR(255) NOT NULL,
        ip VARCHAR(45) NULL,
        created_at DATETIME NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ){ENGINE}",
    "CREATE INDEX idx_audit_created ON audit_log (created_at)",
];
