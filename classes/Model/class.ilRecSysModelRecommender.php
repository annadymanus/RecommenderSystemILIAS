<?php

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/PageView/class.ilRecSysPageUtils.php');


//@author Eschbach-Dymanus Anna
//Recommendations are made individually for each student. Use topics for cold-starting problems.
class ilRecSysModelRecommender{
    var $ilDB;
    private $crs_id;
    private $student;
    private $usr_id; //user id of the student
    
    public function __construct($usr_id, $crs_id)
    {
        global $ilDB;
        $this->ilDB = $ilDB;
        $this->crs_id = $crs_id;
        $this->usr_id = $usr_id;
        //$this->student = ilRecSysModelStudent::getRecSysStudent($usr_id, $crs_id);
    }

    function debug_to_console($data, $context = 'Debug in Console') {

        // Buffering to solve problems frameworks, like header() in this and not a solid return.
        ob_start();
    
        $output  = 'console.info(\'' . $context . ':\');';
        $output .= 'console.log(' . json_encode($data) . ');';
        $output  = sprintf('<script>%s</script>', $output);
    
        echo $output;
    }   

    private function getUniqueTags($section_mattype_tuples){
        $tag_ids = array();
        foreach($section_mattype_tuples as $section_mattype){
            $tag_ids = array_merge($tag_ids, ilRecSysModelTagsPerSection::getAllTagIdsForSection($section_mattype[0], $section_mattype[1]));
        }
        $tag_ids = array_unique($tag_ids);
        $tag_ids = array_filter($tag_ids);
        return $tag_ids;
    }

    public function getTagOnlyRecommend(){
        $most_recent_query = $this->getMostRecentRecommendationQuery($this->crs_id, $this->usr_id);
        if($most_recent_query == null){
            return null;
        }
        $this->debug_to_console($most_recent_query, "most_recent_query");
        $this->debug_to_console($most_recent_query, "most_recent_query");

        $section_mattype_tuples = array();
        foreach($most_recent_query as $query){
            $section_mattype_tuples[] = array($query['material_id'], $query['material_type']);
        }
        $tag_ids = $this->getUniqueTags($section_mattype_tuples);
        $this->debug_to_console($tag_ids, "tag_ids");
        $materials = array();
        foreach($tag_ids as $tag_id){
            $matching_mats = ilRecSysModelTagsPerSection::getAllSectionIDsForTag($tag_id);
            $materials = array_merge($materials, $matching_mats);
        }
        $this->debug_to_console($materials, "materials");        
        $materials = array_filter($materials);
        $this->debug_to_console($materials, "materials");
        $unique_materials = array();
        foreach($materials as $material){
            //dont add if in section_material_tuples
            $found = false;
            foreach($section_mattype_tuples as $section_mattype){
                if($section_mattype[0] == $material[0] && $section_mattype[1] == $material[1]){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                foreach($unique_materials as $unique_material){
                    if($unique_material[0] == $material[0] && $unique_material[1] == $material[1]){
                        $found = true;
                        break;
                    }
                }
            }                      
            if(!$found){
                $unique_materials[] = $material;
            }
        }
        $materials = $unique_materials;
        
        $this->debug_to_console($materials, "materials");
        //$materials = array_diff($materials, $section_mattype_tuples); //dont recommend the things the student wants recommendation for
        //$this->debug_to_console($materials, "materials");

        //add from_to information and matching score (percentage of matching tags)
        $materials_with_score = array();
        foreach($materials as $material){
            $material_tags = ilRecSysModelTagsPerSection::getAllTagIdsForSection($material[0], $material[1]);
            $matching_tags = array_intersect($material_tags, $tag_ids);
            $matching_score = count($matching_tags) * 100 / count($tag_ids);
            $material_tag_names = array();
            foreach($material_tags as $material_tag){
                $material_tag_names[] = ilRecSysModelTags::fetchTagById($material_tag)->getTag_name();
            }
            $material_type = ilRecSysPageUtils::MATERIAL_INDEX_TO_TYPE[$material[1]];
            $section = ilRecSysPageUtils::getSectionBySectionIDAndMaterialType($material[0], $material_type);
            $from_to = $section->getFromTo();
            $obj_id = $section->getObId();
            $materials_with_score[] = array($obj_id, $material[0], $material_type, $material_tag_names, $from_to, $matching_score);
        }
        return $materials_with_score;
    }

    public function getMostRecentRecommendationQuery(){
        //get all recommendation queries that have the same timestamp as most recent recommendation query for this student
        $query = "SELECT * FROM ui_uihk_recsys_u_q WHERE crs_id = %s AND usr_id = %s AND timestamp = (SELECT MAX(timestamp) FROM ui_uihk_recsys_u_q WHERE crs_id = %s AND usr_id = %s)";
        //$query = "SELECT * FROM ui_uihk_recsys_u_q WHERE crs_id = %s AND usr_id = %s ORDER BY timestamp DESC LIMIT 1";        
        $result = $this->ilDB->queryF($query, array("integer", "integer", "integer", "integer"), array($this->crs_id, $this->usr_id, $this->crs_id, $this->usr_id));
        if($result->numRows() == 0){
            return null;
        }
        $results = array();
        while($entry = $this->ilDB->fetchAssoc($result)){
            $results[] = $entry;
        }
        return $results;
    }

    public function setRecommendationQuery($section_mattype_tuples) {
        //contain section_id and material_type
        global $ilDB;
        $time = time();
        foreach($section_mattype_tuples as $section_mattype){
            $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_u_q"
                ."(usr_id, crs_id, material_id, material_type, timestamp)"
                ." VALUES (%s,%s,%s,%s,%s)",
                array("integer","integer","integer","integer","integer"),
                array($this->usr_id, $this->crs_id, $section_mattype[0], $section_mattype[1], $time));
        }
    }

}






?>