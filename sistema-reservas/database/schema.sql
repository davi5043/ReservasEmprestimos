-- =========================================================
-- Banco de Dados: sistema_reservas
-- Sistema de Reservas e Empréstimos de Recursos Escolares
-- =========================================================

CREATE DATABASE IF NOT EXISTS sistema_reservas
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sistema_reservas;

-- ---------------------------------------------------------
-- Tabela: usuarios
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(120) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    senha      VARCHAR(255) NOT NULL,
    tipo       ENUM('admin', 'usuario') NOT NULL DEFAULT 'usuario',
    criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabela: categorias (auxiliar)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(80) NOT NULL,
    descricao  VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabela: setores (auxiliar)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS setores (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(80) NOT NULL,
    descricao  VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabela: recursos (entidade principal / core)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS recursos (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(120) NOT NULL,
    descricao     TEXT DEFAULT NULL,
    categoria_id  INT DEFAULT NULL,
    setor_id      INT DEFAULT NULL,
    foto          VARCHAR(255) DEFAULT NULL,
    disponivel    TINYINT(1) NOT NULL DEFAULT 1,
    criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_recurso_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    CONSTRAINT fk_recurso_setor     FOREIGN KEY (setor_id)     REFERENCES setores(id)    ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabela: reservas
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS reservas (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id    INT NOT NULL,
    recurso_id    INT NOT NULL,
    data_reserva  DATE NOT NULL,
    turno         ENUM('manha', 'tarde', 'noite') NOT NULL,
    status        ENUM('ativa', 'concluida', 'cancelada') NOT NULL DEFAULT 'ativa',
    criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reserva_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_reserva_recurso FOREIGN KEY (recurso_id) REFERENCES recursos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Dados iniciais (seed) - opcional, ajuda a testar o sistema
-- ---------------------------------------------------------

-- Usuário administrador padrão (senha: admin123)
-- Hash gerado com password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Administrador', 'admin@escola.com', '$2b$10$XERYBvDz.DKC2dQbGKY7OOsiP7T9Iepk.Y6b7.FX1f/qrB9UY0Hi.', 'admin');

INSERT INTO categorias (nome, descricao) VALUES
('Laboratórios', 'Laboratórios de informática, química e física'),
('Projetores', 'Projetores multimídia e datashows'),
('Kits de Robótica', 'Kits e peças para aulas de robótica'),
('Quadras', 'Quadras poliesportivas e espaços de educação física');

INSERT INTO setores (nome, descricao) VALUES
('TI', 'Setor de Tecnologia da Informação'),
('Manutenção', 'Setor responsável pela manutenção de equipamentos e espaços'),
('Coordenação', 'Coordenação pedagógica');
