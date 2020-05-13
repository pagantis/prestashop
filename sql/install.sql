CREATE TABLE IF NOT EXISTS `PREFIX_pagantis_cart_process` (
  `id` INT NOT NULL ,
  `timestamp` INT NOT NULL ,
  PRIMARY KEY (`id`)
  ) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `PREFIX_pagantis_order` (
  `id` INT NOT NULL ,
  `order_id` VARCHAR(60) NOT NULL,
  `ps_order_id` varchar(60) NULL,
  `tries` INT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
  ) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS  `PREFIX_pagantis_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `log` TEXT,
  `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
  ) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS  `PREFIX_pagantis_config` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `config` VARCHAR(60) NOT NULL,
  `value` VARCHAR(1000) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE = InnoDB;
