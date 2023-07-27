<?php

//@author Potoskueva Daria

require_once("./Services/Object/classes/class.ilObject.php");

class ilRecSysModelCourse {

    // DEFAULT VALUES
    //const SECURE_PEPPER                     = "leap2008leap.2008";
    
    // Default values for teacher settings
    const COURSE_DEFAULT_SETTINGS_STATUS            = 0;  // Default LEAP Plugin status: 0=LeAP plugin not active / 1=LeAP plugin not active
    const COURSE_DEFAULT_SETTINGS_TRACKING          = 1;  // tracking option : 0=not active / 1=active
    const COURSE_DEFAULT_SETTINGS_LO                = 1;  // learning objectives : 0=not active / 1=active
    const COURSE_DEFAULT_SETTINGS_IG                = 1;  // individual goals : 0=not active / 1=active
    const COURSE_DEFAULT_SETTINGS_IG_DEFAULT        = 0;  // individual goals default goals : 0=not active / 1=active
    const COURSE_DEFAULT_SETTINGS_RECOMMENDATIONS   = 0;  // recommendations : 0=not active / 1=active
    
    // available options for student settings
    const COURSE_DEFAULT_SETTINGS_TRACKING_STATUS   = 0;  // default tracking status students (0=none, 1=active, 2=anonymous)
    const COURSE_DEFAULT_SETTINGS_OPT_OUT           = 1;  // Teacher can give student the option to deactivate tracking
    const COURSE_DEFAULT_SETTINGS_OPT_ANONYM        = 0;  // Teacher can give student the option to set tracking to anonymous
    const COURSE_DEFAULT_SETTINGS_OPT_ACTIVE        = 1;  // Teacher can give student the option to activate tracking
    


    // Database values
    private $il_crs_id;
    private $crs_id;
    private $crs_status; // 0=LeAP plugin not active / 1=LeAP plugin not active
    private $mod_tracking;
    private $mod_lo;
    private $mod_ig;
    //private $mod_ig_default;
    private $opt_default;
    private $opt_out;
    private $opt_anonym;
    private $opt_active;
    private $mod_recommendations;
    
    var $ilDB;
    
    // ---------------------------------------------------------------
    
    private function __construct($crs_id) //Private functions are only accessible within the class that defines them
    {
        global $ilDB;
        $this->ilDB = $ilDB;
        $this->crs_id = $crs_id;
        $this->il_crs_id = ilObject::_lookupObjectId($crs_id);
    }
    
    
    public static function getRecSysCourse($crs_id) 
    {
        $RecSysCourse = new ilRecSysModelCourse($crs_id);
        $RecSysCourse->read();
        return $RecSysCourse;
    }
    
    
    public static function getOrCreateRecSysCourse($crs_id) 
    {        
        if (!self::existsRecSysCourse($crs_id)) {
            $RecSysCourse = new ilRecSysModelCourse($crs_id);            
            $RecSysCourse->create();
        }
        return self::getRecSysCourse($crs_id);
    }
    
    
    public static function existsRecSysCourse($crs_id) {
        global $ilDB;
        #$queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_courses WHERE crs_id = ".$ilDB->quote($crs_id, "integer"));        
        #if ($ilDB->numRows($queryResult) == 1) {
            return true;
        #} else {
        #    return false;
        #}
    }
    
    
    public function save() {
        $this->update();
    }
    // ---------------------------------------------------------------------------
    
