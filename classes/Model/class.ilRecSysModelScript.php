<?php

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelMaterialSection.php');


//@author Potoskueva Daria
//@author Joel Pflomm

class ilRecSysModelScript extends ilRecSysModelMaterialSection{

    const MATERIALTABLENAME = "ui_uihk_recsys_m_s_f_s";
    const SECTIONIDNAME = "script_id";
    const MATERIALTYPE = 0;

    private $start_page;
    private $end_page;

    //-----------------------------------------------------------------------------------

    public function __construct($script_id, $obj_id, $start_page, $end_page, $difficulty, $rating_count, $no_tags) {
        parent::__construct($script_id, $obj_id, $difficulty, $rating_count, $no_tags);

        $this->start_page = $start_page;
        $this->end_page = $end_page;
    }

    public static function fetchByMaterialSectionID($script_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ".self::MATERIALTABLENAME." WHERE ".self::SECTIONIDNAME." = ".$ilDB->quote($script_id, "integer"));
        if($ilDB->numRows($queryResult)==0) {
            return null;
        }
        $fetched_script = $ilDB->fetchObject($queryResult);
        $script = new ilRecSysModelScript(
            $fetched_script->script_id, 
            $fetched_script->obj_id, 
            $fetched_script->start_page, 
            $fetched_script->end_page, 
            $fetched_script->difficulty, 
            $fetched_script->rating_count,
            $fetched_script->no_tags);
        return $script;
    }

    //written by @Anna Eschbach-Dymanus
    // edited by @Joel Pflomm
    public static function fetchByObjID($obj_id, $from_to){
        global $ilDB;
        if(sizeof($from_to) == 2){
            $queryResult = $ilDB->query("SELECT * FROM ".self::MATERIALTABLENAME." WHERE obj_id = ".$ilDB->quote($obj_id, "integer")." AND start_page == ".$ilDB->quote($from_to[0], "integer")." AND end_page == ".$ilDB->quote($from_to[1], "integer"));
        } else {
            throw new Exception("Both start end end page have to be defined for this material_type");
        }
        // check wheter the queryResult holds some entries
        if ($ilDB->numRows($queryResult) === 0) {
            return null;
        }
        $fetched_script = $ilDB->fetchObject($queryResult);
        $script = new ilRecSysModelScript(
            $fetched_script->script_id, 
            $fetched_script->obj_id, 
            $fetched_script->start_page, 
            $fetched_script->end_page, 
            $fetched_script->difficulty, 
            $fetched_script->rating_count,
            $fetched_script->no_tags);
        return $script;
    }

    public static function fetchAllSectionsWithObjID($obj_id) {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ".self::MATERIALTABLENAME." WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
        
        //if query is empty, return null
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
                $fetched_script->rating_count,
                $fetched_script->no_tags);
            array_push($scripts, $script);
        }
        return $scripts;
    }

    public static function getLastMaterialSectionId() {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT ".self::SECTIONIDNAME.
            " FROM ".self::MATERIALTABLENAME.
            " ORDER BY ".self::SECTIONIDNAME." DESC LIMIT 1");
        if ($ilDB->numRows($queryResult) === 0) {
            $last_section_id = 0;
        } else {
            $last_section_id = $ilDB->fetchAssoc($queryResult);
            $last_section_id = $last_section_id[self::SECTIONIDNAME];
        }
        return $last_section_id;
    }

    // ------------------------------------------------------------------------------------
    //add a new script section to the table
    public function createMaterialSection() {   
        $this->ilDB->manipulateF("INSERT INTO ".self::MATERIALTABLENAME
                . " (script_id, obj_id, start_page, end_page, difficulty, rating_count, no_tags)"
                . " VALUES (%s,%s,%s,%s,%s,%s,%s) ;",
                array("integer", "integer", "integer", "integer", "float", "integer", "integer"),
                array($this->section_id, 
                      $this->obj_id, 
                      $this->start_page, 
                      $this->end_page, 
                      $this->difficulty, 
                      $this->rating_count,
                      $this->no_tags
                    ));
    }

    /**
     *  update the difficulty of the script section
     */
    public function updateSectionDifficulty($new_difficulty, $new_rating_count) {
        $this->ilDB->manipulateF("UPDATE ".self::MATERIALTABLENAME
        ." SET"
        ." difficulty = %s,"
        ." rating_count = %s"
        ." WHERE ".self::SECTIONIDNAME." = %s",
        array("double", "integer", "integer"),
        array($new_difficulty, $new_rating_count, $this->section_id)
    );
    $this->difficulty = $new_difficulty;
    $this->rating_count = $new_rating_count;
    }

    /**
     *  update the start and end page attributes of the script section
     */
    public function updateStartEndPage($start_page, $end_page){
        $this->ilDB->manipulateF("UPDATE ".self::MATERIALTABLENAME
            ." SET"
            ." start_page = %s"
            .", end_page = %s"
            ." WHERE script_id = %s",
            array("integer", "integer", "integer"),
            array($start_page, $end_page, $this->section_id)
        );
        $this->start_page = $start_page;
        $this->end_page = $end_page;
    }

    public function addNewRating($rating){
        $new_difficulty = $this->calculateDifficulty($rating);
        $this->updateSectionDifficulty($new_difficulty, ($this->getRatingCount() + 1));
    }

    /**
     *  delete given script section object
     */
    public function deleteSection(){
        $script_id = filter_var($this->section_id, FILTER_VALIDATE_INT);
        $this->ilDB->manipulateF("DELETE FROM " .self::MATERIALTABLENAME." WHERE ".self::SECTIONIDNAME." = %s",
            array("integer"),
            array($script_id));
    }

    //-----------------------------------------------------------------------------------

    public function getMaterialType() {
        return self::MATERIALTYPE;
    }

    public function getStart_page() {
        return $this->start_page;
    }

    public function getEnd_page() {
        return $this->end_page;
    }

    public function setNoTags($no_tags) {
        if($no_tags > 0){
            $this->ilDB->manipulateF("UPDATE " .self::MATERIALTABLENAME ." SET no_tags = %s WHERE ".self::SECTIONIDNAME." = %s", 
                array("integer", "integer"),
                array($no_tags, $this->section_id)
            );
            $this->no_tags = $no_tags;
        }
    }
}