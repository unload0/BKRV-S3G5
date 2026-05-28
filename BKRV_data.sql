INSERT INTO dbProj_users (username, email, password, role) 
VALUES ('admin', 'admin@example.com', AES_ENCRYPT('admin_pass123', '123'), 'Admin');

INSERT INTO dbProj_users (username, email, password, role) 
VALUES ('jdoe_creator', 'jdoe@example.com', AES_ENCRYPT('creator_pass456', '123'), 'Creator');

INSERT INTO dbProj_users (username, email, password, role) 
VALUES ('asmith_creator', 'asmith@example.com', AES_ENCRYPT('secure_key789', '123'), 'Creator');

INSERT INTO dbProj_users (username, email, password, role) 
VALUES ('rwoods_viewer', 'rwoods@example.com', AES_ENCRYPT('viewer_pass1', '123'), 'Viewer');

INSERT INTO dbProj_users (username, email, password, role) 
VALUES ('mgreen_viewer', 'mgreen@example.com', AES_ENCRYPT('viewer_pass2', '123'), 'Viewer');

INSERT INTO dbProj_books (creator_id, title, author_name, short_description, category, image_url, media_url, publish_date) 
VALUES (2, 'The Mystery of the Old Manor', 'Arthur Conan Doyle', 'A thrilling detective story set in Victorian England.', 'Mystery', NULL, NULL, '2023-01-15 10:00:00');

INSERT INTO dbProj_books (creator_id, title, author_name, short_description, category, image_url, media_url, publish_date) 
VALUES (2, 'Data Structures and Algorithms', 'Robert Lafore', 'A comprehensive guide to understanding foundational computer science concepts.', 'Computer Science', NULL, NULL, '2021-06-20 09:30:00');

INSERT INTO dbProj_books (creator_id, title, author_name, short_description, category, image_url, media_url, publish_date) 
VALUES (3, 'The Culinary Art of Pastry', 'Julia Child', 'Mastering the fine techniques of French baking and pastry creation.', 'Culinary', NULL, NULL, '2025-11-05 14:00:00');

INSERT INTO dbProj_books (creator_id, title, author_name, short_description, category, image_url, media_url, publish_date) 
VALUES (3, 'Journey to the Stars', 'Carl Sagan', 'An exploration of space, time, and cosmic evolution.', 'Astronomy', NULL, NULL, '2022-08-12 18:45:00');

INSERT INTO dbProj_comments_ratings (book_id, user_id, rating, comment, created_at) 
VALUES (1, 4, 4.5, 'An absolute masterpiece. Highly educational and inspiring!', CURRENT_TIMESTAMP);

INSERT INTO dbProj_comments_ratings (book_id, user_id, rating, comment, created_at) 
VALUES (1, 5, 5.0, 'Couldn''t put it down, loved every page of it.', CURRENT_TIMESTAMP);

INSERT INTO dbProj_comments_ratings (book_id, user_id, rating, comment, created_at) 
VALUES (2, 4, 4.0, 'Very clear explanations and great code examples.', CURRENT_TIMESTAMP);

INSERT INTO dbProj_comments_ratings (book_id, user_id, rating, comment, created_at) 
VALUES (3, 5, 3.5, 'Good recipes, but some techniques are quite advanced for beginners.', CURRENT_TIMESTAMP);

INSERT INTO dbProj_comments_ratings (book_id, user_id, rating, comment, created_at) 
VALUES (4, 4, 5.0, 'Sagan''s writing style is poetic and breathtaking.', CURRENT_TIMESTAMP);