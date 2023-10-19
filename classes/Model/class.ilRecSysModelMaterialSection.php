<?php

//@author Joel Pflomm
//@author Anna Eschbach-Dymanus


abstract class ilRecSysModelMaterialSection {
    const RATINGMIN = 1.0;
    const RATINGMAX = 5.0;
    
    var $ilDB;

    protected $section_id;
    protected $obj_id;
    protected $difficulty;
    protected $rating_count;
    protected $no_tags;

    // ----------------------------------------------------------
    protected function __construct($section_id, $obj_id, $difficulty, $rating_count, $no_tags){
        // global definitions
        global $ilDB;
        $this->ilDB = $ilDB;
      
        // object definitions
        $this->section_id = $section_id;
        $this->obj_id = $obj_id; //ilObject::_lookupObjectId();
        $this->difficulty = $difficulty;
        $this->rating_count = $rating_count;
        $this->no_tags = $no_tags;
    }

    // -----------------------------------------------------------------
    // abstract static and object functions

    abstract protected static function fetchByMaterialSectionID($section_id);
    abstract protected static function fetchByObjID($obj_id, array $from_to); // array might be empty as the from 
    // to attributes are only supported by a view of the material classes 
    abstract protected static function getLastMaterialSectionId();

    abstract protected function createMaterialSection();
    abstract protected function updateSectionDifficulty($difficulty, $rating_count);
    abstract protected function addNewRating($rating);
    abstract protected function deleteSection();

    // -----------------------------------------------------------------
    // abstract getter and setter functions

    abstract protected function getMaterialType();
    abstract protected function setNoTags($no_tags);

    // -----------------------------------------------------------------

    /**
     *  calculates the difficulty based on one new rating and old ratings of this section
     *  @return difficulty of the section
     */
    protected function calculateDifficulty($rating){
        return $this->difficulty = (($this->difficulty * ($this->rating_count)) + $rating) / ($this->rating_count + 1);
        // TODO: implement a more sofisticated difficulty calculation
    }

    // ----------------------------------------------------------------
    // getter and setter

    public function getSectionID(){
        return $this->section_id;
    }

    public function getObId() {
        return $this->obj_id;
    }

    public function getDifficulty(){
        return $this->difficulty;
    }

    public function getRatingCount() {
        return $this->rating_count;
    }

    public function isRatingValid($rating) {
        if($rating > self::RATINGMIN && $rating < self::RATINGMAX) {
            return true;
        } else {
            return false;
        }
    }

    public function incrementNoTags() {
        $this->setNoTags(($this->no_tags + 1));
    }

    public function decrementNoTags() {
        if(($this->no_tags) > 1){
            $this->setNoTags(($this->no_tags + 1));
        } else if(($this->no_tags) == 1) {
            $this->deleteSection();
        } else {
            throw new Exception("the weblink is not tagged and should have been deleted");
        }
    }

    public function getNoTags() {
        return $this->no_tags;
    }
}
