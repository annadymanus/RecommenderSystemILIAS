<?php
/**
 * @author Joel Pflomm <joel.pflomm@students.uni-mannheim.de>
 * 
 *
 */

 class ilRecSysModelWeblink extends ilRecSysModelMaterialSection {

    const MATERIALTABLENAME = "ui_uihk_recsys_m_s_w";
    const SECTIONIDNAME = "weblink_id";
    const MATERIALTYPE = 4;

    // --------------------------------------------------------

    public function __construct($weblink_id, $obj_id, $difficulty, $rating_count, $no_tags, $teach_diff) {
        parent::__construct($weblink_id, $obj_id, $difficulty, $rating_count, $no_tags, $teach_diff);
    }

    public static function fetchByMaterialSectionID($weblink_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ".self::MATERIALTABLENAME." WHERE ".self::SECTIONIDNAME." = ".$ilDB->quote($weblink_id, "integer"));
        if($ilDB->numRows($queryResult)==0) {
            return null;
        }
        $fetched_weblink = $ilDB->fetchObject($queryResult);
        $weblink = new ilRecSysModelWeblink(
            $fetched_weblink->weblink_id, 
            $fetched_weblink->obj_id,
            $fetched_weblink->difficulty,
            $fetched_weblink->rating_count,
            $fetched_weblink->no_tags,
            $fetched_weblink->teach_diff
        );
        return $weblink;
    }

    public static function fetchByObjID($obj_id, $from_to) {
        global $ilDB;
        if(empty($from_to)){
            $queryResult = $ilDB->query("SELECT * FROM ".self::MATERIALTABLENAME." WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
        } else {
            throw new Exception("The weblink material section does not implement all given variables.");
        }
        if($ilDB->numRows($queryResult)==0) {
            return null;
        }
        $fetched_weblink = $ilDB->fetchObject($queryResult);
        $weblink = new ilRecSysModelWeblink(
            $fetched_weblink->weblink_id, 
            $fetched_weblink->obj_id,
            $fetched_weblink->difficulty, 
            $fetched_weblink->rating_count,
            $fetched_weblink->no_tags,
            $fetched_weblink->teach_diff
        );
        return $weblink;
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
     *  put a new Weblink section in the table
     */
    public function createMaterialSection() {
        $this->ilDB->manipulateF("INSERT INTO ".self::MATERIALTABLENAME
                . "(weblink_id, obj_id, difficulty, rating_count, no_tags, teach_diff)"
                . " VALUES (%s,%s,%s,%s,%s,%s)",
                array("integer", "integer", "double", "integer", "integer", "double"),
                array($this->section_id, 
                      $this->obj_id,  
                      $this->difficulty,    
                      $this->rating_count,
                      $this->no_tags,
                      $this->teach_diff       
                    ));
    }

    /**
     *  update the difficulty of the weblink section
     */
    public function updateSectionDifficulty($new_difficulty, $new_rating_count) {
        $this->ilDB->manipulateF("UPDATE " .self::MATERIALTABLENAME
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

    public function addNewRating($rating){
        $new_difficulty = $this->calculateDifficulty($rating);
        $this->updateSectionDifficulty($new_difficulty, ($this->getRatingCount() + 1));
    }

    public function setTeacherDifficulty($new_teach_diff)
    {
        $this->ilDB->manipulateF("UPDATE " .self::MATERIALTABLENAME
            ." SET"
                ." teach_diff = %s"
            ." WHERE ".self::SECTIONIDNAME." = %s",
            array("double", "integer"),
            array($new_teach_diff, $this->section_id)
        );
        $this->teach_diff;
    }

    /**
     * delete given weblink section object
     */
    public function deleteSection() {
        $weblink_id = filter_var($this->section_id, FILTER_VALIDATE_INT);
        $this->ilDB->manipulateF("DELETE FROM " .self::MATERIALTABLENAME." WHERE ".self::SECTIONIDNAME." = %s",
            array("integer"),
            array($weblink_id));
    }

    // ----------------------------------------------------------------------
    /**
     * Setter and Getter
     */

    public function getMaterialType() {
        return self::MATERIALTYPE;
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
