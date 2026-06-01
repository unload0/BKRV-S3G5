-- ---------------------------------------------------------
-- 1. SAFE DELETION & ID RESET
-- ---------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM dbProj_comments_ratings;
DELETE FROM dbProj_books;
DELETE FROM dbProj_users;

ALTER TABLE dbProj_comments_ratings AUTO_INCREMENT = 1;
ALTER TABLE dbProj_books AUTO_INCREMENT = 1;
ALTER TABLE dbProj_users AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------
-- 2. INSERT USERS (IDs 1 through 9)
-- ---------------------------------------------------------
INSERT INTO dbProj_users (username, email, password, role) VALUES 
('admin', 'admin@example.com', AES_ENCRYPT('admin_pass123', '123'), 'Admin'),
('jdoe_creator', 'jdoe@example.com', AES_ENCRYPT('creator_pass456', '123'), 'Creator'),
('asmith_creator', 'asmith@example.com', AES_ENCRYPT('secure_key789', '123'), 'Creator'),
('rwoods_viewer', 'rwoods@example.com', AES_ENCRYPT('viewer_pass1', '123'), 'Viewer'),
('mgreen_viewer', 'mgreen@example.com', AES_ENCRYPT('viewer_pass2', '123'), 'Viewer'),
('elee_creator', 'elee@example.com', AES_ENCRYPT('creator_pass999', '123'), 'Creator'),
('dtaylor_viewer', 'dtaylor@example.com', AES_ENCRYPT('viewer_pass3', '123'), 'Viewer'),
('hpotter_creator', 'hpotter@example.com', AES_ENCRYPT('magic456', '123'), 'Creator'),
('sconnor_viewer', 'sconnor@example.com', AES_ENCRYPT('terminator789', '123'), 'Viewer');


-- ---------------------------------------------------------
-- 3. INSERT BOOKS (IDs 1 through 19)
-- ---------------------------------------------------------
-- MYSTERY CATEGORY (Exactly 5 to meet project requirements)
INSERT INTO dbProj_books (creator_id, title, author_name, short_description, category, publish_date) VALUES 
(2, 'The Mystery of the Old Manor', 'Arthur Conan Doyle', 'A thrilling detective story set in Victorian England.', 'Mystery', '2023-01-15 10:00:00'),
(2, 'Murder on the Orient Express', 'Agatha Christie', 'A classic locked-room mystery on a famous train.', 'Mystery', '2023-03-10 11:00:00'),
(3, 'The Girl with the Dragon Tattoo', 'Stieg Larsson', 'A disgraced journalist and a hacker investigate a disappearance.', 'Mystery', '2020-09-01 14:20:00'),
(6, 'Gone Girl', 'Gillian Flynn', 'A thriller exploring the dark sides of a marriage.', 'Mystery', '2022-05-18 08:15:00'),
(2, 'The Hound of the Baskervilles', 'Arthur Conan Doyle', 'Sherlock Holmes investigates a mythical beast on the moors.', 'Mystery', '2021-11-22 16:30:00');

-- COMPUTER SCIENCE CATEGORY
INSERT INTO dbProj_books (creator_id, title, author_name, short_description, category, publish_date) VALUES 
(2, 'Data Structures and Algorithms', 'Robert Lafore', 'A comprehensive guide to understanding foundational computer science concepts.', 'Computer Science', '2021-06-20 09:30:00'),
(6, 'Clean Code', 'Robert C. Martin', 'A handbook of agile software craftsmanship.', 'Computer Science', '2024-03-15 09:15:00'),
(6, 'Design Patterns', 'Erich Gamma', 'Elements of reusable object-oriented software.', 'Computer Science', '2019-08-22 16:20:00');

-- CULINARY CATEGORY
INSERT INTO dbProj_books (creator_id, title, author_name, short_description, category, publish_date) VALUES 
(3, 'The Culinary Art of Pastry', 'Julia Child', 'Mastering the fine techniques of French baking and pastry creation.', 'Culinary', '2025-11-05 14:00:00'),
(3, 'The Joy of Cooking', 'Irma S. Rombauer', 'An essential and comprehensive American cookbook.', 'Culinary', '2024-01-05 13:10:00'),
(3, 'Salt, Fat, Acid, Heat', 'Samin Nosrat', 'Mastering the fundamental elements of good cooking.', 'Culinary', '2023-07-30 17:00:00');

