-- Optimize chat unread count query
ALTER TABLE tbl_chat ADD INDEX idx_receiver_unread (receiver_id, is_read);
