<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors',1);

// Session for user login
session_start();

/* 
 * BCMath scale is the number of decimals used in BCMath. It should be the 
 * asset you have with the most decimals times 2. E.g if 4 decimals, scale 
 * should be 8. Reason is to allow for multiplications such as 0.0004 * 0.0004 
 * which gives us: 0.00000016 (8 decimals)
 */
bcscale(24);

// Constant to check for on included pages to ensure they are not run directly 
// (requiring init.php to be run)
define('APP',true);

/* 
 * To run cron from command line (php cron.php) LOOP_FOREVER must be TRUE. 
 * It will then run until you cancel it. This is optimal and recommended.
 * If you are unable to run php from command line, you can run as a cron by
 * including parameter ?cron=1 when opening cron.php as a cron job and it
 * will run only once (each time your cron job runs) e.g
 * wget "http://YOURDOMAIN.com/cron.php?cron=1"
 * 
 * @todo: if cron runs too long and you end up running another cron the 
 * script has not taken this into account currently. So use at your own risk.
 */
if(isset($_GET['cron']) AND $_GET['cron'] == 1)
{  
    define('LOOP_FOREVER',false);     
}
else
{
    define('LOOP_FOREVER',true);
}

// There should be a delay between each loop (in seconds)
define('SLEEP_TIME_SEC',15); 

// Globals
$config = array();

// Includes
require_once('config.php');
require_once('db.php');
require_once('classes/jsonrpc2client.php');
require_once('functions.php');
require_once('classes/class.bc.php');
require_once('classes/class.error.php');
require_once('classes/class.user.php');
require_once('classes/class.log.php');
require_once('classes/class.asset.php');
require_once('classes/asset/cryptonote/class.cryptonote.php');
require_once('classes/asset/cryptonote/class.wallet.php');
require_once('classes/asset/cryptonote/class.daemon.php');

// Include asset classes (if any)
foreach($config['asset'] as $asset_id => $conf)
{
    if( ! isset($conf['properties'])) continue;
    
    $short_name = $conf['properties']['short_name'];
    
    $path = 'classes/asset/cryptonote/'.strtoupper($short_name).'/class.'.strtolower($short_name).'.php';
    if(file_exists($path))
    {
        include $path;
    }
    $path = 'classes/asset/cryptonote/'.strtoupper($short_name).'/class.'.strtolower($short_name).'.daemon.php';
    if(file_exists($path))
    {
        include $path;
    }
    $path = 'classes/asset/cryptonote/'.strtoupper($short_name).'/class.'.strtolower($short_name).'.wallet.php';
    if(file_exists($path))
    {
        include $path;
    }
}

$error = new Error();

// Logout user
if(isset($_POST['logout']) AND csrf_check($_POST['csrf_token'])) 
{
        User::logout();
        refresh();
}