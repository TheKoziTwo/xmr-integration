<?php if(!defined('APP')) exit;
// Asset IDs (these cannot be changed after entries have been added in database, but you may add new ones)
define('XMR',1);
define('BTC',2);
define('USD',3);
define('BCN',4);
define('BBR',5);

$config = array(
    'database' => array(
            'hostname' => 'localhost',
            'username' => 'root',
            'password' => '',
            'database' => 'monero',
    ),
    'site' => array(
        // If you're running script at root of domain leave this one "/", if you run in a subdirectory such as "http://YOURDOMAIN.com/xmr/exchange" you should enter "/xmr/exchange"
        'base_url' => '/', 
    ),
    'asset' => array(
                XMR => array(
                    // Your receiving XMR address, should be the one in your simplewallet
                    'address'       => '4ABnA7A4NuvJFkUdEWrwxmQa2z1s6UKuw3n6LxkC5hsHh5SFBaj7WUDEEepy9VZE85FY5862roiDS26G519ktCTxGPAgGCD', 
                    // Default is 127.0.0.1
                    'daemon_host'   => '127.0.0.1',
                    // Default is 18081
                    'daemon_port'   => '18081',
                    // Default is 127.0.0.1
                    'wallet_host'   => '127.0.0.1',
                    // Default is 18082
                    'wallet_port'   => '18082',
                    // The minimum confirmations before an XMR transaction is approved (Default: 15)
                    'min_conf'      => '15',
                    // Minimum mixin is 0
                    'min_mixin'     => '1', 
                    // Maximum mixin
                    'max_mixin'     => '10',
                    // Default mixin is 3 
                    'default_mixin' => '3',
                    // Minimum withdraw amount in XMR
                    'min_withdraw'  => '0.1', 
                    // Fee per withdraw in XMR
                    'withdraw_fee'  => '0.01',
                    // Properties is sent to the CryptoNote object, DO NOT change unless you know what you are doing
                    'properties'    => array(
                            'protocol'   => 'CryptoNote',
                            'name'       => 'Monero', // Safe to change
                            'short_name' => 'XMR', // if you change this, you must also change name of the files
                            'decimals'   => 12  
                    ),
                ),
                // You could add more cryptonote currencies by following the scheme of XMR (@todo: This is only implemented in admin and cron, user area is xmr only)
                BCN => array(
                ),
                BBR => array(
                ),
    ),
    'user' => array(
        'username' => array(
            // Minimum characters in username (1 or more)
            'min_length' => 3,
            // Maximum characters in username (20 is max in DB)
            'max_length' => 20,
        ), 
        'password' => array(
            'min_length' => 6,
        ),
    ),
    'errors' => array(
        // If set to TRUE, you will receive an e-mail for any critical errors (recommended), such as daemon stops working
        'enable_mail_notify' => TRUE,
        // E-mail to which any error notifications will be sent
        'mail' => 'your@email.com',
    ),
    'admins' => array(
        // List all usernames here whom you'd like to assign admin access
        'admin', 
    ),
);