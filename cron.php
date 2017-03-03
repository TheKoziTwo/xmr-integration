<?php require_once('init.php');
/**
 * Daemon/cron job
 * 
 * This script will process all incoming and outgoing payments. That includes adding/removing from database and communicating with the cryptonote wallet/daemon
 * 
 * Ideally, you should set up as a process on the server running forever (php cron.php), if you for some reason cannot do that
 * you can still run the script as a cron job by adding ?cron=1 as a parameter, e.g:
 * http://yourdomain.com/cron.php?cron=1 
 * 
 * If you want to setup the script to run forever my recommendation is to login to ssh and use screen.
 *      1. Screen allows you to run background processes after disconnecting from ssh, open screen with cmd: screen
 *      2. Navigate to the location of this file (cron.php)
 *      3. cmd: php cron.php
 *      4. Now this script is running inside a screen, to close screen enter CTRL + A + D (hold CTRL, press A, release A, press D)
 *      5. You may now disconnect from SSH. If you want ot connect to the screen again view list of screens with cmd: screen -list
 *      6. Connect to a specific screen cmd: screen -r 1111.pts-42.example (replace with the name of your screen obviously)
 * 
 * This script will process all assets that you have enabled in config, but will shut down if daemon or wallet is not running (you will be notified by e-mail
 * if you have enabled that in config).
 * 
 * @author     TheKoziTwo <thekozitwo@gmail.com>
 * @copyright  2015
 * @license    Public Domain
 */ 

# Ramblings/todos/etc:
# ensure that fork will not destroy system: e.g check again each time if txn is still found using get_bulk_payments.. 
# take care of payments without payment id.
  
$asset_ids = Asset::get_assets();

$has_run = false;

