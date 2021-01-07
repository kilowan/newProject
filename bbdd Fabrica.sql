create database Fabrica;
use Fabrica;

CREATE TABLE Empleados (
    id INT(50) PRIMARY KEY NOT NULL auto_increment,
    dni VARCHAR(9) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido1 VARCHAR (100) NOT NULL,
    apellido2 VARCHAR (100),
    tipo VARCHAR(50) NOT NULL,
    borrado TINYINT(1) NOT NULL DEFAULT 0
);

CREATE TABLE permissionlist (
    id INT(50) PRIMARY KEY NOT NULL auto_increment,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE employee_permissions (
    id INT(50) PRIMARY KEY NOT NULL auto_increment,
    employee INT(50) NOT NULL,
    permission INT(50) NOT NULL,
    CONSTRAINT perm_id
    FOREIGN KEY (permission)
    REFERENCES permissionlist (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
    CONSTRAINT emp_id
    FOREIGN KEY (employee)
    REFERENCES Empleados (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
);

INSERT INTO permissionlist (id, name) VALUES
(1, 'Datos personales'),
(2, 'Estadísticas'),
(3, 'Partes abiertos de empleados'),
(4, 'Partes cerrados de empleados'),
(5, 'Partes atendidos de empleados'),
(6, 'Partes abiertos creados por el mismo'),
(7, 'Partes atendidos creados por el mismo'),
(8, 'Partes cerrados (visibles) creados por el mismo'),
(9, 'Partes cerrados (ocultos) creados por el mismo'),
(10, 'Partes abiertos de empleados (no propios)'),
(11, 'Partes cerrados de empleados (no propios)'),
(12, 'Partes atendidos de empleados (no propios)'),
(13, 'Crear parte'),
(14, 'Borrar parte propio no atendido'),
(15, 'Atender parte de empleados (no propios)'),
(16, 'Lista de empleados'),
(17, 'Estadísticas globales'),
(18, 'Piezas reportadas'),
(19, 'Crear empleado'),
(20, 'Modificar parte de empleados'),
(21, 'Modificar parte de empleados'),
(22, 'Ocultar parte propio cerrado');

CREATE TABLE parte (
    id_part INT(50) PRIMARY KEY auto_increment,
    emp_crea INT(50) NOT NULL,
    tec_res INT(50),
	nom_tec VARCHAR(100),
    inf_part VARCHAR(200) NOT NULL,
    fecha_resolucion date DEFAULT NULL,
    fecha_hora_creacion timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    hora_resolucion time(5) DEFAULT NULL,
	pieza VARCHAR(100) DEFAULT NULL,
    state INT(50) DEFAULT 1 NOT NULL,
    CONSTRAINT part_crea_emp
    FOREIGN KEY (emp_crea)
    REFERENCES Empleados (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
    CONSTRAINT part_res_tec
    FOREIGN KEY (tec_res)
    REFERENCES Empleados (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
    CONSTRAINT incidence_state
    FOREIGN KEY (state)
    REFERENCES state (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

CREATE TABLE state (
    id INT(50) PRIMARY KEY NOT NULL auto_increment,
    name VARCHAR(50) NOT NULL
);

 CREATE TABLE Notes (
    Id INT(50) PRIMARY KEY NOT NULL auto_increment,
    employee INT(50) NOT NULL,
    incidence INT(50) NOT NULL,
    noteType VARCHAR(50) NOT NULL,
    noteStr VARCHAR(200) NOT NULL,
    date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    CONSTRAINT notes_employee
    FOREIGN KEY (employee)
    REFERENCES Empleados (id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
    CONSTRAINT notes_incidence
    FOREIGN KEY (incidence)
    REFERENCES parte (id_part)
    ON UPDATE CASCADE
    ON DELETE CASCADE
 );

 CREATE TABLE Credentials(
     Id INT(50) PRIMARY KEY NOT NULL auto_increment,
     username VARCHAR(100) UNIQUE NOT NULL,
     password VARCHAR(100) NOT NULL,
     employee INT(50) NOT NULL,
     CONSTRAINT credentials_employee
    FOREIGN KEY (employee)
    REFERENCES Empleados (id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
 );

INSERT INTO state (name) VALUES 
('Nuevo'),
('En curso'),
('Cerrado');

INSERT INTO Empleados (dni, nombre, apellido1, apellido2, tipo) VALUES 
('12345678Z', 'Jose Javier', 'Valero', 'Fuentes', 'Tecnico'),
('12345679Z', 'Juan Francisco', 'Navarro', 'Ramiro', 'Tecnico'),
('11111111Z', 'Jose', 'admin', 'istrador', 'Admin'),
('12345678A', 'Jose', 'jackson', 'arzapalo', 'Limpiador'),
('12345678S', 'Jose Antonio', 'Lidon', 'Ferrer', 'Limpiador'),
('12345678C', 'Samuel', 'Garcia', 'Sanchez', 'Encargado'),
('12345678B', 'jessie', 'deep', 'Tecnico');

INSERT INTO credentials (username, password, employee) VALUES 
('12345678Z', MD5('1234'), 1),
('12345679', MD5('1234'), 2),
('11111111Z', MD5('1234'), 3),
('12345678A', MD5('1234'), 4),
('12345678S', MD5('1234'), 5),
('12345678C', MD5('1234'), 6),
('12345678B', MD5('1234'), 7);

CREATE USER 'Ad'@'localhost' IDENTIFIED BY '1234';
GRANT ALL PRIVILEGES ON `fabrica`.* TO 'Ad'@'localhost' WITH GRANT OPTION;

CREATE OR REPLACE VIEW Tiempo_resolucion AS 
SELECT ((((YEAR(fecha_resolucion))-(YEAR(fecha_hora_creacion)))*31536000)+
(((MONTH(fecha_resolucion))-(MONTH(fecha_hora_creacion)))*2592000)+
(((DAY(fecha_resolucion))-(DAY(fecha_hora_creacion)))*86400)+
(((HOUR(hora_resolucion))-(HOUR(fecha_hora_creacion)))*3600)+
(((MINUTE(hora_resolucion))-(MINUTE(fecha_hora_creacion)))*60)+
((SECOND(hora_resolucion))-(SECOND(fecha_hora_creacion)))) AS "Tiempo", id_part, tec_res, nom_tec
FROM parte
WHERE state=3
GROUP BY id_part, tec_res, nom_tec;