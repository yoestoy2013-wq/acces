CREATE DATABASE IF NOT EXISTS access CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE access;

CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    fecha DATE NOT NULL,
    lugar VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ticket_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    color VARCHAR(25) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_ticket_types_evento FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS invitados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    ticket_type_id INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    apellido VARCHAR(150) NULL,
    dni VARCHAR(50) NULL,
    email VARCHAR(150) NULL,
    telefono VARCHAR(50) NULL,
    observaciones TEXT NULL,
    CONSTRAINT fk_invitados_evento FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    CONSTRAINT fk_invitados_ticket_type FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS checkins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invitado_id INT NOT NULL,
    ticket_type_id INT NOT NULL,
    estado_ingreso VARCHAR(50) NOT NULL DEFAULT 'pendiente',
    checkin_time DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_checkins_invitado FOREIGN KEY (invitado_id) REFERENCES invitados(id) ON DELETE CASCADE,
    CONSTRAINT fk_checkins_ticket_type FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS checkin_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checkin_id INT NOT NULL,
    estado_anterior VARCHAR(50) NULL,
    estado_nuevo VARCHAR(50) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_checkin_history_checkin FOREIGN KEY (checkin_id) REFERENCES checkins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
