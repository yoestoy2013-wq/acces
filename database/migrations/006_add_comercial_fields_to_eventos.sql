USE access;

ALTER TABLE eventos
ADD COLUMN estado ENUM('ACTIVO','SUSPENDIDO','FINALIZADO') NOT NULL DEFAULT 'ACTIVO',
ADD COLUMN organizador VARCHAR(150) NULL,
ADD COLUMN fecha_vencimiento DATE NULL;

CREATE INDEX idx_eventos_estado ON eventos(estado);
