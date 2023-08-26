<?php

//@author Potoskueva Daria

class ilRecSysModelConfig
{
    var $ilDB;
	
    var $configItems = [
            "enabled_users"       => "",
            "leapcore_apiurl"     => "",
            "leapcore_username"   => "",
            "leapcore_password"   => "",
            "leapcore_tracking_username"   => "",
            "leapcore_tracking_password"   => "",
            //"leap_url"                 => "",
            //"tracking_url"             => "",  
    ];

    public function __construct()
    {
        global $ilDB;
        $this->ilDB = $ilDB;
    }

    public function getConfigItem($key)
    {
        $queryResult = $this->ilDB->query("SELECT value FROM ui_uihk_recsys_config WHERE item = ".$this->ilDB->quote($key, "text"));
        $row = $this->ilDB->fetchObject($queryResult);
        if (empty($row))
            return False;
        return $row->value;
    }
    
    
    public function setConfigItem($key, $value)
    {        
        $this->initConfigItemIfNotExist($key);
       
        $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_config SET value= %s WHERE item = %s ;",
                array("text", "text",),
                array($value, $key,) );
        return true;
    }
    
    
    public function isUserRecSysEnabled($user_login) {
        $queryResult = $this->ilDB->query("SELECT value FROM ui_uihk_recsys_config WHERE item = 'enabled_users'");
        $row = $this->ilDB->fetchObject($queryResult);
        if (empty($row))
            return false;
        $enabled_users = $row->value;
        $enabled_users = preg_replace('/\s+/', '', $enabled_users); //remove white spaces
        $enabled_users_array = explode(",", $enabled_users);
        return in_array($user_login, $enabled_users_array);
    }
    
   
    private function initConfigItemIfNotExist($key) {
        if (!$this->configItemExists($key)) {
            $this->initConfigItem($key);
        }
    }
    
    
    private function configItemExists($key) {
        $queryResult = $this->ilDB->query("SELECT * FROM ui_uihk_recsys_config WHERE item = ".$this->ilDB->quote($key, "text").";");
        if ($this->ilDB->numRows($queryResult) >= 1) {
            return true;
        } else {
            return false;
        }
    }
    
    
    private function initConfigItem($key) {
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_config (item, value) VALUES ".
            " (%s,%s)",
            array("text", "text",),
            array($key, $this->configItems[$key]));
        return true;
    }
}
