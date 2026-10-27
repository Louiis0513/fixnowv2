-- Fix Now — Base de datos (Bogotá)
-- Importar: /Applications/XAMPP/xamppfiles/bin/mysql -u root < bd/fixnow.sql

CREATE DATABASE IF NOT EXISTS fixnow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fixnow;

DROP TABLE IF EXISTS contacto;
DROP TABLE IF EXISTS tecnico;
DROP TABLE IF EXISTS barrio;
DROP TABLE IF EXISTS especialidad;
DROP TABLE IF EXISTS admin;

CREATE TABLE admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE especialidad (
    id_especialidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE barrio (
    id_barrio INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tecnico (
    id_tecnico INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    cedula VARCHAR(20) NOT NULL,
    id_especialidad INT NOT NULL,
    id_barrio INT NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    correo VARCHAR(150) DEFAULT NULL,
    password_hash VARCHAR(255) DEFAULT NULL,
    descripcion TEXT,
    foto_url VARCHAR(255) DEFAULT NULL,
    latitud DECIMAL(10,7) DEFAULT NULL,
    longitud DECIMAL(10,7) DEFAULT NULL,
    verificado TINYINT(1) NOT NULL DEFAULT 0,
    fecha_registro DATE NOT NULL,
    UNIQUE KEY uq_tecnico_cedula (cedula),
    UNIQUE KEY uq_tecnico_correo (correo),
    CONSTRAINT fk_tecnico_especialidad FOREIGN KEY (id_especialidad) REFERENCES especialidad(id_especialidad) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_tecnico_barrio FOREIGN KEY (id_barrio) REFERENCES barrio(id_barrio) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE contacto (
    id_contacto INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(100) NOT NULL,
    telefono_usuario VARCHAR(20) NOT NULL,
    descripcion_problema TEXT NOT NULL,
    fecha_solicitud DATE NOT NULL,
    id_tecnico INT NOT NULL,
    medio ENUM('formulario','whatsapp') NOT NULL DEFAULT 'formulario',
    CONSTRAINT fk_contacto_tecnico FOREIGN KEY (id_tecnico) REFERENCES tecnico(id_tecnico) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin: admin@demo.com / Admin123!
INSERT INTO admin (nombre, correo, password_hash) VALUES
('Administrador', 'admin@demo.com', '$2y$10$eiuYxuFkvYIz7z2BhZBxq.LgVGz9kckdPQUlpQviC51ZzztLyo8Vi');

INSERT INTO especialidad (nombre) VALUES
('Electricista'),
('Plomero'),
('Carpintero'),
('Cerrajero'),
('Pintor'),
('Gasista'),
('Técnico en aires acondicionados'),
('Albañil'),
('Técnico en electrodomésticos');

INSERT INTO barrio (nombre) VALUES
('Chapinero'),
('Usaquén'),
('Suba'),
('Kennedy'),
('Engativá'),
('Fontibón'),
('Bosa'),
('Ciudad Bolívar'),
('Teusaquillo'),
('Puente Aranda'),
('Barrios Unidos'),
('Los Mártires'),
('Santa Fe'),
('San Cristóbal'),
('Rafael Uribe Uribe'),
('Tunjuelito'),
('Antonio Nariño'),
('La Candelaria'),
('Soacha'),
('Chía');

-- Técnicos verificados con fotos reales de oficios + pins aprox. por barrio (Bogotá)
-- cedula: seed demo (no usar en producción como documentos reales)
INSERT INTO tecnico (nombre, apellido, cedula, id_especialidad, id_barrio, telefono, descripcion, foto_url, latitud, longitud, verificado, fecha_registro) VALUES
('Carlos', 'Méndez', '10000000001', 1, 1, '3001234567',
 'Electricista certificado RETIE con 12 años en instalaciones residenciales y comerciales. Tableros, tomacorrientes, iluminación LED y diagnóstico de fallas. Atención misma día en Chapinero y Teusaquillo.',
 '/FixNow/img/tecnicos/electricista1.jpg', 4.6486200, -74.0628100, 1, '2025-01-15'),

('Luis', 'Ramírez', '10000000002', 2, 4, '3109876543',
 'Plomero especializado en fugas, destapes y cambio de tubería PVC/cobre. Servicio urgente 7 días. Garantía escrita en reparaciones mayores. Cobertura Kennedy, Bosa y Fontibón.',
 '/FixNow/img/tecnicos/plomero1.jpg', 4.6281500, -74.1562200, 1, '2025-02-20'),

('Andrés', 'Gómez', '10000000003', 3, 9, '3205551234',
 'Carpintero con taller propio: closets a medida, puertas, muebles de cocina y reparaciones de madera. Cotización sin costo. Experiencia 15 años en Bogotá.',
 '/FixNow/img/tecnicos/carpintero1.jpg', 4.6401800, -74.0903500, 1, '2025-03-10'),

('Pedro', 'Vargas', '10000000004', 4, 2, '3154447788',
 'Cerrajero 24 horas. Apertura de puertas sin daño, cambio de cerraduras multipunto, llaves de seguridad y blindaje. Respuesta promedio 40 minutos en Usaquén y norte.',
 '/FixNow/img/tecnicos/cerrajero1.jpg', 4.7142200, -74.0301500, 1, '2025-04-05'),

('María', 'Torres', '10000000005', 5, 5, '3012223344',
 'Pintora profesional de interiores y fachadas. Acabados mate/satinado, estuco y asesoría de color. Trabajo limpio, protección de muebles incluida. Referencias en Engativá.',
 '/FixNow/img/tecnicos/pintor1.jpg', 4.7010500, -74.1221800, 1, '2025-05-12'),

('Diana', 'Rojas', '10000000006', 2, 7, '3007778899',
 'Plomería residencial: lavaderos, sanitarios, calentadores y sifones. Disponible fines de semana. Certificada y verificada. Atención prioritaria en Bosa y Soacha.',
 '/FixNow/img/tecnicos/plomero2.jpg', 4.6150800, -74.1854200, 1, '2025-06-18'),

('Jorge', 'Castro', '10000000007', 1, 3, '3186669900',
 'Electricista junior con certificación SENA. Instalaciones básicas, cambio de breakers y mantenimiento preventivo. En proceso de verificación de documentos.',
 '/FixNow/img/tecnicos/electricista2.jpg', 4.7411200, -74.0832500, 0, '2025-08-01'),

('Felipe', 'Suárez', '10000000008', 3, 19, '3121112233',
 'Carpintería general y estructuras livianas. Pendiente de verificación de documentos. Disponible en Soacha y sur de Bogotá.',
 '/FixNow/img/tecnicos/carpintero2.jpg', 4.5871500, -74.2143800, 0, '2025-09-01'),

('Rocío', 'Hernández', '10000000009', 6, 6, '3114567890',
 'Gasista autorizada. Revisión de estufas, hornos, calentadores a gas y detección de fugas. Emisión de certificado de conformidad. Cobertura Fontibón y Engativá.',
 '/FixNow/img/tecnicos/gasista1.jpg', 4.6782200, -74.1410500, 1, '2025-03-22'),

('Miguel', 'Ortega', '10000000010', 7, 2, '3045678901',
 'Técnico en aires acondicionados split e inverter. Instalación, carga de gas, limpieza profunda y mantenimiento preventivo. Garantía 6 meses en mano de obra.',
 '/FixNow/img/tecnicos/aire1.jpg', 4.7098500, -74.0354200, 1, '2025-04-18'),

('Héctor', 'Pineda', '10000000011', 8, 8, '3136789012',
 'Albañil y maestro de obra: enchapes, pañetes, reparaciones de humedad y remodelaciones menores. Cotización clara y materiales de calidad. Ciudad Bolívar y Tunjuelito.',
 '/FixNow/img/tecnicos/albanil1.jpg', 4.5661800, -74.1542500, 1, '2025-05-28'),

('Sandra', 'López', '10000000012', 9, 11, '3008901234',
 'Técnica en electrodomésticos: neveras, lavadoras, secadoras y hornos eléctricos. Diagnóstico a domicilio. Repuestos originales cuando aplica. Barrios Unidos y Chapinero.',
 '/FixNow/img/tecnicos/mujer1.jpg', 4.6681500, -74.0753200, 1, '2025-07-02'),

('Óscar', 'Beltrán', '10000000013', 1, 10, '3169012345',
 'Electricista industrial y residencial. Redes, UPS, puesta a tierra y automatización básica. Atención empresas y hogares en Puente Aranda y zona industrial.',
 '/FixNow/img/tecnicos/tecnicogen1.jpg', 4.6120800, -74.1102200, 1, '2025-02-08'),

('Camila', 'Duarte', '10000000014', 5, 1, '3020123456',
 'Pintura decorativa, murales y renovación de locales comerciales. Acabados premium y tiempos acordados. Portafolio disponible. Chapinero y Teusaquillo.',
 '/FixNow/img/tecnicos/pintor2.jpg', 4.6451800, -74.0589200, 1, '2025-06-30'),

('Iván', 'Morales', '10000000015', 4, 14, '3171234567',
 'Cerrajero con servicio diurno. Duplicado de llaves, cambio de cilindros y asesoría en seguridad perimetral. San Cristóbal y Rafael Uribe Uribe.',
 '/FixNow/img/tecnicos/cerrajero2.jpg', 4.5642200, -74.0851800, 1, '2025-07-15'),

('Natalia', 'Quiroga', '10000000016', 2, 9, '3052345678',
 'Plomera con formación SENA. Instalaciones sanitarias nuevas, cambio de sifones y sellado de filtraciones. Atención cuidadosa en apartamentos. Teusaquillo.',
 '/FixNow/img/tecnicos/mujer2.jpg', 4.6360500, -74.0861200, 1, '2025-08-20'),

('Ricardo', 'Salamanca', '10000000017', 7, 5, '3183456789',
 'Instalación y mantenimiento de aires comerciales. Trabajo en altura con EPPs. Contratos de mantenimiento para oficinas. Engativá y Suba.',
 '/FixNow/img/tecnicos/hombre1.jpg', 4.7058800, -74.1184500, 1, '2025-09-05'),

('Elena', 'Cárdenas', '10000000018', 6, 4, '3014567890',
 'Revisión de redes de gas natural y GLP. Certificados para predios. Atención rápida en Kennedy. Documentación al día y verificada.',
 '/FixNow/img/tecnicos/gasista1.jpg', 4.6312200, -74.1510800, 1, '2025-01-28');

INSERT INTO contacto (nombre_usuario, telefono_usuario, descripcion_problema, fecha_solicitud, id_tecnico, medio) VALUES
('Ana Pérez', '3001112233', 'Se fue la luz en la cocina y el breaker no reinicia. Zona Chapinero.', '2025-10-01', 1, 'formulario'),
('Roberto Díaz', '3104445566', 'Fuga de agua bajo el lavamanos del baño principal. Urgente.', '2025-10-05', 2, 'whatsapp'),
('Laura Sánchez', '3207778899', 'Necesito una cerradura nueva para la puerta principal del apartamento.', '2025-10-10', 4, 'formulario'),
('Miguel Ortiz', '3158889900', 'Quiero pintar la sala y el comedor, aprox. 40 m². Preferible fin de semana.', '2025-10-12', 5, 'whatsapp'),
('Sofía Vargas', '3019990011', 'El aire acondicionado no enfría y hace ruido. Edificio en Usaquén.', '2025-10-14', 10, 'formulario'),
('Diego Molina', '3125556677', 'Revisión de estufa a gas: olor a gas ocasional. Fontibón.', '2025-10-15', 9, 'whatsapp');
