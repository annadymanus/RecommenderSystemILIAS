<?php

//@author Potoskueva Daria
//not sure about manipulations with $text

class ilRecSysModelFeedback {
     
    const FEEDBACK_LOW      = 1;
    const FEEDBACK_MEDIUM   = 2;
    const FEEDBACK_HIGH     = 3;
    
    private $feed_id;
    private $lo_id;//what it does?
    private $usr_id;
    private $crs_id;
    private $rating;
    private $lastupdate;
    private $text;

    var $ilDB;
    
    // ----------------------------------------------------------------
    
    public function __construct()
    {
    }
    
    // ----------------------------------------------------------------
    
    public static function getLearningObjectiveFeedbackById($feed_id) {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_feedback WHERE feed_id=".$ilDB->quote($feed_id, "integer")." ;");
        $row = $ilDB->fetchObject($queryResult);
        
        $lof = new ilRecSysModelFeedback();
        $lof->setFeed_id($feed_id);
        $lof->setLo_id($row->lo_id);
        $lof->setUsr_id($row->usr_id);
        $lof->setCrs_id($row->crs_id);
        $lof->setRating($row->rating);   
        $lof->setLastUpdate($row->lastupdate);
        $lof->setText($row->text);      
        
        return $lof;
    }
    
    
    public static function getLearningObjectiveFeedback($lo_id, $usr_id, $crs_id) {
        global $ilDB;        
        $query = "SELECT * FROM ui_uihk_recsys_feedback"
            ." WHERE 1=1"
                ." AND lo_id=".$ilDB->quote($lo_id, "integer")
                ." AND usr_id=".$ilDB->quote($usr_id, "integer")
                ." AND crs_id=".$ilDB->quote($crs_id, "integer")
                ." ;";
        $queryResult = $ilDB->query($query);
        $row = $ilDB->fetchObject($queryResult);
        
        $lof = new ilRecSysModelFeedback();
        $lof->setFeed_id($row->feed_id);
        $lof->setLo_id($row->lo_id);
        $lof->setUsr_id($row->usr_id);        
        $lof->setCrs_id($row->crs_id);
        $lof->setRating($row->rating);
        $lof->setLastUpdate($row->lastupdate);
        $lof->setText($row->text);
        
        return $lof;
    }
    
    
    public static function createOrUpdateFeedback($lo_id, $usr_id, $crs_id, $rating) {        
        if( self::exists($lo_id, $usr_id, $crs_id) ) {            
            $lof = self::getLearningObjectiveFeedback($lo_id, $usr_id, $crs_id); 
            $lof->setRating($rating);
            $lof->update();
        } else {            
            $lof = new ilRecSysModelFeedback();
            $lof->setLo_id($lo_id);
            $lof->setUsr_id($usr_id);
            $lof->setCrs_id($crs_id);
            $lof->setRating($rating);
            $lof->create();
        }
    }

    
    public static function exists($lo_id, $usr_id, $crs_id) {
        global $ilDB;
        $query = "SELECT count(*) AS 'count' FROM ui_uihk_recsys_feedback"
                    ." WHERE 1=1"
                        ." AND lo_id=".$ilDB->quote($lo_id, "integer")
                        ." AND usr_id=".$ilDB->quote($usr_id, "integer")
                        ." AND crs_id=".$ilDB->quote($crs_id, "integer")
                        ." ;";        
        $queryResult = $ilDB->query($query);
        $row = $ilDB->fetchObject($queryResult);
        
        if ($row->count == 0 ) {
            return false;
        } else {
            return true;
        }
    }
    
    public static function getLearningObjectiveFeedbacks($lo_id, $crs_id) {
        global $ilDB;
        $query = "SELECT * FROM ui_uihk_recsys_feedback"
            ." WHERE 1=1"
                ." AND lo_id=".$ilDB->quote($lo_id, "integer")
                ." AND crs_id=".$ilDB->quote($crs_id, "integer")
                ." ;";
        $queryResult = $ilDB->query($query);
        $rows = $ilDB->fetchAll($queryResult);        
        return $rows;
    }
    
