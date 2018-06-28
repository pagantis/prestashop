CREATE TABLE IF NOT EXISTS `PREFIX_pmt_cart_process` (
  `id` INT NOT NULL ,
  `timestamp` INT NOT NULL ,
  PRIMARY KEY (`id`)
  ) ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS `PREFIX_pmt_order` (
  `id` INT NOT NULL ,
  `order_id` VARCHAR(60) NOT NULL ,
  PRIMARY KEY (`id`)
  ) ENGINE = InnoDB;
