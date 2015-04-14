CREATE TABLE IF NOT EXISTS `info` (
  `name` varchar(255) NOT NULL COMMENT 'Name of info, e.g. last_admin_login etc',
  `value` varchar(255) NOT NULL COMMENT 'Value of info, could be anything.',
  `comment` text COMMENT 'Optional comment to explain info',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `info` (`name`, `value`, `comment`) VALUES
('1_block_height', '0', 'Last checked block height for monero'),
('1_display_block_height', '0', NULL),
('last_cron', '0', 'When last cron was run (unixtime)'),
('member_count', '0', NULL);

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `user` (
  `user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` char(64) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users_assets` (
  `user_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Foreign key to user.user_id',
  `asset_id` int(11) unsigned NOT NULL COMMENT '1 = XMR, we use this field to allow multiple currencies/assets',
  `balance` decimal(56,24) NOT NULL DEFAULT '0.000000000000000000000000' COMMENT 'Current available balance',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-------------------------------------------------

CREATE TABLE IF NOT EXISTS `users_cn_payment_ids` (
  `pid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) unsigned NOT NULL COMMENT 'Asset ID',
  `payment_id` char(64) NOT NULL COMMENT 'Payment ID',
  `user_id` bigint(20) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`pid`),
  UNIQUE KEY `payment_id` (`payment_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users_cn_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'CryptoNote Txn ID',
  `pid` bigint(20) unsigned NOT NULL COMMENT 'Foreign key to users_cn_payment_ids.pid',
  `amount` decimal(28,12) unsigned NOT NULL COMMENT 'The amount transaction',
  `block_height` int(11) unsigned NOT NULL COMMENT 'The block height of this transaction',
  `tx_hash` char(64) NOT NULL,
  `datetime` datetime NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 = pending, 1 = complete',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users_transactions` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Txn ID',
  `user_id` bigint(20) unsigned NOT NULL COMMENT 'Foreign key to user.user_id',
  `amount` decimal(56,24) NOT NULL COMMENT 'Positive = incoming, Negative = outgoing',
  `asset_id` int(11) unsigned NOT NULL COMMENT 'Asset that was transacted',
  `datetime` datetime NOT NULL COMMENT 'Date and time of txn',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `withdraws_complete` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Withdraw ID',
  `user_id` bigint(20) unsigned NOT NULL COMMENT 'User ID who requested payment',
  `address` text NOT NULL COMMENT 'Reciever address, e.g bitcoin address, monero address, bank info etc',
  `amount` decimal(56,24) unsigned NOT NULL DEFAULT '0.000000000000000000000000' COMMENT 'Amount',
  `fee` decimal(56,24) unsigned NOT NULL DEFAULT '0.000000000000000000000000' COMMENT 'Fee amount (already detucted from amount)',
  `date_paid` datetime NOT NULL,
  `asset_id` int(11) unsigned NOT NULL COMMENT 'Asset ID',
  `mixin` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `txn` text NOT NULL COMMENT 'Transaction id onec status is 1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Completed withdraws' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `withdraws_pending` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Withdraw ID',
  `user_id` bigint(20) unsigned NOT NULL COMMENT 'User ID who requested payment',
  `address` text NOT NULL COMMENT 'Reciever address, e.g bitcoin address, monero address, bank info etc',
  `payment_id` char(64) NOT NULL,
  `amount` decimal(56,24) unsigned NOT NULL DEFAULT '0.000000000000000000000000' COMMENT 'Amount (without fee / receivable)',
  `fee` decimal(56,24) unsigned NOT NULL DEFAULT '0.000000000000000000000000' COMMENT 'Fee amount (already detucted from amount)',
  `date_requested` datetime NOT NULL,
  `asset_id` int(11) unsigned NOT NULL COMMENT 'Asset ID',
  `mixin` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL COMMENT '0 = Pending, 1 = Approved (waiting for payment processing), -1 error/failed, rejected/canceled are deleted',
  `error` text NOT NULL COMMENT 'If status = -1, there can be an error message here',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Pending withdraws' AUTO_INCREMENT=1 ;