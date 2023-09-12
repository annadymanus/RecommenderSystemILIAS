<?php

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelMaterialSection.php');


//@author Potoskueva Daria

class ilRecSysModelScript {

    private $script_id;
    private $obj_id;
    private $start_page;
    private $end_page;
    private $difficulty;
    private $rating_count;

    var $ilDB;

    //-----------------------------------------------------------------------------------

    //constructor
    public function __construct($script_id, $obj_id, $start_page, $end_page, $difficulty, $rating_count)
    {
        global $ilDB;
        $this->ilDB = $ilDB;
        $this->script_id = $script_id;// TODO: figure  out whether we can put a counter in here and make it thread safe, so that the id is unique.
        $this->obj_id = $obj_id;  //ilObject::_lookupObjectId(); //needed to be find out
        $this->start_page = $start_page;
        $this->end_page = $end_page;
        $this->difficulty = $difficulty;
        $this->rating_count = $rating_count;
    }

    // ------------------------------------------------------------------------------------
    //add a new script to the table
    public function createScript() {   
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_m_c_f_s"
                . " (script_id, obj_id, start_page, end_page, difficulty, rating_count)"
                . " VALUES (%s,%s,%s,%s,%s,%s) ;",
                array("integer", "integer", "integer", "integer", "float", "integer"),
                array($this->script_id, 
                      $this->obj_id, 
                      $this->start_page, 
                      $this->end_page, 
                      $this->difficulty, 
                      $this->rating_count
                    ));
    }

    //update the script
    public function update(){
        $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_m_c_f_s"
            ." SET"
                ." start_page = %s"
                .", end_page = %s"
                .", difficulty = %s"
                .", rating_count = %s"
            ." WHERE script_id = %s",
        array("integer","integer","double", "integer", "integer"),
        array($this->start_page, $this->end_page, $this->difficulty, $this->rating_count, $this->script_id)
        );
    }

    //written by @Anna Eschbach-Dymanus
    public static function fetchByObjID($obj_id, $from=null, $to=null){
        global $ilDB;
        if($from != null && $to != null){
            $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_f_s WHERE obj_id = ".$ilDB->quote($obj_id, "integer")." AND start_page == ".$ilDB->quote($from, "integer")." AND end_page == ".$ilDB->quote($to, "integer"));
        }
        else if($from == null && $to == null){
            $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_f_s WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
        }
        else{
            throw new Exception("Either both from and to have to be null or both have to be set");
        }
        if ($ilDB->numRows($queryResult) === 0) {
            return null;
        }
        $scripts = array();
        while($fetched_script = $ilDB->fetchObject($queryResult)){
            $script = new ilRecSysModelScript(
                $fetched_script->script_id, 
                $fetched_script->obj_id, 
                $fetched_script->start_page, 
                $fetched_script->end_page, 
                $fetched_script->difficulty, 
                $fetched_script->rating_count);
            $scripts[] = $script;
        }
        return $scripts;
    }

    //Do we really need getScript? What would be the usecase
    //written by @Anna Eschbach-Dymanus
    public static function fetchByMaterialID($script_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_f_s WHERE script_id = ".$ilDB->quote($script_id, "integer"));
        $fetched_script = $ilDB->fetchObject($queryResult);
        $script = new ilRecSysModelScript(
            $fetched_script->script_id, 
            $fetched_script->obj_id, 
            $fetched_script->start_page, 
            $fetched_script->end_page, 
            $fetched_script->difficulty, 
            $fetched_script->rating_count);
        return $script;
    }

    public function getMaterial(){
        global $ilDB;
        $queryResult = $this->$ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_f_s WHERE script_id = ".$this->$ilDB->quote($this->script_id, "integer"));
        $script = $this->$ilDB->fetchObject($queryResult);
        $this->script_id = $script->script_id;
        $this->obj_id = $script->obj_id;
        $this->start_page = $script->start_page;
        $this->end_page = $script->end_page;
        $this->difficulty = $script->difficulty;
        $this->rating_count = $script->rating_count;
        return $this;
    }

    public static function deleteMaterial($script_id){
        global $ilDB;
        // Validate and sanitize the input with the filter_var() function.
        $script_id = filter_var($script_id, FILTER_VALIDATE_INT);
        $ilDB->manipulateF("DELETE FROM ui_uihk_recsys_m_c_f_s WHERE script_id = %s",
            array("integer"),
            array($script_id));
    }

    //-----------------------------------------------------------------------------------

    public function get_id()
    {
        return $this->script_id;
    }

    public function getObj_id()
    {
        return $this->obj_id;
    }

    public function getStart_page()
    {
        return $this->start_page;
    }

    public function getEnd_page()
    {
        return $this->end_page;
    }

    public function getDifficulty()
    {
        return $this->difficulty;
    }

    public function getRating_count()
    {
        return $this->rating_count;
    }

    public function setStart_page($start_page)
    {
        $this->start_page = $start_page;
    }

    public function setEnd_page($end_page)
    {
        $this->end_page = $end_page;
    }

    public function calculateDifficulty($rating){
        $this->difficulty = (($this->difficulty * ($this->rating_count - 1)) + $rating) / $this->rating_count;
        // TODO: implement a more sofisticated difficulty calculation
    }

    /**
     * rating_count counts the users that have given a rating
     * this function increments the rating_count of the given object by 1
     */
    public function incrementRating_count()
    {
        $this->rating_count++; 
    }
    
}