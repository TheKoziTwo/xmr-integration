<?php if(!defined('APP')) exit;
/**
 * User class
 * 
 * @author     TheKoziTwo <thekozitwo@gmail.com>
 * @copyright  2015
 * @license    Public Domain
 */
class User {
    
    /**
     * Create an instance of a specific user
     * 
     * @param   int     user id
     * @return  User    User object
     */ 
    public function __construct($user_id)
    {
        $res = db()->query('
            SELECT      user_id, 
                        username, 
                        password 
            FROM        user 
            WHERE       user_id = '.quote_escape($user_id).' 
            LIMIT       1
        ');

        if($res)
        {
            $row = $res->fetch_assoc();
            $this->id = $row['user_id'];
            $this->username = $row['username'];
            $this->password = $row['password'];
        }
        
        return $this;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Get user id
     * 
     * @return  int     user id in current instance
     */ 
    public function id()
    {
        return $this->id;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Get username
     * 
     * @return  string      username in current instance
     */ 
    public function username()
    {
        return $this->username;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Register new user
     * 
     * @param   string      username
     * @param   string      password
     * @return  bool        true if successfully added to db
     */ 
    public static function register($username,$password)
    {
        $sql = insert_query('user',array(
            'username' => $username,
            'password' => hash('sha256',$password),
        ));
        
        return (bool) db()->query($sql);
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Try to find, and then login a user by setting sessions
     * 
     * @param   string  username
     * @param   string  password (unencrypted)
     * @return  bool    false if user not found, otherwise user object 
     */ 
    public static function login($username,$password)
    {
        $user_id = FALSE;
        
        $res = db()->query('
                SELECT      user_id
                FROM        user 
                WHERE       username = '.quote_escape($username).'
                AND         password = '.quote_escape(hash('sha256',$password)).' 
                LIMIT       1
        ');
        
        if($res)
        {
            $row = $res->fetch_assoc();
            $user_id = $_SESSION['user_id'] = $row['user_id'];
        }
        
        return $user_id;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Fetch a user from db
     * 
     * @param   string      field value (e.g user id, username (default))
     * @param   string      field to search by, default is username
     *                      the chosen field must be unique to user
     * @return  array       User row if found or null 
     */ 
    public static function get_user($value = '',$field = 'username')
    {
        $res = db()->query('
                SELECT      *
                FROM        user 
                WHERE       '.$field.' = '.quote_escape($value).'
                LIMIT       1
        ');
        
        if($res)
        {
            return $res->fetch_assoc();
        }
        
        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Logout user by deleting sessions
     * 
     * @param   int     user id
     * @return  void
     */ 
    public static function logout()
    {
        unset($_SESSION['user_id']);
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Check if user is logged in
     * 
     * @return  bool
     */ 
    public static function is_logged_in()
    {
        return (bool) isset($_SESSION['user_id']);
    }

    // ------------------------------------------------------------------------

    /**
     * Lookup db for username to check if it exists
     * 
     * @param   string      username
     * @return  bool        true if exists
     */ 
    public static function username_exists($username)
    {
        $res = db()->query('
            SELECT      user_id
            FROM        user 
            WHERE       username = '.quote_escape($username).'
            LIMIT       1
        ');
        
        return (bool) $res->num_rows; 
    }

    // ------------------------------------------------------------------------

    /**
     * Check if user has admin privileges
     * 
     * @return  bool        true if yes
     */ 
    public function is_admin()
    {
        global $config;
        
        if(self::is_logged_in())
        {
            if($user = self::get_user($this->username()))
            {
                if(in_array($user['username'],$config['admins'])) return TRUE;    
            }
        }   
        
        return FALSE;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Validate username
     * 
     * A username must be alphanumeric and a certain minimum and maxinimum length
     *
     * @param   string      username
     * @return  bool
     */ 
    public static function valid_username($username)
    {
        global $config;
        
        // The string can only be alphanumeric characters
        if( ! ctype_alnum($username)) return FALSE;
        
        // Enforce minimum and maximum length of username
        if(strlen($username) < $config['user']['username']['min_length']) return FALSE;
        if(strlen($username) > $config['user']['username']['max_length']) return FALSE;
   
        return TRUE;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Validate password
     * 
     * A password must be a certain minimum length, except from that it does not 
     * matter, as it will be encrypted
     * 
     * @param   string      password
     * @return  bool 
     */ 
    public static function valid_password($password)
    {
        global $config;
        
        return (bool) ! (strlen($password) < $config['user']['password']['min_length']);
        
    }
}