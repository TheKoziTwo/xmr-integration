<?php if(!defined('APP')) exit;
/**
 * Asset XMR
 * 
 * Implementation of monero specific methods
 * 
 * Any method here will override those in Asset_Cryptonote
 * 
 * @author     TheKoziTwo <thekozitwo@gmail.com>
 * @copyright  2015
 * @license    Public Domain
 */  
class Asset_Cryptonote_XMR extends Asset_Cryptonote {
    
    public function __construct($properties)
    {
        parent::__construct($properties); 
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Validates amount to ensure it follows the rules of monero
     * 
     * @param   string|float        amount
     * @return  bool    
     */ 
    public function valid_amount($amount) 
    {
        if( ! is_numeric($amount)) return FALSE;
        
        // XMR is limited to 12 decimals
        if(bc::count_decimals($amount) > 12) return FALSE;
        
        #// The maximum payment is depended on your system, 32-bit is 10 digits and 64-bit is 19 digits
        #$max_digits = strlen(PHP_INT_MAX);
        
        // PHP 64-bit does not support more than 9223372036854775807 (19-digits)
        if(bc::is($amount,'>=','9223372.036854775807'))
        {
            return FALSE;
        }
        
        return TRUE;
    }
    
}