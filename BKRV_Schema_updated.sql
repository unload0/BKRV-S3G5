DROP TABLE IF EXISTS dbProj_comments_ratings;
DROP TABLE IF EXISTS dbProj_books;
DROP TABLE IF EXISTS dbProj_users;

-- =====================================================
-- USERS
-- =====================================================

CREATE TABLE dbProj_users
(
    user_id INT AUTO_INCREMENT NOT NULL,

    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARBINARY(255) NOT NULL,

    role ENUM('Viewer','Creator','Admin')
         NOT NULL DEFAULT 'Viewer',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (user_id),

    UNIQUE KEY uq_username (username),
    UNIQUE KEY uq_email (email)

) ENGINE=InnoDB;


-- =====================================================
-- BOOKS
-- =====================================================

CREATE TABLE dbProj_books
(
    book_id INT AUTO_INCREMENT NOT NULL,

    creator_id INT NULL,

    title VARCHAR(255) NOT NULL,
    author_name VARCHAR(255) NOT NULL,

    short_description VARCHAR(500) NOT NULL,

    category VARCHAR(100) NULL,

    image_url VARCHAR(255) NULL,
    media_url VARCHAR(255) NULL,

    publish_date DATETIME DEFAULT CURRENT_TIMESTAMP,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
               ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (book_id),

    INDEX idx_creator (creator_id),
    INDEX idx_author (author_name),
    INDEX idx_category (category),
    INDEX idx_publish_date (publish_date),

    FULLTEXT KEY fx_search_idx
    (
        title,
        author_name,
        short_description,
        category
    ),

    CONSTRAINT FK_dbProj_users_TO_dbProj_books
    FOREIGN KEY (creator_id)
    REFERENCES dbProj_users(user_id)
    ON DELETE SET NULL
    ON UPDATE CASCADE

) ENGINE=InnoDB;


-- =====================================================
-- COMMENTS & RATINGS
-- =====================================================

CREATE TABLE dbProj_comments_ratings
(
    comment_id INT AUTO_INCREMENT NOT NULL,

    book_id INT NOT NULL,
    user_id INT NOT NULL,

    rating DECIMAL(3,2) NOT NULL,

    comment VARCHAR(500) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (comment_id),

    INDEX idx_book (book_id),
    INDEX idx_user (user_id),
    INDEX idx_rating (rating),

    CONSTRAINT chk_rating
    CHECK (rating >= 1 AND rating <= 5),

    CONSTRAINT FK_dbProj_books_TO_dbProj_comments_ratings
    FOREIGN KEY (book_id)
    REFERENCES dbProj_books(book_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

    CONSTRAINT FK_dbProj_users_TO_dbProj_comments_ratings
    FOREIGN KEY (user_id)
    REFERENCES dbProj_users(user_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE

) ENGINE=InnoDB;