<?php if(!defined('APP')) exit;
/**
 * Error class
 * 
 * @author     TheKoziTwo <thekozitwo@gmail.com>
 * @copyright  2015
 * @license    Public Domain
 */ 
class Error_ {
    
    private $errors = array();
    
    /**
     * Retrive an error message for a specific field
     * 
     * @param   string   field name
     * @param   bool     true to unset the error after retrival
     * @return  string   the error message
     */ 
    public function get($field,$unset = true)
    {
        $err = isset($this->errors[$field]) ? $this->errors[$field] : '';
       
        if($unset) unset($this->errors[$field]);
        
        return $err;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Set an error message for a specific field
     * 
     * @param   string  field name
     * @param   string  error message
     * @return  void
     */ 
    public function set($field,$err)
    {
        $this->errors[$field] = $err;
    }

    // ------------------------------------------------------------------------

    /**
     * Check if error exists for a given field
     * 
     * @param   string          field to check for errors
     * @param   bool|string     if string, this string will be returned if error 
     *                          instead of true
     * @param   bool            true to unset the error
     * @return  bool|string     true/false (or instead of false it may return a 
     *                          string if the $label param is a string)
     */ 
    public function is($field,$label = false,$unset = false)
    {
        $err = $this->get($field,$unset);
        
        $is_error = (bool) ( ! empty($err));
        
        if($is_error AND ($label !== false)) return $label;
        
        return $is_error;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Check if there are any errors
     * 
     * @return  bool    true if errors exists
     */ 
    public function is_errors()
    {
        return (bool) ! empty($this->errors);
    }
}
