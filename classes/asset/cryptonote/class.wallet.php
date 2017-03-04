<?php if(!defined('APP')) exit;
/**
 * CryptoNote Wallet
 * 
 * This class is a wrapper for commands sent to the wallet and the responses it sends
 *
 * @author     TheKoziTwo <thekozitwo@gmail.com>
 * @copyright  2015
 * @license    Public Domain
 */  
class CryptoNote_Wallet {
    
	/**
	 * @var  jsonRPCClient     instance of the client
	 */
    private $wallet;
    
    /**
	 * @var  array     errors from the rpc (code,message,params)
	 */
    private $errors = array();
    
    /**
	 * @var  string     multiplier, the amount of base units in 1 (XMR, BBR etc)
	 */
    private $multiplier = '1000000000000';
    
    /**
     * Initialize an instance of daemon
     * 
     * @param   string      ip or hostname (default: 127.0.0.1)
     * @param   int         port (default: 18081)
     */ 
    public function __construct($host,$port)
    {
        $this->wallet = new jsonRPCClientWallet('http://'.$host.':'.$port.'/json_rpc');
    }   
    
    // ------------------------------------------------------------------------
    
    /**
     * Retrive errors
     * 
     * The array can contain multiple errors, each error has code, message & 
     * params as variable names
     * 
     * @return  array       errors
     */ 
    public function get_errors()
    {
        return $this->errors;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Resets the errors array
     * 
     * @return  void
     */ 
    private function _reset_errors()
    {
        $this->errors = array();
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Check if wallet is responding/reachable
     * 
     * @return  bool    true if available
     */ 
    public function is_responding()
    {
        return (bool) ($this->_get_balances() !== null);
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Get total balance (not all funds may be available to spend yet)
     * 
     * @return  decimal     total balance in XMR
     */ 
    public function get_balance()
    {
        $result = $this->_get_balances();
        return bc::div($result['balance'],$this->multiplier);
    }
    
    // ------------------------------------------------------------------------   
    
    /**
     * Get unlocked balance (current amount available to spend)
     * 
     * @return  decimal     unlocked balance in XMR
     */ 
    public function get_unlocked_balance()
    {
        $result = $this->_get_balances();
        return bc::div($result['unlocked_balance'],$this->multiplier);
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Get locked balance (the current amount of unavailable funds)
     * 
     * @return  decimal     locked balance in XMR 
     */ 
    public function get_locked_balance()
    {
        $result = $this->_get_balances();
        return bc::div(bc::op($result['balance'],'-',$result['unlocked_balance']),$this->multiplier);
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Transfer to multiple people at once (but with same payment id)
     * 
     * @param   array           an array in following format: 
     *                          array(
     *                                  array(
     *                                      'amount'    => 0.1,
     *                                      'address'   => 'ADDRESS HERE'
     *                                  ),
     *                                  array(
     *                                      'amount'    => '2.1',
     *                                      'address'   => 'ADDRESS HERE'
     *                          )
     * @param   string          payment id, 64-character long string (optional)
     * @param   decimal         fee amount in XMR
     * @param   int             mixin count
     * @param   int             unlock time
     * @return  string|bool     if transfer was successful (all of them) it will 
     *                          return transaction id, if any error occured no 
     *                          transfers will be made and the function will 
     *                          return false. Use $this->get_errors() to get error
     */ 
    public function bulk_transfer($destinations = array(),$payment_id = '',$mixin = 3,$fee = 0.01,$unlock_time = 0)
    {
        // Convert decimals to integer, but without casting to int. Due to limits of PHP 32/64-bit we must store as string.
        foreach($destinations as &$destination)
        {
            $destination['amount'] = bc::strip_trailing_zeros(bc::mul($destination['amount'],$this->multiplier));
        }
        
        $params = array(
                    'destinations' => $destinations,
                    'payment_id'   => $payment_id,
                    'fee'          => bc::strip_trailing_zeros(bc::mul($fee,$this->multiplier)),
                    'mixin'        => $mixin,
                    'unlock_time'  => $unlock_time
        );
        
        $result = $this->_execute('transfer',$params,array('destinations'=>array('amount'),'fee'));
         
        if(isset($result['tx_hash']))
        {
            return trim($result['tx_hash'],'<>');
        }
        
        return false;
        
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Transfer to one address
     * 
     * @param   string          address
     * @param   decimal         amount in XMR
     * @param   string          payment id, 64-character long string (optional)
     * @param   decimal         fee amount in XMR
     * @param   int             mixin count
     * @param   int             unlock time
     * @return  string|bool     if transfer was successful it will return transaction id, 
     *                          if any error occured it will return false and you can 
     *                          lookup the error with $this->get_errors()
     */ 
    public function transfer($address,$amount,$payment_id,$mixin,$fee = 0.01,$unlock_time = 0)
    {
        return $this->bulk_transfer(array(array('amount' => $amount,'address' => $address)),$payment_id,$mixin,$fee,$unlock_time);
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Get payments by block height and payment id
     * 
     * @param   int         block height (will search this height and higher)
     * @param   array       payment ids as array, will filter by payment ids specified, leave empty to ignore payment ids
     * @return  array       returns array with payments or null if nothing found
     */ 
    public function get_bulk_payments($min_block_height,$payment_ids = array())
    { 
        $params = array('min_block_height'=> $min_block_height);
        if( ! empty($payment_ids))
        {
            $params['payment_ids'] = $payment_ids;
        }
        
        $res = $this->wallet->get_bulk_payments(json_encode($params));
  
        return (isset($res['payments']) ? $res['payments'] : null);   
    }
    
    /**
     * Get balances
     * 
     * @return  array       balance and unlocked balance as integers
     */ 
    private function _get_balances()
    {
        return $this->wallet->getbalance();
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Execute / send rpc command to wallet
     * 
     * @param   string          wallet command
     * @param   array           params as array
     * @return  array           response from wallet
     */ 
    private function _execute($command,$params = null)
    {
        $this->_reset_errors();
 
        // Numeric check ensures that large numbers stored as string is converted to int in the json output
        $json = json_encode($params,JSON_NUMERIC_CHECK);
              
        $result = $this->wallet->$command($json);
        
        if(isset($result['error']))
        {
            $this->errors[] = array(
                'code'      => $result['error']['code'],
                'message'   => $result['error']['message'],
                'params'    => $params,
            );    
        }
        
        return $result;
    }
}
