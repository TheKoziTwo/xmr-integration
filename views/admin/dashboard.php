<?php if(!defined('APP')) exit; ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Admin Panel</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container">
            <div class="page-header">
                <h1>Admin Panel</h1>
            </div>
            <?php if(User::is_logged_in()) { ?>
                <?php foreach($assets as $asset) { ?>
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?=$asset['class']->name?> (<?=$asset['class']->short_name?>) </strong></h3>
                    </div>
                    <div class="panel-body">
                    <table class="table table-striped">    
                            <tr>
                                <td>Wallet Status</td>
                                <td>
                                <?php if($asset['wallet_ready']) { ?>
                                    <strong class="text-success">OK</strong>
                                <?php } else { ?>
                                    <strong class="text-danger">NOT RESPONDING</strong>
                                <?php } ?>
                                - (HOST: <strong><?=$asset['config']['wallet_host'];?></strong> 
                                   PORT: <strong><?=$asset['config']['wallet_port'];?></strong>)</td>
                            </tr>
                            <tr>
                                <td>Daemon Status</td><td>
                                <?php if($asset['daemon']->is_ready()) { ?>
                                    <strong class="text-success">OK</strong>
                                <?php } elseif($asset['daemon']->is_busy()) { ?>                        
                                    <strong class="text-warning">BUSY</strong>
                                <?php } else { ?>
                                    <strong class="text-danger">NOT RESPONDING</strong>
                                <?php } ?>
                                - (HOST: <strong><?=$asset['config']['daemon_host'];?></strong> 
                                   PORT: <strong><?=$asset['config']['daemon_port'];?></strong>)</td>
                            </tr>
                            <?php if($asset['daemon']->is_ready()) { ?>
                                <tr><td>Block Height</td><td><?=$asset['daemon']->get_block_count()?></td></tr>
                            <?php } ?>
                            <tr><td>Address</td><td><?=$asset['config']['address'];?></td></tr>
                            <tr><td>Min. Confirmations</td><td><?=$asset['config']['min_conf'];?></td></tr>
                            <tr><td>Mixin</td><td><?=$asset['config']['min_mixin'];?>-<?=$asset['config']['max_mixin'];?> (Default: <?=$asset['config']['default_mixin'];?>)</td></tr>
                            <tr><td>Min. Withdraw</td><td><?=$asset['config']['min_withdraw'];?> <?=$asset['class']->short_name?></td></tr>
                            <tr><td>Withdraw Fee</td><td><?=$asset['config']['withdraw_fee'];?> <?=$asset['class']->short_name?></td></tr>
                    </table>
                    <strong>Wallet/daemon status:</strong><br />
                    <strong class="text-success">OK</strong> = Everything is functioning correctly.<br />
                    <strong class="text-warning">BUSY</strong> = the blockchain is being saved, just wait for a while and it should return to OK.<br />
                    <strong class="text-danger">NOT RESPONDING</strong> = the daemon or wallet is shut off or not responding on the IP/PORT and will require investigation.
                  
                  
                    <?php if($asset['wallet_ready']) { ?>
                        <legend>Balance</legend>
                        <table class="table table-striped">
                            <tr><td>Locked</td><td><?=$asset['wallet']->get_locked_balance()?> <?=$asset['class']->short_name?></td></tr>
                            <tr><td>Unlocked</td><td><?=$asset['wallet']->get_unlocked_balance()?> <?=$asset['class']->short_name?></td></tr>
                            <tr><td>Total Balance</td><td><strong><?=$asset['wallet']->get_balance()?> <?=$asset['class']->short_name?></strong></td></tr>
                        </table>
                    <?php } ?>
                    
                    </div>
                </div>
                <?php } ?>
            <?php } ?>
        </div>
    </body>
</html>