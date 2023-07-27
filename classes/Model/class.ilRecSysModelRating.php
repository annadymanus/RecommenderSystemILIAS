<?php
class ilRecSysModelRating {
     
    const RATING_LOW      = 1;
    const RATING_MEDIUM   = 2;
    const RATING_HIGH     = 3;
    
    private $rating_id;
    private $lo_id;
    private $usr_id;
    private $crs_id;
    private $rating;
    private $lastupdate;

    var $ilDB;
    
    // ----------------------------------------------------------------
    
    public function __construct()
    {
    }
    
    // ----------------------------------------------------------------
    
    public static function getLearningObjectiveRatingById($rating_id) {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_ratings WHERE rating_id=".$ilDB->quote($rating_id, "integer")." ;");
        $row = $ilDB->fetchObject($queryResult);
        
        $lor = new ilRecSysModelRating();
        $lor->setRating_id($rating_id);
        $lor->setLo_id($row->lo_id);
        $lor->setUsr_id($row->usr_id);
        $lor->setCrs_id($row->crs_id);
        $lor->setRating($row->rating);   
        $lor->setLastUpdate($row->lastupdate);      
        
        return $lor;
    }
    
    
    public static function getLearningObjectiveRating($lo_id, $usr_id, $crs_id) {
        global $ilDB;        
        $query = "SELECT * FROM ui_uihk_recsys_ratings"
            ." WHERE 1=1"
                ." AND lo_id=".$ilDB->quote($lo_id, "integer")
                ." AND usr_id=".$ilDB->quote($usr_id, "integer")
                ." AND crs_id=".$ilDB->quote($crs_id, "integer")
                ." ;";
        $queryResult = $ilDB->query($query);
        $row = $ilDB->fetchObject($queryResult);
        
        $lor = new ilRecSysModelRating();
        $lor->setRating_id($row->rating_id);
        $lor->setLo_id($row->lo_id);
        $lor->setUsr_id($row->usr_id);        
        $lor->setCrs_id($row->crs_id);
        $lor->setRating($row->rating);
        $lor->setLastUpdate($row->lastupdate);
        
        return $lor;
    }
    
    
    public static function createOrUpdateRating($lo_id, $usr_id, $crs_id, $rating) {        
        if( ilRecSysModelRating::exists($lo_id, $usr_id, $crs_id) ) {            
            $lor = self::getLearningObjectiveRating($lo_id, $usr_id, $crs_id); 
            $lor->setRating($rating);
            $lor->update();
        } else {            
            $lor = new ilRecSysModelRating();
            $lor->setLo_id($lo_id);
            $lor->setUsr_id($usr_id);
            $lor->setCrs_id($crs_id);
            $lor->setRating($rating);
            $lor->create();
        }
    }

    
    public static function exists($lo_id, $usr_id, $crs_id) {
        global $ilDB;
        $query = "SELECT count(*) AS 'count' FROM ui_uihk_recsys_ratings"
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
    
    public static function getLearningObjectiveRatings($lo_id, $crs_id) {
        global $ilDB;
        $query = "SELECT * FROM ui_uihk_recsys_ratings"
            ." WHERE 1=1"
                ." AND lo_id=".$ilDB->quote($lo_id, "integer")
                ." AND crs_id=".$ilDB->quote($crs_id, "integer")
                ." ;";      
        $queryResult = $ilDB->query($query);
        $rows = $ilDB->fetchAll($queryResult);        
        return $rows;
    }
    
    /**
     * Returns an array of aggregated ratings to the given learning objective and course
     * 
     * @param int $lo_id
     * @param int $crs_id
     * @return array[int, int, int] (low, medium, high)
     */
    public static function getLearningObjectiveRatingsSummary($lo_id, $crs_id) {       
        $result = array(
            'low'   => 0,
            'medium'=> 0,
            'high'  => 0
        );
        $ratings = self::getLearningObjectiveRatings($lo_id, $crs_id);
        
        foreach ($ratings as $rating) {            
            switch ($rating['rating']) {
                case self::RATING_LOW:
                    $result['low'] = $result['low'] + 1;
                    break;
                case self::RATING_MEDIUM:
                    $result['medium'] = $result['medium'] + 1;
                    break;
                case self::RATING_HIGH:
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
        $query = "INSERT INTO ui_uihk_recsys_ratings ("
                    ." rating_id"
                    .", lo_id"
                    .", usr_id"
                    .", crs_id"
                    .", rating"
                    .", lastupdate)"
                ." VALUES (%s,%s,%s,%s,%s,%s);";
        $args = array(
                    "integer", 
                    "integer", 
                    "integer", 
                    "integer", 
                    "integer", 
                    "integer");
        $values = array(
                    $ilDB->nextID('ui_uihk_recsys_ratings'),
                    $this->lo_id,
                    $this->usr_id,
                    $this->crs_id,
                    $this->rating,
                    time() );        
        $ilDB->manipulateF($query, $args, $values);           
    }
    
    
    private function update() {
        global $ilDB;
        $query = "UPDATE ui_uihk_recsys_ratings"
               ." SET rating=%s"
                   .", lastupdate=%s" 
               ." WHERE rating_id=%s;";
        $args = array(
            "integer",
            "integer", 
            "integer");
        $values = array(
            $this->rating, 
            time(), 
            $this->rating_id);        
        $ilDB->manipulateF($query, $args, $values);
    }
    
    
    private function remove() {
        $this->ilDB->manipulateF("DELETE FROM ui_uihk_recsys_ratings WHERE rating_id=%s;",
            array("integer"),
            array($this->rating_id) );
    }
    
    // ----------------------------------------------------------------------------------------
    
    public function getRating_id()
    {
        return $this->rating_id;
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
    public function setRating_id($rating_id)
    {
        $this->rating_id = $rating_id;
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
}
