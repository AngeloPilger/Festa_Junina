DROP DATABASE IF EXISTS junina;

CREATE DATABASE junina CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE junina;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario',
    comidas VARCHAR(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    comida VARCHAR(50) NOT NULL,
    quantidade INT NOT NULL,
    valor_total DECIMAL(8,2) NOT NULL,
    lucro DECIMAL(8,2) NOT NULL,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO usuarios (usuario, senha, tipo, comidas) VALUES
('TerA', 'TrA9x2Lq', 'usuario', 'Cachorro-quente,Algodão doce'),
('TerB', '7bGv4Lqz', 'usuario', 'Salgado no copo,Churros'),
('TerC', 'Kp39Wxzq', 'usuario', 'Pipoca doce,Pipoca salgada'),
('TerD', 'mA72tXvQ', 'usuario', 'Amendoim cri-cri,Maçã do amor'),
('admin', 'AdM91zQt', 'admin', 'Cachorro-quente,Algodão doce,Salgado no copo,Churros,Pipoca doce,Pipoca salgada,Amendoim cri-cri,Maçã do amor,Bolo,Bebidas,Pastel,Crepe');