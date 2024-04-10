<?php

class MLOutput {
    public int $usr_id;
    public int $crs_id;
    public array $predictions; // An array of Prediction objects

    public function __construct(int $usr_id, int $crs_id, array $predictions) {
        $this->usr_id = $usr_id;
        $this->crs_id = $crs_id;
        $this->predictions = $predictions;
    }
}

class Prediction {
    public $section_id;
    public $material_type;
    public $score;

    public function __construct($section_id, $material_type, $score) {
        $this->section_id = $section_id;
        $this->material_type = $material_type;
        $this->score = $score;
    }
}

?>