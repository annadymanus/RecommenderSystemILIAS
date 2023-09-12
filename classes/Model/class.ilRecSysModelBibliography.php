<?php
/**
 *  @author Joel Pflomm <joel.pflomm@students.uni-mannheim.de>
 * 
 *
 */

 class ilRecSysModelBibliography{


    var $ilDB;
    
    private $bibl_id;
    private $obj_id;
    private $difficulty;
    private $rating_count;

    //------------------------------------------------------------------
    public function __construct($bibl_id, $obj_id, $difficulty, $rating_count){
        // global definitions
        global $ilDB;
        $this->ilDB = $ilDB;
      
        // object definitions
        $this->bibl_id = $bibl_id;
        $this->obj_id = $obj_id;
        $this->difficulty = $difficulty;
        $this->rating_count = $rating_count;
    }

    public static function fetchByMaterialID($bibl_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_bib WHERE bibl_id = ".$ilDB->quote($bibl_id, "integer"));
        $fetched_bibliography = $ilDB->fetchObject($queryResult);
        $bibliography = new ilRecSysModelWeblink(
            $fetched_bibliography->bibl_id, 
            $fetched_bibliography->obj_id,
            $fetched_bibliography->difficulty, 
            $fetched_bibliography->rating_count);
        return $bibliography;
    }

    public static function fetchByObjID($obj_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_bib WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
        if($ilDB->numRows($queryResult)==0){
            return null;
        }
        $bibliography = array();
        while($fetched_bibliography = $ilDB->fetchObject($queryResult)){
            $bibliography = new ilRecSysModelWeblink(
                $fetched_bibliography->bibl_id, 
                $fetched_bibliography->obj_id,
                $fetched_bibliography->difficulty, 
                $fetched_bibliography->rating_count);
            $weblinks[] = $bibliography;
        }
        return $bibliography;
    }
    // ----------------------------------------------------------------------
    /**
     * functions that implement queries to the db
     */

    /**
     * get a bibliography element by its id, this is done by initializing the values of "this" object with the values stored in the table.
     */
    public function getBibliography($bibl_id){
        $queryResult = $this->ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_bib WHERE bibl_id = " . $this->ilDB->quote($bibl_id, "integer"));
        $bibliography = $this->ilDB->fetchObject($queryResult);
        $this->bibl_id = $bibliography->bibl_id;
        $this->obj_id = $bibliography->obj_id;
        $this->difficulty = $bibliography->difficulty;
        $this->rating_count = $bibliography->rating_count;
        return $this;
    }

    /**
     * put a new Bibliography element in the table
     */

     public function createBibliography() {
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_m_c_bib"
                . "(bibl_id, obj_id, difficulty, rating_count)"
                . " VALUES (%s,%s,%s,%s)",
                array("integer", "integer", "double", "integer"),
                array($this->bibl_id, 
                      $this->obj_id,  
                      $this->difficulty,    
                      $this->rating_count       
                    ));
    }

    /**
     *  update the attributes of the bibliography
     */
    public function update($difficulty, $rating_count) {
        $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_m_c_bib"
        ."SET"
            ." ,difficulty = %s"
            ." ,rating_count = %s"
        ." WHERE bibl_id = %s",
        array("double", "integer", "integer"),
        array($difficulty, $rating_count, $this->bibl_id)
    );
        $this->difficulty = $difficulty;
        $this->rating_count = $rating_count;
    }

    /**
     * delete Bibliography with the given $bibl_id
     */
    public function deleteBibliography() {
        // Validate and sanitize the input with the filter_var() function.
        $this->bibl_id = filter_var($this->bibl_id, FILTER_VALIDATE_INT);
        $this->ilDB->manipulateF("DELETE FROM ui_uihk_recsys_m_c_bib WHERE bibl_id = %s",
            array("integer"),
            array($this->bibl_id));
    }
    // ----------------------------------------------------------------------
   
    /**
     * Setter and Getter
     */
    public function getBiblId(){
        return $this->bibl_id;
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