-- ASTRONOMY CATEGORY
INSERT INTO dbProj_books (creator_id, title, author_name, short_description, category, publish_date) VALUES 
(3, 'Journey to the Stars', 'Carl Sagan', 'An exploration of space, time, and cosmic evolution.', 'Astronomy', '2022-08-12 18:45:00'),
(3, 'Astrophysics for People in a Hurry', 'Neil deGrasse Tyson', 'A quick and accessible guide to the cosmos.', 'Astronomy', '2022-12-18 10:30:00');

-- SCI-FI CATEGORY
INSERT INTO dbProj_books (creator_id, title, author_name, short_description, category, publish_date) VALUES 
(6, 'Dune', 'Frank Herbert', 'A sweeping sci-fi epic set on the desert planet of Arrakis.', 'Sci-Fi', '2021-02-14 09:00:00'),
(3, 'Neuromancer', 'William Gibson', 'The pioneering cyberpunk novel that defined a genre.', 'Sci-Fi', '2024-01-05 10:45:00');

-- MISCELLANEOUS CATEGORIES
INSERT INTO dbProj_books (creator_id, title, author_name, short_description, category, publish_date) VALUES 
(2, 'The Hobbit', 'J.R.R. Tolkien', 'A peaceful hobbit goes on an unexpected journey with dwarves.', 'Fantasy', '2023-08-30 13:10:00'),
(6, '1984', 'George Orwell', 'A dystopian novel about totalitarianism and surveillance.', 'Fiction', '2020-04-12 07:50:00'),
(3, 'Sapiens', 'Yuval Noah Harari', 'A brief history of humankind and our evolution.', 'History', '2022-10-10 11:25:00'),
(2, 'Steve Jobs', 'Walter Isaacson', 'The definitive biography of the Apple co-founder.', 'Biography', '2023-12-01 15:00:00');


-- ---------------------------------------------------------
-- 4. INSERT COMMENTS & RATINGS
-- Note: book_ids align precisely with the insertion order above.
-- ---------------------------------------------------------
INSERT INTO dbProj_comments_ratings (book_id, user_id, rating, comment) VALUES 
(1, 4, 4.5, 'An absolute masterpiece. Highly educational and inspiring!'),
(1, 5, 5.0, 'Couldn''t put it down, loved every page of it.'),
(2, 4, 5.0, 'Classic Poirot. The ending still shocks me.'),
(4, 7, 4.5, 'Such a gripping mystery!'),
(6, 4, 4.0, 'Very clear explanations and great code examples.'),
(7, 7, 4.0, 'Essential reading for any developer.'),
(8, 4, 5.0, 'This coding book changed my career.'),
(9, 5, 3.5, 'Good recipes, but some techniques are quite advanced for beginners.'),
(12, 4, 5.0, 'Sagan''s writing style is poetic and breathtaking.'),
(14, 5, 5.0, 'The spice must flow! Incredible world-building.'),
(16, 5, 5.0, 'A timeless adventure. Perfect for all ages.');


-- =====================================================
-- ADDITIONAL BOOKS & RATINGS/COMMENTS FOR BETTER TESTING
-- =====================================================

INSERT INTO dbProj_books
(creator_id, title, author_name, short_description, category, publish_date)
VALUES

-- More Arthur Conan Doyle books
(2, 'A Study in Scarlet', 'Arthur Conan Doyle',
 'The first Sherlock Holmes investigation.',
 'Mystery', '2022-01-15 10:00:00'),

(2, 'The Sign of Four', 'Arthur Conan Doyle',
 'Holmes investigates a hidden treasure.',
 'Mystery', '2023-04-18 12:00:00'),

-- More Agatha Christie books
(3, 'Death on the Nile', 'Agatha Christie',
 'A murder mystery aboard a river cruise.',
 'Mystery', '2024-02-10 09:30:00'),

(3, 'And Then There Were None', 'Agatha Christie',
 'Ten strangers trapped on an isolated island.',
 'Mystery', '2021-06-20 18:15:00'),

-- More Computer Science books
(6, 'Refactoring', 'Martin Fowler',
 'Improving software design through refactoring.',
 'Computer Science', '2022-11-10 14:00:00'),

(6, 'Patterns of Enterprise Application Architecture',
 'Martin Fowler',
 'Enterprise software architecture concepts.',
 'Computer Science', '2023-03-11 10:00:00'),

