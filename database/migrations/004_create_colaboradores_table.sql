-- Create colaboradores table
CREATE TABLE IF NOT EXISTS colaboradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(50) NULL,
    observaciones TEXT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add colaborador_id to invitados table
ALTER TABLE invitados ADD COLUMN colaborador_id INT NULL AFTER evento_id;

-- Add foreign key constraint
ALTER TABLE invitados ADD CONSTRAINT fk_invitados_colaborador FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE SET NULL;

-- Create index for faster lookups
CREATE INDEX idx_colaboradores_activo ON colaboradores(activo);
CREATE INDEX idx_invitados_colaborador ON invitados(colaborador_id);
