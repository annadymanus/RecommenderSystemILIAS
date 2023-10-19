<?php
/**
 * @author Dasha
 * @author Joel Pflomm <joel.pflomm@students.uni-mannheim.de>
 * 
 *
 */
class ilRecSysModelTagsPerSection{

    private $tag_id;
    private $material_type;
    private $section_id;


    var $ilDB;

    //-----------------------------------------------------------------------------------

    //constructor
    public function __construct($tag_id, $material_type, $section_id)
    {
        global $ilDB;
        $this->ilDB = $ilDB;

        $this->tag_id = $tag_id;
        $this->material_type = $material_type;
        $this->section_id = $section_id;
    }

    
    public static function fetchTagsPerSection($section_id, $tag_id, $material_type)
    {
        global $ilDB;
        $result = $ilDB->queryF("SELECT * FROM ui_uihk_recsys_t_p_s WHERE section_id = %s AND tag_id = %s AND material_type = %s",
                array("integer", "integer", "integer"),
                array($section_id, $tag_id, $material_type));
        if($ilDB->numRows($result) == 0){
            return null;
        }
        $row = $ilDB->fetchAssoc($result);
        $tag = new ilRecSysModelTagsPerSection($row['tag_id'], $row['material_type'], $row['section_id']);
        return $tag;
    }

    //written by @Anna Eschbach-Dymanus
    //corrected by @Joel Pflomm
    public static function getAllTagIdsForSection($section_id, $material_type){
        global $ilDB;
        $result = $ilDB->queryF("SELECT tag_id FROM ui_uihk_recsys_t_p_s WHERE section_id = %s AND material_type = %s",
                array("integer", "integer"),
                array($section_id, $material_type));
        $tag_ids = array();
        while($row = $ilDB->fetchAssoc($result)){
            $tag_ids[] = $row['tag_id'];
        }
        return $tag_ids;
    }

    public static function getAllSectionIDsForTag($tag_id){
        global $ilDB;
        $queryResult = $ilDB->query("SELECT material_type, section_id FROM ui_uihk_recsys_t_p_s WHERE tag_id = %s", "integer", $tag_id);
        $material_type_id_pair = array(array());
        while($row = $ilDB->fetchAssoc($queryResult)){
            array_push($material_type_id_pair, array($row['material_type'], $row['section_id']));
        }
        return $material_type_id_pair;
    }
    

    public function deleteTagPerSection(){
        $this->ilDB->manipulateF("DELETE FROM ui_uihk_recsys_t_p_s WHERE tag_id = %s AND material_type = %s AND section_id = %s",
                array("integer", "integer", "integer"),
                array($this->tag_id, 
                      $this->material_type, 
                      $this->section_id
                    ));
    }

    //add a new tag to a material section
    public function addNewTagToSection()
    {   
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_t_p_s"
                . "(tag_id, material_type, section_id)"
                . " VALUES (%s,%s,%s)",
                array("integer", "integer", "integer"),
                array($this->tag_id, 
                      $this->material_type, 
                      $this->section_id
                    ));
    }

    //get tag_id from 

    public function getMaterial_type()
    {
        return $this->material_type;
    }

    public function getSection_id()
    {
        return $this->section_id;
    }

    public function setMaterial_type($material_type)
    {
        $this->material_type = $material_type;
    }

    public function setSection_id($section_id)
    {
        $this->section_id = $section_id;
    }

}

?>