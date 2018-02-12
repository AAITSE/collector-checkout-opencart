CREATE TABLE IF NOT EXISTS `oc_collector_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quote_id` int(11) NOT NULL,
  `private_id` varchar(255) NOT NULL,
  `public_token` varchar(255) NOT NULL,
  `cart_items` text COMMENT 'Cart items, JSON',
  `currency_code` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `order_id` int(11) NOT NULL,
  `activated` tinyint(4) NOT NULL DEFAULT '0',
  `canceled` tinyint(4) NOT NULL DEFAULT '0',
  `credited` tinyint(4) NOT NULL DEFAULT '0',
  `status` varchar(50) DEFAULT NULL,
  `paymentName` varchar(255) DEFAULT NULL,
  `info` text,
  `purchaseIdentifier` varchar(255) DEFAULT NULL,
  `purchaseStatus` varchar(255) DEFAULT NULL,
  `country_code` varchar(50) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL,
  `expiresAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `private_id` (`private_id`),
  UNIQUE KEY `quote_id` (`quote_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `oc_collector_quote` (
  `quote_id` int(11) NOT NULL AUTO_INCREMENT,
  `visitor_id` int(11) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `quote_data` text COMMENT 'Serialized data in JSON',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`quote_id`),
  UNIQUE KEY `token` (`token`),
  KEY `visitor_id` (`visitor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `oc_collector_visitors` (
  `visitor_id` int(11) NOT NULL AUTO_INCREMENT,
  `api_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `session_id` varchar(32) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`visitor_id`),
  KEY `api_id` (`api_id`),
  KEY `customer_id` (`customer_id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
