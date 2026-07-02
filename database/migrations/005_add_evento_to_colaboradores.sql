-- Add evento_id to colaboradores table
ALTER TABLE colaboradores ADD COLUMN evento_id INT NULL AFTER id;

-- Add foreign key constraint
ALTER TABLE colaboradores ADD CONSTRAINT fk_colaboradores_evento FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE;

-- Create index for faster lookups
CREATE INDEX idx_colaboradores_evento ON colaboradores(evento_id);
