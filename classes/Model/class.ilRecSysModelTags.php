<?php

//@author Potoskueva Daria

class ilRecSysModelTags {

    //default values
    const TAG_DEFAULT_OCCURENCE = 1;

    private $tag_id;
    private $tag_name;
    private $tag_description;
    private $tag_occurence;

    //tags per user
    private $usr_id;
    private $priority;
   
    
    private $ilDB;

    // -----------------------------------------------------------------------------------
    // constructor

    public function __construct($tag_id, $tag_name, $tag_description, $tag_occurence)
    {
        global $ilDB;
        $this->ilDB = $ilDB;
        $this->tag_id = $tag_id; //check if it's possible to use directly the getNextTagId() function
        $this->tag_name = $tag_name;
        $this->tag_description = $tag_description;
        $this->tag_occurence = $tag_occurence;
    }

    // -----------------------------------------------------------------------------------
    // static functions to get tag ids

    public static function fetchTagByName($tag_name)
    {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_tags WHERE tag_name = ".$ilDB->quote($tag_name, "text"));
        if($ilDB->numRows($queryResult) === 0){
            return null;
        }
        $tag = $ilDB->fetchAssoc($queryResult);
        $tag_id = $tag['tag_id'];
        $tag_name = $tag['tag_name'];
        $tag_description = $tag['tag_description'];
        $tag_occurence = $tag['tag_occurence'];
        return new ilRecSysModelTags($tag_id, $tag_name, $tag_description, $tag_occurence);
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
        $tag_description = $tag['tag_description'];
        $tag_occurence = $tag['tag_occurence'];
        return new ilRecSysModelTags($tag_id, $tag_name, $tag_description, $tag_occurence);
    }

    public static function fetchAllTagNames(){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT tag_name FROM ui_uihk_recsys_tags");
        $tag_names = array();
        while($tag = $ilDB->fetchAssoc($queryResult)){
            $tag_names[] = $tag['tag_name'];
        }
        return $tag_names;
    }

    // -----------------------------------------------------------------------------------
    // create function

    //function for adding a new tag to a tag table
    public function addNewTag()
    {   
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_tags"
                . "(tag_id, tag_name, tag_description, tag_occurence)"
                . " VALUES (%s,%s,%s,%s)",
                array("integer", "text", "text", "integer"),
                array($this->tag_id,
                      $this->tag_name, 
                      $this->tag_description, 
                      self::TAG_DEFAULT_OCCURENCE
                    ));
    }

    // -----------------------------------------------------------------------------------
    // update functions
    public function updateTag($tag_description, $tag_occurence) {
        $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_tags"
                ." SET"
                ." ,description = %s"
                ." ,occurence = %s"
                ." WHERE tag_id = %s",
            array("text", "integer", "integer"),
            array($tag_description, $tag_occurence, $this->tag_id)
        );
        $this->tag_description = $tag_description;
        $this->tag_occurence = $tag_occurence;
    }

    public function incrementOccurrence() {
        $this->tag_occurence++;
        $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_tags"
                ." SET"
                ." ,occurence = %s"
                ." WHERE tag_id = %s",
            array("text", "integer"),
            array($this->tag_occurence, $this->tag_id)
        );
    }

    public function decrementOccurrence() {
        $this->tag_occurence--;
        $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_tags"
                ."SET"
                ." ,occurence = %s"
                ." WHERE tag_id = %s",
            array("text", "integer"),
            array($this->tag_occurence, $this->tag_id)
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

    public function getTag_description()
    {
        return $this->tag_description;
    }

    public function getTag_occurence()
    {
        return $this->tag_occurence;
    }

    public function getUsr_id()
    {
        return $this->usr_id;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setUsr_id($usr_id)
    {
        $this->usr_id = $usr_id;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }
}