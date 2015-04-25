<?php require_once('init.php');

if(User::is_logged_in())
{
    $user = new User($_SESSION['user_id']); 
    $asset = Asset::init(XMR,$config['asset'][XMR]['properties']);
    $asset_config = $config['asset'][XMR];
    $asset_id = $asset->get_id();
    $user_id = $user->id();
    $payment_id  = null;
    $balance = null;

    $balance = $asset->get_balance($user);
    
    $payment_id = $asset->get_payment_id($user);
    
    // If payment id was not found, create one
    if( ! $payment_id OR isset($_POST['new_payment_id']))
    {
        $asset->create_payment_id($user);
        refresh();
    }
    
    if(isset($_POST['withdraw_xmr']))
    {
        $amount = trim($_POST['xmr_amount']);
        
        // Prepare POST data
        $post = array(
            'address'           => trim($_POST['xmr_address']),
            'payment_id'        => trim($_POST['xmr_payment_id']),
            'amount'            => $amount,
            'mixin'             => filter_var($_POST['xmr_mixin'], FILTER_VALIDATE_INT, array(
                                    'options' => array(
                                        'default' => $asset_config['default_mixin'],
                                        'min_range' => $asset_config['min_mixin'],
                                        'max_range' => $asset_config['max_mixin']
                                    ),
                                )),
            'receivable_amount'  => bc::op($amount,'-',$asset_config['withdraw_fee']),
            'asset_id'           => $asset->id
            );
        
        if( ! csrf_check($_POST['csrf_token']))
        {
            $error->set('xmr_address','Invalid CSRF, session expired. Please refresh.');
        }
        
        if( ! $asset->valid_address($post['address']))
        {
            $error->set('xmr_address','Please enter a valid XMR Address');
        }
        
        if( ! $asset->valid_payment_id($post['payment_id']))
        {
            $error->set('xmr_payment_id','Please enter a valid Payment ID (64 characters, alpha-numeric string) or leave the field empty to send without payment id');
        }
        
        if( ! $asset->valid_amount($post['amount']))
        {
            $error->set('xmr_amount','Enter a valid amount');
        }
        
        if( ! $asset->valid_withdraw($post['amount'],$asset_config['withdraw_fee']))
        {
            $error->set('xmr_amount','Enter a valid amount');
        }
       
        if( ! $asset->available_balance($user,$post['amount']))
        {
            $error->set('xmr_amount','You do not have sufficient balance to withdraw this amount');
        }
        
        if( ! $error->is_errors())
        {
            $db->query('START TRANSACTION');
            
            // Detuct balance
            if($db->query('UPDATE users_assets SET balance = balance - '.$post['amount'].' WHERE user_id = '.$user_id.' AND asset_id = '.$asset_id.' AND balance >= '.$post['amount']))
            {
                // Add withdraw    
                $sql = insert_query('withdraws_pending',array(
                        'user_id'           => $user_id,
                        'address'           => $post['address'],
                        'payment_id'        => $post['payment_id'],
                        'amount'            => $post['receivable_amount'],
                        'fee'               => $asset_config['withdraw_fee'],
                        'date_requested'    => array('UTC_TIMESTAMP()'),
                        'asset_id'          => $post['asset_id'],
                        'mixin'             => $post['mixin'],
                        'status'            => 1
                ));
                
                if($db->query($sql))
                {
                    $db->query('COMMIT');
                    $_SESSION['flash_message'] = '<p class="flashmsg bg-success">Your withdraw has been successfully submitted!</p>';
                    refresh();
                }
                else
                {
                    $db->query('ROLLBACK');    
                }
            }
            else
            {
                $db->query('ROLLBACK');
                // Could not detuct funds, try again etc
            }
        }
    
    }
    
    $deposits = $db->query("
                    SELECT      uct.id, 
                                uct.amount, 
                                uct.block_height, 
                                uct.tx_hash, 
                                uct.datetime, 
                                uct.status 
                    FROM        users_cn_payment_ids AS ucpi 
                    LEFT JOIN   users_cn_transactions AS uct 
                    ON          ucpi.pid = uct.pid 
                    WHERE       ucpi.user_id = ".$user->id()." 
                    AND         ucpi.asset_id = ".$asset->get_id()." 
                    AND         uct.id IS NOT NULL
    ");
    
    $withdraws_pending = $db->query('
                            SELECT      * 
                            FROM        withdraws_pending
                            WHERE       user_id = '.$user->id().' 
                            ORDER BY    id 
                            DESC'
    );
    
    $withdraws_complete = $db->query('
                            SELECT      * 
                            FROM        withdraws_complete 
                            WHERE       user_id = '.$user->id().' 
                            ORDER BY    id 
                            DESC'
    );
}
else // if not logged in
{
    if($_POST)
    {
        if(isset($_POST['login']))
        {
            if(User::login($_POST['username'],$_POST['password']))
            {
                refresh();
            }
            else
            {
                $error->set('username','Invalid username or password.');
                $error->set('password','Password');
            }
        }
        
        if(isset($_POST['register']))
        {
            $username = trim($_POST['register_username']);
            $password = $_POST['register_password'];
            
            
            
            if( ! User::valid_username($username))
            {
                $error->set('register_username','Invalid username, please enter an alphanumeric '.$config['user']['username']['min_length'].'-'.$config['user']['username']['max_length'].' in length');
            }
            
            // Do not allow multiple users with the same username
            if(User::username_exists($username))
            {
                $error->set('register_username','Username has been taken, please try something else...');
            }
            
            
            if ( ! User::valid_password($password))
            {
                $error->set('register_password','Enter a password of minimum '.$config['user']['password']['min_length'].' characters');
            }
            
            if( ! $error->is_errors())
            {
                if(User::register($username,$password))
                {
                    User::login($username,$password);
                    refresh();
                }
            
            }
        }
        
    }   
}

include 'views/dashboard.php';