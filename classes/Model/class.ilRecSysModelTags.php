<?php

//@author Potoskueva Daria

class ilRecSysModelTags {

    //default values
    const TAG_DEFAULT_COUNT = 0;

    private $tag_id;
    private $tag_name;
    private $crs_id;
    private $tag_description;
    private $tag_count;
   
    
    private $ilDB;

    // -----------------------------------------------------------------------------------
    // constructor

    public function __construct($tag_id, $tag_name, $crs_id, $tag_description, $tag_count)
    {
        global $ilDB;
        $this->ilDB = $ilDB;
        $this->tag_id = $tag_id; //check if it's possible to use directly the getNextTagId() function
        $this->tag_name = $tag_name;
        $this->crs_id = $crs_id;
        $this->tag_description = $tag_description;
        $this->tag_count = $tag_count;
    }

    // -----------------------------------------------------------------------------------
    // static functions to get tag ids

    public static function fetchTagByName($tag_name, $crs_id)
    {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_tags WHERE tag_name = ".$ilDB->quote($tag_name, "text") 
            . "AND crs_id = " .$ilDB->quote($crs_id, "integer"));
        if($ilDB->numRows($queryResult) === 0){
            return null;
        }
        $tag = $ilDB->fetchAssoc($queryResult);
        $tag_id = $tag['tag_id'];
        $tag_name = $tag['tag_name'];
        $crs_id = $tag['crs_id'];
        $tag_description = $tag['tag_description'];
        $tag_count = $tag['tag_count'];
        return new ilRecSysModelTags($tag_id, $tag_name, $crs_id, $tag_description, $tag_count);
    }

    public static function fetchTagById($tag_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_tags WHERE tag_id = ".$ilDB->quote($tag_id, "integer"));
        if($ilDB->numRows($queryResult) === 0){
            return null;
        }
        $tag = $ilDB->fetchAssoc($queryResult);
        $tag_id = $tag['tag_id'];
        $tag_name = $tag['tag_name'];
        $crs_id = $tag['crs_id'];
        $tag_description = $tag['tag_description'];
        $tag_count= $tag['tag_count'];
        return new ilRecSysModelTags($tag_id, $tag_name, $crs_id, $tag_description, $tag_count);
    }

    public static function fetchAllTagIDsForCourse($crs_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT tag_id FROM ui_uihk_recsys_tags WHERE crs_id = ".$ilDB->quote($crs_id, "integer"));
        $tag_ids = array();
        while($tag = $ilDB->fetchAssoc($queryResult)){
            $tag_ids[] = $tag['tag_id'];
        }
        return $tag_ids;
    }

    public static function checkIfNameExists($name, $crs_id) {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_tags WHERE tag_name = ".$ilDB->quote($name, "text") 
            . "AND crs_id = " .$ilDB->quote($crs_id, "integer"));
        if($ilDB->numRows($queryResult) === 0){
            return false;
        }
        else { 
            return true;
        }
    }

    public static function getLastTagId() {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT tag_id FROM ui_uihk_recsys_tags ORDER BY tag_id DESC LIMIT 1");
        if ($ilDB->numRows($queryResult) === 0) {
            $last_tag_id = 0;
        } else {
            $last_tag_id = $ilDB->fetchAssoc($queryResult);
            $last_tag_id = $last_tag_id['tag_id'];
        }
        return $last_tag_id;
    }

    // -----------------------------------------------------------------------------------
    // create function

    //function for adding a new tag to a tag table
    public function addNewTag()
    {   
        //checks whether Name was already Given to a Tag
        if(self::checkIfNameExists($this->tag_name, $this->crs_id)){
            throw new Exception("The given tag name is already assigned.");
        }
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_tags"
                . "(tag_id, tag_name, crs_id, tag_description, tag_occurence)"
                . " VALUES (%s,%s,%s,%s, %s)",
                array("integer", "text", "integer", "text", "integer"),
                array($this->tag_id,
                      $this->tag_name,
                      $this->crs_id, 
                      $this->tag_description, 
                      self::TAG_DEFAULT_COUNT
                    ));
    }

    // -----------------------------------------------------------------------------------
    // update functions
    public function updateTag($tag_description, $tag_count) {
        $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_tags"
                ." SET"
                ." tag_description = %s"
                ." , tag_count = %s"
                ." WHERE tag_id = %s",
            array("text", "integer", "integer"),
            array($tag_description, $tag_count, $this->tag_id)
        );
        $this->tag_description = $tag_description;
        $this->tag_count = $tag_count;
    }

    public function incrementCount() {
        $this->tag_count++;
        $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_tags"
                ." SET"
                ." tag_count = %s"
                ." WHERE tag_id = %s",
            array("text", "integer"),
            array($this->tag_count, $this->tag_id)
        );
    }

    public function decrementCount() {
        $this->tag_count--;
        $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_tags"
                ."SET"
                ." tag_count = %s"
                ." WHERE tag_id = %s",
            array("text", "integer"),
            array($this->tag_count, $this->tag_id)
        );
    }

    //-----------------------------------------------------------------------------------
    // delete
    public function deleteTag(){
    // Validate and sanitize the input with the filter_var() function.
    $this->tag_id = filter_var($this->tag_id, FILTER_VALIDATE_INT);
    $this->ilDB->manipulateF("DELETE FROM ui_uihk_recsys_tags WHERE tag_id = %s",
        array("integer"),
        array($this->tag_id));
    }

    // -----------------------------------------------------------------------------------
    // simple getter 

    public function getTag_id()
    {
        return $this->tag_id;
    }

    public function getTag_name()
    {
        return $this->tag_name;
    }

    public function getCrs_ID(){
        return $this->crs_id;
    }

    public function getTag_description()
    {
        return $this->tag_description;
    }

    public function getTag_count()
    {
        return $this->tag_count;
    }
}