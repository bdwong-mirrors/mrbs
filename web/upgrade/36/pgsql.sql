-- $Id$

-- Create the room junction tables.

CREATE TABLE %DB_TBL_PREFIX%room_entry
(
  id             serial primary key,
  room_id        int DEFAULT NULL REFERENCES %DB_TBL_PREFIX%room(id)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE,
  entry_id       int DEFAULT NULL REFERENCES %DB_TBL_PREFIX%entry(id)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE
);
create index %DB_TBL_PREFIX%idxRoomEntryRoom on %DB_TBL_PREFIX%room_entry(room_id);
create index %DB_TBL_PREFIX%idxRoomEntryEntry on %DB_TBL_PREFIX%room_entry(entry_id);

INSERT INTO %DB_TBL_PREFIX%room_entry (room_id, entry_id)
  SELECT room_id, id FROM %DB_TBL_PREFIX%entry;
  
CREATE TABLE %DB_TBL_PREFIX%room_repeat
(
  id             serial primary key,
  room_id        int DEFAULT NULL REFERENCES %DB_TBL_PREFIX%room(id)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE,
  repeat_id      int DEFAULT NULL REFERENCES %DB_TBL_PREFIX%repeat(id)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE
);
create index %DB_TBL_PREFIX%idxRoomRepeatRoom on %DB_TBL_PREFIX%room_repeat(room_id);
create index %DB_TBL_PREFIX%idxRoomRepeatEntry on %DB_TBL_PREFIX%room_repeat(repeat_id);

INSERT INTO %DB_TBL_PREFIX%room_repeat (room_id, repeat_id)
  SELECT room_id, id FROM %DB_TBL_PREFIX%repeat;

