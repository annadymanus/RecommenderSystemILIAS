<?php

//@author Potoskueva Daria

class ilRecSysModelTags {

    //default values
    const TAG_DEFAULT_OCCURENCE = 1;

    private $tag_id;
    private $tag_name;
    private $tag_description;
    private $tag_occurence;
    private $next_tag_id;
    //tags per user
    private $usr_id;
    private $priority;
    private $tag_counter;
   
    
    private $ilDB;

    //-----------------------------------------------------------------------------------
    //Tag table (ui_uihk_recsys_tags)

    //constructor
    public function __construct($tag_id, $tag_name, $tag_description, $tag_occurence)
    {
        global $ilDB;
        $this->ilDB = $ilDB;
        $this->tag_id = $tag_id; //check if it's possible to use directly the getNextTagId() function
        $this->tag_name = $tag_name;
        $this->tag_description = $tag_description;
        $this->tag_occurence = $tag_occurence;
    }

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

    //function for getting a tag
    public function getTag()
    {
        global $ilDB;
        $queryResult = $this->ilDB->query("SELECT * FROM ui_uihk_recsys_tags WHERE tag_id = ".$this->$ilDB->quote($this->tag_id, "integer"));
        $tag = $this->ilDB->fetchAssoc($queryResult);
        $this->tag_id = $tag->tag_id;
        $this->tag_name = $tag->tag_name;
        $this->tag_description = $tag->tag_description;
        $this->tag_occurence = $tag->tag_occurence;
        return $this;
    }


    //function for adding a new tag to a tag table
    public function addNewTag()
    {   
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_tags"
                . "(tag_id, tag_name, tag_description, tag_occurence)"
                . " VALUES (%s,%s,%s,%s)",
                array("integer", "text", "text", "integer"),
                array(self::getNextTagId(), 
                      $this->tag_name, 
                      $this->tag_description, 
                      self::TAG_DEFAULT_OCCURENCE
                    ));
    }


    //function for generation of tag_id by using the last tag_id in the table
    public static function getNextTagId() {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT tag_id FROM ui_uihk_recsys_tags ORDER BY tag_id DESC LIMIT 1");
        if ($ilDB->numRows($queryResult) === 0) {
            $next_tag_id = 1;
        } else {
            $last_tag_row = $queryResult->fetchAssoc();
            $next_tag_id = $last_tag_row->tag_id + 1;
        }
        return $next_tag_id;
    }
    //-----------------------------------------------------------------------------------




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

    public function getTag_counter()
    {
        return $this->tag_counter;
    }

    public function setTag_id($tag_id)
    {
        $this->tag_id = $tag_id;
    }

    public function setTag_name($tag_name)
    {
        $this->tag_name = $tag_name;
    }

    public function setTag_description($tag_description)
    {
        $this->tag_description = $tag_description;
    }

    public function setTag_occurence($tag_occurence)
    {
        $this->tag_occurence = $tag_occurence;
    }


    public function setUsr_id($usr_id)
    {
        $this->usr_id = $usr_id;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function setTag_counter($tag_counter)
    {
        $this->tag_counter = $tag_counter;
    }
    














}