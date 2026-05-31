DELIMITER $$

CREATE PROCEDURE sp_ReportBooksByCreator
(
    IN p_creator_id INT
)
BEGIN

    SELECT
        b.book_id,
        b.title,
        b.author_name,
        b.category,
        b.publish_date,

        IFNULL(AVG(cr.rating),0) AS average_rating,
        COUNT(cr.comment_id) AS review_count

    FROM dbProj_books b

    LEFT JOIN dbProj_comments_ratings cr
        ON b.book_id = cr.book_id

    WHERE b.creator_id = p_creator_id

    GROUP BY b.book_id

    ORDER BY b.publish_date DESC;

END$$

CREATE PROCEDURE sp_MostPopularBooks
(
    IN p_start DATE,
    IN p_end DATE
)
BEGIN

    SELECT
        b.book_id,
        b.title,
        b.author_name,
        AVG(cr.rating) AS avg_rating,
        COUNT(cr.comment_id) AS review_count

    FROM dbProj_books b
    LEFT JOIN dbProj_comments_ratings cr
        ON b.book_id = cr.book_id

    WHERE DATE(b.publish_date)
          BETWEEN p_start AND p_end

    GROUP BY b.book_id

    ORDER BY avg_rating DESC,
             review_count DESC;

END$$

DELIMITER ;