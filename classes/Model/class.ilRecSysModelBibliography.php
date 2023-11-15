<?php
/**
 *  @author Joel Pflomm <joel.pflomm@students.uni-mannheim.de>
 * 
 *
 */

 class ilRecSysModelBibliography extends ilRecSysModelMaterialSection{
    
    public const MATERIALTABLENAME = "ui_uihk_recsys_m_s_bib";
    public const SECTIONIDNAME = "bibl_id";
    public const MATERIALTYPE = 5;

    //------------------------------------------------------------------
    public function __construct($bibl_id, $obj_id, $difficulty, $rating_count, $no_tags){
        parent::__construct($bibl_id, $obj_id, $difficulty, $rating_count, $no_tags);
    }

    public static function fetchByMaterialSectionID($bibl_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ".self::MATERIALTABLENAME." WHERE ".self::SECTIONIDNAME." = ".$ilDB->quote($bibl_id, "integer"));
        if($ilDB->numRows($queryResult)==0) {
            return null;
        }
        $fetched_bibliography = $ilDB->fetchObject($queryResult);
        $bibliography = new ilRecSysModelBibliography(
            $fetched_bibliography->bibl_id, 
            $fetched_bibliography->obj_id,
            $fetched_bibliography->difficulty, 
            $fetched_bibliography->rating_count,
            $fetched_bibliography->no_tags);
        return $bibliography;
    }

    public static function fetchByObjID($obj_id, $from_to){
        global $ilDB;
        if(empty($from_to)){
            $queryResult = $ilDB->query("SELECT * FROM ".self::MATERIALTABLENAME." WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
        } else {
            throw new Exception("The picture material section does not implement all given variables.");
        }
        if($ilDB->numRows($queryResult)==0){
            return null;
        }
        $fetched_bibliography = $ilDB->fetchObject($queryResult);
        $bibliography = new ilRecSysModelBibliography(
            $fetched_bibliography->bibl_id, 
            $fetched_bibliography->obj_id,
            $fetched_bibliography->difficulty, 
            $fetched_bibliography->rating_count,
            $fetched_bibliography->no_tags);
        return $bibliography;
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
    // ----------------------------------------------------------------------
    /**
     * functions that implement queries to the db
     */

    /**
     *  put a new Bibliography section in the table
     */
     public function createMaterialSection() {
        $this->ilDB->manipulateF("INSERT INTO ".self::MATERIALTABLENAME
                . "(bibl_id, obj_id, difficulty, rating_count, no_tags)"
                . " VALUES (%s,%s,%s,%s,%s)",
                array("integer", "integer", "double", "integer", "integer"),
                array($this->section_id, 
                      $this->obj_id,  
                      $this->difficulty,    
                      $this->rating_count,
                      $this->no_tags     
                    ));
    }

    /**
     *  update the attributes of the bibliography section
     */
    public function updateSectionDifficulty($new_difficulty, $new_rating_count) {
        $this->ilDB->manipulateF("UPDATE " .self::MATERIALTABLENAME
            ." SET"
                ." difficulty = %s"
                ." ,rating_count = %s"
            ." WHERE ".self::SECTIONIDNAME." = %s",
            array("double", "integer", "integer"),
            array($new_difficulty, $new_rating_count, $this->section_id)
        );
        $this->difficulty = $new_difficulty;
        $this->rating_count = $new_rating_count;
    }

    public function addNewRating($rating){
        $new_difficulty = $this->calculateDifficulty($rating);
        $this->updateSectionDifficulty($new_difficulty, ($this->getRatingCount() + 1));
    }

    /**
     * delete given bibliography section object
     */
    public function deleteSection() {
        $bibl_id = filter_var($this->section_id, FILTER_VALIDATE_INT);
        $this->ilDB->manipulateF("DELETE FROM " .self::MATERIALTABLENAME." WHERE ".self::SECTIONIDNAME." = %s",
            array("integer"),
            array($bibl_id));
    }

    // ----------------------------------------------------------------------
    /**
     * Setter and Getter
     */

    public function getMaterialType() {
        return self::MATERIALTABLENAME;
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

    public function getFromTo(){
        return null;
    }
}
?>