<?php
/**
 * @author Joel Pflomm <joel.pflomm@students.uni-mannheim.de>
 * 
 *
 */

 class ilRecSysModelExercise{
    
    var $ilDB;

    private $exercise_id;
    private $obj_id;
    private $task_no;
    private $subtask_no;
    private $difficulty;
    private $rating_count;

    // ----------------------------------------------------------
    public function __construct($exercise_id, $obj_id, $task_no, $subtask_no, $difficulty, $rating_count) {
        // global definitions
        global $ilDB;
        $this->ilDB = $ilDB;

        //object definitions
        $this->exercise_id = $exercise_id;
        $this->obj_id = $obj_id;
        $this->task_no = $task_no;
        $this->subtask_no = $subtask_no;
        $this->difficulty = $difficulty;
        $this->rating_count = $rating_count;
    }

    public static function fetchByMaterialID($exercise_id) {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_a_e WHERE exercise_id = " . $ilDB->quote($exercise_id, "integer"));
        $fetched_exercise = $ilDB->fetchObject($queryResult);
        $exercise = new ilRecSysModelExercise(
            $fetched_exercise->exercise_id,
            $fetched_exercise->obj_id,
            $fetched_exercise->task_no,
            $fetched_exercise->subtask_no,
            $fetched_exercise->difficulty,
            $fetched_exercise->rating_count);
        return $exercise;
    }

    public static function fetchByObjID($obj_id, $task_no=null){
        global $ilDB;
        if($task_no != null){
            $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_a_e WHERE obj_id = " . $ilDB->quote($obj_id, "integer")." AND task_no = ".$ilDB->quote($task_no, "integer"));
        }
        else{
            $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_a_e WHERE obj_id = " . $ilDB->quote($obj_id, "integer"));
        }  
        if($ilDB->numRows($queryResult)==0){
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
                $fetched_exercise->rating_count);
            $exercises[] = $exercise;
        }
        return $exercises;
    }
    // -------------------------------------------------------------------------
    /**
     *  functions that implement queries to the db
     */

    /**
     *  get a exercise element by its id, this is done by initializing the values of "this" object with the values stored in the table.
     */
    public function getExercise($exercise_id) {
        $queryResult = $this->ilDB->query("SELECT * FROM ui_uihk_recsys_m_a_e WHERE exercise_id = " . $this->ilDB->quote($exercise_id, "integer"));
        $result = $this->ilDB->fetchObject($queryResult);
        $this->exercise_id = $result->exercise_id;
        $this->obj_id = $result->obj_id;
        $this->task_no = $result->task_no;
        $this->subtask_no = $result->subtask_no;
        $this->difficulty = $result->difficulty;
        $this->rating_count = $result->rating_count;
        return $this;
    }

    /**
     *  put a new Exercise element in the table
     */
    public function createExercise(){
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_m_a_e"
                . "(exercise_id, obj_id, task_no, subtask_no, difficulty, rating_count)"
                . " VALUES (%s,%s,%s,%s,%s,%s)",
                array("integer", "integer", "integer", "integer", "double", "integer"),
                array($this->exercise_id, 
                      $this->obj_id,  
                      $this->task_no,
                      $this->subtask_no,
                      $this->difficulty,    
                      $this->rating_count       
                    ));
    }

    /**
     *  update the attributes of the given exercise object
     */
    public function update($task_no, $subtask_no, $difficulty, $rating_count) {
        $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_m_a_e"
        ."SET"
            ." , task_no = %s"
            ." , subtask_no = %s"
            ." ,difficulty = %s"
            ." ,rating_count = %s"
        ." WHERE exercise_id = %s",
        array("integer", "integer", "double", "integer", "integer"),
        array($task_no, $subtask_no, $difficulty, $rating_count, $this->exercise_id)
    );
        $this->task_no = $task_no;
        $this->subtask_no = $subtask_no;
        $this->difficulty = $difficulty;
        $this->rating_count = $rating_count;
    }

    /**
     *  delete Exercise object using its given $exercise_id
     */
    public function deleteExercise() {
        // Validate and sanitize the input with the filter_var() fucntion.
        $this->exercise_id = filter_var($this->exercise_id, FILTER_VALIDATE_INT);
        $this->ilDB->manipulateF("DELETE FROM ui_uihk_recsys_m_a_e WHERE exercise_id = %s", 
            array("integer"),
            array($this->exercise_id));
    }


    // ----------------------------------------------------------------------------------------------
    /**
     * Setter and Getter
     */
    public function getExerciseId() {
        return $this->exercise_id;
    }

    public function getObId() {
        return $this->obj_id;
    }

    public function getTaskNo() {
        return $this->task_no;
    }

    public function getSubtaskNo() {
        return $this->subtask_no;
    }

    public function getDifficulty(){
        return $this->difficulty;
    }

    /**
     * rating_count counts the users that have given a rating
     * this function increments the rating_count of the given object by 1
     */
    public function incrementRatingCount() {
        $this->rating_count++;
    }

    public function getRatingCount() {
        $this->rating_count;
    }
 }
?>