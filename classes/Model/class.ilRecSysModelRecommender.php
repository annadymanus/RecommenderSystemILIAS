<?php

//@author Eschbach-Dymanus Anna
//Recommendations are made individually for each student. Use topics for cold-starting problems.
class ilRecSysModelRecommender{
    var $ilDB;
    private $crs_id;
    private $student;
    private $usr_id; //user id of the student
    private $material_type;
    private $material_id;
    private $tag_id;
    private $timestamp;
    private $clicked_material_type;
    private $clicked_material_id;
    private $timestamp_clicked;

    
    public function __construct($usr_id, $crs_id)
    {
        global $ilDB;
        $this->ilDB = $ilDB;
        $this->crs_id = $crs_id;
        //$this->student = ilRecSysModelStudent::getRecSysStudent($usr_id, $crs_id);
    }

    private function getUniqueTags($mattype_section_tuples){
        $tag_ids = array();
        foreach($mattype_section_tuples as $mattype_section){
            $tag_ids = array_merge($tag_ids, ilRecSysModelTagsPerSection::getAllTagIdsForSection($mattype_section[1], $mattype_section[0]));
        }
        $tag_ids = array_unique($tag_ids);
        $tag_ids = array_filter($tag_ids);
        return $tag_ids;
    }

    public function getTagOnlyRecommend($mattype_section_tuples){
        $tag_ids = $this->getUniqueTags($mattype_section_tuples);
        $materials = array();
        foreach($tag_ids as $tag_id){
            $matching_mats = ilRecSysModelTagsPerSection::getAllSectionIDsForTag($tag_id);
            $materials = array_merge($materials, $matching_mats);
        }
        $materials = array_unique($materials);
        $materials = array_filter($materials);
        $materials = array_diff($materials, $mattype_section_tuples); //dont recommend the things the student wants recommendation for
        return $materials;
    }

    public function getChosenMaterials($usr_id, $material_type, $material_id, $tag_id) {
        global $ilDB;
        $query = "SELECT * FROM ui_uihk_recsys_u_r_a_c WHERE usr_id = %s AND material_type = %s AND material_id = %s AND tag_id = %s";
        $result = $ilDB->queryF(
            $query,
            array(
                'integer',
                'integer', 
                'integer',
                'integer'
            ),
            array(
                $usr_id,
                $material_type,
                $material_id,
                $tag_id
            )
        );
    
        // Check if the query was successful
        if ($result->numRows() > 0) {

            while ($row = $ilDB->fetchAssoc($result)) {
                $data[] = $row;
            }
    
            return $data;
        } else {
            return false;
        }
    }

    public function getChosenMaterialsForUser($usr_id) {
        global $ilDB;
        $query = "SELECT * FROM ui_uihk_recsys_u_r_a_c WHERE usr_id = %s";
        $result = $ilDB->queryF(
            $query,
            array(
                'integer'
            ),
            array(
                $usr_id
            )
        );
    
        // Check if the query was successful
        if ($result->numRows() > 0) {

            while ($row = $ilDB->fetchAssoc($result)) {
                $data[] = $row;
            }
    
            return $data;
        } else {
            return false;
        }
    }
    
    public function getChosenMaterialsForUserAndTag($usr_id, $tag_id) {
        global $ilDB;
        $query = "SELECT * FROM ui_uihk_recsys_u_r_a_c WHERE usr_id = %s AND tag_id = %s";
        $result = $ilDB->queryF(
            $query,
            array(
                'integer',
                'integer'
            ),
            array(
                $usr_id,
                $tag_id
            )
        );
    
        // Check if the query was successful
        if ($result->numRows() > 0) {

            while ($row = $ilDB->fetchAssoc($result)) {
                $data[] = $row;
            }
    
            return $data;
        } else {
            return false;
        }
    }

    public function setRecord() {
        global $ilDB;

        $data = array(
            'usr_id' => $this->usr_id,
            'material_type' => $this->material_type,
            'material_id' => $this->material_id,
            'tag_id' => $this->tag_id,
            'timestamp' => $this->timestamp,
            'clicked_material_type' => $this->clicked_material_type,
            'clicked_material_id' => $this->clicked_material_id,
            'timestamp_clicked' => $this->timestamp_clicked
        );

        $ilDB->insert('ui_uihk_recsys_u_r_a_c', $data);
    }

}






?>