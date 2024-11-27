-- Insertar tipos de usuarios
INSERT INTO tipos_usuario (tipo) VALUES
('Profesor'),
('Estudiante'),
('Administrador');

-- Insertar carreras
INSERT INTO carreras (nombre_carrera) VALUES
('Ingeniería Civil'),
('Ingeniería Mecánica'),
('Ingeniería Eléctrica'),
('Ingeniería Industrial'),
('Ingeniería de Sistemas Computacionales'),
('Ingeniería en Telecomunicaciones'),
('Arquitectura'),
('Diseño Gráfico'),
('Administración de Empresas'),
('Ciencias de la Computación');

-- Insertar cursos
INSERT INTO cursos (nombre_curso) VALUES
('Cálculo I'),
('Física I'),
('Química General'),
('Estructuras I'),
('Mecánica de Materiales'),
('Teoría de Circuitos'),
('Algoritmos y Estructuras de Datos'),
('Programación en C'),
('Bases de Datos'),
('Gestión de Proyectos');

INSERT INTO grupos (nombre_grupo) VALUES
('4LS101'),
('3LS201'),
('2LS301'),
('1LS401'),
('4IC101'),
('3IC201'),
('2IC301'),
('1IC401'),
('4MC101'),
('3MC201'),
('2MC301'),
('1MC401'),
('4EE101'),
('3EE201'),
('2EE301'),
('1EE401'),
('4II101'),
('3II201'),
('2II301'),
('1II401');

INSERT INTO usuarios (cedula, nombre, apellido, correo, id_tipoUsuario, pass)
VALUES ('9-763-2168', 'Jason', 'Arena', 'jason.arena@utp.ac.pa', 2, '1234');
