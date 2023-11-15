<?php

//@author Potoskueva Daria
//@author Joel Pflomm
class ilRecSysModelPresentation extends ilRecSysModelMaterialSection{

    const MATERIALTABLENAME = "ui_uihk_recsys_m_s_f_p";
    const SECTIONIDNAME = "presentation_id";
    const MATERIALTYPE = 1;

    // attributes
    private $start_slide;
    private $end_slide;

    //-----------------------------------------------------------------------------------

    public function __construct($presentation_id, $obj_id, $start_slide, $end_slide, $difficulty, $rating_count, $no_tags) {
        parent::__construct($presentation_id, $obj_id, $difficulty, $rating_count, $no_tags);

        $this->$start_slide = $start_slide;
        $this->$end_slide = $end_slide;
    }

    public static function fetchByMaterialSectionID($presentation_id) {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ".self::MATERIALTABLENAME." WHERE ".self::SECTIONIDNAME." = ".$ilDB->quote($presentation_id, "integer"));
        if($ilDB->numRows($queryResult)==0) {
            return null;
        }
        $fetched_presentation = $ilDB->fetchObject($queryResult);
        $presentation = new ilRecSysModelPresentation(
            $fetched_presentation->presentation_id, 
            $fetched_presentation->obj_id,
            $fetched_presentation->start_slide,
            $fetched_presentation->end_slide,
            $fetched_presentation->difficulty, 
            $fetched_presentation->rating_count,
            $fetched_presentation->no_tags);
        return $presentation;
    }

    public static function fetchByObjID($obj_id, $from_to) {
        global $ilDB;
        if(sizeof($from_to) == 2) {
            $queryResult = $ilDB->query("SELECT * FROM ".self::MATERIALTABLENAME." WHERE obj_id = ".$ilDB->quote($obj_id, "integer")." AND start_slide == ".$ilDB->quote($from_to[0], "integer")." AND end_slide == ".$ilDB->quote($from_to[1], "integer"));
        } else {
            throw new Exception("Both start end end slide have to be defined for this material_type");
        }
        //if query is empty, return null
        if ($ilDB->numRows($queryResult) === 0) {
            return null;
        }   
        $fetched_presentation = $ilDB->fetchObject($queryResult);
        $presentation = new ilRecSysModelPresentation(
            $fetched_presentation->presentation_id, 
            $fetched_presentation->obj_id,
            $fetched_presentation->start_slide,
            $fetched_presentation->end_slide,
            $fetched_presentation->difficulty, 
            $fetched_presentation->rating_count,
            $fetched_presentation->no_tags);
        return $presentation;
    }

    public static function fetchAllSectionsWithObjID($obj_id) {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ".self::MATERIALTABLENAME." WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
        
        //if query is empty, return null
        if ($ilDB->numRows($queryResult) === 0) {
            return null;
        }
        $presentations = array();
        while($fetched_presentation = $ilDB->fetchObject($queryResult)){
            $presentation = new ilRecSysModelPresentation(
                $fetched_presentation->presentation_id, 
                $fetched_presentation->obj_id,
                $fetched_presentation->start_slide,
                $fetched_presentation->end_slide,
                $fetched_presentation->difficulty, 
                $fetched_presentation->rating_count,
                $fetched_presentation->no_tags);
            array_push($presentations, $presentation);
        }
        return $presentations;
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
    
    // --------------------------------------------------------------
    /**
     * functions that implement queries to the db
     */

    /**
     * put a new Presentation section in the table
     */
     public function createMaterialSection() {
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_m_c_f_p"
                . "(presentation_id, obj_id, start_slide, end_slide, difficulty, rating_count, no_tags)"
                . " VALUES (%s,%s,%s,%s,%s,%s,%s)",
                array("integer", "integer", "integer", "integer", "float", "integer", "integer"),
                array($this->section_id, 
                      $this->obj_id,
                      $this->start_slide,
                      $this->end_slide,  
                      $this->difficulty,          // difficulty
                      $this->rating_count,       // rating_count
                      $this->no_tags
                    ));
    }

    /**
     *  update the difficulty of the presentation section
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
     *  update the start and end slide attributes of the presentation section
     */
    public function updateStartEndSlide($start_slide, $end_slide) {
        $this->ilDB->manipulateF("UPDATE ".self::MATERIALTABLENAME
            ."SET"
            ." start_slide = %s,"
            ." end_slide = %s"
            ." WHERE presentation_id = %s",
            array("integer", "integer", "integer"),
            array($start_slide, $end_slide, $this->section_id)
        );
        $this->start_slide = $start_slide;
        $this->end_slide = $end_slide;
    }

    public function addNewRating($rating) {
        $new_difficulty = $this->calculateDifficulty($rating);
        $this->updateSectionDifficulty($new_difficulty, ($this->getRatingCount() + 1));
    }

    /**
     * delete given presentation section object
     */
    public function deleteSection() {   
        $presentation_id = filter_var($this->section_id, FILTER_VALIDATE_INT);
        $this->ilDB->manipulateF("DELETE FROM " .self::MATERIALTABLENAME." WHERE ".self::SECTIONIDNAME." = %s",
            array("integer"),
            array($presentation_id));
    }

    // ----------------------------------------------------------------------
    /**
     * Setter and Getter
     */

    public function getMaterialType() {
        return self::MATERIALTYPE;
    }

    public function getStart_slide() {
        return $this->start_slide;
    }

    public function getEnd_slide() {
        return $this->end_slide;
    }

    public function getFromTo(){
        return array($this->start_slide, $this->end_slide);
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
