<?php

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelTagHandler.php');

/**
 * @author Anna Eschbach-Dymanus
 */
class ilRecSysPageUtils {

    static function debug_to_console($data, $context = 'Debug in Console') {

        // Buffering to solve problems frameworks, like header() in this and not a solid return.
        ob_start();
    
        $output  = 'console.info(\'' . $context . ':\');';
        $output .= 'console.log(' . json_encode($data) . ');';
        $output  = sprintf('<script>%s</script>', $output);
    
        echo $output;
    }   


    public static function fileIsType($obj_id){
        $material = ilRecSysModelScript::fetchByObjID($obj_id);
        if($material != null){
            return "script";
        }
        $material = ilRecSysModelVideo::fetchByObjID($obj_id);
        if($material != null){
            return "video";
        }
        $material = ilRecSysModelPresentation::fetchByObjID($obj_id);
        if($material != null){
            return "presentation";
        }
        $material = ilRecSysModelExercise::fetchByObjID($obj_id);
        if($material != null){
            return "excsheet";
        }
        else{
            return "script";
        }
    }  

    public static function getMaterialTagEntries($obj_id, $material_type) {
        //In shape [[section_id, [tag1, tag2, tag3], [from, to]], ...] 
       
        $material_tag_entries = array();
        switch ($material_type){
            case "script":
                $materials = ilRecSysModelScript::fetchByObjID($obj_id);
                break;
            case "video":
                $materials = ilRecSysModelVideo::fetchByObjID($obj_id);
                break;
            case "presentation":
                $materials = ilRecSysModelPresentation::fetchByObjID($obj_id);
                break;
            case "webr":
                $materials = ilRecSysModelWeblink::fetchByObjID($obj_id);
                break;
            case "exc":
            case "excsheet":
                $materials = ilRecSysModelExercise::fetchByObjID($obj_id);
                break;
        }
        $tag_handler = ilRecSysModelTagHandler::getInstance();
        if($materials != null){
            foreach($materials as $material){
                $tags = $tag_handler->getTagsForMaterial($material->get_id(), $material_type);
                //TODO: might differ based on type !! IMPLEMENT
                $from_to = [$material->getStart_page(), $material->getEnd_page()];
                if(!empty($tags)){
                    $tag_names = array_map(function($tag){return $tag->getTag_name();}, $tags);
                    $material_tag_entries[] = array($material->get_id(), $tag_names, $from_to);
                }

            }
        }
        //check if empty array
        if(empty($material_tag_entries)){
            $material_tag_entries = array(array(-1, array(""), ""));
        }
        ilRecSysPageUtils::debug_to_console($material_tag_entries, "material_tag_entries");
        return $material_tag_entries;
    }

    public static function getAllValidObjTypes(){
        //get all ilias obj types that are supported by recsys
        $obj_types = array("file", "webr", "exc");
        return $obj_types;
    }

    public static function getItemsOfCourse($courseObject, $type=null)
    {
        $items = $courseObject->getSubItems();
        $items = $items['_all'];                // get all Items of this course
        $items = ilRecSysPageUtils::getAllSubItems($items, $type); // get also Items of folders
        return $items;
    }
    
    public static function getAllSubItems($container, $type = null)
    {             
        if (!isset($container)) return [];  //return empty array if there is no container
        
        $items = array();
        
        foreach ($container as $item) {    
          
            if ($item['type'] == $type || $type == null) {
                array_push($items, $item);
            } else if ($item['type'] == 'fold') {
                $ilObjFolder = new ilObjFolder($item['ref_id']);
                $objects = $ilObjFolder->getSubItems();
                $objects = $objects['_all'];
                $items = array_merge($items, ilRecSysPageUtils::getAllSubItems($objects, $type));
            } else if ($item['type'] == 'grp') {
                $ilObjGroup = new ilObjGroup($item['ref_id']);
                $objects = $ilObjGroup->getSubItems();
                $objects = $objects['_all'];            
                $items = array_merge($items, ilRecSysPageUtils::getAllSubItems($objects, $type));
            }
        }
        return $items;
    }
}

?>