DROP DATABASE IF EXISTS video_streaming;
CREATE DATABASE video_streaming;
use video_streaming;

CREATE TABLE `users`
(
    `id`         bigint       NOT NULL AUTO_INCREMENT,
    `email`      varchar(255) NOT NULL UNIQUE,
    `password`   varchar(255) NOT NULL,
    `is_admin`   char(1)           default 'N',
    `created_at` DATETIME     NOT NULL,
    `updated_at` DATETIME     NULL default NULL,
    PRIMARY KEY (`id`, `email`)
);

CREATE TABLE `videos`
(
    `id`                bigint       NOT NULL AUTO_INCREMENT,
    `video_title`       varchar(255) NOT NULL,
    `video_description` TEXT         NOT NULL,
    `video_location`    varchar(255) NOT NULL UNIQUE,
    `video_thumbnail`   varchar(255) NOT NULL UNIQUE,
    `duration`          integer      not null,
    `user_id`           bigint       NOT NULL,
    `category_id`       bigint       NOT NULL,
    `permission_level`  char(1)      NOT NULL default 'H',
    `created_at`        DATETIME     NOT NULL,
    `updated_at`        DATETIME     NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE `video_processing_queue`
(
    `id`          bigint       NOT NULL AUTO_INCREMENT,
    `source_path` varchar(255) NOT NULL UNIQUE,
    `video_id`    bigint       NOT NULL UNIQUE,
    `status`      BOOLEAN      NOT NULL DEFAULT false,
    `created_at`  DATETIME     NOT NULL,
    `finished_at` DATETIME     NOT NULL,
    PRIMARY KEY (`id`)
);
--  not used in application
CREATE TABLE `comments`
(
    `id`                bigint NOT NULL AUTO_INCREMENT,
    `user_id`           bigint NOT NULL,
    `video_id`          bigint NOT NULL,
    `parent_comment_id` bigint,
    `comment`           TEXT   NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE `categories`
(
    `id`   bigint       NOT NULL AUTO_INCREMENT,
    `slug` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    PRIMARY KEY (`id`, `slug`)
);

ALTER TABLE `videos`
    ADD CONSTRAINT `videos_fk0` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `videos`
    ADD CONSTRAINT `videos_fk1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

ALTER TABLE `video_processing_queue`
    ADD CONSTRAINT `video_processing_queue_fk0` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`);

ALTER TABLE `comments`
    ADD CONSTRAINT `comments_fk0` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `comments`
    ADD CONSTRAINT `comments_fk1` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`);






