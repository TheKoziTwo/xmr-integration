<?php if(!defined('APP')) exit;
/**
 * Retrive DB instance
 * 
 * @return  object        MySQLi DB Instance (or null)
 */ 
function db()
{
    global $db;
    return $db;
}

// ----------------------------------------------------------------------------

/**
 * Quote Escape
 * 
 * Will both quote and escape a string (against sql injection)
 * 
 * @param   string      value
 * @return  string      quoted and escaped value
 */ 
function quote_escape($value)
{
    global $db;
    return "'".mysqli_real_escape_string($db,$value)."'";
}

// ----------------------------------------------------------------------------

/**
 * Insert query helper
 * 
 * Simply send in an array with key => value pair and table name, the method
 * will quote and escape everything automatically. To not quote/escape simply
 * send the value as array($value) instead of $value.
 * 
 * @param   string      table name
 * @param   array       key => val to be inserted
 * @return  string      the finished sql query 
 */ 
function insert_query($table, $data)
{
    $keys = array_keys($data);
    $values = array_values($data);
    
    $sql = 'INSERT INTO '.$table;
    
    $col = ' (';
    $val = ' VALUES(';
    
    foreach($data as $k => $v)
    {
        $col .= $k.',';
        $val .= (is_array($v) ? $v[0] : quote_escape($v)).',';
    }
    
    $val = rtrim($val,',').');';
    $col = rtrim($col,',').')';
    
    return $sql.' '.$col.' '.$val;
    
}

// ----------------------------------------------------------------------------

/**
 * Retrive info value from db
 * 
 * @param   string          name/identifier
 * @return  string          the value
 */ 
function info($name)
{    
    $s = db()->prepare('SELECT `value` FROM `info` WHERE `name` = ? LIMIT 1');
    $s->bind_param('s',$name);
    $s->execute();
    $s->bind_result($value);
    $s->fetch();
    
    return $value;
}    

// ----------------------------------------------------------------------------

/**
 * Flash Message (quick message)
 * 
 * Checks for a message stored in session, if it exists it will return it and 
 * delete it from session.
 * 
 * @return  string      message or empty string if none
 */ 
function flash_msg()
{
    if(isset($_SESSION['flash_message']))
    {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $msg;        
    }
    return '';
}

// ----------------------------------------------------------------------------

/**
 * Force refresh of current page
 * 
 * Must be executed before any output and will send user back to the same back 
 * he is on (clearing $_POST variables)
 * 
 * @return void
 */ 
function refresh()
{
    redirect($_SERVER['REQUEST_URI'],true);
}

// ----------------------------------------------------------------------------

/**
 * Redirect user to another page
 * 
 * @param   string|null     The url to redirect to
 * @param   bool            determines if the url specified is a full url or short (within the site)
 * @return  void
 */ 
function redirect($url = null,$full_url = false)
{
    global $config;
    
    if($url === null)
    {
        $url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$config['site']['base_url'];
    }
    else
    {
        if( ! $full_url)
        {
            $url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$config['site']['base_url'].$url;
        }
    }
    header('Location: '.$url);
    exit;
}

// ----------------------------------------------------------------------------

/**
 * CSRF token generation
 * 
 * Adds token to session, as array. 
 * 
 * @todo    it may be smart to add expiration for more security
 * @return  string      a token
 */ 
function csrf_token()
{
    if( ! isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = array();
    $token = $_SESSION['csrf_token'][] = random_str();
    return $token;
}

// ----------------------------------------------------------------------------

/**
 * CSRF check
 * 
 * Will validate a token against those stored in sessions (to protect against CSRF)
 * 
 * @param   string      token
 * @return  bool
 */ 
function csrf_check($token)
{
    if(isset($_SESSION['csrf_token']) and is_array($_SESSION['csrf_token']))
    {
        if(($key = array_search($token,$_SESSION['csrf_token'])) !== false)
        {
            unset($_SESSION['csrf_token'][$key]);
            return TRUE;
        }
    }
    
    return FALSE;
}

// ----------------------------------------------------------------------------

/**
 * Generate random non-case alpha-numeric hash/string
 * 
 * @param   int         length of hash
 * @return  string      random hash
 */ 
function random_str($length = 64)
{
    $str = '';
    while(strlen($str) < $length)
    {
        $str .= sha1(mcrypt_create_iv(24, MCRYPT_DEV_URANDOM));
    }
    return substr($str,0,$length);
}

// ----------------------------------------------------------------------------

### For compability, re-implementation of functions that may not be available on all servers ###
 
if( ! function_exists('ctype_alnum'))
{
    function ctype_alnum($text)
    {
        return preg_match('/^[a-zA-Z0-9]+$/', $text);
    }
}