-- =====================================================
-- BASE DE DATOS: labclinics
-- Normalización: Tercera Forma Normal (3FN)
-- =====================================================

DROP DATABASE IF EXISTS labclinics;
CREATE DATABASE labclinics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE labclinics;

-- =====================================================
-- TABLA: especialidades
-- Almacena las especialidades médicas (catálogo)
-- =====================================================
CREATE TABLE especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activo BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB;

-- =====================================================
-- TABLA: usuarios
-- Usuarios del backoffice (admin y usuarios)
-- =====================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt',
    rol ENUM('admin', 'usuario') NOT NULL DEFAULT 'usuario',
    activo BOOLEAN DEFAULT 1,
    ultimo_acceso TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_rol (rol)
) ENGINE=InnoDB;

-- =====================================================
-- TABLA: medicos
-- Información principal de médicos
-- =====================================================
CREATE TABLE medicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE COMMENT 'Código único para QR (ej: DR001)',
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    especialidad_id INT NOT NULL,
    
    -- Información de contacto
    telefono VARCHAR(20),
    email VARCHAR(100),
    
    -- Ubicación consultorio
    direccion_consultorio VARCHAR(255),
    ciudad VARCHAR(100),
    referencia_ubicacion TEXT COMMENT 'Ej: Cerca del hospital central',
    
    -- Horarios
    horario_atencion TEXT COMMENT 'Formato: Lun-Vie 8:00-17:00',
    
    -- Información adicional
    descripcion TEXT COMMENT 'Bio del médico',
    foto VARCHAR(255) COMMENT 'Ruta del archivo de foto',
    
    -- Control
    activo BOOLEAN DEFAULT 1,
    destacado BOOLEAN DEFAULT 0 COMMENT 'Para destacar médicos principales',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Claves foráneas
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) ON DELETE RESTRICT,
    
    -- Índices
    INDEX idx_codigo (codigo),
    INDEX idx_apellido (apellido),
    INDEX idx_especialidad (especialidad_id),
    INDEX idx_activo (activo)
) ENGINE=InnoDB;

-- =====================================================
-- TABLA: redes_sociales_medico
-- Redes sociales de cada médico (relación N:N)
-- =====================================================
CREATE TABLE redes_sociales_medico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medico_id INT NOT NULL,
    tipo_red ENUM('facebook', 'instagram', 'whatsapp', 'linkedin', 'twitter', 'tiktok', 'youtube') NOT NULL,
    url_perfil VARCHAR(255) NOT NULL COMMENT 'URL completa o username',
    orden TINYINT DEFAULT 0 COMMENT 'Orden de visualización',
    activo BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Claves foráneas
    FOREIGN KEY (medico_id) REFERENCES medicos(id) ON DELETE CASCADE,
    
    -- Índices
    INDEX idx_medico (medico_id),
    INDEX idx_tipo (tipo_red),
    
    -- Restricción: un médico no puede tener la misma red duplicada
    UNIQUE KEY unique_medico_red (medico_id, tipo_red)
) ENGINE=InnoDB;

-- =====================================================
-- TABLA: referencias
-- Tracking de escaneos de QR (métricas)
-- =====================================================
CREATE TABLE referencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medico_id INT NOT NULL,
    
    -- Información del escaneo
    fecha_escaneo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) COMMENT 'IPv4 o IPv6',
    user_agent TEXT COMMENT 'Navegador/dispositivo',
    
    -- Geolocalización (opcional para futuro)
    pais VARCHAR(50),
    ciudad VARCHAR(100),
    
    -- Claves foráneas
    FOREIGN KEY (medico_id) REFERENCES medicos(id) ON DELETE CASCADE,
    
    -- Índices para reportes
    INDEX idx_medico (medico_id),
    INDEX idx_fecha (fecha_escaneo),
    INDEX idx_medico_fecha (medico_id, fecha_escaneo)
) ENGINE=InnoDB;

-- =====================================================
-- TABLA: configuracion
-- Configuraciones generales del sistema
-- =====================================================
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(50) NOT NULL UNIQUE,
    valor TEXT,
    descripcion VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;