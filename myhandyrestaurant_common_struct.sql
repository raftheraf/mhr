-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: Nov 12, 2013 alle 10:51
-- Versione del server: 5.5.32
-- Versione PHP: 5.4.6-1ubuntu1.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `myhandyrestaurant`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_accounting_dbs`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Nov 11, 2013 alle 16:14
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_accounting_dbs`;
CREATE TABLE `mhr_accounting_dbs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `db` text NOT NULL,
  `print_bill` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_allowed_clients`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_allowed_clients`;
CREATE TABLE `mhr_allowed_clients` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `host` text NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_autocalc`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_autocalc`;
CREATE TABLE `mhr_autocalc` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `quantity` tinyint(4) NOT NULL DEFAULT '0',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_categories`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_categories`;
CREATE TABLE `mhr_categories` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `ordine` int(11) NOT NULL DEFAULT '0',
  `htmlcolor` text NOT NULL,
  `vat_rate` mediumint(9) NOT NULL DEFAULT '0',
  `priority` tinyint(4) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `visible` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_categories_en`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_categories_en`;
CREATE TABLE `mhr_categories_en` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `table_id` mediumint(9) NOT NULL DEFAULT '0',
  `table_name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_categories_it`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_categories_it`;
CREATE TABLE `mhr_categories_it` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `table_id` mediumint(9) NOT NULL DEFAULT '0',
  `table_name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_conf`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Nov 11, 2013 alle 14:08
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_conf`;
CREATE TABLE `mhr_conf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` mediumtext NOT NULL,
  `value` mediumtext NOT NULL,
  `bool` tinyint(4) NOT NULL DEFAULT '0',
  `defaultval` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`(10))
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_conf_en`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_conf_en`;
CREATE TABLE `mhr_conf_en` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL DEFAULT '0',
  `table_name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_conf_it`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_conf_it`;
CREATE TABLE `mhr_conf_it` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL DEFAULT '0',
  `table_name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_countries`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_countries`;
CREATE TABLE `mhr_countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `currency_letter` text NOT NULL,
  `currency_name` text NOT NULL,
  `currency_html` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_customers`
--
-- Creazione: Apr 14, 2013 alle 06:23
-- Ultimo cambiamento: Nov 11, 2013 alle 16:14
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_customers`;
CREATE TABLE `mhr_customers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` tinytext NOT NULL,
  `surname` tinytext NOT NULL,
  `address` text NOT NULL,
  `city` tinytext NOT NULL,
  `zip` tinytext NOT NULL,
  `phone` tinytext NOT NULL,
  `mobile` tinytext NOT NULL,
  `email` mediumtext NOT NULL,
  `vat_account` tinytext NOT NULL,
  `codice_fiscale` tinytext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_dests`
--
-- Creazione: Nov 12, 2013 alle 02:08
-- Ultimo cambiamento: Nov 12, 2013 alle 09:39
-- Ultimo controllo: Nov 12, 2013 alle 09:39
--

