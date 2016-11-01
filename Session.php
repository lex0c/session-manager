<?php

/**
 * PHP Session Manager 
 * Session Manager with monitoring access by IP
 * @link https://github.com/lleocastro/session-manager
 * @license https://github.com/lleocastro/session-manager/blob/master/LICENSE
 * @copyright 2016 Leonardo Carvalho <leonardo_carvalho@outlook.com>
 */

class Session 
{
    /**
     * 
     */
    private static $session = null;
    private static $sessionID = '';
    private static $sessionName = '';
    private static $sessionNameUser = '';
    
    /**
     * 
     */
    private static $userAgent = null;
    private static $serverName = '';
    private static $remoteADDR = 0;
    
    /**
     * 
     */
    private static $status = false;
    private static $data = [];
    
    /**
     * 
     */
    public static function start($sessionName='')
    {
        if((!empty(self::getData('active'))) && (is_bool(self::getData('active'))) 
                                             && (self::getData('active') == true)):
            self::$status = self::getData('active');
        endif;

        if(!self::$status):
            self::$session = session_start();
            
            session_regenerate_id();
            self::$sessionID = strrev(md5(strrev(session_id())));
            
            self::$userAgent  = (string) htmlentities(filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'));
            self::$serverName = (string) htmlentities(filter_input(INPUT_SERVER, 'SERVER_NAME'));
            self::$remoteADDR = (string) htmlentities(filter_input(INPUT_SERVER, 'REMOTE_ADDR'));
            
            self::$sessionNameUser = ($sessionName=='')?md5(uniqid(mt_rand(), true)):(string) $sessionName;
            self::$sessionName = session_name(
                md5(sha1(strrev(
                    self::$sessionNameUser.
                    self::$serverName.self::$remoteADDR.self::$userAgent        
                )))
            );           
            self::$status = true;
            self::addData('active', true);

            return self::$session;

        elseif((self::$status) && (self::checkSession())):
            return self::$session;
        else:
            self::destroy();
        endif;
        
        return self::start(md5(uniqid(mt_rand(), true)));
    }
    
    /**
     * 
     */
    // public static function addData($key, $value)
    // {
    //     $k = (string) trim(htmlentities(strip_tags($key)));

    //     if(self::$status):
    //         $_SESSION[$k] = trim(htmlentities(strip_tags($value)));
    //         return true;
    //     endif;
        
    //     return false;
    // }

    public static function addData($key, $value, $type='session')
    {
        $k = (string) trim(htmlentities(strip_tags($key)));
        if(self::$status):          
            switch($type):
                case 'session':
                    $_SESSION[$k] = htmlentities(strip_tags($value));
                    return true;
                    break;
                case 'local':
                    self::$data[$k] = htmlentities(strip_tags($value));
                    return true;
                    break;
            endswitch;
        endif;
        return false;
    }
    
    /**
     * 
     */
    // public static function updateData($key, $value)
    // {
    //     $k = (string) trim(htmlentities(strip_tags($key)));

    //     if((self::$status) && (isset($_SESSION[$k]))):
    //         unset($_SESSION[$k]);
    //         $_SESSION[$k] = trim(htmlentities(strip_tags($value)));
    //         return true;
    //     endif;
        
    //     return false;
    // }

    public static function updateData($key, $value, $type='session')
    {
        $k = (string) trim(htmlentities(strip_tags($key)));
        if((self::$status) && ((isset($_SESSION[$k])) || (isset(self::$data[$k])))):          
            switch($type):
                case 'session':
                    unset($_SESSION[$k]);
                    $_SESSION[$k] = htmlentities(strip_tags($value));
                    return true;
                    break;
                case 'local':
                    self::$data[$k] = '';
                    self::$data[$k] = htmlentities(strip_tags($value));
                    return true;
                    break;
            endswitch;
        endif;
        return false;
    }
    
    /**
     * 
     */
    // public static function removeData($key)
    // {
    //     $k = (string) trim(htmlentities(strip_tags($key)));

    //     if((self::$status) && (isset($_SESSION[$k]))):
    //         unset($_SESSION[$k]);
    //         return true;
    //     endif;
        
    //     return false;
    // }
    
    public static function removeData($key, $type='session')
    {
        $k = (string) trim(htmlentities(strip_tags($key)));
        if((self::$status) && ((isset($_SESSION[$k])) || (isset(self::$data[$k])))):
            switch($type):
                case 'session':
                    unset($_SESSION[$k]);
                    return true;
                    break;
                case 'local':
                    self::$data[$k] = '';
                    return true;
                    break;
            endswitch;
        endif;
        return false;
    }

    /**
     * 
     */
    public static function destroy()
    {
        unset($_SESSION);
        self::$data = [];
        self::$sessionID = '';
        self::$sessionName = '';
        self::$sessionNameUser = '';
        self::$session = session_destroy();
        self::$userAgent = null;
        self::$serverName = '';
        self::$remoteADDR = 0;
        self::$status = false;
        return true;
    }
    
    /**
     * 
     */
    private static function checkSession()
    {
        $userAgent  = (string) htmlentities(filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'));
        $serverName = (string) htmlentities(filter_input(INPUT_SERVER, 'SERVER_NAME'));
        $remoteADDR = (string) htmlentities(filter_input(INPUT_SERVER, 'REMOTE_ADDR'));
        
        $sessionName = md5(sha1(strrev(
                self::$sessionNameUser.$serverName.$remoteADDR.$userAgent       
            )));
        
        if((self::$userAgent == $userAgent) && (self::$serverName  == $serverName) 
                                            && (self::$remoteADDR  == $remoteADDR)
                                            && (self::$sessionName == $sessionName)):        
            return true;          
        endif;
        
        return false;
    }
    
    /**
     * 
     */
    // public static function getData($key)
    // {
    //     $k = (string) trim(htmlentities(strip_tags($key)));

    //     if((self::$status) && (isset($_SESSION[$k]))):
    //         return $_SESSION[$k];
    //     endif;
        
    //     return false;
    // }

    public static function getData($key, $type='session')
    {
        $k = (string) trim(htmlentities(strip_tags($key)));
        if((self::$status) && ((isset($_SESSION[$k])) || (isset(self::$data[$k])))):
            switch($type):
                case 'session':
                    return $_SESSION[$k];
                    break;
                case 'local':
                    return self::$data[$k];
                    break;
            endswitch;
        endif;
        return false;
    }
    
    /**
     * 
     */
    public static function getStatus()
    {
        return self::$status;
    }
       
}