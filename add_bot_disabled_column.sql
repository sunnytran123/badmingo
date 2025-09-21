-- Thêm cột bot_disabled vào bảng chat_history
USE sunny_sport;

ALTER TABLE chat_history 
ADD COLUMN bot_disabled TINYINT(1) DEFAULT 0 
COMMENT 'Trạng thái bot: 0=bật, 1=tắt';

-- Tạo index để tối ưu query
CREATE INDEX idx_chat_history_user_bot_disabled ON chat_history(user_id, bot_disabled);
