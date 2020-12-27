create database Fabrica;
use Fabrica;
CREATE TABLE Empleados (
    id INT (50) PRIMARY KEY NOT NULL auto_increment,
    dni VARCHAR(9) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido1 VARCHAR (100) NOT NULL,
    apellido2 VARCHAR (100),
    tipo VARCHAR(50) NOT NULL
);
create table parte (
    id_part int(50) primary key auto_increment,
    emp_crea int(50) not null,
    tec_res int(50),
	nom_tec varchar(100),
	oculto boolean default '0',
    resuelto boolean default '0',
    inf_part varchar(200) not null,
    fecha_resolucion date DEFAULT NULL,
    fecha_hora_creacion timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    hora_resolucion time(5) DEFAULT NULL,
	pieza varchar(100) DEFAULT null,
    constraint part_crea_emp
    foreign key (emp_crea)
    references Empleados (id)
    on update cascade
    on delete cascade,
    constraint part_res_tec
    foreign key (tec_res)
    references Empleados (id)
    on delete cascade
    on update cascade
);

 CREATE TABLE Notes (
    Id INT(50) PRIMARY KEY NOT NULL auto_increment,
    employee INT(50) NOT NULL,
    incidence INT(50) NOT NULL,
    noteType VARCHAR(50) NOT NULL,
    noteStr VARCHAR(200) NOT NULL,
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

insert into Empleados (dni, nombre, apellido1, apellido2, tipo)
values ('12345678Z', 'Jose Javier', 'Valero', 'Fuentes', 'Tecnico');

insert into Empleados (dni, nombre, apellido1, apellido2, tipo)
values ('12345679Z', 'Juan Francisco', 'Navarro', 'Ramiro', 'Tecnico');

insert into Empleados (dni, nombre, apellido1, apellido2, tipo)
values ('11111111Z', 'Jose', 'admin', 'istrador', 'Admin');

insert into Empleados (dni, nombre, apellido1, apellido2, tipo)
values ('12345678A', 'Jose', 'jackson', 'arzapalo', 'Limpiador');

insert into Empleados (dni, nombre, apellido1, apellido2, tipo)
values ('12345678S', 'Jose Antonio', 'Lidon', 'Ferrer', 'Limpiador');

insert into Empleados (dni, nombre, apellido1, apellido2, tipo)
values ('12345678C', 'Samuel', 'Garcia', 'Sanchez', 'Encargado');

insert into Empleados (dni, nombre, apellido1, tipo)
values ('12345678B', 'jessie', 'deep', 'Tecnico');

INSERT INTO credentials (username, password, employee) VALUES ('12345678Z', MD5('1234'), 1);
INSERT INTO credentials (username, password, employee) VALUES ('12345679', MD5('1234'), 2);
INSERT INTO credentials (username, password, employee) VALUES ('11111111Z', MD5('1234'), 3);
INSERT INTO credentials (username, password, employee) VALUES ('12345678A', MD5('1234'), 4);
INSERT INTO credentials (username, password, employee) VALUES ('12345678S', MD5('1234'), 5);
INSERT INTO credentials (username, password, employee) VALUES ('12345678C', MD5('1234'), 6);
INSERT INTO credentials (username, password, employee) VALUES ('12345678B', MD5('1234'), 7);

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
WHERE resuelto=1
GROUP BY id_part, tec_res, nom_tec;
