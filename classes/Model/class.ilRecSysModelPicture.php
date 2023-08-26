<?php

//@author Potoskueva Daria

class ilRecSysModelPicture {

    private $picture_id;
    private $obj_id;
    private $difficulty;
    private $rating_count;

    //private $ilDB;

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

    public function setPicture_id($picture_id)
    {
        $this->picture_id = $picture_id;
    }

    public function setObj_id($obj_id)
    {
        $this->obj_id = $obj_id;
    }

    public function setDifficulty($difficulty)
    {
        $this->difficulty = $difficulty;
    }

    public function setRating_count($rating_count)
    {
        $this->rating_count = $rating_count;
    }


}