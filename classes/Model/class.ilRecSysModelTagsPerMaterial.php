<?php
/**
 * @author Dasha
 * @author Joel Pflomm <joel.pflomm@students.uni-mannheim.de>
 * 
 *
 */
class ilRecSysModelTagsPerMaterial{

    private $tag_id;
    private $material_type;
    private $material_id;


    var $ilDB;

    //-----------------------------------------------------------------------------------

    //constructor
    public function __construct($tag_id, $material_type, $material_id)
    {
        global $ilDB;
        $this->ilDB = $ilDB;
        $this->tag_id = $tag_id;
        $this->material_type = $material_type;
        $this->material_id = $material_id;
    }

    //written by @Anna Eschbach-Dymanus
    public static function fetchTagsToMaterial($material_id, $tag_id, $material_type)
    {
        global $ilDB;
        $result = $ilDB->queryF("SELECT * FROM ui_uihk_recsys_t_p_m WHERE material_id = %s AND tag_id = %s AND material_type = %s",
                array("integer", "integer", "text"),
                array($material_id, $tag_id, $material_type));
        if($ilDB->numRows($result) == 0){
            return null;
        }
        $row = $ilDB->fetchAssoc($result);
        $tag = new ilRecSysModelTagsPerMaterial($row['tag_id'], $row['material_type'], $row['material_id']);
        return $tag;
    }

    //written by @Anna Eschbach-Dymanus
    public static function getAllTagIds($material_id, $material_type){
        global $ilDB;
        $result = $ilDB->queryF("SELECT tag_id FROM ui_uihk_recsys_t_p_m WHERE material_id = %s AND material_type = %s",
                array("integer", "text"),
                array($material_id, $material_type));
        $tag_ids = array();
        while($row = $ilDB->fetchAssoc($result)){
            $tag_ids[] = $row['tag_id'];
        }
        return $tag_ids;
    }
    
    //written by @Anna Eschbach-Dymanus
    public function deleteTagToMaterial(){
        $this->ilDB->manipulateF("DELETE FROM ui_uihk_recsys_t_p_m WHERE tag_id = %s AND material_type = %s AND material_id = %s",
                array("integer", "text", "integer"),
                array($this->tag_id, 
                      $this->material_type, 
                      $this->material_id
                    ));
    }

    //add a new tag to a material
    public function addNewTagToMaterial()
    {   
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_t_p_m"
                . "(tag_id, material_type, material_id)"
                . " VALUES (%s,%s,%s)",
                array("integer", "text", "integer"),
                array($this->tag_id, 
                      $this->material_type, 
                      $this->material_id
                    ));
    }

    //get tag_id from 

    public function getMaterial_type()
    {
        return $this->material_type;
    }

    public function getMaterial_id()
    {
        return $this->material_id;
    }

    public function setMaterial_type($material_type)
    {
        $this->material_type = $material_type;
    }

    public function setMaterial_id($material_id)
    {
        $this->material_id = $material_id;
    }
    

    

}

?>