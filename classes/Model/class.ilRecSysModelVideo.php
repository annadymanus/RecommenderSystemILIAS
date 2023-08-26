<?php

//@author Potoskueva Daria

class ilRecSysModelVideo {

    private static $instance;
    private static $uniquecounter;

    private $video_id;
    private $obj_id;
    private $start_min;
    private $end_min;
    private $difficulty;
    private $rating_count;

    var $ilDB;

    //-----------------------------------------------------------------------------------

    //constructor
    private function __construct($video_id, $obj_id, $start_min, $end_min, $difficulty, $rating_count)
    {
        global $ilDB;
        $this->ilDB = $ilDB;
        $this->$video_id = $video_id;// TODO: figure  out whether we can put a counter in here and make it thread safe, so that the id is unique.
        $this->$obj_id = $obj_id; //needed to be find out
        $this->$start_min = $start_min;
        $this->$end_min = $end_min;
        $this->$difficulty = $difficulty;
        $this->$rating_count = $rating_count;

    }


    public static function getInstance() {
        if (self::$instance === null) {
            // set instance to latest Video stored in Table;
            self::$uniquecounter = self::getLastVideoId();
            // see wether there is a latest Video stored in the Table
            if(self::$uniquecounter == 0){
                // initialize instance with a first dummy object not yet inserted
                self::$instance = new self(self::$uniquecounter, 0, 0, 0, 0, 0);
            } else {
                // initialize everything with the last Video that was stored inside the database
                $video = self::getVideoById(self::$uniquecounter);
                self::$instance = new self($video->video_id, $video->obj_id,$video->start_min, $video->end_min, $video->difficulty, $video->rating_count);
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
     * class function that gets the lastVideoId-attribute from the table ui_uihk_recsys_m_c_f_v
     */
    private static function getLastVideoId() {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT video_id FROM ui_uihk_recsys_m_c_f_v ORDER BY video_id DESC LIMIT 1");
        if ($ilDB->numRows($queryResult) === 0) {
            $last_video_id = 0;
        } else {
            $last_video_id = $ilDB->fetchAssoc($queryResult);
            $last_video_id = $last_video_id['video_id'];
        }
        return $last_video_id;
    }

        /**
     * class function that gets the last Video object from the table ui_uihk_recsys_m_c_f_v
     */
    public static function getVideoById($video_id) {
        $queryResult = self::$ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_f_v WHERE video_id = ".self::$ilDB->quote($video_id, "integer"));
        $video = self::$ilDB->fetchObject($queryResult);
        return $video;
    }

        /**
     * class function that increments the unique counter of the class. This is done to produce unique ids for the ui_uihk_recsys_m_c_f_v table
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
     * get a video element by its id, this is done by initializing the values of "this" object with the values stored in the table.
     */
    public function getVideo($video_id){
        $queryResult = $this->ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_f_v WHERE video_id = " . $this->ilDB->quote($video_id, "integer"));
        $video = $this->ilDB->fetchObject($queryResult);
        $this->video_id = $video->video_id;
        $this->obj_id = $video->obj_id;
        $this->start_min = $video->start_min;
        $this->end_min = $video->end_min;
        $this->difficulty = $video->difficulty;
        $this->rating_count = $video->rating_count;
        return $this;
    }

    /**
     * put a new Video element in the table
     */

    public function createWeblink($obj_id, $start_min, $end_min){
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_m_c_f_v"
                . "(video_id, obj_id, start_min, end_min, difficulty, rating_count)"
                . " VALUES (%s,%s,%s,%s,%s,%s)",
                array("integer", "integer", "timestamp", "timestamp", "float", "integer"),
                array(self::incrementUniquecounter(), 
                      $obj_id,
                      $start_min,
                      $end_min,  
                      0.0,          // difficulty
                      0       // rating_count
                    ));
    }

    /**
     *  update the attributes of the video, give by its id
     */
    public function updateWeblink($video_id, $start_min, $end_min, $difficulty, $rating_count) {
        $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_m_c_f_v"
        ."SET"
            ." start_min = %s"
            ." ,end_min = %s"
            ." ,difficulty = %s"
            ." ,rating_count = %s"
        ." WHERE video_id = %s",
    array("timestamp", "timestamp", "float", "integer", "integer"),
    array($start_min, $end_min, $difficulty, $rating_count, $video_id)
    );
    }

    /**
     * delete Video with the given $video_id
     */
    public function deleteVideo($video_id) {
        // Validate and sanitize the input with the filter_var() function.
        $video_id = filter_var($video_id, FILTER_VALIDATE_INT);
        $this->ilDB->manipulateF("DELETE FROM ui_uihk_recsys_m_c_f_v WHERE video_id = %s",
            array("integer"),
            array($video_id));
    }

    // ----------------------------------------------------------------------

    /**
     * Setter and Getter
     */

    public function getVideo_id()
    {
        return $this->video_id;
    }

    public function getObj_id()
    {
        return $this->obj_id;
    }

    public function getStart_min()
    {
        return $this->start_min;
    }

    public function getEnd_min()
    {
        return $this->end_min;
    }

    public function calculateDifficulty($rating){
        $this->difficulty = (($this->difficulty * ($this->rating_count - 1)) + $rating) / $this->rating_count;
        // TODO: implement a more sofisticated difficulty calculation
    }

    public function getDifficulty()
    {
        return $this->difficulty;
    }

    public function getRating_count()
    {
        return $this->rating_count;
    }

    public function setStart_min($start_min)
    {
        $this->start_min = $start_min;
    }

    public function setEnd_min($end_min)
    {
        $this->end_min = $end_min;
    }

    /**
     * rating_count counts the users that have given a rating
     * this function increments the rating_count of the given object by 1
     */
    public function incrementRatingCount() {
        $this->rating_count++;
    }


}