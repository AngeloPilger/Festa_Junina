CREATE DATABASE IF NOT EXISTS junina;
USE junina;

DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(50) NOT NULL,
    tipo ENUM('usuario','admin') NOT NULL DEFAULT 'usuario',
    comidas VARCHAR(100) NOT NULL
);

DROP TABLE IF EXISTS vendas;
CREATE TABLE vendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    comida VARCHAR(50) NOT NULL,
    quantidade INT NOT NULL,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO usuarios (usuario, senha, tipo, comidas) VALUES
('TerA', '123', 'usuario', 'Pamonha,Canjica'),
('TerB', '123', 'usuario', 'Milho Cozido,Bolo de Fubá'),
('TerC', '123', 'usuario', 'Arroz Doce,Maçã do Amor'),
('TerD', '123', 'usuario', 'Pé-de-moleque,Cocada'),
('admin', 'admin123', 'admin', 'Pamonha,Canjica,Milho Cozido,Bolo de Fubá,Arroz Doce,Maçã do Amor,Pé-de-moleque,Cocada');