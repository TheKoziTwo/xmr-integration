<?php if(!defined('APP')) exit;
/**
 * CryptoNote Daemon
 * 
 * This class is a wrapper for commands sent to daemon and the responses it sends
 * 
 * @author     TheKoziTwo <thekozitwo@gmail.com>
 * @copyright  2015
 * @license    Public Domain
 */  
class CryptoNote_Daemon {
    
    const READY   =  1;
    const BUSY    =  0;
    const OFFLINE = -1;
    
    private $daemon;
    private $status;
    
    /**
     * Initialize an instance of daemon
     * 
     * @param   string      ip or hostname (default: 127.0.0.1)
     * @param   int         port (default: 18081)
     */ 
    public function __construct($host = '127.0.0.1',$port = '18081')
    {
        $this->daemon = new jsonRPCClient('http://'.$host.':'.$port.'/json_rpc');
        
        // Run random cmd to set status:
        $this->_execute('getblockcount');
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Check if daemon is ready
     * @return  bool        true if ready
     */ 
    public function is_ready()
    {
        $this->_execute('getblockcount');
        return (bool) ($this->status === self::READY);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Check if daemon is busy (it will be busy when saving blockchain etc)
     * @return  bool        true if busy
     */ 
    public function is_busy()
    {
        $this->_execute('getblockcount');
        return (bool) ($this->status === self::BUSY);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Check if daemon is responding
     * @return  bool        true if no contact could be established
     */ 
    public function is_offline()
    {
        $this->_execute('getblockcount');
        return (bool) ($this->status === self::OFFLINE);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Get current block height / count (keep in mind if daemon is not 
     * fully synced, this will be lower than actually exist in blockchain)
     * 
     * @return  int|bool        block height (or false on error/busy)
     */ 
    public function get_block_count()
    {
        return $this->_execute('getblockcount')['count'];
    } 
    
    // --------------------------------------------------------------------
    
    /**
     * Get block header by hash
     * 
     * @param   int             hash
     * @return  int|bool        block header (or false on error/busy)
     */ 
    public function get_block_header_by_hash($hash)
    {
        return $this->_execute('getblockheaderbyhash',array('hash'=>$hash));
    }

    // --------------------------------------------------------------------
    
    /**
     * Get block header by height
     * 
     * @param   int             height (e.g 12345)
     * @return  int|bool        block header (or false on error/busy)
     */ 
    public function get_block_header_by_height($height)
    {
        return $this->_execute('getblockheaderbyheight',array('height'=>$height));
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Get last block header
     * @return  bool|int        block header (or false on error/busy) 
     */ 
    public function get_last_block_header()
    {
        return $this->_execute('getlastblockheader');   
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Execute / send rpc command to daemon
     * 
     * Status of daemon will be set after sending cmd: OK, BUSY or OFFLINE
     * 
     * @param   string          daemon command
     * @param   array           params as array
     * @return  array|bool      array as sent from daemon or false if daemon is OFFLINE or BUSY
     */ 
    private function _execute($command,$params = null)
    {
        $result = $this->daemon->$command(json_encode($params));
      
        if($result['status'] == 'OK') 
        {
            $this->status = self::READY;
            return $result;
        }
        elseif($result['status'] == 'BUSY')
        {
            $this->status = self::BUSY;
        }
        else
        {
            $this->status = self::OFFLINE;
        }
                
        return FALSE;
    }

}