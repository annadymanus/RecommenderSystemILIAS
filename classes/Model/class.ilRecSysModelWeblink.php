<?php
/**
 * @author Joel Pflomm <joel.pflomm@students.uni-mannheim.de>
 * 
 *
 */

 class ilRecSysModelWeblink {
  
    var $ilDB;
   
    private $weblink_id;
    private $obj_id;
    private $difficulty;
    private $rating_count;

    // --------------------------------------------------------

    public function __construct($weblink_id, $obj_id, $difficulty, $rating_count) {
        // global definitions
        global $ilDB;
        $this->ilDB = $ilDB;
      
        // object definitions
        $this->weblink_id = $weblink_id;
        $this->obj_id = $obj_id;
        $this->difficulty = $difficulty;
        $this->rating_count = $rating_count;
    }

    public static function fetchByMaterialID($weblink_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_w WHERE weblink_id = ".$ilDB->quote($weblink_id, "integer"));
        $fetched_weblink = $ilDB->fetchObject($queryResult);
        $weblink = new ilRecSysModelWeblink(
            $fetched_weblink->weblink_id, 
            $fetched_weblink->obj_id,
            $fetched_weblink->difficulty, 
            $fetched_weblink->rating_count);
        return $weblink;
    }

    public static function fetchByObjID($obj_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_w WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
        if($ilDB->numRows($queryResult)==0){
            return null;
        }
        $weblinks = array();
        while($fetched_weblink = $ilDB->fetchObject($queryResult)){
            $weblink = new ilRecSysModelWeblink(
                $fetched_weblink->weblink_id, 
                $fetched_weblink->obj_id,
                $fetched_weblink->difficulty, 
                $fetched_weblink->rating_count);
            $weblinks[] = $weblink;
        }
        return $weblinks;
    }
    // ----------------------------------------------------------------------
    /**
     * functions that implement queries to the db
     */

    /**
     * get a weblink element by its id, this is done by initializing the values of "this" object with the values stored in the table.
     */
    public function getWeblink($weblink_id){
        $queryResult = $this->ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_w WHERE weblink_id = " . $this->ilDB->quote($weblink_id, "integer"));
        $weblink = $this->ilDB->fetchObject($queryResult);
        $this->weblink_id = $weblink->weblink_id;
        $this->obj_id = $weblink->obj_id;
        $this->difficulty = $weblink->difficulty;
        $this->rating_count = $weblink->rating_count;
        return $this;
    }

    /**
     * put a new Weblink element in the table
     */

    public function createWeblink() {
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_m_c_w"
                . "(weblink_id, obj_id, difficulty, rating_count)"
                . " VALUES (%s,%s,%s,%s)",
                array("integer", "integer", "double", "integer"),
                array($this->weblink_id, 
                      $this->obj_id,  
                      $this->difficulty,    
                      $this->rating_count       
                    ));
    }

    /**
     *  update the attributes of the weblink
     */
    public function update($difficulty, $rating_count) {
        $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_m_c_w"
        ."SET"
            ." ,difficulty = %s"
            ." ,rating_count = %s"
        ." WHERE weblink_id = %s",
        array("double", "integer", "integer"),
        array($difficulty, $rating_count, $this->weblink_id)
    );
        $this->difficulty = $difficulty;
        $this->rating_count = $rating_count;
    }

    /**
     * delete Weblink with the given $weblink_id
     */
    public function deleteWeblink() {
        // Validate and sanitize the input with the filter_var() function.
        $this->weblink_id = filter_var($this->weblink_id, FILTER_VALIDATE_INT);
        $this->ilDB->manipulateF("DELETE FROM ui_uihk_recsys_m_c_w WHERE weblink_id = %s",
            array("integer"),
            array($this->weblink_id));
    }

    // ----------------------------------------------------------------------
    /**
     * Setter and Getter
     */
    public function getWeblinkId(){
        return $this->weblink_id;
    }

    public function getObId() {
        return $this->obj_id;
    }

    public function calculateDifficulty($rating){
        $this->difficulty = (($this->difficulty * ($this->rating_count - 1)) + $rating) / $this->rating_count;
        // TODO: implement a more sofisticated difficulty calculation
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
