-- $Id$

-- Create the room junction tables.

CREATE TABLE %DB_TBL_PREFIX%room_entry
(
  room_id        int NOT NULL REFERENCES %DB_TBL_PREFIX%room(id)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE,
  entry_id       int NOT NULL REFERENCES %DB_TBL_PREFIX%entry(id)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE,
  PRIMARY KEY (room_id, entry_id)
);

INSERT INTO %DB_TBL_PREFIX%room_entry (room_id, entry_id)
  SELECT room_id, id FROM %DB_TBL_PREFIX%entry;
  
CREATE TABLE %DB_TBL_PREFIX%room_repeat
(
  room_id        int NOT NULL REFERENCES %DB_TBL_PREFIX%room(id)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE,
  repeat_id      int NOT NULL REFERENCES %DB_TBL_PREFIX%repeat(id)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE,
  PRIMARY KEY (room_id, repeat_id)
);

INSERT INTO %DB_TBL_PREFIX%room_repeat (room_id, repeat_id)
  SELECT room_id, id FROM %DB_TBL_PREFIX%repeat;

