

CREATE TABLE tb_persons (
  idperson INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  desperson VARCHAR(64) NOT NULL,
  desemail VARCHAR(128) NULL DEFAULT NULL,
  nrphone BIGINT(20) NULL DEFAULT NULL,
  dtregister TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO `tb_persons` VALUES (1,'JoÃ£o Rangel','admin@hcode.com.br',2147483647,'2017-03-01 03:00:00'),(7,'Suporte','suporte@hcode.com.br',1112345678,'2017-03-15 16:10:27');



-- -----------------------------------------------------
-- Table `tb_addresses`
-- -----------------------------------------------------

CREATE TABLE tb_addresses (
  idaddress INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  idperson INT(11) NOT NULL,
  desaddress VARCHAR(128) NOT NULL,
  descomplement VARCHAR(32) NULL DEFAULT NULL,
  descity VARCHAR(32) NOT NULL,
  desstate VARCHAR(32) NOT NULL,
  descountry VARCHAR(32) NOT NULL,
  nrzipcode INT(11) NOT NULL,
  dtregister TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_addresses_persons
    FOREIGN KEY (idperson)
    REFERENCES tb_persons (idperson)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);

CREATE INDEX fk_addresses_persons_idx ON tb_addresses (idperson ASC);


-- -----------------------------------------------------
-- Table `tb_users`
-- -----------------------------------------------------

CREATE TABLE tb_users (
  iduser INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  idperson INT(11) NOT NULL,
  deslogin VARCHAR(64) NOT NULL,
  despassword VARCHAR(256) NOT NULL,
  inadmin TINYINT(4) NOT NULL DEFAULT '0',
  dtregister TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_persons
    FOREIGN KEY (idperson)
    REFERENCES tb_persons (idperson)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);

CREATE INDEX FK_users_persons_idx ON tb_users (idperson ASC);

INSERT INTO `tb_users` VALUES (1,1,'admin','$2y$12$YlooCyNvyTji8bPRcrfNfOKnVMmZA9ViM2A3IpFjmrpIbp5ovNmga',1,'2017-03-13 03:00:00'),(7,7,'suporte','$2y$12$HFjgUm/mk1RzTy4ZkJaZBe0Mc/BA2hQyoUckvm.lFa6TesjtNpiMe',1,'2017-03-15 16:10:27');


-- -----------------------------------------------------
-- Table `tb_carts`
-- -----------------------------------------------------

CREATE TABLE tb_carts (
  idcart INT(11) NOT NULL PRIMARY KEY,
  dessessionid VARCHAR(64) NOT NULL,
  iduser INT(11) NULL DEFAULT NULL,
  idaddress INT(11) NULL DEFAULT NULL,
  vlfreight DECIMAL(10,2) NULL DEFAULT NULL,
  dtregister TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_carts_addresses
    FOREIGN KEY (idaddress)
    REFERENCES tb_addresses (idaddress)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_carts_users
    FOREIGN KEY (iduser)
    REFERENCES tb_users (iduser)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);

CREATE INDEX FK_carts_users_idx ON tb_carts (iduser ASC);

CREATE INDEX fk_carts_addresses_idx ON tb_carts (idaddress ASC);


-- -----------------------------------------------------
-- Table `tb_products`
-- -----------------------------------------------------

CREATE TABLE tb_products (
  idproduct INT(11) NOT NULL PRIMARY KEY,
  desproduct VARCHAR(64) NOT NULL,
  vlprice DECIMAL(10,2) NOT NULL,
  vlwidth DECIMAL(10,2) NOT NULL,
  vlheight DECIMAL(10,2) NOT NULL,
  vllength DECIMAL(10,2) NOT NULL,
  vlweight DECIMAL(10,2) NOT NULL,
  desurl VARCHAR(128) NOT NULL,
  dtregister TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO `tb_products` VALUES (1,'Smartphone Android 7.0',999.95,75.00,151.00,80.00,167.00,'smartphone-android-7.0','2017-03-13 03:00:00'),(2,'SmartTV LED 4K',3925.99,917.00,596.00,288.00,8600.00,'smarttv-led-4k','2017-03-13 03:00:00'),(3,'Notebook 14\" 4GB 1TB',1949.99,345.00,23.00,30.00,2000.00,'notebook-14-4gb-1tb','2017-03-13 03:00:00');

-- -----------------------------------------------------
-- Table `tb_cartsproducts`
-- -----------------------------------------------------


CREATE TABLE tb_cartsproducts (
  idcartproduct INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  idcart INT(11) NOT NULL,
  idproduct INT(11) NOT NULL,
  dtremoved DATETIME NOT NULL,
  dtregister TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cartsproducts_carts
    FOREIGN KEY (idcart)
    REFERENCES tb_carts (idcart)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_cartsproducts_products
    FOREIGN KEY (idproduct)
    REFERENCES tb_products (idproduct)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);

CREATE INDEX FK_cartsproducts_carts_idx ON tb_cartsproducts (idcart ASC);

CREATE INDEX FK_cartsproducts_products_idx ON tb_cartsproducts (idproduct ASC);


-- -----------------------------------------------------
-- Table `tb_categories`
-- -----------------------------------------------------

CREATE TABLE tb_categories (
  idcategory INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  descategory VARCHAR(32) NOT NULL,
  dtregister TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- -----------------------------------------------------
-- Table `tb_ordersstatus`
-- -----------------------------------------------------

CREATE TABLE tb_ordersstatus (
  idstatus INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  desstatus VARCHAR(32) NOT NULL,
  dtregister TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
  AUTO_INCREMENT = 5
);

INSERT INTO `tb_ordersstatus` VALUES (1,'Em Aberto','2017-03-13 03:00:00'),(2,'Aguardando Pagamento','2017-03-13 03:00:00'),(3,'Pago','2017-03-13 03:00:00'),(4,'Entregue','2017-03-13 03:00:00');

-- -----------------------------------------------------
-- Table `tb_orders`
-- -----------------------------------------------------

CREATE TABLE tb_orders (
  idorder INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  idcart INT(11) NOT NULL,
  iduser INT(11) NOT NULL,
  idstatus INT(11) NOT NULL,
  vltotal DECIMAL(10,2) NOT NULL,
  dtregister TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_carts
    FOREIGN KEY (idcart)
    REFERENCES tb_carts (idcart)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_orders_ordersstatus
    FOREIGN KEY (idstatus)
    REFERENCES tb_ordersstatus (idstatus)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_orders_users
    FOREIGN KEY (iduser)
    REFERENCES tb_users (iduser)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);

CREATE INDEX FK_orders_carts_idx ON tb_orders (idcart ASC);

CREATE INDEX FK_orders_users_idx ON tb_orders (iduser ASC);

CREATE INDEX fk_orders_ordersstatus_idx ON tb_orders (idstatus ASC);


-- -----------------------------------------------------
-- Table `tb_productscategories`
-- -----------------------------------------------------

CREATE TABLE tb_productscategories (
  idcategory INT(11) NOT NULL PRIMARY KEY,
  idproduct INT(11) NOT NULL,
  CONSTRAINT fk_productscategories_categories
    FOREIGN KEY (idcategory)
    REFERENCES tb_categories (idcategory)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_productscategories_products
    FOREIGN KEY (idproduct)
    REFERENCES tb_products (idproduct)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);

CREATE INDEX fk_productscategories_products_idx ON tb_productscategories (idproduct ASC);


-- -----------------------------------------------------
-- Table `tb_userslogs`
-- -----------------------------------------------------

CREATE TABLE tb_userslogs (
  idlog INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  iduser INT(11) NOT NULL,
  deslog VARCHAR(128) NOT NULL,
  desip VARCHAR(45) NOT NULL,
  desuseragent VARCHAR(128) NOT NULL,
  dessessionid VARCHAR(64) NOT NULL,
  desurl VARCHAR(128) NOT NULL,
  dtregister TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_userslogs_users
    FOREIGN KEY (iduser)
    REFERENCES tb_users (iduser)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);
CREATE INDEX fk_userslogs_users_idx ON tb_userslogs (iduser ASC);


-- -----------------------------------------------------
-- Table `tb_userspasswordsrecoveries`
-- -----------------------------------------------------

CREATE TABLE tb_userspasswordsrecoveries (
  idrecovery INT(11) NOT NULL AUTO_INCREMENT,
  iduser INT(11) NOT NULL,
  desip VARCHAR(45) NOT NULL,
  dtrecovery DATETIME NULL DEFAULT NULL,
  dtregister TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (idrecovery),
  CONSTRAINT fk_userspasswordsrecoveries_users
    FOREIGN KEY (iduser)
    REFERENCES tb_users (iduser)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);

CREATE INDEX fk_userspasswordsrecoveries_users_idx ON tb_userspasswordsrecoveries (iduser ASC);
INSERT INTO `tb_userspasswordsrecoveries` VALUES (1,7,'127.0.0.1',NULL,'2017-03-15 16:10:59'),(2,7,'127.0.0.1','2017-03-15 13:33:45','2017-03-15 16:11:18'),(3,7,'127.0.0.1','2017-03-15 13:37:35','2017-03-15 16:37:12');
