<?php if(!defined('APP')) exit;
/**
 * Log class
 * 
 * @author     TheKoziTwo <thekozitwo@gmail.com>
 * @copyright  2015
 * @license    Public Domain
 * 
 * @todo    enable logging to file
 */ 
class Log {
    
    /**
     * @const   int     priorities
     */ 
    const PRIORITY_HIGH = 3;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_LOW = 1;
    
    /**
     * Log error
     * 
     * Will send an e-mail on critical errors to notify admin (if enabled)
     * 
     * @param   int         priority
     * @param   string      error title
     * @param   string      error description
     * @return  void 
     */ 
    public static function error($priority,$error,$error_msg = '')
    {
        global $config;
        
        if($config['errors']['enable_mail_notify'] AND $priority == self::PRIORITY_HIGH)
        {
            mail($config['errors']['mail'],$error,$error_msg);
        }
    }   
}