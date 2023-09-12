<?php

//@author Potoskueva Daria

class ilRecSysModelPresentation {


    private $presentation_id;
    private $obj_id;
    private $start_slide;
    private $end_slide;
    private $difficulty;
    private $rating_count;

    var $ilDB;

    //-----------------------------------------------------------------------------------

    //constructor
    public function __construct($presentation_id, $obj_id, $start_slide, $end_slide, $difficulty, $rating_count)
    {
        global $ilDB;
        $this->ilDB = $ilDB;
        $this->$presentation_id = $presentation_id;   // TODO: figure  out whether we can put a counter in here and make it thread safe, so that the id is unique.
        $this->$obj_id = $obj_id; //needed to be find out
        $this->$start_slide = $start_slide;
        $this->$end_slide = $end_slide;
        $this->$difficulty = $difficulty;
        $this->$rating_count = $rating_count;

    }

    public static function fetchByMaterialID($presentation_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_f_p WHERE presentation_id = ".$ilDB->quote($presentation_id, "integer"));
        $fetched_presentation = $ilDB->fetchObject($queryResult);
        $presentation = new ilRecSysModelPresentation(
            $fetched_presentation->presentation_id, 
            $fetched_presentation->obj_id,
            $fetched_presentation->start_slide,
            $fetched_presentation->end_slide,
            $fetched_presentation->difficulty, 
            $fetched_presentation->rating_count);
        return $presentation;
    }

    public static function fetchByObjID($obj_id, $from=null, $to=null){
        global $ilDB;
        if($from != null && $to != null){
            $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_f_p WHERE obj_id = ".$ilDB->quote($obj_id, "integer")." AND start_slide == ".$ilDB->quote($from, "integer")." AND end_slide == ".$ilDB->quote($to, "integer"));
        }
        else if($from == null && $to == null){
            $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_f_p WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
        }
        else{
            throw new Exception("Either both from and to have to be null or both have to be set");
        }
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
                $fetched_presentation->rating_count);
            $presentations[] = $presentation;
        }
        return $presentations;
    }
    

    // --------------------------------------------------------------
    /**
     * functions that implement queries to the db
     */

    
    /**
     * get a presentation element by its id, this is done by initializing the values of "this" object with the values stored in the table.
     */
    public function getPresentation($presentation_id){
        $queryResult = $this->ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_f_p WHERE presentation_id = " . $this->ilDB->quote($presentation_id, "integer"));
        $presentation = $this->ilDB->fetchObject($queryResult);
        $this->presentation_id = $presentation->presentation_id;
        $this->obj_id = $presentation->obj_id;
        $this->start_slide = $presentation->start_slide;
        $this->end_slide = $presentation->end_slide;
        $this->difficulty = $presentation->difficulty;
        $this->rating_count = $presentation->rating_count;
        return $this;
    }

    /**
     * put a new Presentation element in the table
     */

     public function createPresentation() {
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_m_c_f_p"
                . "(presentation_id, obj_id, start_slide, end_slide, difficulty, rating_count)"
                . " VALUES (%s,%s,%s,%s,%s,%s)",
                array("integer", "integer", "integer", "integer", "float", "integer"),
                array($this->presentation_id, 
                      $this->obj_id,
                      $this->start_slide,
                      $this->end_slide,  
                      $this->difficulty,          // difficulty
                      $this->rating_count       // rating_count
                    ));
    }

    /**
     *  update the attributes of the presentation, give by its id
     */
    public function update($start_slide, $end_slide, $difficulty, $rating_count) {
        $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_m_c_f_p"
        ."SET"
            ." start_slide = %s"
            ." ,end_slide = %s"
            ." ,difficulty = %s"
            ." ,rating_count = %s"
        ." WHERE presentation_id = %s",
    array("integer", "integer", "float", "integer", "integer"),
    array($start_slide, $end_slide, $difficulty, $rating_count, $this->presentation_id)
    );
    }

    /**
     * delete presentation with the given $presentation_id
     */
    public function deletePresentation($presentation_id)
    {   
        // Validate and sanitize the input with filter_var
        $presentation_id = filter_var($presentation_id, FILTER_SANITIZE_NUMBER_INT);
        $this->ilDB->manipulateF("DELETE FROM ui_uihk_recsys_m_c_f_p WHERE presentation_id = %s",
                array("integer"),
                array($presentation_id));
    }

    // ----------------------------------------------------------------------
    /**
     * Setter and Getter
     */

    public function getPresentation_id()
    {
        return $this->presentation_id;
    }

    public function getObj_id()
    {
        return $this->obj_id;
    }

    public function getStart_slide()
    {
        return $this->start_slide;
    }

    public function getEnd_slide()
    {
        return $this->end_slide;
    }

    public function getDifficulty()
    {
        return $this->difficulty;
    }

    public function getRating_count()
    {
        return $this->rating_count;
    }

    public function calculateDifficulty($rating){
        $this->difficulty = (($this->difficulty * ($this->rating_count - 1)) + $rating) / $this->rating_count;
        // TODO: implement a more sofisticated difficulty calculation
    }

    /**
     * rating_count counts the users that have given a rating
     * this function increments the rating_count of the given object by 1
     */
    public function incrementRatingCount() {
        $this->rating_count++;
    }

}