<?php if(!defined('APP')) exit;
/**
 * Asset class
 * 
 * Should be initiated with the init() method, various subclasses will be used, each one unique to that specific asset type. 
 * 
 * @author     TheKoziTwo <thekozitwo@gmail.com>
 * @copyright  2015
 * @license    Public Domain
 */ 
abstract class Asset {
    
    /**
     * @var array   holds all properties for this asset
     */ 
    private $_data;
    
    // ------------------------------------------------------------------------
    
    /**
     * Initialize an asset (e.g CryptoNote based (XMR,BBR), Bitcoin based (BTC,
     * LTC,NMC) etc)
     * 
     * @param   int         asset id to initalize
     * @param   array       the properties/configs for this asset
     * @return  object      an instance of the asset, E.g Asset_CryptoNote(), 
     *                      Asset_Bitcoin() or if unique features it may return 
     *                      asset specific classes (if they exists) such as: 
     *                      Asset_CryptoNote_XMR(), Asset_CryptoNote_BBR(), 
     *                      Asset_Bitcoin_NMC())
     */ 
    public static function init($asset_id, $properties = array())
    {
        $class_protocol = 'Asset_'.ucfirst($properties['protocol']);
        $class = $class_protocol.'_'.strtoupper($properties['short_name']);
        
        // If asset does not have unique features, it may use the default class:
        if( ! class_exists($class))
        {
            $class = $class_protocol;
        }
         
        $properties['id'] = $asset_id;
        return new $class($properties);
    } 
    
    // ------------------------------------------------------------------------
    
    /**
     * Constructor, only to be called from subclasses
     * 
     * Sets the properties for the instance and returns it.
     * 
     * @param   array       configs/settings for the asset
     * @return  object      instance
     */ 
    protected function __construct($properties = array())
    {
        $this->_data = $properties;
        return $this;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Magic method, to get from $_data
     * 
     * @param   string      variable name
     * @return  string      value
     */ 
    public function __get($property)
    {
        return array_key_exists($property, $this->_data) ? $this->_data[$property] : null;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Magic method, to set $_data
     * 
     * @param   string      variable name
     * @param   string      value
     * @return  void
     */ 
    public function __set($property, $value)
    {
        $this->_data[$property] = $value;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Get Asset ID
     * 
     * @todo    is this method needed when we already got __get?
     * @return  int     asset id of current instance
     */ 
    public function get_id()
    {
        return $this->id;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Get active assets
     * 
     * @return      array       asset ids
     */ 
    public static function get_assets()
    {
        global $config;
        
        $assets = array_keys($config['asset']);
        
        $ids = array();
        
        foreach($assets as $asset_id)
        {
            // Skip undefined assets
            if( ! isset($config['asset'][$asset_id]['properties'])) continue;
            
            $ids[] = $asset_id; 
        }
        
        return $ids;
    } 
    
    // ------------------------------------------------------------------------
    
    /**
     * Retrive a users balance
     * 
     * @param   object      user
     * @return  decimal     the current balance
     */ 
    public function get_balance($user)
    {
        $res = db()->query('
            SELECT      balance 
            FROM        users_assets 
            WHERE       user_id = '.quote_escape($user->id()).' 
            AND         asset_id = '.quote_escape($this->id).' 
            LIMIT 1
        ');

        if($row = $res->fetch_assoc())
        {
            return $row['balance'];
        }
        
        return '0.00';
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Check if user has sufficient balance
     * 
     * @param   object      user
     * @param   decimal     amount to check
     * @return  bool        true if enough balance (equal or more)
     */ 
    public function available_balance($user,$amount)
    {
        return (bool) bc::is($this->get_balance($user),'>=',$amount);
    }
}