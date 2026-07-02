UPDATE invitados 
SET unique_id = CONCAT('INV-', YEAR(NOW()), '-', UPPER(SUBSTR(MD5(CONCAT(id, nombre, DATE_ADD(NOW(), INTERVAL -id HOUR))), 1, 4)), 
              LPAD(SUBSTR(MD5(CONCAT(id, nombre, DATE_ADD(NOW(), INTERVAL id HOUR))), 1, 5), 5, '0'))
WHERE unique_id IS NULL;
