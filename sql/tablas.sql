-- Tabla de usuarios generales (estudiantes y profesores)
CREATE TABLE usuarios (
    cedula VARCHAR(12) PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL,
    apellido VARCHAR(20) NOT NULL,
    correo VARCHAR(50) NOT NULL UNIQUE,
    id_tipoUsuario INT NOT NULL,  -- Estudiante o Profesor
    pass VARCHAR(255) NOT NULL   -- Contraseña (asegurada con hash)
);

-- Tabla de estudiantes
CREATE TABLE estudiantes (
    cedula VARCHAR(12) PRIMARY KEY,
    id_carrera INT NOT NULL,
    grupo INT NOT NULL,
    estado_academico ENUM('Activo', 'Retirado', 'Suspendido') DEFAULT 'Activo'
);

-- Tabla de profesores
CREATE TABLE profesores (
    cedula VARCHAR(12) PRIMARY KEY,
    id_cursos INT NOT NULL
);

-- Tabla de carreras
CREATE TABLE carreras (
    id_carrera INT AUTO_INCREMENT PRIMARY KEY,
    nombre_carrera VARCHAR(50) NOT NULL
);

-- Tabla de grupos
CREATE TABLE grupos (
    id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_grupo VARCHAR(50) NOT NULL
);

-- Tabla de cursos
CREATE TABLE cursos (
    id_curso INT AUTO_INCREMENT PRIMARY KEY,
    nombre_curso VARCHAR(50) NOT NULL
);

-- Tabla de tipos de usuario (estudiante, profesor, etc.)
CREATE TABLE tipos_usuario (
    id_tipoUsuario INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(20) NOT NULL
);

-- Tabla de asistencia
CREATE TABLE asistencia (
    id_asistencia INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(12) NOT NULL,
    id_curso INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    asistencia ENUM('Asistió', 'No asistió') NOT NULL
);

-- Tabla de credenciales para login
CREATE TABLE login (
    correo VARCHAR(50) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL
);

-- Tabla de reportes
CREATE TABLE reportes(
    id_reporte INT AUTO_INCREMENT PRIMARY KEY,
    descripcion TEXT NOT NULL 
);

-- Tabla de reportes de asistencia
CREATE TABLE reportes_asistencia (
    id_reporte INT AUTO_INCREMENT PRIMARY KEY,
    id_clase INT NOT NULL,
    fecha DATE NOT NULL,
    total_estudiantes INT NOT NULL,
    estudiantes_presentes INT NOT NULL,
    estudiantes_ausentes INT NOT NULL,
    porcentaje_asistencia DECIMAL(5,2),
    cedula_profesor VARCHAR(12) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de historial de clases
CREATE TABLE historial_clase (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    id_curso INT NOT NULL
);

-- Tabla de estadísticas
CREATE TABLE estadisticas (
    id_estadistica INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    fecha DATE NOT NULL,
    total_estudiantes INT NOT NULL,
    total_faltantes INT NOT NULL,
    porcentaje_asistencia DECIMAL(5,2) NOT NULL
);

-- Tabla de usuarios
ALTER TABLE usuarios
    ADD CONSTRAINT FK_usuarios_tipoUsuario FOREIGN KEY (id_tipoUsuario) REFERENCES tipos_usuario(id_tipoUsuario);

-- Tabla de estudiantes
ALTER TABLE estudiantes
    ADD CONSTRAINT FK_estudiantes_usuarios FOREIGN KEY (cedula) REFERENCES usuarios(cedula),
    ADD CONSTRAINT FK_estudiantes_carrera FOREIGN KEY (id_carrera) REFERENCES carreras(id_carrera);

-- Tabla de profesores
ALTER TABLE profesores
    ADD CONSTRAINT FK_profesores_usuarios FOREIGN KEY (cedula) REFERENCES usuarios(cedula),
    ADD CONSTRAINT FK_profesores_cursos FOREIGN KEY (id_cursos) REFERENCES cursos(id_curso);

-- Tabla de asistencia
ALTER TABLE asistencia
    ADD CONSTRAINT FK_asistencia_usuarios FOREIGN KEY (cedula) REFERENCES usuarios(cedula),
    ADD CONSTRAINT FK_asistencia_cursos FOREIGN KEY (id_curso) REFERENCES cursos(id_curso);

-- Tabla de reportes de asistencia
ALTER TABLE reportes_asistencia
    ADD CONSTRAINT FK_reportes_asistencia_historial FOREIGN KEY (id_clase) REFERENCES historial_clase(id_historial),
    ADD CONSTRAINT FK_reportes_asistencia_profesor FOREIGN KEY (cedula_profesor) REFERENCES usuarios(cedula);

-- Tabla de historial de clases
ALTER TABLE historial_clase
    ADD CONSTRAINT FK_historial_curso FOREIGN KEY (id_curso) REFERENCES cursos(id_curso);

-- Tabla de estadísticas
ALTER TABLE estadisticas
    ADD CONSTRAINT FK_estadisticas_curso FOREIGN KEY (id_curso) REFERENCES cursos(id_curso);
    
    