    /**
     * Returns an array of aggregated feedbacks to the given learning objective and course
     * 
     * @param int $lo_id
     * @param int $crs_id
     * @return array[int, int, int] (low, medium, high)
     */
    public static function getLearningObjectiveFeedBacksSummary($lo_id, $crs_id) {       
        $result = array(
            'low'   => 0,
            'medium'=> 0,
            'high'  => 0
        );
        $feedbacks = self::getLearningObjectiveFeedbacks($lo_id, $crs_id);
        
        foreach ($feedbacks as $feedback) {            
            switch ($feedback['rating']) {
                case self::FEEDBACK_LOW:
                    $result['low'] = $result['low'] + 1;
                    break;
                case self::FEEDBACK_MEDIUM:
                    $result['medium'] = $result['medium'] + 1;
                    break;
                case self::FEEDBACK_HIGH:
                    $result['high'] = $result['high'] + 1;
                    break;            
            }
        }
        
        return $result;
    }
    
    // -----------------------------------------------------------------------------------
    
    private function delete() {
        $this->remove();
    }
    
    // ------------------------------------------------------------------------------------
    
    private function create() {        
        global $ilDB;
        $query = "INSERT INTO ui_uihk_recsys_feedback ("
                    ." feed_id"
                    .", lo_id"
                    .", usr_id"
                    .", crs_id"
                    .", rating"
                    .", lastupdate"
                    .", text)"
                ." VALUES (%s,%s,%s,%s,%s,%s,%s);";
        $args = array(
                    "integer", 
                    "integer", 
                    "integer", 
                    "integer", 
                    "integer", 
                    "integer",
                    "text");
        $values = array(
                    $ilDB->nextID('ui_uihk_recsys_feedback'),
                    $this->lo_id,
                    $this->usr_id,
                    $this->crs_id,
                    $this->rating,
                    time() );        
        $ilDB->manipulateF($query, $args, $values);           
    }
    
    
    private function update() {
        global $ilDB;
        $query = "UPDATE ui_uihk_recsys_feedback"
               ." SET rating=%s"
                   .", lastupdate=%s" 
               ." WHERE feed_id=%s;";
        $args = array(
            "integer",
            "integer", 
            "integer");
        $values = array(
            $this->rating, 
            time(), 
            $this->feed_id);        
        $ilDB->manipulateF($query, $args, $values);
    }
    
    
    private function remove() {
        $this->ilDB->manipulateF("DELETE FROM ui_uihk_recsys_feedback WHERE feed_id=%s;",
            array("integer"),
            array($this->feed_id) );
    }
    
    // ----------------------------------------------------------------------------------------
    
    public function getFeed_id()
    {
        return $this->feed_id;
    }
    public function getLo_id()
    {
        return $this->lo_id;
    }
    public function getUsr_id()
    {
        return $this->usr_id;
    }
    public function getCrs_id()
    {
        return $this->crs_id;
    }
    public function getLastUpdate()
    {
        return $this->lastupdate;
    }
    public function getText()
    {
        return $this->text;
    }
    public function setFeed_id($feed_id)
    {
        $this->feed_id = $feed_id;
    }
    public function setLo_id($lo_id)
    {
        $this->lo_id = $lo_id;
    }

    public function setUsr_id($usr_id)
    {
        $this->usr_id = $usr_id;
    }
    public function setCrs_id($crs_id)
    {
        $this->crs_id = $crs_id;
    }
    public function setLastUpdate($lastupdate)
    {
        $this->lastupdate = $lastupdate;
    }
    public function getRating()
    {
        return $this->rating;
    }
    public function setRating($rating)
    {
        $this->rating = $rating;
    }
    public function setText($text)
    {
        $this->text = $text;
    }
}
