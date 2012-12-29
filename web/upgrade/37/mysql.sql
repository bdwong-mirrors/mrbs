# $Id$

# Remove the room_id columns as they are no longer needed
# (the room_id is now held in the junction tables)

ALTER TABLE %DB_TBL_PREFIX%entry 
DROP COLUMN room_id;

ALTER TABLE %DB_TBL_PREFIX%repeat 
DROP COLUMN room_id;
