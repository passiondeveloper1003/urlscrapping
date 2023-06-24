/*
SQLyog Community v13.1.6 (64 bit)
MySQL - 10.4.25-MariaDB : Database - scrap
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`scrap` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `scrap`;

/*Table structure for table `urls` */

DROP TABLE IF EXISTS `urls`;

CREATE TABLE `urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `url` varchar(256) NOT NULL,
  `domain` varchar(256) DEFAULT NULL,
  `screenshot` TEXT NULL DEFAULT '',
  `status` varchar(3) NULL DEFAULT '',
  `gtag` TEXT NULL DEFAULT '',
  `gtag_updatedtime` TEXT NULL DEFAULT '',
  `screenshot_updatedtime` TEXT NULL DEFAULT '',
  `status_updatedtime` TEXT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `urls_ibfk_1` (`user_id`),
  CONSTRAINT `urls_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;


/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `create_datetime` datetime NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `code` varchar(255) NOT NULL,
  `url` text DEFAULT NULL,
  `url_domain` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Data for the table `users` */

insert  into `users`(`id`,`username`,`email`,`password`,`create_datetime`,`active`,`code`,`url`,`url_domain`) values 
(6,'Tony Lee','topdeveloper0908@gmail.com','8bf4e6addd72a9c4c4714708d2941528','2022-10-19 09:57:55',1,'1822761125',NULL,NULL);

/*Table structure for table `verification` */

DROP TABLE IF EXISTS `verification`;

CREATE TABLE `verification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `password` varchar(256) NOT NULL,
  `email` varchar(256) NOT NULL,
  `code` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `verification` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` varchar(256) NOT NULL,
  `time` varchar(256) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;