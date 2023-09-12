<?php

//@author Potoskueva Daria

class ilRecSysModelVideo {

    private $video_id;
    private $obj_id;
    private $start_min;
    private $end_min;
    private $difficulty;
    private $rating_count;

    var $ilDB;

    //-----------------------------------------------------------------------------------

    //constructor
    public function __construct($video_id, $obj_id, $start_min, $end_min, $difficulty, $rating_count)
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

    public static function fetchByMaterialID($video_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_f_v WHERE video_id = ".$ilDB->quote($video_id, "integer"));
        $fetched_video = $ilDB->fetchObject($queryResult);
        $video = new ilRecSysModelVideo(
            $fetched_video->video_id, 
            $fetched_video->obj_id,
            $fetched_video->start_min,
            $fetched_video->end_min,
            $fetched_video->difficulty, 
            $fetched_video->rating_count);
        return $video;
    }

    public static function fetchByObjID($obj_id, $from=null, $to=null){
        global $ilDB;
        if($from != null && $to != null){
            $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_f_v WHERE obj_id = ".$ilDB->quote($obj_id, "integer")." AND start_min == ".$ilDB->quote($from, "integer")." AND end_min == ".$ilDB->quote($to, "integer"));
        }
        else if($from == null && $to == null){
            //get all video_entries for the given obj_id
            $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_f_v WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
        }
        else{
            throw new Exception("Either both from and to have to be null or both have to be set");
        }
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
                $fetched_video->end_min,
                $fetched_video->difficulty, 
                $fetched_video->rating_count);
            $videos[] = $video;
        }
        return $videos;
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

    public function createVideo(){
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_m_c_f_v"
                . "(video_id, obj_id, start_min, end_min, difficulty, rating_count)"
                . " VALUES (%s,%s,%s,%s,%s,%s)",
                array("integer", "integer", "timestamp", "timestamp", "float", "integer"),
                array($this->video_id, 
                      $this->obj_id,
                      $this->start_min,
                      $this->end_min,  
                      $this->difficulty,          // difficulty
                      $this->rating_count       // rating_count
                    ));
    }

    /**
     *  update the attributes of the video, give by its id
     */
    public function update($video_id, $start_min, $end_min, $difficulty, $rating_count) {
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