(2, 'The Pragmatic Programmer',
 'Andrew Hunt',
 'Best practices for modern software developers.',
 'Computer Science', '2024-05-10 08:30:00'),

(2, 'Programming Pearls',
 'Jon Bentley',
 'Problem solving and programming techniques.',
 'Computer Science', '2021-09-15 11:00:00'),

-- More Culinary
(3, 'Mastering the Art of French Cooking',
 'Julia Child',
 'Classic French cooking techniques.',
 'Culinary', '2022-04-01 12:30:00'),

(3, 'Baking with Julia',
 'Julia Child',
 'Advanced baking recipes and techniques.',
 'Culinary', '2023-08-17 16:00:00'),

(3, 'Cook Once Eat All Week',
 'Cassy Joy Garcia',
 'Meal planning and preparation guide.',
 'Culinary', '2024-09-01 09:45:00'),

-- More Astronomy
(3, 'Cosmos',
 'Carl Sagan',
 'Humanity and the universe explained.',
 'Astronomy', '2021-10-10 08:00:00'),

(3, 'Pale Blue Dot',
 'Carl Sagan',
 'A vision of humanity in space.',
 'Astronomy', '2024-04-20 10:00:00'),

(6, 'Welcome to the Universe',
 'Neil deGrasse Tyson',
 'Introduction to modern astrophysics.',
 'Astronomy', '2025-01-10 15:20:00'),

-- More Sci-Fi
(6, 'Foundation',
 'Isaac Asimov',
 'A galactic empire faces collapse.',
 'Sci-Fi', '2020-02-14 09:00:00'),

(6, 'I, Robot',
 'Isaac Asimov',
 'Stories exploring artificial intelligence.',
 'Sci-Fi', '2022-07-08 13:00:00'),

(3, 'Snow Crash',
 'Neal Stephenson',
 'A cyberpunk virtual reality adventure.',
 'Sci-Fi', '2025-02-01 11:15:00'),

-- Fantasy
(2, 'The Fellowship of the Ring',
 'J.R.R. Tolkien',
 'The journey begins.',
 'Fantasy', '2021-05-01 10:00:00'),

(2, 'The Two Towers',
 'J.R.R. Tolkien',
 'The fellowship is divided.',
 'Fantasy', '2022-05-01 10:00:00'),

(2, 'The Return of the King',
 'J.R.R. Tolkien',
 'The final battle for Middle-earth.',
 'Fantasy', '2023-05-01 10:00:00'),

-- History
(3, 'Homo Deus',
 'Yuval Noah Harari',
 'The future of humanity.',
 'History', '2024-10-01 12:00:00'),

-- Fiction
(6, 'Animal Farm',
 'George Orwell',
 'Political satire about power.',
 'Fiction', '2021-03-10 13:00:00'),

(6, 'Brave New World',
 'Aldous Huxley',
 'A dystopian future society.',
 'Fiction', '2025-06-12 09:00:00'),

-- Biography
(2, 'Elon Musk',
 'Walter Isaacson',
 'Biography of Elon Musk.',
 'Biography', '2025-02-01 11:00:00');


INSERT INTO dbProj_comments_ratings
(book_id, user_id, rating, comment)
VALUES

(20,5,4.5,'Excellent Holmes story.'),
(20,7,5.0,'One of Doyle''s best works.'),

(21,4,4.0,'Very entertaining mystery.'),

(22,5,5.0,'One of Christie''s greatest novels.'),
(22,7,4.5,'Kept me guessing until the end.'),

(24,4,4.5,'Every developer should read this.'),

(25,5,4.0,'Useful architecture concepts.'),

(26,7,5.0,'A must-read programming classic.'),

(28,4,4.5,'Fantastic recipes and techniques.'),

(31,5,5.0,'Carl Sagan at his best.'),

(32,7,4.5,'Very inspiring read.'),

(34,4,5.0,'Foundation is a masterpiece.'),

(35,5,4.5,'Excellent AI concepts.'),

(37,7,4.5,'Unique cyberpunk atmosphere.'),

(38,4,5.0,'Tolkien never disappoints.'),

(39,5,4.5,'Great continuation of the story.'),

(40,7,5.0,'Perfect ending to the trilogy.'),

(41,4,4.5,'Thought provoking.'),

(42,5,5.0,'Still relevant today.'),

(43,7,4.0,'Interesting social commentary.'),

(43,4,4.5,'Very detailed biography.');