DROP TABLE IF EXISTS `mhr_dests`;
CREATE TABLE `mhr_dests` (
  `id` bigint(9) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `dest` text NOT NULL,
  `driver` tinytext NOT NULL,
  `preconto` tinyint(4) NOT NULL DEFAULT '0',
  `preconto1` tinyint(4) NOT NULL DEFAULT '0',
  `bill` tinyint(4) NOT NULL DEFAULT '0',
  `bill1` tinyint(4) NOT NULL DEFAULT '0',
  `invoice` tinyint(4) NOT NULL DEFAULT '0',
  `invoice1` tinyint(4) NOT NULL DEFAULT '0',
  `receipt` tinyint(4) NOT NULL DEFAULT '0',
  `language` tinytext NOT NULL,
  `template` text NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_dishes`
--
-- Creazione: Mar 25, 2013 alle 08:22
-- Ultimo cambiamento: Apr 08, 2013 alle 10:48
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_dishes`;
CREATE TABLE `mhr_dishes` (
  `id` bigint(9) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `ingreds` text NOT NULL,
  `destid` mediumint(9) NOT NULL DEFAULT '0',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `category` mediumint(9) NOT NULL DEFAULT '1',
  `autocalc` smallint(4) NOT NULL DEFAULT '0',
  `dispingreds` text NOT NULL,
  `stock_is_on` tinyint(4) NOT NULL DEFAULT '0',
  `stock` int(11) NOT NULL DEFAULT '0',
  `generic` tinyint(4) NOT NULL DEFAULT '0',
  `visible` tinyint(4) NOT NULL DEFAULT '1',
  `autocalc_skip` tinyint(4) NOT NULL DEFAULT '0',
  `personal_list` tinyint(4) NOT NULL DEFAULT '0',
  `personal_list_order` int(11) NOT NULL DEFAULT '0',
  `menufisso` tinyint(4) NOT NULL DEFAULT '0',
  `dishesmenufisso` text NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`(14))
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_dishes_en`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_dishes_en`;
CREATE TABLE `mhr_dishes_en` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `table_id` mediumint(9) NOT NULL DEFAULT '0',
  `table_name` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `table_id` (`table_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_dishes_it`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_dishes_it`;
CREATE TABLE `mhr_dishes_it` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `table_id` mediumint(9) NOT NULL DEFAULT '0',
  `table_name` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `table_id` (`table_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_ingreds`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_ingreds`;
CREATE TABLE `mhr_ingreds` (
  `id` bigint(9) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `category` mediumint(9) NOT NULL DEFAULT '1',
  `override_autocalc` tinyint(4) NOT NULL DEFAULT '0',
  `visible` tinyint(4) NOT NULL DEFAULT '1',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_ingreds_en`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_ingreds_en`;
CREATE TABLE `mhr_ingreds_en` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `table_id` mediumint(9) NOT NULL DEFAULT '0',
  `table_name` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `table_id` (`table_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_ingreds_it`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_ingreds_it`;
CREATE TABLE `mhr_ingreds_it` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `table_id` mediumint(9) NOT NULL DEFAULT '0',
  `table_name` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `table_id` (`table_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_lang`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_lang`;
CREATE TABLE `mhr_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`(20))
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_lang_en`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_lang_en`;
CREATE TABLE `mhr_lang_en` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL DEFAULT '0',
  `table_name` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `table_id` (`table_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_lang_it`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_lang_it`;
CREATE TABLE `mhr_lang_it` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL DEFAULT '0',
  `table_name` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `table_id` (`table_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_last_orders`
--
-- Creazione: Apr 14, 2013 alle 06:23
-- Ultimo cambiamento: Nov 12, 2013 alle 02:04
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_last_orders`;
CREATE TABLE `mhr_last_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dishid` mediumint(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_mgmt_people_types`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_mgmt_people_types`;
CREATE TABLE `mhr_mgmt_people_types` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_mgmt_people_types_en`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_mgmt_people_types_en`;
CREATE TABLE `mhr_mgmt_people_types_en` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `table_id` tinyint(4) NOT NULL DEFAULT '0',
  `table_name` text NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_mgmt_people_types_it`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_mgmt_people_types_it`;
CREATE TABLE `mhr_mgmt_people_types_it` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `table_id` tinyint(4) NOT NULL DEFAULT '0',
  `table_name` text NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_mgmt_types`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_mgmt_types`;
CREATE TABLE `mhr_mgmt_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `account_only` tinyint(4) NOT NULL DEFAULT '0',
  `is_invoice` tinyint(4) NOT NULL DEFAULT '0',
  `is_receipt` tinyint(4) NOT NULL DEFAULT '0',
  `log_to_bank` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_invoice_payment` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_bill` tinyint(4) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_mgmt_types_en`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_mgmt_types_en`;
CREATE TABLE `mhr_mgmt_types_en` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL DEFAULT '0',
  `table_name` text NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_mgmt_types_it`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_mgmt_types_it`;
CREATE TABLE `mhr_mgmt_types_it` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL DEFAULT '0',
  `table_name` text NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_orders`
--
-- Creazione: Nov 12, 2013 alle 01:20
-- Ultimo cambiamento: Nov 12, 2013 alle 09:39
-- Ultimo controllo: Nov 12, 2013 alle 09:39
--

DROP TABLE IF EXISTS `mhr_orders`;
CREATE TABLE `mhr_orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `dishid` mediumint(9) NOT NULL DEFAULT '0',
  `printed` datetime DEFAULT '0000-00-00 00:00:00',
  `suspend` tinyint(4) NOT NULL DEFAULT '0',
  `associated_id` mediumint(9) NOT NULL DEFAULT '0',
  `operation` mediumint(9) NOT NULL DEFAULT '0',
  `ingredid` text NOT NULL,
  `ingred_qty` tinyint(4) NOT NULL DEFAULT '0',
  `extra_care` tinyint(4) NOT NULL DEFAULT '0',
  `sourceid` mediumint(9) NOT NULL DEFAULT '0',
  `quantity` mediumint(9) NOT NULL DEFAULT '0',
  `priority` smallint(6) NOT NULL DEFAULT '1',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ingreds` text NOT NULL,
  `category` mediumint(9) NOT NULL DEFAULT '1',
  `paid` mediumint(9) NOT NULL DEFAULT '0',
  `deleted` smallint(6) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dest_id` mediumint(9) NOT NULL DEFAULT '0',
  `menu_fisso` tinyint(4) NOT NULL DEFAULT '0',
  `nota_ordine` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sourceid` (`sourceid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_sources`
--
-- Creazione: Apr 09, 2013 alle 07:33
-- Ultimo cambiamento: Nov 12, 2013 alle 09:39
-- Ultimo controllo: Nov 12, 2013 alle 09:39
--

DROP TABLE IF EXISTS `mhr_sources`;
CREATE TABLE `mhr_sources` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `ordernum` int(11) NOT NULL DEFAULT '0',
  `visible` tinyint(4) NOT NULL DEFAULT '1',
  `userid` mediumint(9) NOT NULL DEFAULT '0',
  `toclose` tinyint(4) NOT NULL DEFAULT '0',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `paid` tinyint(4) NOT NULL DEFAULT '0',
  `catprinted` text NOT NULL,
  `catprinted_time` datetime DEFAULT '0000-00-00 00:00:00',
  `last_access_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_access_userid` mediumint(9) NOT NULL DEFAULT '0',
  `takeaway` tinyint(4) NOT NULL DEFAULT '0',
  `takeaway_surname` text NOT NULL,
  `takeaway_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `customer` bigint(20) NOT NULL DEFAULT '0',
  `tablehtmlcolor` text NOT NULL,
  `nota_tavolo` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_stock_ingredient_quantities`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_stock_ingredient_quantities`;
CREATE TABLE `mhr_stock_ingredient_quantities` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `obj_id` bigint(20) NOT NULL DEFAULT '0',
  `dish_id` bigint(20) NOT NULL DEFAULT '0',
  `quantity` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `obj_id` (`obj_id`),
  KEY `dish_id` (`dish_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_stock_ingredient_samples`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_stock_ingredient_samples`;
CREATE TABLE `mhr_stock_ingredient_samples` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `obj_id` bigint(20) NOT NULL DEFAULT '0',
  `dish_id` bigint(20) NOT NULL DEFAULT '0',
  `quantity` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `obj_id` (`obj_id`),
  KEY `dish_id` (`dish_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_stock_movements`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Nov 12, 2013 alle 00:56
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_stock_movements`;
CREATE TABLE `mhr_stock_movements` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `obj_id` bigint(20) NOT NULL DEFAULT '0',
  `dish_id` bigint(20) NOT NULL DEFAULT '0',
  `dish_quantity` float NOT NULL DEFAULT '0',
  `quantity` float NOT NULL DEFAULT '0',
  `unit_type` tinyint(4) NOT NULL DEFAULT '0',
  `value` float NOT NULL DEFAULT '0',
  `user` mediumint(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `obj_id` (`obj_id`),
  KEY `dish_id` (`dish_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_stock_objects`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_stock_objects`;
CREATE TABLE `mhr_stock_objects` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `ref_type` tinyint(4) NOT NULL DEFAULT '0',
  `ref_id` bigint(20) NOT NULL DEFAULT '0',
  `quantity` float NOT NULL DEFAULT '0',
  `unit_type` tinyint(4) NOT NULL DEFAULT '0',
  `value` float NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`(10)),
  KEY `ref_id` (`ref_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_stock_samples`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_stock_samples`;
CREATE TABLE `mhr_stock_samples` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `obj_id` bigint(20) NOT NULL DEFAULT '0',
  `quantity` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`,`obj_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_system`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_system`;
CREATE TABLE `mhr_system` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_users`
--
-- Creazione: Nov 11, 2013 alle 16:17
-- Ultimo cambiamento: Nov 11, 2013 alle 20:36
--

DROP TABLE IF EXISTS `mhr_users`;
CREATE TABLE `mhr_users` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `can_open_closed_table` tinyint(4) NOT NULL DEFAULT '0',
  `language` tinytext NOT NULL,
  `level` int(11) NOT NULL DEFAULT '0',
  `password` text NOT NULL,
  `template` text NOT NULL,
  `disabled` tinyint(4) NOT NULL DEFAULT '0',
  `user_host` text NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `preconto` tinyint(4) NOT NULL DEFAULT '0',
  `preconto1` tinyint(4) NOT NULL DEFAULT '0',
  `ricevuta` tinyint(4) NOT NULL DEFAULT '0',
  `ricevuta1` tinyint(4) NOT NULL DEFAULT '0',
  `fattura` tinyint(4) NOT NULL DEFAULT '0',
  `fattura1` tinyint(4) NOT NULL DEFAULT '0',
  `scontrino` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `mhr_vat_rates`
--
-- Creazione: Mar 05, 2013 alle 23:27
-- Ultimo cambiamento: Apr 02, 2013 alle 18:02
-- Ultimo controllo: Nov 11, 2013 alle 16:14
--

DROP TABLE IF EXISTS `mhr_vat_rates`;
CREATE TABLE `mhr_vat_rates` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `rate` decimal(3,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
