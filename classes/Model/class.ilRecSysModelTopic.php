<?php

//@author Potoskueva Daria

class ilRecSysModelTopic {

    private $topic_id;
    private $usr_id;
    private $crs_id;
    private $priority;
    private $progress;
    private $difficulty;
    private $title;
    private $text;
    private $startdate;
    private $enddate;
    private $materials;


    private $ilDB;


    /*the function below checks whether topics for the course exist
    public static function existRecSysCourseTopics($crs_id) {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT * FROM ui_uihk_recsys_courses WHERE crs_id = ".$ilDB->quote($crs_id, "integer"));        
        if ($ilDB->numRows($queryResult) > 1) {
            return true;
        } else {
            return false;
        }

    }
    */

    /* THIS IS A DRAFT
    private function ReadTopicStatistic($topic_id) 
    {
        $queryResult = $this->ilDB->query("SELECT * FROM ui_uihk_recsys_topics WHERE topic_id = ".$this->ilDB->quote($this->topic_id, "integer"));
        $topic = $this->ilDB->fetchObject($queryResult);
        $this->il_crs_id              = $course->il_crs_id;
        $this->crs_id                 = $course->crs_id;
        $this->crs_status             = $course->crs_status;
        $this->mod_tracking           = $course->mod_tracking ;
        $this->mod_lo                 = $course->mod_lo;
        $this->mod_ig                 = $course->mod_ig;
        //$this->mod_ig_default         = $course->mod_ig_default;
        $this->mod_recommendations    = $course->mod_recommendations;
        $this->opt_default            = $course->opt_default;
        $this->opt_out                = $course->opt_out;
        $this->opt_anonym             = $course->opt_anonym;
        $this->opt_active             = $course->opt_active;
        return $this;
    }
    */

    //the functions to get and to set the values
    public function getTopic_id()
    {
        return $this->topic_id;
    }

    public function getUsr_id()
    {
        return $this->usr_id;
    }

    public function getCrs_id()
    {
        return $this->crs_id;
    }
    
    public function getPriority()
    {
        return $this->priority;
    }
    
    public function getProgress()
    {
        return $this->progress;
    }

    public function getDifficulty()
    {
        return $this->difficulty;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getStartdate()
    {
        return $this->startdate;
    }

    public function getEnddate()
    {
        return $this->enddate;
    }
    
    public function getMaterials()
    {
        return $this->materials;
    }
    
    public function setTopic_id($topic_id)
    {
        $this->topic_id = $topic_id;
    }

    public function setUsr_id($usr_id)
    {
        $this->usr_id = $usr_id;
    }

    public function setCrs_id($crs_id)
    {
        $this->crs_id = $crs_id;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function setProgress($progress)
    {
        $this->progress = $progress;
    }

    public function setDifficulty($difficulty)
    {
        $this->difficulty = $difficulty;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function setStartdate($startdate)
    {
        $this->startdate = $startdate;
    }

    public function setEnddate($enddate)
    {
        $this->enddate = $enddate;
    }

    public function setMaterials($materials)
    {
        $this->materials = $materials;
    }
}
