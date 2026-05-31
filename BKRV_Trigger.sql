DELIMITER $$

CREATE TRIGGER trg_one_review_per_user
BEFORE INSERT ON dbProj_comments_ratings
FOR EACH ROW
BEGIN

    IF EXISTS (
        SELECT 1
        FROM dbProj_comments_ratings
        WHERE book_id = NEW.book_id
        AND user_id = NEW.user_id
    ) THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'User has already reviewed this book';

    END IF;

END$$

DELIMITER ;