    private function read() 
    {
        $queryResult = $this->ilDB->query("SELECT * FROM ui_uihk_recsys_courses WHERE crs_id = ".$this->ilDB->quote($this->crs_id, "integer"));
        $course = $this->ilDB->fetchObject($queryResult);
        $this->il_crs_id              = $course->il_crs_id;
        $this->crs_id                 = $course->crs_id;
        $this->crs_status             = $course->crs_status;
        $this->mod_tracking           = $course->mod_tracking ;
        $this->mod_lo                 = $course->mod_lo;
        $this->mod_ig                 = $course->mod_ig;
        //$this->mod_ig_default         = $course->mod_ig_default;
        $this->mod_recommendations    = $course->mod_recommendations;
        $this->opt_default            = $course->opt_default;
        $this->opt_out                = $course->opt_out;
        $this->opt_anonym             = $course->opt_anonym;
        $this->opt_active             = $course->opt_active;
        return $this;
    }
    
    
    private function create()
    {        
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_courses "
            . "(crs_id, il_crs_id, crs_status, mod_tracking, mod_lo, mod_ig, mod_recommendations, opt_default, opt_out, opt_anonym, opt_active )"
            . " VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
            array("integer", "integer", "integer", "integer", "integer", "integer", "integer", "integer", "integer", "integer", "integer"),
            array(  $this->crs_id, 
                    $this->il_crs_id, 
                    self::COURSE_DEFAULT_SETTINGS_STATUS,
                    self::COURSE_DEFAULT_SETTINGS_TRACKING, 
                    self::COURSE_DEFAULT_SETTINGS_LO,  
                    self::COURSE_DEFAULT_SETTINGS_IG,
                    self::COURSE_DEFAULT_SETTINGS_IG_DEFAULT,
                    self::COURSE_DEFAULT_SETTINGS_RECOMMENDATIONS,
                    self::COURSE_DEFAULT_SETTINGS_TRACKING_STATUS,
                    self::COURSE_DEFAULT_SETTINGS_OPT_OUT,
                    self::COURSE_DEFAULT_SETTINGS_OPT_ANONYM ,
                    self::COURSE_DEFAULT_SETTINGS_OPT_ACTIVE,
                ));
    }
    
    
    private function update() 
    {    
        $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_courses"
                            ." SET"
                                ." crs_status=%s"
                                ." ,mod_tracking=%s"
                                ." ,mod_lo=%s"
                                ." ,mod_ig=%s"
                                ." ,mod_recommendations=%s"
                                ." ,opt_default=%s"
                                ." ,opt_out=%s"
                                ." ,opt_anonym=%s"
                                ." ,opt_active=%s" 
                            ." WHERE il_crs_id=%s"
                                ." AND crs_id=%s ;",
                array(  "integer", 
                        "integer", 
                        "integer", 
                        "integer", 
                        "integer", 
                        "integer", 
                        "integer", 
                        "integer", 
                        "integer", 
                        "integer",
                        "integer"),
                array(  $this->crs_status, 
                        $this->mod_tracking,       
                        $this->mod_lo,               
                        $this->mod_ig,
                        //$this->mod_ig_default,
                        $this->mod_recommendations,
                        $this->opt_default,
                        $this->opt_out,
                        $this->opt_anonym,
                        $this->opt_active,               
                        $this->il_crs_id, 
                        $this->crs_id
                )
            );
    }
    
    
    // --- Getters and Setters ---------------------------------------------    
    public function getTitle() {
        return ilObject::_lookupTitle($this->getIl_crs_id());
    }
    
    public function getDescription() {
        return ilObject::_lookupDescription($this->getIl_crs_id());
    }    
    
    public function getIl_crs_id()
    {
        return $this->il_crs_id;
    }
    
    public function getCrs_id()
    {
        return $this->crs_id;
    }
    
    public function getCrs_status()
    {
        return $this->crs_status;
    }
    
    public function getMod_tracking()
    {
        return $this->mod_tracking;
    }
    
    public function getMod_lo()
    {
        return $this->mod_lo;
    }

    public function getMod_ig()
    {
        return $this->mod_ig;
    }
    
    public function getMod_recommendations()
    {
        return $this->mod_recommendations;
    }

    public function getOpt_default()
    {
        return $this->opt_default;
    }
  
    public function getOpt_out()
    {
        return $this->opt_out;
    }
  
    public function getOpt_anonym()
    {
        return $this->opt_anonym;
    }

    public function getOpt_active()
    {
        return $this->opt_active;
    }
    
    public function setIl_crs_id($il_crs_id)
    {
        $this->il_crs_id = $il_crs_id;
    }
    public function setCrs_id($crs_id)
    {
        $this->crs_id = $crs_id;
    }

    public function setCrs_status($crs_status)
    {
        $this->crs_status = $crs_status;
    }
    
    public function setMod_tracking($mod_tracking)
    {
        $this->mod_tracking = $mod_tracking;
    }
    
    public function setMod_lo($mod_lo)
    {
        $this->mod_lo = $mod_lo;
    }
    
    public function setMod_ig($mod_ig)
    {
        $this->mod_ig = $mod_ig;
    }
    
    public function setMod_recommendations($mod_recommendations)
    {
        $this->mod_recommendations = $mod_recommendations;
    }
    
    public function setOpt_default($opt_default)
    {
        $this->opt_default = $opt_default;
    }
    
    public function setOpt_out($opt_out)
    {
        $this->opt_out = $opt_out;
    }
    
    public function setOpt_anonym($opt_anonym)
    {
        $this->opt_anonym = $opt_anonym;
    }
    
    public function setOpt_active($opt_active)
    {
        $this->opt_active = $opt_active;
    }
    //public function getMod_ig_default()
    //{
    //    return $this->mod_ig_default;
    //}

    //public function setMod_ig_default($mod_ig_default)
    //{
    //    $this->mod_ig_default = $mod_ig_default;
    //}

    
}
