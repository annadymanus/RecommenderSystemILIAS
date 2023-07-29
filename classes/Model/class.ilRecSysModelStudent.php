<?php
require_once("./Services/Object/classes/class.ilObject.php");
require_once("./Services/User/classes/class.ilObjUser.php");

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelCourse.php');

class ilRecSysModelStudent {
	
    const USER_STATUS_INACTIVE 	= 0;    
    const USER_STATUS_ACTIVE 	= 1;
        
	var $ilDB;
	
	private $usr_id;
	private $crs_id;   // ref_id of the course
	private $status;
	private $updates = array();
	private $lastvisit;
	
	private function __construct($usr_id, $crs_id, $default_status=self::USER_STATUS_INACTIVE)
	{
		global $ilDB, $ilUser;
		
		$this->ilDB = $ilDB;
		
		$this->usr_id = $usr_id;
		$this->crs_id = $crs_id;
		$this->status = $default_status;
		$this->updates[] = date("Y-m-d H:i:s", time())."->".$this->status;
	}
				
	// ----------------------------------------------------------------
	
	public static function existsRecSysStudent($usr_id, $crs_id)
	{   
	    global $ilDB;	    

        #create entry in the recsys database
 	    #$queryResult = $ilDB->query("SELECT * FROM ui_uihk_leap2_users WHERE usr_id=".$ilDB->quote($usr_id, "integer")." AND crs_id=".$ilDB->quote($crs_id, "integer")." ;");	    
	    
	    #if ($ilDB->numRows($queryResult) == 1) {
	    #    return true;
	    #} else {
	    #    return false;
	    #}
	}
	
	
	public static function getRecSysStudent($usr_id, $crs_id)
	{	    
	    $RecSysStudent = new ilRecSysModelStudent($usr_id, $crs_id);	    
	    return $RecSysStudent->read();
	}
	
	
	public static function getOrCreateRecSysStudent($usr_id, $crs_id)
    {   
        if (!self::existsRecSysStudent($usr_id, $crs_id)) {            
            $default_status = ilRecSysModelCourse::getRecSysCourse($crs_id)->getOpt_default();            
            $student = new ilRecSysModelStudent($usr_id, $crs_id, 0);  
            $student->create();
        }        
        return self::getRecSysStudent($usr_id, $crs_id);
    }
	        
	
	public static function getIliasStudentsOfCourse($crs_id)
    /* returns an array of ilObjUser objects from the global ILIAS database based on the crs_id*/
	{    
	    $obj_id = ilObject::_lookupObjectId($crs_id);
	    
	    global $ilDB;
	    $query = "SELECT usr_data.usr_id, usr_data.login"
        	    ." FROM usr_data"
        	    ." JOIN obj_members"
        	       ." ON (usr_data.usr_id = obj_members.usr_id)"
        	       ." WHERE obj_members.obj_id = ".$ilDB->quote($obj_id, "integer")
        	       ." AND obj_members.member = 1;";
	    
	    $queryResult = $ilDB->query($query);	    
	    $students = $ilDB->fetchAll($queryResult);
	    return $students;
	}
	
	
	public static function countAllStudentsOfCourse($crs_id)
	{
	    $obj_id = ilObject::_lookupObjectId($crs_id);
	    
	    global $ilDB;
	    $queryResult = $ilDB->query("SELECT * FROM obj_members WHERE obj_id = ".$ilDB->quote($obj_id, "integer")." AND member = 1;");
	    return $ilDB->numRows($queryResult);
	}
	
	
	public static function countActiveStudentsOfCourse($crs_id)
	{
	    return self::countRecSysStudentsOfCourseWithStatus($crs_id, self::USER_STATUS_ACTIVE);
	}
	
	public static function countInactiveStudentsOfCourse($crs_id)
	{
	    return self::countRecSysStudentsOfCourseWithStatus($crs_id, self::USER_STATUS_INACTIVE);
	}
	
	// -----------------------------------------------------------------------------------

	public function saveRecSysStudent() {
	    $this->update();
	}
	
	
	public function refreshLeapStudent() {
	    $this->read();
	}
	
	// -----------------------------------------------------------------------------------
	
	private function read() {
	    #global $ilDB;
        #create database for recsys first then uncomment the code and change the name of the table ofcourse
	    #$queryResult = $ilDB->query("SELECT * FROM ui_uihk_leap2_users WHERE usr_id=".$ilDB->quote($this->usr_id, "integer")." AND crs_id=".$ilDB->quote($this->crs_id, "integer")." ;");
	    #$student = $ilDB->fetchObject($queryResult);	    
	    #$this->status = $student->status;
	    #$this->updates = json_decode($student->updates);
	    #$this->lastvisit = $student->lastvisit;
	    #return $this;
	}
	
	private function create()
	{    
        #same here with the database creation first 
	    #$this->ilDB->manipulateF("INSERT INTO ui_uihk_leap2_users (usr_id, crs_id, status, updates)"
         #                          ." VALUES (%s,%s,%s,%s) ;",
	      #  array("integer", "integer", "text", "text"),
	       # array($this->usr_id, $this->crs_id, $this->status, json_encode($this->updates) ) );
	}
	
	
	private function update() 
	{
        #same here with the database creation first
		#$this->ilDB->manipulateF("UPDATE ui_uihk_leap2_users SET status=%s, updates=%s, lastvisit=%s WHERE usr_id=%s AND crs_id=%s ;",
		#    array("integer", "text", "integer", "integer", "integer"),
		#	array($this->status, json_encode($this->updates),$this->lastvisit, $this->usr_id, $this->crs_id) );
	}
	
	private static function countRecSysStudentsOfCourseWithStatus($crs_id, $status)
	{
	   global $ilDB;
	   $obj_id = ilObject::_lookupObjectId($crs_id);
    
       #create database first and adjust the query below as the LEAP team was also mentioning some mistake in the query
          /*
            $query = "SELECT * "
                   ." FROM ui_uihk_leap2_users LEFT JOIN obj_members on ui_uihk_leap2_users.usr_id = obj_members.usr_id"
                   ." WHERE ui_uihk_leap2_users.crs_id = ".$ilDB->quote($crs_id, "integer")
                   ." AND ui_uihk_leap2_users.status = ".$ilDB->quote($status, "integer")
                   ." AND obj_members.obj_id = ".$ilDB->quote($obj_id, "integer")
                   ." AND obj_members.member = 1";
	           $queryResult = $ilDB->query($query);
	           return $ilDB->numRows($queryResult);
              */
	}
	
	// --- Getters and Setters ------------------------------------------------------------------------------	
	
	public function getLogin() 
	{
	    return ilObjUser::_lookupLogin( $this->usr_id );
	}
    
    public function getUsr_id()
    {
        return $this->usr_id;
    }

    public function getCrs_id()
    {
        return $this->crs_id;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setUsr_id($usr_id)
    {
        $this->usr_id = $usr_id;
    }

    public function setCrs_id($crs_id)
    {
        $this->crs_id = $crs_id;
    }

    /**
     * Set new Status and update updates
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->updates[] = date("Y-m-d H:i:s", time())."->".$status;        
        $this->updates = array_slice($this->updates, -10); // Merkt sich nur die letzten 10 Änderungen.        
        $this->status = $status;
    }

    public function getUpdates()
    {        
        return $this->updates;
    }
    public function getLastvisit()
    {
        return $this->lastvisit;
    }

    public function setLastvisit($lastvisit)
    {
        $this->lastvisit = $lastvisit;
    }
 

}
