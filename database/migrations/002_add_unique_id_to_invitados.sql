-- Add unique alphanumeric ID column to invitados table
ALTER TABLE invitados ADD COLUMN unique_id VARCHAR(50) UNIQUE NULL AFTER id;

-- Create index for faster lookups
CREATE INDEX idx_invitados_unique_id ON invitados(unique_id);
