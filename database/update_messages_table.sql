-- Modify the messages table to allow null user_id values
ALTER TABLE messages MODIFY COLUMN user_id INT NULL;

-- Update the foreign key constraint
ALTER TABLE messages DROP FOREIGN KEY messages_ibfk_2;
ALTER TABLE messages ADD CONSTRAINT messages_ibfk_2 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE; 