// Loop forever or once (depending on configs)
while(LOOP_FOREVER OR ! $has_run) 
{              
    // Take a break between each loop
    if($has_run)
    {
        sleep(SLEEP_TIME_SEC);
    }
    
    $has_run = true;
    
    foreach($asset_ids as $asset_id)
    {    
        $asset = Asset::init($asset_id,$config['asset'][$asset_id]['properties']);
        
        $class = 'CryptoNote_Wallet_'.$asset->short_name;
        if( ! class_exists($class)) $class = 'CryptoNote_Wallet';
        $wallet = new $class($config['asset'][$asset->get_id()]['wallet_host'],$config['asset'][$asset->get_id()]['wallet_port']);
        
        $class = 'CryptoNote_Daemon_'.$asset->short_name;
        if( ! class_exists($class)) $class = 'CryptoNote_Daemon';
        $daemon = new $class($config['asset'][$asset->get_id()]['daemon_host'],$config['asset'][$asset->get_id()]['daemon_port']);
    
        if( ! $wallet->is_responding())
        {
            Log::error(Log::PRIORITY_HIGH,$asset->short_name.' Wallet is not responding','The cron.php script was shut down due to wallet not responding.');
            // @todo : in the future we may want to let the script continue (if we have multiple assets), perhaps remove the one which is not responding from the loop?
            exit;
        }
          
        // Get current block height:
        $block_height = $daemon->get_block_count();
        
        // If no contact could be established the daemon could be offline. If so, shut down and notify admin
        if($daemon->is_offline())
        {
            Log::error(Log::PRIORITY_HIGH,$asset->short_name.' Daemon is not responding','The cron.php script was shut down due to daemon not responding');
            // @todo : in the future we may want to let the script continue (if we have multiple assets), perhaps remove the one which is not responding from the loop?
            exit;
        }
        
        // Daemon is busy while saving blockchain, if so, just try again until it is no longer busy
        if($daemon->is_busy())
        {
            continue;
        }
        
        // --------------------------------------------------------------------
        //
        //                  Add new incoming payments to database
        // 
        // --------------------------------------------------------------------

        // Look for any new payments (detuct one, since method will fetch more than > and not more than or equal >=)
        $min_height = (int) (info($asset->get_id().'_block_height') - 1 );
    
        if($payments = $wallet->get_bulk_payments($min_height))
        {   
            $db->query('START TRANSACTION');
    
            foreach($payments as $payment)
            {
                // Skip any payments that are time locked (we won't allow that)
                if($payment['unlock_time'] !== 0)
                {
                    continue;
                }
                
                // Convert amount to decimal value
                $amount = bcdiv($payment['amount'],pow(10,$asset->decimals),12);
        
                $q = $db->query("SELECT * FROM users_cn_payment_ids WHERE asset_id = ".$asset->get_id()." AND payment_id = ".quote_escape($payment['payment_id'])." LIMIT 1");
        
                $user_payment_id = $q->fetch_assoc();
                
                // If no payment id was specified/found, default to 0 (for admin to manually approve later)
                $pid = ($user_payment_id ? $user_payment_id['pid'] :  0);
        
                // Make sure we don't already have the transaction in db
                $q = $db->query("SELECT * FROM users_cn_transactions WHERE tx_hash = ".quote_escape($payment['tx_hash'])." LIMIT 1");
                
                if( ! $q->fetch_row() )
                {  
                    $sql = insert_query('users_cn_transactions',array(
                            'pid'           => $pid,
                            'amount'        => $amount,
                            'block_height'  => $payment['block_height'],
                            'tx_hash'       => $payment['tx_hash'],
                            'datetime'      => array('UTC_TIMESTAMP()'),
                            'status'        => 0
                    ));
                    
                    if( ! $db->query($sql))
                    {
                        $db->query('ROLLBACK');
                        break;       
                    }
                }
            }
            
            $db->query('COMMIT'); 
        }
    
        // Only update block height if we received a valid result from wallet (if $res == null the wallet was/is down, and would result in skipping blocks)
        if(is_array($payments))
        {   
            $db->query("UPDATE info SET value = ".$block_height." WHERE name = '".$asset->get_id()."_block_height'");    
        }
       
        // display block height (basically just cache so that each user doesn't require a call to daemon)
        $db->query("UPDATE info SET value = ".$block_height." WHERE name = '".$asset->get_id()."_display_block_height'");

        // --------------------------------------------------------------------
        //
        //                   Process pending incoming payments
        // 
        // --------------------------------------------------------------------
         
        $db->query('START TRANSACTION'); 
    
        $result = $db->query('SELECT * FROM users_cn_transactions WHERE status = 0');
        
        while($row = $result->fetch_array(MYSQLI_ASSOC))
        {
            // If transaction has reached the required confirmations, approve it
            if( ($block_height - $config['asset'][$asset->get_id()]['min_conf']) >= $row['block_height'])
            {
                // First mark txn as approved
                $db->query('UPDATE users_cn_transactions SET status = 1 WHERE id = '.$row['id'].' AND status = 0');
                
                // Find user based on payment id
                $pids = $db->query("SELECT * FROM users_cn_payment_ids WHERE asset_id = ".$asset->get_id()." AND pid = ".quote_escape($row['pid'])." LIMIT 1");
                $payment_ids = $pids->fetch_assoc(); 
                            
                // Credit user his funds (if user has no balance for this asset, we create it, otherwise update)
                $db->query("INSERT INTO 
                                users_assets (`user_id`, `asset_id`, `balance`) 
                            VALUES 
                                (".$payment_ids['user_id'].",".$asset->get_id().",".$row['amount'].")
                            ON DUPLICATE KEY UPDATE 
                                `balance` = balance + ".$row['amount'].";");
            }
        }
        $db->query('COMMIT');
        
        // --------------------------------------------------------------------
        //
        //                      Process pending withdraws 
        // 
        // --------------------------------------------------------------------
         
        $result = $db->query('SELECT * FROM withdraws_pending WHERE status = 1 ORDER BY id ASC LIMIT 1000');
        
        $payments = array();
        
        while($row = $result->fetch_array(MYSQLI_ASSOC))
        {
            // If there is not enough balance, we'll try again later ("break" instead of "continue" so that payment are processed as a queue, most fair)
            if(bc::is($row['amount'],'>',$wallet->get_unlocked_balance())) break;
            
            // In rare cases that bulk_transfer sends payment, but script does a rollback, the transfer will not be repeated by setting status to error first (requiring manual approval): 
            $db->query("UPDATE withdraws_pending SET status = -1, error = 'PAYMENT IN PROCESS' WHERE id = ".$row['id']);
            
            $tx_id = $wallet->transfer($row['address'],$row['amount'],$row['payment_id'],$row['mixin'],$row['fee'],0);
            
            $db->query('START TRANSACTION');
            
            if( ! $tx_id )
            {
                $errors = $wallet->get_errors();
                $error_message = (isset($errors[0]) and isset($errors[0]['message'])) ? $errors[0]['message'] : 'Unknown error'; 
                
                $db->query("UPDATE withdraws_pending SET error = ".quote_escape($error_message)." WHERE id = ".$row['id']);
            }
            else
            {
                $sql = insert_query('withdraws_complete',array(
                        'user_id'       => $row['user_id'],
                        'address'       => $row['address'],
                        'amount'        => $row['amount'],
                        'fee'           => $row['fee'],
                        'date_paid'     => array('UTC_TIMESTAMP()'),
                        'asset_id'      => $row['asset_id'],
                        'mixin'         => $row['mixin'],
                        'txn'           => $tx_id,
                ));
                
                $db->query($sql);
                $db->query("DELETE FROM withdraws_pending WHERE id = ".$row['id']);
            }
            $db->query('COMMIT');
        }
    }
}
