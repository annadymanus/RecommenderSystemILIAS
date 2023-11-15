<?php
/**
 * @author Joel Pflomm <joel.pflomm@students.uni-mannheim.de>
 * 
 * Note: subtask_no is currently unused in the frontend. Possible future use.
 */

 class ilRecSysModelExercise extends ilRecSysModelMaterialSection{

    const MATERIALTABLENAME = "ui_uihk_recsys_m_s_e";
    const SECTIONIDNAME = "exercise_id";
    const MATERIALTYPE = 6;
    

    // attributes
    private $task_no;
    private $subtask_no;

    // ----------------------------------------------------------
    
    public function __construct($exercise_id, $obj_id, $task_no, $subtask_no, $difficulty, $rating_count, $no_tags) {
        parent::__construct($exercise_id, $obj_id, $difficulty, $rating_count, $no_tags);
        
        $this->task_no = $task_no;
        $this->subtask_no = $subtask_no;
    }

    public static function fetchByMaterialSectionID($exercise_id) {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ".self::MATERIALTABLENAME." WHERE ".self::SECTIONIDNAME." = " . $ilDB->quote($exercise_id, "integer"));
        if($ilDB->numRows($queryResult)==0) {
            return null;
        }
        $fetched_exercise = $ilDB->fetchObject($queryResult);
        $exercise = new ilRecSysModelExercise(
            $fetched_exercise->exercise_id,
            $fetched_exercise->obj_id,
            $fetched_exercise->task_no,
            $fetched_exercise->subtask_no,
            $fetched_exercise->difficulty,
            $fetched_exercise->rating_count,
            $fetched_exercise->no_tags);
        return $exercise;
    }

    public static function fetchByObjID($obj_id, $task_subtask){
        global $ilDB;
        if(sizeof($task_subtask) == 2){
            $queryResult = $ilDB->query("SELECT * FROM ".self::MATERIALTABLENAME." WHERE obj_id = ".$ilDB->quote($obj_id, "integer")." AND task_no = ".$ilDB->quote($task_subtask[0], "integer")." AND subtask_no = ".$ilDB->quote($task_subtask[1], "integer") );
        }
        else{
            throw new Exception("Both task and subtask have to be defined for this material_type");        }  
        if($ilDB->numRows($queryResult)==0){
            return null;
        }
        $fetched_exercise = $ilDB->fetchObject($queryResult);
        $exercise = new ilRecSysModelExercise(
            $fetched_exercise->exercise_id,
            $fetched_exercise->obj_id,
            $fetched_exercise->task_no,
            $fetched_exercise->subtask_no,
            $fetched_exercise->difficulty,
            $fetched_exercise->rating_count,
            $fetched_exercise->no_tags);
        return $exercise;
    }

    public static function fetchAllSectionsWithObjID($obj_id) {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ".self::MATERIALTABLENAME." WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
        
        //if query is empty, return null
        if ($ilDB->numRows($queryResult) === 0) {
            return null;
        }
        $exercises = array();
        while($fetched_exercise = $ilDB->fetchObject($queryResult)){
            $exercise = new ilRecSysModelExercise(
                $fetched_exercise->exercise_id, 
                $fetched_exercise->obj_id,
                $fetched_exercise->task_no,
                $fetched_exercise->subtask_no,
                $fetched_exercise->difficulty, 
                $fetched_exercise->rating_count,
                $fetched_exercise->no_tags);
            array_push($exercises, $exercise);
        }
        return $exercises;
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

    // -------------------------------------------------------------------------
    /**
     *  functions that implement queries to the db
     */

    /**
     *  put a new Exercise section in the table
     */
    public function createMaterialSection(){
        $this->ilDB->manipulateF("INSERT INTO ".self::MATERIALTABLENAME
                . "(exercise_id, obj_id, task_no, subtask_no, difficulty, rating_count, no_tags)"
                . " VALUES (%s,%s,%s,%s,%s,%s,%s)",
                array("integer", "integer", "integer", "integer", "double", "integer", "integer"),
                array($this->section_id, 
                      $this->obj_id,  
                      $this->task_no,
                      $this->subtask_no,
                      $this->difficulty,    
                      $this->rating_count,
                      $this->no_tags       
                    ));
    }

    /**
     *  update the difficulty of the exercise section
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
     *  update the attributes of the exercise section
     */
    public function updateTask($task_no, $subtask_no) {
        $this->ilDB->manipulateF("UPDATE ".self::MATERIALTABLENAME
            ." SET"
            ." task_no = %s,"
            ." subtask_no = %s"
            ." WHERE ".self::SECTIONIDNAME." = %s",
            array("integer", "integer", "integer"),
            array($task_no, $subtask_no, $this->section_id)
        );
        $this->task_no = $task_no;
        $this->subtask_no = $subtask_no;
    }

    public function addNewRating($rating){
        $new_difficulty = $this->calculateDifficulty($rating);
        $this->updateSectionDifficulty($new_difficulty, ($this->getRatingCount() + 1));
    }

    /**
     *  delete given exercise section object
     */
    public function deleteSection() {
        $exercise_id = filter_var($this->section_id, FILTER_VALIDATE_INT);
        $this->ilDB->manipulateF("DELETE FROM " .self::MATERIALTABLENAME." WHERE ".self::SECTIONIDNAME." = %s",
            array("integer"),
            array($exercise_id));
    }

    public function getFromTo(){
        return array($this->task_no, $this->task_no);
    }


    // ----------------------------------------------------------------------------------------------
    /**
     * Setter and Getter
     */

    public function getMaterialType() {
        return self::MATERIALTYPE;
    }

    public function getTaskNo() {
        return $this->task_no;
    }

    public function getSubtaskNo() {
        return $this->subtask_no;
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
?>