<?php

//@author Potoskueva Daria

class ilRecSysModelPresentation {

    private static $instance;
    private static $uniquecounter;


    private $presentation_id;
    private $obj_id;
    private $start_slide;
    private $end_slide;
    private $difficulty;
    private $rating_count;

    var $ilDB;

    //-----------------------------------------------------------------------------------

    //constructor
    private function __construct($presentation_id, $obj_id, $start_slide, $end_slide, $difficulty, $rating_count)
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

    public static function getInstance() {
        if (self::$instance === null) {
            // set instance to latest Presentation stored in Table;
            self::$uniquecounter = self::getLastPresentationId();
            // see wether there is a latest Presentation stored in the Table
            if(self::$uniquecounter == 0){
                // initialize instance with a first dummy object not yet inserted
                self::$instance = new self(self::$uniquecounter, 0, 0, 0, 0, 0);
            } else {
                // initialize everything with the last Presentation that was stored inside the database
                $presentation = self::getPresentationById(self::$uniquecounter);
                self::$instance = new self($presentation->presentation_id, $presentation->obj_id, $presentation->start_slide, $presentation->end_slide, $presentation->difficulty, $presentation->rating_count);
            }
        }
        return self::$instance;
    }
    private function clone() {
        // Private clone method to prevent cloning of the instance
    }

    private function __wakeup() {
        // Private wakeup method to prevent unserialization of the instance
    }

    // --------------------------------------------------------------

        
    /**
     * class function that gets the lastWeblinkId-attribute from the table ui_uihk_recsys_m_c_w
     */
    private static function getLastPresentationId() {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT presentation_id FROM ui_uihk_recsys_m_c_f_p ORDER BY presentation_id DESC LIMIT 1");
        if ($ilDB->numRows($queryResult) === 0) {
            $last_presentation_id = 0;
        } else {
            $last_presentation_id = $ilDB->fetchAssoc($queryResult);
            $last_presentation_id = $last_presentation_id['presentation_id'];
        }
        return $last_presentation_id;
    }

    
    /**
     * class function that gets the last Presentation object from the table ui_uihk_recsys_m_c_f_p
     */
    public static function getPresentationById($presentation_id) {
        $queryResult = self::$ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_f_p WHERE presentation_id = ".self::$ilDB->quote($presentation_id, "integer"));
        $weblink = self::$ilDB->fetchObject($queryResult);
        return $weblink;
    }

    /**
     * class function that increments the unique counter of the class. This is done to produce unique ids for the ui_uihk_recsys_m_c_f_p table
     */
    public static function incrementUniquecounter() {
        self::$uniquecounter ++;
        return self::$uniquecounter;
    }

    // ----------------------------------------------------------------------
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

     public function createPresentation($obj_id, $start_slide, $end_slide){
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_m_c_f_p"
                . "(presentation_id, obj_id, start_slide, end_slide, difficulty, rating_count)"
                . " VALUES (%s,%s,%s,%s,%s,%s)",
                array("integer", "integer", "integer", "integer", "float", "integer"),
                array(self::incrementUniquecounter(), 
                      $obj_id,
                      $start_slide,
                      $end_slide,  
                      0.0,          // difficulty
                      0       // rating_count
                    ));
    }

    /**
     *  update the attributes of the presentation, give by its id
     */
    public function updateWeblink($presentation_id, $start_slide, $end_slide, $difficulty, $rating_count) {
        $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_m_c_f_p"
        ."SET"
            ." start_slide = %s"
            ." ,end_slide = %s"
            ." ,difficulty = %s"
            ." ,rating_count = %s"
        ." WHERE presentation_id = %s",
    array("integer", "integer", "float", "integer", "integer"),
    array($start_slide, $end_slide, $difficulty, $rating_count, $presentation_id)
    );
    }

    /**
     * delete Weblink with the given $presentation_id
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