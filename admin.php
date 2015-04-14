<?php require_once('init.php');

// Redirect user to login page if not logged in
if( ! User::is_logged_in())
{
    redirect();  
}

$user = new User($_SESSION['user_id']); 

// Require admin access:
if( ! $user->is_admin())
{
    redirect();
}    

$assets_ids = Asset::get_assets();

$assets = array();
foreach($assets_ids as $asset_id)
{
    $asset = Asset::init($asset_id,$config['asset'][$asset_id]['properties']);
    
    $short_name = $config['asset'][$asset_id]['properties']['short_name'];
    
    $class = 'CryptoNote_Wallet_'.$asset->short_name;
    if( ! class_exists($class)) $class = 'CryptoNote_Wallet';
    $wallet = new $class($config['asset'][$asset_id]['wallet_host'],$config['asset'][$asset_id]['wallet_port']);
    
    $class = 'CryptoNote_Daemon_'.$asset->short_name;
    if( ! class_exists($class)) $class = 'CryptoNote_Daemon';
    $daemon = new $class($config['asset'][$asset_id]['daemon_host'],$config['asset'][$asset_id]['daemon_port']);
    
    $assets[] = array(
        'id' => $asset_id,
        'config' => $config['asset'][$asset_id],
        'class' => $asset,
        'wallet' => $wallet,
        'daemon' => $daemon,
        'wallet_ready' => $wallet->is_responding(),
    );
}

include 'views/admin/dashboard.php';
