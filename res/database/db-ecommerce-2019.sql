CREATE DATABASE IF NOT EXISTS db_ecommerce /*!40100 DEFAULT CHARACTER SET utf8 */;
USE db_ecommerce;


DROP TABLE IF EXISTS tb_categories;
CREATE TABLE tb_categories
(
    idcategory  int(11)     NOT NULL AUTO_INCREMENT PRIMARY KEY,
    descategory varchar(32) NOT NULL,
    dtregister  timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


DROP TABLE IF EXISTS tb_ordersstatus;
CREATE TABLE tb_ordersstatus
(
    idstatus   int(11)     NOT NULL AUTO_INCREMENT PRIMARY KEY,
    desstatus  varchar(32) NOT NULL,
    dtregister timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB
  AUTO_INCREMENT = 5
  DEFAULT CHARSET = utf8;
INSERT INTO tb_ordersstatus
VALUES (1, 'Em Aberto', '2017-03-13 03:00:00'),
       (2, 'Aguardando Pagamento', '2017-03-13 03:00:00'),
       (3, 'Pago', '2017-03-13 03:00:00'),
       (4, 'Entregue', '2017-03-13 03:00:00');


DROP TABLE IF EXISTS tb_products;
CREATE TABLE tb_products
(
    idproduct  int(11)        NOT NULL AUTO_INCREMENT PRIMARY KEY,
    desproduct varchar(64)    NOT NULL,
    vlprice    decimal(10, 2) NOT NULL,
    vlwidth    decimal(10, 2) NOT NULL,
    vlheight   decimal(10, 2) NOT NULL,
    vllength   decimal(10, 2) NOT NULL,
    vlweight   decimal(10, 2) NOT NULL,
    desurl     varchar(128)   NOT NULL,
    dtregister timestamp      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
INSERT INTO tb_products (desproduct, vlprice, vlwidth, vlheight, vllength, vlweight, desurl)
VALUES ('Smartphone Motorola Moto G5 Plus', 1135.23, 15.2, 7.4, 0.7, 0.160, 'smartphone-motorola-moto-g5-plus'),
       ('Smartphone Moto Z Play', 1887.78, 14.1, 0.9, 1.16, 0.134, 'smartphone-moto-z-play'),
       ('Smartphone Samsung Galaxy J5 Pro', 1299, 14.6, 7.1, 0.8, 0.160, 'smartphone-samsung-galaxy-j5'),
       ('Smartphone Samsung Galaxy J7 Prime', 1149, 15.1, 7.5, 0.8, 0.160, 'smartphone-samsung-galaxy-j7'),
       ('Smartphone Samsung Galaxy J3 Dual', 679.90, 14.2, 7.1, 0.7, 0.138, 'smartphone-samsung-galaxy-j3');


DROP TABLE IF EXISTS tb_persons;
CREATE TABLE tb_persons
(
    idperson   int(11)     NOT NULL AUTO_INCREMENT PRIMARY KEY,
    desperson  varchar(64) NOT NULL,
    desemail   varchar(128)         DEFAULT NULL,
    nrphone    bigint(20)           DEFAULT NULL,
    dtregister timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
INSERT INTO tb_persons
VALUES (1, 'Luiz Fernandes de Oliveira', 'luizfernandes29111997116669964@gmail.com', 84981195936,
        '2019-04-14 02:00:00');



DROP TABLE IF EXISTS tb_addresses;

CREATE TABLE `tb_addresses`
(
    `idaddress`     int(11)      NOT NULL AUTO_INCREMENT,
    `idperson`      int(11)      NOT NULL,
    `desaddress`    varchar(128) NOT NULL,
    `descomplement` varchar(32)           DEFAULT NULL,
    `descity`       varchar(32)  NOT NULL,
    `desstate`      varchar(32)  NOT NULL,
    `descountry`    varchar(32)  NOT NULL,
    `deszipcode`    char(8)      NOT NULL,
    `desdistrict`   varchar(32)  NOT NULL,
    `dtregister`    timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`idaddress`),
    KEY `fk_addresses_persons_idx` (`idperson`),
    CONSTRAINT `fk_addresses_persons` FOREIGN KEY (`idperson`) REFERENCES `tb_persons` (`idperson`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8;


DROP TABLE IF EXISTS tb_users;
CREATE TABLE tb_users
(
    iduser      int(11)      NOT NULL AUTO_INCREMENT PRIMARY KEY,
    idperson    int(11)      NOT NULL,
    deslogin    varchar(64)  NOT NULL,
    despassword varchar(256) NOT NULL,
    inadmin     tinyint(4)   NOT NULL DEFAULT '0',
    dtregister  timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY FK_users_persons_idx (idperson),
    CONSTRAINT fk_users_persons FOREIGN KEY (idperson) REFERENCES tb_persons (idperson) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
INSERT INTO tb_users
VALUES (1, 1, 'admin', '$2y$12$YlooCyNvyTji8bPRcrfNfOKnVMmZA9ViM2A3IpFjmrpIbp5ovNmga', 1, '2017-03-13 03:00:00');


DROP TABLE IF EXISTS tb_carts;
CREATE TABLE tb_carts
(
    idcart       int(11)     NOT NULL AUTO_INCREMENT PRIMARY KEY,
    dessessionid varchar(64) NOT NULL,
    iduser       int(11)              DEFAULT NULL,
    deszipcode   char(8)              DEFAULT NULL,
    vlfreight    decimal(10, 2)       DEFAULT NULL,
    nrdays       int(11)              DEFAULT NULL,
    dtregister   timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY FK_carts_users_idx (iduser),
    CONSTRAINT fk_carts_users FOREIGN KEY (iduser) REFERENCES tb_users (iduser) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


DROP TABLE IF EXISTS tb_cartsproducts;
CREATE TABLE tb_cartsproducts
(
    idcartproduct int(11)   NOT NULL AUTO_INCREMENT PRIMARY KEY,
    idcart        int(11)   NOT NULL,
    idproduct     int(11)   NOT NULL,
    dtremoved     datetime,
    dtregister    timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY FK_cartsproducts_carts_idx (idcart),
    KEY FK_cartsproducts_products_idx (idproduct),
    CONSTRAINT fk_cartsproducts_carts FOREIGN KEY (idcart) REFERENCES tb_carts (idcart) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_cartsproducts_products FOREIGN KEY (idproduct) REFERENCES tb_products (idproduct) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


DROP TABLE IF EXISTS tb_orders;
CREATE TABLE tb_orders
(
    idorder    int(11)        NOT NULL AUTO_INCREMENT PRIMARY KEY,
    idcart     int(11)        NOT NULL,
    iduser     int(11)        NOT NULL,
    idstatus   int(11)        NOT NULL,
    vltotal    decimal(10, 2) NOT NULL,
    dtregister timestamp      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY FK_orders_carts_idx (idcart),
    KEY FK_orders_users_idx (iduser),
    KEY fk_orders_ordersstatus_idx (idstatus),
    CONSTRAINT fk_orders_carts FOREIGN KEY (idcart) REFERENCES tb_carts (idcart) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_orders_ordersstatus FOREIGN KEY (idstatus) REFERENCES tb_ordersstatus (idstatus) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_orders_users FOREIGN KEY (iduser) REFERENCES tb_users (iduser) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


DROP TABLE IF EXISTS tb_productscategories;
CREATE TABLE tb_productscategories
(
    idcategory int(11) NOT NULL,
    idproduct  int(11) NOT NULL,
    PRIMARY KEY (idcategory, idproduct),
    KEY fk_productscategories_products_idx (idproduct),
    CONSTRAINT fk_productscategories_categories FOREIGN KEY (idcategory) REFERENCES tb_categories (idcategory) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_productscategories_products FOREIGN KEY (idproduct) REFERENCES tb_products (idproduct) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


DROP TABLE IF EXISTS tb_userslogs;
CREATE TABLE tb_userslogs
(
    idlog        int(11)      NOT NULL AUTO_INCREMENT PRIMARY KEY,
    iduser       int(11)      NOT NULL,
    deslog       varchar(128) NOT NULL,
    desip        varchar(45)  NOT NULL,
    desuseragent varchar(128) NOT NULL,
    dessessionid varchar(64)  NOT NULL,
    desurl       varchar(128) NOT NULL,
    dtregister   timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY fk_userslogs_users_idx (iduser),
    CONSTRAINT fk_userslogs_users FOREIGN KEY (iduser) REFERENCES tb_users (iduser) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


DROP TABLE IF EXISTS tb_userspasswordsrecoveries;
CREATE TABLE tb_userspasswordsrecoveries
(
    idrecovery int(11)     NOT NULL AUTO_INCREMENT PRIMARY KEY,
    iduser     int(11)     NOT NULL,
    desip      varchar(45) NOT NULL,
    dtrecovery datetime             DEFAULT NULL,
    dtregister timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY fk_userspasswordsrecoveries_users_idx (iduser),
    CONSTRAINT fk_userspasswordsrecoveries_users FOREIGN KEY (iduser) REFERENCES tb_users (iduser) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


DELIMITER ;;
CREATE PROCEDURE sp_userspasswordsrecoveries_create(piduser INT,
                                                    pdesip VARCHAR(45))
BEGIN
    INSERT INTO tb_userspasswordsrecoveries (iduser, desip)
    VALUES (piduser, pdesip);

    SELECT *
    FROM tb_userspasswordsrecoveries
    WHERE idrecovery = LAST_INSERT_ID();
END ;;
DELIMITER ;


DELIMITER ;;
CREATE PROCEDURE sp_usersupdate_save(piduser INT,
                                     pdesperson VARCHAR(64),
                                     pdeslogin VARCHAR(64),
                                     pdespassword VARCHAR(256),
                                     pdesemail VARCHAR(128),
                                     pnrphone BIGINT,
                                     pinadmin TINYINT)
BEGIN

    DECLARE vidperson INT;

    SELECT idperson INTO vidperson
    FROM tb_users
    WHERE iduser = piduser;

    UPDATE tb_persons
    SET desperson = pdesperson,
        desemail  = pdesemail,
        nrphone   = pnrphone
    WHERE idperson = vidperson;

    UPDATE tb_users
    SET deslogin    = pdeslogin,
        despassword = pdespassword,
        inadmin     = pinadmin
    WHERE iduser = piduser;

    SELECT *
    FROM tb_users a
             INNER JOIN tb_persons b USING (idperson)
    WHERE a.iduser = piduser;

END ;;
DELIMITER ;


DELIMITER ;;
CREATE PROCEDURE sp_users_delete(
    piduser INT
)
BEGIN

    DECLARE vidperson INT;

    SELECT idperson INTO vidperson
    FROM tb_users
    WHERE iduser = piduser;

    DELETE FROM tb_users WHERE iduser = piduser;
    DELETE FROM tb_persons WHERE idperson = vidperson;

END ;;
DELIMITER ;


DELIMITER ;;
CREATE PROCEDURE sp_users_save(pdesperson VARCHAR(64),
                               pdeslogin VARCHAR(64),
                               pdespassword VARCHAR(256),
                               pdesemail VARCHAR(128),
                               pnrphone BIGINT,
                               pinadmin TINYINT)
BEGIN

    DECLARE vidperson INT;

    INSERT INTO tb_persons (desperson, desemail, nrphone)
    VALUES (pdesperson, pdesemail, pnrphone);

    SET vidperson = LAST_INSERT_ID();

    INSERT INTO tb_users (idperson, deslogin, despassword, inadmin)
    VALUES (vidperson, pdeslogin, pdespassword, pinadmin);

    SELECT *
    FROM tb_users a
             INNER JOIN tb_persons b USING (idperson)
    WHERE a.iduser = LAST_INSERT_ID();

END ;;
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_categories_save(pidcategory INT,
                                    pdescategory VARCHAR(64))
BEGIN

    IF pidcategory > 0 THEN

        UPDATE tb_categories
        SET descategory = pdescategory
        WHERE idcategory = pidcategory;

    ELSE

        INSERT INTO tb_categories (descategory) VALUES (pdescategory);

        SET pidcategory = LAST_INSERT_ID();

    END IF;

    SELECT * FROM tb_categories WHERE idcategory = pidcategory;

END$$

DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_products_save(pidproduct int(11),
                                  pdesproduct varchar(64),
                                  pvlprice decimal(10, 2),
                                  pvlwidth decimal(10, 2),
                                  pvlheight decimal(10, 2),
                                  pvllength decimal(10, 2),
                                  pvlweight decimal(10, 2),
                                  pdesurl varchar(128))
BEGIN

    IF pidproduct > 0 THEN

        UPDATE tb_products
        SET desproduct = pdesproduct,
            vlprice    = pvlprice,
            vlwidth    = pvlwidth,
            vlheight   = pvlheight,
            vllength   = pvllength,
            vlweight   = pvlweight,
            desurl     = pdesurl
        WHERE idproduct = pidproduct;

    ELSE

        INSERT INTO tb_products (desproduct, vlprice, vlwidth, vlheight, vllength, vlweight, desurl)
        VALUES (pdesproduct, pvlprice, pvlwidth, pvlheight, pvllength, pvlweight, pdesurl);

        SET pidproduct = LAST_INSERT_ID();

    END IF;

    SELECT * FROM tb_products WHERE idproduct = pidproduct;

END$$

DELIMITER ;

DELIMITER $$
CREATE PROCEDURE `sp_carts_save`(pidcart INT,
                                 pdessessionid VARCHAR(64),
                                 piduser INT,
                                 pdeszipcode CHAR(8),
                                 pvlfreight DECIMAL(10, 2),
                                 pnrdays INT)
BEGIN

    IF pidcart > 0 THEN

        UPDATE tb_carts
        SET dessessionid = pdessessionid,
            iduser       = piduser,
            deszipcode   = pdeszipcode,
            vlfreight    = pvlfreight,
            nrdays       = pnrdays
        WHERE idcart = pidcart;

    ELSE

        INSERT INTO tb_carts (dessessionid, iduser, deszipcode, vlfreight, nrdays)
        VALUES (pdessessionid, piduser, pdeszipcode, pvlfreight, pnrdays);

        SET pidcart = LAST_INSERT_ID();

    END IF;

    SELECT * FROM tb_carts WHERE idcart = pidcart;

END$$

DELIMITER ;

DELIMITER $$
CREATE PROCEDURE `sp_addresses_save`(pidaddress int(11),
                                     pidperson int(11),
                                     pdesaddress varchar(128),
                                     pdescomplement varchar(32),
                                     pdescity varchar(32),
                                     pdesstate varchar(32),
                                     pdescountry varchar(32),
                                     pdeszipcode char(8),
                                     pdesdistrict varchar(32))
BEGIN

    IF pidaddress > 0 THEN

        UPDATE tb_addresses
        SET idperson      = pidperson,
            desaddress    = pdesaddress,
            descomplement = pdescomplement,
            descity       = pdescity,
            desstate      = pdesstate,
            descountry    = pdescountry,
            deszipcode    = pdeszipcode,
            desdistrict   = pdesdistrict
        WHERE idaddress = pidaddress;

    ELSE

        INSERT INTO tb_addresses (idperson, desaddress, descomplement, descity, desstate, descountry, deszipcode,
                                  desdistrict)
        VALUES (pidperson, pdesaddress, pdescomplement, pdescity, pdesstate, pdescountry, pdeszipcode, pdesdistrict);

        SET pidaddress = LAST_INSERT_ID();

    END IF;

    SELECT * FROM tb_addresses WHERE idaddress = pidaddress;

END$$
DELIMITER ;
