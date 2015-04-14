<?php if(!defined('APP')) exit;
/**
 * Asset CryptoNote
 * 
 * Main class for all cryptonote currencies. Extend this class to override functions (folder: classes/asset/cryptonote/SHORT_NAME/class.SHORT_NAME.php)
 * Replace "SHORT_NAME" with the one specified in config.
 * 
 * @author     TheKoziTwo <thekozitwo@gmail.com>
 * @copyright  2015
 * @license    Public Domain
 */  
class Asset_Cryptonote extends Asset {
    
    public function __construct($properties)
    {
        parent::__construct($properties); 
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Get latest generated payment id for user
     * 
     * @param   object      User
     * @return  string      payment id
     */ 
    public function get_payment_id($user)
    {
        $res = db()->query('
                SELECT      payment_id 
                FROM        users_cn_payment_ids 
                WHERE       user_id = '.quote_escape($user->id()).' 
                AND         asset_id = '.quote_escape($this->id).'
                ORDER BY    pid 
                DESC 
                LIMIT       1'
        );

        if($res)
        {
            $row = $res->fetch_assoc();
            return $row['payment_id'];
        }
        
        return FALSE;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Create & Assign a new payment ID to user 
     * 
     * @param   object      User
     * @return  string      payment id    
     */ 
    public function create_payment_id($user)
    {
        $res = false;
        
        // Keep generating payment id until successfully inserted.
        while( ! $res )
        {
            $payment_id = random_str(64);
            
            $sql = insert_query('users_cn_payment_ids',array(
                    'asset_id'      => $this->id,
                    'payment_id'    => $payment_id,
                    'user_id'       => $user->id(),
                    'date_created'  => array('UTC_TIMESTAMP()')
            ));
            
            $res = db()->query($sql);
        }
        
        return $payment_id;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Validates a cryptonote address
     * 
     * @param   string      cryptonote address
     * @return  bool        true if valid
     */ 
    public function valid_address($address)
    {
        global $config;
        
        // 95 characters
        if(strlen($address) !== 95) return FALSE;
        
        // Start with 4
        if($address[0] != '4') return FALSE;
        
        // Second character can only be a number (0-9) or letters A or B
        if( ! in_array($address[1],array_merge(array_map(function($v){
            return (string) $v;
        },range(0,9)),array('A','B')))) return FALSE;
        
        // The string can only be alphanumeric characters
        if( ! ctype_alnum($address)) return FALSE;    
        
        // Can not send to self
        if($address == $config['asset'][$this->id]['address']) return FALSE;
        
        return TRUE;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Validates a payment id
     * 
     * @param   string      payment id
     * @return  bool        true if valid
     */ 
    public function valid_payment_id($payment_id)
    {   
        if(empty($payment_id)) return TRUE;
        
        // 64 characters
        if(strlen($payment_id) !== 64) return FALSE;
    
        // The string can only be alphanumeric characters
        if( ! ctype_alnum($payment_id)) return FALSE;
        
        return TRUE;    
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Validates amount to ensure it follows the rules of cryptonote
     * 
     * @param   string|float        amount
     * @return  bool    
     */ 
    public function valid_amount($amount) 
    {
        if( ! is_numeric($amount)) return FALSE;

        if(bc::count_decimals($amount) > 8) return FALSE;
        
        return TRUE;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Validates withdraw (minimum withdraw)
     * 
     * @param   string|float        amount
     * @param   string|float        fee
     * @return  bool
     */ 
    public function valid_withdraw($amount,$fee)
    {
        global $config;
        
        $amount_after_fee = bc::op($amount,'-',$fee);
        
        // Check if amount after fee is negative
        if(bc::is($amount_after_fee,'=<','0')) return FALSE;
        
        // Enforce minimum withdraw
        return (bool) (bccomp($amount,$config['asset'][$this->id]['min_withdraw']) !== -1);
    }

}