ALTER TABLE checkins
    ADD INDEX idx_checkins_invitado_id (invitado_id),
    ADD INDEX idx_checkins_ticket_type_id (ticket_type_id),
    ADD INDEX idx_checkins_estado_ingreso (estado_ingreso),
    ADD INDEX idx_checkins_checkin_time (checkin_time);

ALTER TABLE checkin_history
    ADD INDEX idx_checkin_history_checkin_id (checkin_id);
