<?php

//@author Potoskueva Daria

class ilRecSysModelPicture {

    private $picture_id;
    private $obj_id;
    private $difficulty;
    private $rating_count;

    var $ilDB;

    //constructor
    public function __construct($picture_id, $obj_id, $difficulty, $rating_count) {
        global $ilDB;
        $this->ilDB = $ilDB;
        $this->picture_id = $picture_id;
        $this->obj_id = $obj_id;
        $this->difficulty = $difficulty;
        $this->rating_count = $rating_count;
    }

    public static function fetchByMaterialID($picture_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_pic WHERE picture_id = ".$ilDB->quote($picture_id, "integer"));
        $fetched_picture = $ilDB->fetchObject($queryResult);
        $picture = new ilRecSysModelPicture(
            $fetched_picture->picture_id, 
            $fetched_picture->obj_id,
            $fetched_picture->difficulty, 
            $fetched_picture->rating_count);
        return $picture;
    }

    public static function fetchByObjID($obj_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_pic WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
        if($ilDB->numRows($queryResult)==0){
            return null;
        }
        $pictures = array();
        while($fetched_picture = $ilDB->fetchObject($queryResult)){
            $picture = new ilRecSysModelPicture(
                $fetched_picture->picture_id, 
                $fetched_picture->obj_id,
                $fetched_picture->difficulty, 
                $fetched_picture->rating_count);
            $pictures[] = $picture;
        }
        return $pictures;
    }

        /**
     * get a picture element by its id, this is done by initializing the values of "this" object with the values stored in the table.
     */
    public function getPicture($picture_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_m_c_pic WHERE picture_id = ".$ilDB->quote($picture_id, "integer"));
        $fetched_picture = $ilDB->fetchObject($queryResult);
        $picture = new ilRecSysModelPicture(
            $fetched_picture->picture_id, 
            $fetched_picture->obj_id,
            $fetched_picture->difficulty, 
            $fetched_picture->rating_count);
        return $picture;
    }
    /**
     * put a new Picture element in the table
     */
    public function createPicture(){
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_m_c_pic"
                . "(picture_id, obj_id, difficulty, rating_count)"
                . " VALUES (%s,%s,%s,%s)",
                array("integer", "integer", "float", "integer"),
                array($this->picture_id, 
                      $this->obj_id,  
                      $this->difficulty,    
                      $this->rating_count       
                    ));
    }

    /**
     * update an existing Picture element in the table
     */

    public function updatePicture($difficulty, $rating_count){
        $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_m_c_pic SET "
                . "difficulty = %s, "
                . "rating_count = %s "
                . "WHERE picture_id = %s",
                array("float", "integer", "integer"),
                array($this->difficulty, 
                      $this->rating_count, 
                      $this->picture_id
                    ));
    }

    /**
     * delete an existing Picture element in the table
     */
    public function deletePicture(){
        $this->ilDB->manipulateF("DELETE FROM ui_uihk_recsys_m_c_pic WHERE picture_id = %s",
                array("integer"),
                array($this->picture_id));
    }

    // ----------------------------------------------------------------------
    /**
     * Setter and Getter
     */
    
    public function calculateDifficulty($rating){
        $this->difficulty = (($this->difficulty * ($this->rating_count - 1)) + $rating) / $this->rating_count;
        // TODO: implement a more sofisticated difficulty calculation
    }

    public function incrementRatingCount() {
        $this->rating_count++;
    }

    public function getPicture_id()
    {
        return $this->picture_id;
    }

    public function getObj_id()
    {
        return $this->obj_id;
    }

    public function getDifficulty()
    {
        return $this->difficulty;
    }

    public function getRating_count()
    {
        return $this->rating_count;
    }


}