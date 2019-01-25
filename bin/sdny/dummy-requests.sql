
/** un-schedule some requests for test purposes */

SET @min=(select id from requests order by created desc limit 1 offset 11);
DELETE e.* FROM events e JOIN requests r ON e.id = r.event_id WHERE r.id >= @min;
UPDATE requests SET pending = 1 WHERE pending = 0 AND id >= @min;
