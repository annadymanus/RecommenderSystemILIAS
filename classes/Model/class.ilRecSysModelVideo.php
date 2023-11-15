<?php

//@author Potoskueva Daria
//@author Joel Pflomm
class ilRecSysModelVideo extends ilRecSysModelMaterialSection{

    const MATERIALTABLENAME = "ui_uihk_recsys_m_s_f_v";
    const SECTIONIDNAME = "video_id";
    const MATERIALTYPE = 2;

    //attribute
    private $start_min;
    private $end_min;
    private $start_sec;
    private $end_sec;

    //-----------------------------------------------------------------------------------

    //constructor
    public function __construct($video_id, $obj_id, $start_min, $start_sec, $end_min, $end_sec, $difficulty, $rating_count, $no_tags) {
        parent::__construct($video_id, $obj_id, $difficulty, $rating_count, $no_tags);

        $this->$start_min = $start_min;
        $this->$end_min = $end_min;
        $this->$start_sec = $start_sec;
        $this->$end_sec = $end_sec;
    }

    public static function fetchByMaterialSectionID($video_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ".self::MATERIALTABLENAME." WHERE ".self::SECTIONIDNAME." = ".$ilDB->quote($video_id, "integer"));
        if($ilDB->numRows($queryResult)==0) {
            return null;
        }
        $fetched_video = $ilDB->fetchObject($queryResult);
        $video = new ilRecSysModelVideo(
            $fetched_video->video_id, 
            $fetched_video->obj_id,
            $fetched_video->start_min,
            $fetched_video->start_sec,
            $fetched_video->end_min,
            $fetched_video->end_sec,
            $fetched_video->difficulty, 
            $fetched_video->rating_count,
            $fetched_video->no_tags);
        return $video;
    }

    public static function parseFromTo($from_to){
        $from_min = explode(":", $from_to[0])[0];
        $from_sec = explode(":", $from_to[0])[1];
        $to_min = explode(":", $from_to[1])[0];
        $to_sec = explode(":", $from_to[1])[1];
        return array($from_min, $from_sec, $to_min, $to_sec);
    }

    public function getFromTo(){
        return array($this->start_min.":".$this->start_sec, $this->end_min.":".$this->end_sec);
    }

    public static function fetchByObjID($obj_id, $from_to){
        $parsed_time = ilRecSysModelVideo::parseFromTo($from_to);
        $start_min = $parsed_time[0];
        $start_sec = $parsed_time[1];
        $end_min = $parsed_time[2];
        $end_sec = $parsed_time[3];
        global $ilDB;
        if(sizeof($parsed_time) == 4){
            $queryResult = $ilDB->query("SELECT * FROM ".self::MATERIALTABLENAME." WHERE obj_id = ".$ilDB->quote($obj_id, "integer")." AND start_min == ".$ilDB->quote($start_min, "integer")." AND end_min == ".$ilDB->quote($end_min, "integer")." AND start_sec == ".$ilDB->quote($start_sec, "integer")." AND end_sec == ".$ilDB->quote($end_sec, "integer") );
        } else {
            throw new Exception("FromTo Attribute could not be parsed correctly: ".$from_to);
        }
        //if query is empty, return null
        if ($ilDB->numRows($queryResult) === 0) {
            return null;
        }
        $fetched_video = $ilDB->fetchObject($queryResult);
        $video = new ilRecSysModelVideo(
            $fetched_video->video_id, 
            $fetched_video->obj_id,
            $fetched_video->start_min,
            $fetched_video->start_sec,
            $fetched_video->end_min,
            $fetched_video->end_sec,
            $fetched_video->difficulty, 
            $fetched_video->rating_count,
            $fetched_video->no_tags);
        return $video;
    }

    public static function fetchAllSectionsWithObjID($obj_id) {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ".self::MATERIALTABLENAME." WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
        
        //if query is empty, return null
        if ($ilDB->numRows($queryResult) === 0) {
            return null;
        }
        $videos = array();
        while($fetched_video = $ilDB->fetchObject($queryResult)){
            $video = new ilRecSysModelVideo(
                $fetched_video->video_id, 
                $fetched_video->obj_id,
                $fetched_video->start_min,
                $fetched_video->start_sec,
                $fetched_video->end_min,
                $fetched_video->end_sec,
                $fetched_video->difficulty, 
                $fetched_video->rating_count,
                $fetched_video->no_tags);
            array_push($videos, $video);
        }
        return $videos;
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
     * put a new Video section in the table
     */
    public function createMaterialSection(){
        $this->ilDB->manipulateF("INSERT INTO ".self::MATERIALTABLENAME
                . "(video_id, obj_id, start_min, end_min, difficulty, rating_count, no_tags)"
                . " VALUES (%s,%s,%s,%s,%s,%s,%s)",
                array("integer", "integer", "integer", "integer","integer", "integer", "float", "integer", "integer"),
                array($this->section_id, 
                      $this->obj_id,
                      $this->start_min,
                      $this->start_sec,
                      $this->end_min, 
                      $this->end_sec,
                      $this->difficulty,          // difficulty
                      $this->rating_count,       // rating_count
                      $this->no_tags
                    ));
    }

    /**
     *  update the attributes of the video, given by its id
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
     *  update the start and end minute attributes of the video section
     */
    public function updateTimeInterval($start_min, $start_sec, $end_min, $end_sec) {
        $this->ilDB->manipulateF("UPDATE ".self::MATERIALTABLENAME
            ."SET"
            ." start_min = %s"
            ." ,start_sec = %s"
            ." ,end_min = %s"
            ." ,end_sec = %s"
            ." WHERE video_id = %s",
            array("integer", "integer", "integer", "integer", "integer"),
            array($start_min, $start_sec, $end_min, $end_sec, $this->section_id)
        );
        $this->start_min = $start_min;
        $this->end_min = $end_min;
        $this->start_sec = $start_sec;
        $this->end_sec = $end_sec;
    }

    public function addNewRating($rating){
        $new_difficulty = $this->calculateDifficulty($rating);
        $this->updateSectionDifficulty($new_difficulty, ($this->getRatingCount() + 1));
    }

    /**
     * delete Video with the given $video_id
     */
    public function deleteSection() {
        $video_id = filter_var($this->section_id, FILTER_VALIDATE_INT);
        $this->ilDB->manipulateF("DELETE FROM " .self::MATERIALTABLENAME." WHERE ".self::SECTIONIDNAME." = %s",
            array("integer"),
            array($video_id));
    }

    // ----------------------------------------------------------------------
    /**
     * Setter and Getter
     */

    public function getMaterialType () {
        return self::MATERIALTABLENAME;
    }

    public function getStart_sec() {
        return $this->start_sec;
    }

    public function getEnd_sec() {
        return $this->end_sec;
    }

    public function getStart_min() {
        return $this->start_min;
    }

    public function getEnd_min() {
        return $this->end_min;
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
