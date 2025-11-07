-- =====================================================
-- DATOS INICIALES - LabClinics
-- =====================================================

USE labclinics;

-- =====================================================
-- ESPECIALIDADES MÉDICAS
-- =====================================================
INSERT INTO especialidades (nombre, descripcion) VALUES
('Cardiología', 'Especialista en enfermedades del corazón'),
('Dermatología', 'Especialista en piel, cabello y uñas'),
('Pediatría', 'Especialista en salud infantil'),
('Ginecología', 'Especialista en salud femenina'),
('Traumatología', 'Especialista en huesos y articulaciones'),
('Oftalmología', 'Especialista en ojos y visión'),
('Neurología', 'Especialista en sistema nervioso'),
('Psiquiatría', 'Especialista en salud mental'),
('Medicina General', 'Atención médica general'),
('Endocrinología', 'Especialista en hormonas y metabolismo'),
('Gastroenterología', 'Especialista en sistema digestivo'),
('Urología', 'Especialista en sistema urinario'),
('Odontología', 'Especialista en salud dental'),
('Nutrición', 'Especialista en alimentación y dietas');

-- =====================================================
-- USUARIO ADMINISTRADOR INICIAL
-- Password: admin123 (cambiar en producción)
-- =====================================================
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Administrador', 'admin@labclinics.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Password hash de "admin123"

-- =====================================================
-- USUARIO REGULAR DE PRUEBA
-- Password: usuario123
-- =====================================================
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Usuario Test', 'usuario@labclinics.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario');

-- =====================================================
-- MÉDICOS DE EJEMPLO
-- =====================================================
INSERT INTO medicos (codigo, nombre, apellido, especialidad_id, telefono, email, direccion_consultorio, ciudad, horario_atencion, descripcion, activo, destacado) VALUES
('DR001', 'Juan Carlos', 'Pérez Rodríguez', 1, '+591 77123456', 'jperez@email.com', 'Av. 16 de Julio #1234', 'La Paz', 'Lun-Vie: 9:00-18:00, Sáb: 9:00-13:00', 'Cardiólogo con 15 años de experiencia. Especialista en arritmias y prevención cardiovascular.', 1, 1),
('DR002', 'María Elena', 'Gutierrez Silva', 2, '+591 77234567', 'mgutierrez@email.com', 'Calle Comercio #567', 'La Paz', 'Lun-Vie: 10:00-19:00', 'Dermatóloga especializada en tratamientos estéticos y medicina antienvejecimiento.', 1, 1),
('DR003', 'Roberto', 'Mamani Quispe', 5, '+591 77345678', 'rmamani@email.com', 'Av. Buenos Aires #890', 'La Paz', 'Lun-Vie: 8:00-17:00, Sáb: 8:00-12:00', 'Traumatólogo deportivo. Especialista en lesiones de rodilla y hombro.', 1, 0),
('DR004', 'Ana Patricia', 'López Vargas', 3, '+591 77456789', 'alopez@email.com', 'Calle Sucre #345', 'La Paz', 'Lun-Vie: 9:00-18:00', 'Pediatra con experiencia en recién nacidos y vacunación infantil.', 1, 0);

-- =====================================================
-- REDES SOCIALES DE MÉDICOS
-- =====================================================
INSERT INTO redes_sociales_medico (medico_id, tipo_red, url_perfil, orden) VALUES
-- Dr. Juan Carlos Pérez
(1, 'facebook', 'https://facebook.com/dr.juanperez', 1),
(1, 'whatsapp', '+59177123456', 2),
(1, 'instagram', 'https://instagram.com/dr.juanperez', 3),

-- Dra. María Elena Gutierrez
(2, 'instagram', 'https://instagram.com/dra.mgutierrez', 1),
(2, 'whatsapp', '+59177234567', 2),
(2, 'facebook', 'https://facebook.com/dra.mariagutierrez', 3),

-- Dr. Roberto Mamani
(3, 'whatsapp', '+59177345678', 1),
(3, 'facebook', 'https://facebook.com/dr.rmamani', 2),

-- Dra. Ana Patricia López
(4, 'whatsapp', '+59177456789', 1),
(4, 'instagram', 'https://instagram.com/dra.analopez', 2);

-- =====================================================
-- REFERENCIAS DE EJEMPLO (para testing de métricas)
-- =====================================================
INSERT INTO referencias (medico_id, ip_address, user_agent, fecha_escaneo) VALUES
(1, '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X)', '2024-11-01 10:30:00'),
(1, '192.168.1.101', 'Mozilla/5.0 (Android 13; Mobile)', '2024-11-01 14:20:00'),
(1, '192.168.1.102', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X)', '2024-11-02 09:15:00'),
(2, '192.168.1.103', 'Mozilla/5.0 (Android 13; Mobile)', '2024-11-02 11:45:00'),
(2, '192.168.1.104', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X)', '2024-11-03 16:30:00'),
(3, '192.168.1.105', 'Mozilla/5.0 (Android 13; Mobile)', '2024-11-03 10:00:00');

-- =====================================================
-- CONFIGURACIÓN DEL SISTEMA
-- =====================================================
INSERT INTO configuracion (clave, valor, descripcion) VALUES
('nombre_sistema', 'LabClinics', 'Nombre del sistema'),
('email_contacto', 'info@labclinics.com', 'Email de contacto'),
('telefono_contacto', '+591 2 2345678', 'Teléfono de contacto'),
('direccion', 'Av. Principal #123, La Paz - Bolivia', 'Dirección del laboratorio'),
('url_base', 'http://localhost/labclinics/public/', 'URL base del sistema');