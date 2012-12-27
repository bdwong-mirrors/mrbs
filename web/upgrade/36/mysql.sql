# $Id$

# Create the room junction tables.   First we have to make sure that
# the room, entry and repeat tables use the InnoDB engine.  Ordering them
# first by the primary key column speeds up the operation.

ALTER TABLE %DB_TBL_PREFIX%entry ORDER BY id;
ALTER TABLE %DB_TBL_PREFIX%entry ENGINE = INNODB;
ALTER TABLE %DB_TBL_PREFIX%repeat ORDER BY id;
ALTER TABLE %DB_TBL_PREFIX%repeat ENGINE = INNODB;
ALTER TABLE %DB_TBL_PREFIX%room ORDER BY id;
ALTER TABLE %DB_TBL_PREFIX%room ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS %DB_TBL_PREFIX%room_entry
(
  id             int NOT NULL auto_increment,
  room_id        int DEFAULT NULL,
  entry_id       int DEFAULT NULL,
  FOREIGN KEY (room_id) REFERENCES %DB_TBL_PREFIX%room(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  FOREIGN KEY (entry_id) REFERENCES %DB_TBL_PREFIX%entry(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  PRIMARY KEY (id),
  KEY idxRoomEntryRoom  (room_id),
  KEY idxRoomEntryEntry (entry_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO %DB_TBL_PREFIX%room_entry (room_id, entry_id)
  SELECT room_id, id FROM %DB_TBL_PREFIX%entry;
  
CREATE TABLE IF NOT EXISTS %DB_TBL_PREFIX%room_repeat
(
  id             int NOT NULL auto_increment,
  room_id        int DEFAULT NULL,
  repeat_id      int DEFAULT NULL,
  FOREIGN KEY (room_id) REFERENCES %DB_TBL_PREFIX%room(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  FOREIGN KEY (repeat_id) REFERENCES %DB_TBL_PREFIX%repeat(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  PRIMARY KEY (id),
  KEY idxRoomRepeatRoom   (room_id),
  KEY idxRoomRepeatRepeat (repeat_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO %DB_TBL_PREFIX%room_repeat (room_id, repeat_id)
  SELECT room_id, id FROM %DB_TBL_PREFIX%repeat;

