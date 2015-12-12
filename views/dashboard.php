<?php include('qr/qrlib.php');
 if(!defined('APP')) exit; ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>XMR</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container">
            <div class="page-header">
                <h1>XMR integration <small>by TheKoziTwo</small></h1>
            </div>
            <?php if(User::is_logged_in()) { ?>
            
            <?=flash_msg()?>
            
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">Welcome <strong><?=$user->username()?></strong></h3>
                </div>
                <div class="panel-body">This is a basic integration of monero where all essential parts have been coded. Feel free to use the code for anything you please.<?=($user->is_admin()?'<br/><br/>You have admin permissions, click <a href="admin.php">here</a> to go to admin panel':'')?></div>
            </div>

            <legend>Account Balance</legend>
            
            <p>Current balance: <strong><?php echo $balance;?></strong> XMR</p>
            
            <br />
            <form action="" method="post">
                <legend>Deposit</legend>
                <p>XMR: <strong><?=$asset_config['address'];?></strong></p>
                <p>Payment ID: <strong><?=$payment_id;?></strong></p>
                <button type="submit" class="btn btn-success" name="new_payment_id">Generate new payment id</button>

            </form>
<img src="qrPR.php?id=<?php echo $payment_id; ?>" alt="QR code Payment Request ID" />
<img src="qrXMR.php?id=<?php echo $asset_config['address']; ?>" alt="QR code XMR address" />
     
            <br /><br />
            
            <form action="" method="post">
                
                <legend>Withdraw</legend>
                
                <div class="form-group <?=$error->is('xmr_address','has-error has-feedback')?>">
                    <label class="control-label" for="xmr_address"><?=( ! $error->is('xmr_address') ? 'Receiver Address' : $error->get('xmr_address'))?></label>
                    <input type="text" name="xmr_address" class="form-control" id="xmr_address" aria-describedby="inputError2Status" placeholder="Enter XMR address" value="<?=(isset($_POST['xmr_address']) ? htmlspecialchars($_POST['xmr_address']) : '')?>">
                    <span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>                 
                </div>
                
                <div class="form-group <?=$error->is('xmr_payment_id','has-error has-feedback')?>">
                    <label class="control-label" for="xmr_payment_id"><?=( ! $error->is('xmr_payment_id') ? 'Payment ID' : $error->get('xmr_payment_id'))?></label>
                    <input type="text" name="xmr_payment_id" class="form-control" id="xmr_payment_id" placeholder="e.g. 59af9132941ec6e9f6ba3c4867e1cd92f2bd5fbce4325fc7b19bcdb55d640de5" value="<?=(isset($_POST['xmr_payment_id']) ? htmlspecialchars($_POST['xmr_payment_id']) : '')?>">
                    <span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>
                </div>
                
                 <div class="form-group form-inline <?=$error->is('mixin','has-error has-feedback')?>">
                    <div class="input-group">
                      <div class="input-group-addon">Mixin</div>
                      <input type="text" class="form-control" id="exampleInputAmount" placeholder="Amount" name="xmr_mixin" value="<?=(isset($_POST['mixin']) ? htmlspecialchars($_POST['mixin']) : $asset_config['default_mixin'])?>">
                      <div class="input-group-addon">Valid range: <strong><?=$asset_config['min_mixin']?>-<?=$asset_config['max_mixin']?></strong></div>
                    </div>
                  </div>

                 <div class="form-group form-inline <?=$error->is('xmr_amount','has-error has-feedback',true)?>">
                    <div class="input-group">
                      <div class="input-group-addon">XMR</div>
                      <input type="text" class="form-control" id="xmr_amount"  name="xmr_amount" placeholder="Amount" onkeyup="calculateTotal();" value="<?=(isset($_POST['xmr_amount']) ? htmlspecialchars($_POST['xmr_amount']) : '')?>">
                      <div class="input-group-addon">Minimum: <strong><?=$asset_config['min_withdraw']?> XMR</strong></div>
                    </div>
                  </div>
                  
                 <div class="form-group form-inline">
                    <label class="control-label" for="fee">Withdraw fee: <?=$asset_config['withdraw_fee']?></label>
                  </div>
                  
                 <div class="form-group form-inline">
                    
                    <div class="input-group">
                      <div class="input-group-addon"><strong>You will receive:</strong></div>
                      <input type="text" class="form-control" id="xmr_total" placeholder="" value="<?=(isset($_POST['total']) ? htmlspecialchars($_POST['total']) : '')?>" disabled="disabled">
                      <div class="input-group-addon">XMR</div>
                    </div>
                  </div>
                  <input type="hidden" name="csrf_token" value="<?=csrf_token()?>" />
                <button type="submit" name="withdraw_xmr" class="btn btn-primary">Withdraw XMR</button>
                
            </form>
            
            <br />

            <h3>Deposits</h3>

            <table class="table">
                <tr>
                    <th>ID</th>
                    <th>Amount</th>
                    <th>Conf.</th>
                    <th>Tx</th>
                    <th>Date</th>
                </tr>
                <?php if( ! $deposits->num_rows): ?>
                    <tr class="warning"><td colspan="5">You have no recorded deposits.</td></tr>
                <?php endif;?>
                <?php while($row = $deposits->fetch_array(MYSQLI_ASSOC)): ?>
                    <tr class="<?php echo ($row['status'] ? 'success' : 'warning');?>">
                        <td><?=$row['id']?></td>
                        <td><?=$row['amount']?></td>
                        <td><?=($row['status'] == 0 ? ((info($asset->get_id().'_display_block_height')-$row['block_height']).'/'.$asset_config['min_conf']) : '<strong>OK</strong>')?></td>
                        <td><?=$row['tx_hash']?></td>
                        <td><?=$row['datetime']?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
            
            <h3>Withdraws</h3>

            <table class="table">

                <?php if( ! $withdraws_pending->num_rows AND ! $withdraws_complete->num_rows) { ?>
                    <tr class="warning"><td colspan="5">You have no recorded withdraws.</td></tr>
                <?php } ?>
                
                <?php if($withdraws_pending->num_rows) { ?>
               
                    <tr>
                        <th>Amount</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                    
                    <?php while($row = $withdraws_pending->fetch_array(MYSQLI_ASSOC)) { ?>
                        <tr class="<?php
                            switch($row['status'])
                            {
                                case 0:
                                    echo 'primary';
                                    break;
                                case 1:
                                    echo 'warning';
                                    break;                            
                                case -1:
                                    echo 'danger';
                                    break;
                            }
                        ?>">
                            <td><?=$row['amount']?></td>
                            <td><input type="text" readonly="readonly" value="<?=$row['address']?>"/></td>
                            <td><?php 
                            switch($row['status'])
                            {
                                case 0:
                                    echo 'Pending Approval';
                                    break;
                                case 1:
                                    echo 'Processing...';
                                    break;
                                case -1:
                                    echo 'ERROR';
                                    break;
                            }
                            ?></td>
                            <td><?=$row['date_requested']?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
                <?php if($withdraws_complete->num_rows) { ?>
           
                    <tr>
                        <th>Amount</th>
                        <th>Address</th>
                        <th>TX ID</th>
                        <th>Date</th>
                    </tr>
                    <?php while($row = $withdraws_complete->fetch_array(MYSQLI_ASSOC)) { ?>
                        <tr class="success">
                            <td><?=$row['amount']?></td>
                            <td><input type="text" readonly="readonly" value="<?=$row['address']?>"/></td>
                            <td><?=$row['txn']?></td>
                            <td><?=$row['date_paid']?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </table>
            
            <form action="" method="post">
                <input type="hidden" name="csrf_token" value="<?=csrf_token()?>" />
                <button type="submit" name="logout" class="btn btn-danger">Logout</button>
            </form>
            
            <?php } else { /* Not logged in */ ?>
                
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">Login</h3>
                </div>
                <div class="panel-body">
                    <form action="" method="post" class="form">
                                                
                        <div class="form-group <?=$error->is('username','has-error has-feedback')?>">
                            <label class="control-label" for="username"><?=( ! $error->is('username') ? 'Username' : $error->get('username'))?></label>
                            <input type="text" name="username" class="form-control" id="username" aria-describedby="inputError2Status" placeholder="Enter your username" value="<?=(isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '')?>">
                            <span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>                 
                        </div>
                                                
                        <div class="form-group <?=$error->is('password','has-error has-feedback')?>">
                            <label class="control-label" for="username"><?=( ! $error->is('password') ? 'Password' : $error->get('password'))?></label>
                            <input type="password" name="password" class="form-control" id="password" aria-describedby="inputError2Status" placeholder="Enter your password" value="<?=(isset($_POST['password']) ? htmlspecialchars($_POST['password']) : '')?>">
                            <span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>                 
                        </div>
                        
                        <button type="submit" name="login" class="btn btn-success">Login</button>
                        
                    </form>
                </div>
            </div>
            
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Register</h3>
                </div>
                <div class="panel-body">
                
                    <form action="" method="post" class="form">
                                                
                        <div class="form-group <?=$error->is('register_username','has-error has-feedback')?>">
                            <label class="control-label" for="register_username"><?=( ! $error->is('register_username') ? 'Username' : $error->get('register_username'))?></label>
                            <input type="text" name="register_username" class="form-control" id="register_username" aria-describedby="inputError2Status" placeholder="Enter a username" value="<?=(isset($_POST['register_username']) ? htmlspecialchars($_POST['register_username']) : '')?>">
                            <span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>                 
                        </div>
                                                
                        <div class="form-group <?=$error->is('register_password','has-error has-feedback')?>">
                            <label class="control-label" for="register_password"><?=( ! $error->is('register_password') ? 'Password' : $error->get('register_password'))?></label>
                            <input type="password" name="register_password" class="form-control" id="register_password" aria-describedby="inputError2Status" placeholder="Enter a password" value="<?=(isset($_POST['register_password']) ? htmlspecialchars($_POST['register_password']) : '')?>">
                            <span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>                 
                        </div>
                        
                        <button type="submit" name="register" class="btn btn-primary">Register</button>
                        
                    </form>
                
                </div>
            </div>
                
            <?php } ?>
        </div>
        <script type="text/javascript">
        function calculateTotal() {
            var amount = parseFloat(document.getElementById('xmr_amount').value);
            var total = Math.round((amount - <?=$asset_config['withdraw_fee']?>)*<?=pow(10,$asset->decimals)?>)/<?=pow(10,$asset->decimals)?>;
            
            if(isNaN(amount)) total = 0;
            
            document.getElementById('xmr_total').value = total;
        }
        calculateTotal();
        </script>
    </body>
</html>