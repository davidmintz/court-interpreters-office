DELIMITER //
DROP FUNCTION IF EXISTS SDNY_DATE_DIFF;
CREATE FUNCTION SDNY_DATE_DIFF(req_date DATE, req_time TIME, event_date DATE, event_time TIME) 
RETURNS FLOAT DETERMINISTIC 
READS SQL DATA
BEGIN

DECLARE diff FLOAT;

SET @day_of_week = DAYOFWEEK(req_date);

IF @day_of_week IN (1,7) OR 1 = (SELECT COUNT(*) FROM court_closings WHERE date = req_date)

THEN
	SET req_time = '00:00:00'; 
	SET @i = 0;
	REPEAT 	
		SET @i = @i + 1;
		SET req_date = DATE_ADD(req_date, INTERVAL 1 DAY);
		IF @i = 10 
			THEN SIGNAL SQLSTATE VALUE '45000' SET MESSAGE_TEXT = "probable infinite loop detected in EVENT_NOTICE()"; 
		END IF;
	UNTIL DAYOFWEEK(req_date) NOT IN (1,7) AND 0 = (SELECT COUNT(*) FROM court_closings WHERE date = req_date) END REPEAT;
END IF;

SET diff = DATEDIFF(event_date,req_date);
SET @weeks = FLOOR ( DATEDIFF (event_date, req_date )/7 );
/* deduct the two weekend days for each full week */
SET diff = diff - (2 * @weeks);

IF DAYOFWEEK(event_date) < DAYOFWEEK(req_date) 
THEN SET diff = diff - 2;

/* deduct any intervening holidays */
SET diff = diff - (SELECT COUNT(*) FROM court_closings WHERE date BETWEEN req_date AND event_date);
END IF;

IF event_time IS NOT NULL AND req_time IS NOT NULL
/*  add the difference in time of day as a fraction of 1 day */
THEN SET diff = diff + TIME_TO_SEC(TIMEDIFF(event_time,req_time))/86400;
END IF;
return diff;
END //

DELIMITER ;

