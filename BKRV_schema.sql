CREATE TABLE dbProj_users
(
  user_id  INT AUTO_INCREMENT NOT NULL,
  username VARCHAR(100)       NOT NULL,
  email    VARCHAR(100)       NOT NULL,
  password varbinary(255)       NOT NULL,
  role     VARCHAR(100)       NOT NULL DEFAULT 'Viewer',
  PRIMARY KEY (user_id)
) ENGINE=InnoDB;

CREATE TABLE dbProj_books
(
  book_id           INT          NOT NULL AUTO_INCREMENT,
  creator_id        INT          NULL    ,
  title             VARCHAR(255) NOT NULL,
  author_name       VARCHAR(255) NOT NULL,
  short_description VARCHAR(500) NOT NULL,
  image_url         VARCHAR(255) NULL    ,
  media_url         VARCHAR(255) NULL    ,
  publish_date      DATETIME     NULL    ,
  PRIMARY KEY (book_id),

  FULLTEXT KEY fx_search_idx (title, author_name, short_description)
) ENGINE=InnoDB;

CREATE TABLE dbProj_comments_ratings
(
  comment_id INT            NOT NULL AUTO_INCREMENT,
  book_id    INT            NOT NULL,
  user_id    INT            NOT NULL,
  rating     DECIMAL(18, 2) NOT NULL,
  comment    VARCHAR(500)   NULL,
  created_at TIMESTAMP      NULL     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (comment_id)
) ENGINE=InnoDB;

ALTER TABLE dbProj_comments_ratings
  ADD CONSTRAINT FK_dbProj_books_TO_dbProj_comments_ratings
    FOREIGN KEY (book_id)
    REFERENCES dbProj_books (book_id);

ALTER TABLE dbProj_comments_ratings
  ADD CONSTRAINT FK_dbProj_users_TO_dbProj_comments_ratings
    FOREIGN KEY (user_id)
    REFERENCES dbProj_users (user_id);

ALTER TABLE dbProj_books
  ADD CONSTRAINT FK_dbProj_users_TO_dbProj_books
    FOREIGN KEY (creator_id)
    REFERENCES dbProj_users (user_id);

ALTER TABLE dbProj_users 
  ADD CONSTRAINT chk_role_values CHECK (role IN ('Viewer', 'Creator', 'Admin'));