<?php if(!defined('APP')) exit;
/*
 * Copyright 2007 Sergio Vaccaro <sergio@inservibile.org>
 * GNU GPL LICENSE
 * The object of this class are generic jsonRPC 1.0 clients
 * http://json-rpc.org/wiki/specification
 * @author sergio <jsonrpcphp@inservibile.org>
 */
class jsonRPCClientWallet {
private $debug;
private $url;
private $id;
private $notification = false;
public function __construct($url,$debug = false) {
    $this->url = $url;
    empty($proxy) ? $this->proxy = '' : $this->proxy = $proxy;
    empty($debug) ? $this->debug = false : $this->debug = true;
    $this->id = 1;
}
public function setRPCNotification($notification) {
    empty($notification) ? $this->notification = false : $this->notification = true;
}
public function __call($method,$params) {
  
    $is_param_arr = true;
    if(isset($params[0]) and !is_array($params[0]))
    {
          $is_param_arr = false;
    }
    if (!is_scalar($method)) { throw new Exception('Method name has no scalar value'); }              
    if (is_array($params)) { $params = array_values($params);}else{throw new Exception('Params must be given as array');}
    if ($this->notification) {$currentId = NULL; }else{ $currentId = $this->id;}
    if($is_param_arr)
    {
        $request = array( 'method' => $method, 'params' => $params, 'id' => $currentId );
        $request = json_encode($request);
    }
    else
    {
        $request = '{"jsonrpc":"2.0","id":"'.$currentId.'","method":"'.$method.'","params":'.$params[0].'}';
    }
    $this->debug && $this->debug.='***** Request *****'."\n".$request."\n".'***** End Of request *****'."\n\n";
    $ch = curl_init($this->url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
    curl_setopt($ch, CURLOPT_USERPWD, RPC_CRED); // RPC_CRED is set in ../init.php
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    #curl_setopt($ch,CURLOPT_TIMEOUT,10);
    
    $response = json_decode(curl_exec($ch),true);
    curl_close($ch);
        
    if ($this->debug) { echo nl2br($debug); }
    if (!$this->notification) {
        
            if ($response['id'] != $currentId) { return $response; }
            if (isset($response['error']) AND !is_null($response['error'])) { return $response; } 
            return $response['result'];
    }else{
            return true;
    }
    
}
}
?>
