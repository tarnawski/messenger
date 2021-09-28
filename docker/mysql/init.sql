DROP TABLE IF EXISTS `message`;

CREATE TABLE `message` (
   `identity` VARCHAR(36) NOT NULL UNIQUE,
   `content` VARCHAR(500) NOT NULL,
   `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (`identity`)
) DEFAULT CHARSET=utf8;

INSERT INTO message (identity, content, created_at) VALUES ('559a70e9-faec-4926-91c1-05605e16adbc', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', '2019-06-17 18:24:21');
INSERT INTO message (identity, content, created_at) VALUES ('bfe4ef65-8e90-456c-9d3d-ee7d722e98e9', 'Praesent feugiat purus sed neque porttitor pulvinar.', '2019-06-17 18:24:30');
INSERT INTO message (identity, content, created_at) VALUES ('dacbb20b-a92f-4e1e-8dc8-54a8db9f3421', 'Donec nibh mi, laoreet nec lectus sit amet, lobortis commodo nulla.', '2019-06-17 18:25:21');
