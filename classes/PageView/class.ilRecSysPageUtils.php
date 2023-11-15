<?php

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelTagHandler.php');

/**
 * @author Anna Eschbach-Dymanus
 */
class ilRecSysPageUtils {

    const MATERIAL_TYPE_TO_INDEX = array(
        "script" => 0,
        "presentation" => 1,
        "video" => 2,
        "picture" => 3,
        "webr" => 4, //iliastype
        "bib" => 5, //iliastype
        "exc" => 6, //iliastype
        "excsheet" => 6
    );

    const MATERIAL_INDEX_TO_TYPE = array(
        0 => "script",
        1 => "presentation",
        2 => "video",
        3 => "picture",
        4 => "webr", //iliastype
        5 => "bib", //iliastype
        6 => "exc" //iliastype
    );


    static function debug_to_console($data, $context = 'Debug in Console') {

        // Buffering to solve problems frameworks, like header() in this and not a solid return.
        ob_start();
    
        $output  = 'console.info(\'' . $context . ':\');';
        $output .= 'console.log(' . json_encode($data) . ');';
        $output  = sprintf('<script>%s</script>', $output);
    
        echo $output;
    }

    public static function objIsType($obj_id){
        $material = ilRecSysModelScript::fetchAllSectionsWithObjID($obj_id);
        if($material != null){
            return "script";
        }
        $material = ilRecSysModelVideo::fetchAllSectionsWithObjID($obj_id);
        if($material != null){
            return "video";
        }
        $material = ilRecSysModelPresentation::fetchAllSectionsWithObjID($obj_id);
        if($material != null){
            return "presentation";
        }
        $material = ilRecSysModelExercise::fetchAllSectionsWithObjID($obj_id);
        if($material != null){
            return "excsheet";
        }
        $material = ilRecSysModelWeblink::fetchByObjID($obj_id, null);
        if($material != null){
            return "webr";
        }
        $material = ilRecSysModelPicture::fetchByObjID($obj_id, null);
        if($material != null){
            return "picture";
        }
        $material = ilRecSysModelBibliography::fetchByObjID($obj_id, null);
        if($material != null){
            return "bib";
        }
        else{
            throw new Exception("Object ".$obj_id." is not of a valid type");
        }
    }


    public static function fileIsType($obj_id){
        $material = ilRecSysModelScript::fetchAllSectionsWithObjID($obj_id);
        if($material != null){
            return "script";
        }
        $material = ilRecSysModelVideo::fetchAllSectionsWithObjID($obj_id);
        if($material != null){
            return "video";
        }
        $material = ilRecSysModelPresentation::fetchAllSectionsWithObjID($obj_id);
        if($material != null){
            return "presentation";
        }
        $material = ilRecSysModelExercise::fetchAllSectionsWithObjID($obj_id);
        if($material != null){
            return "excsheet";
        }
        else{
            return "script";
        }
    }  

    public static function getSectionBySectionIDAndMaterialType($section_id, $material_type){
        switch ($material_type){
            case "script":
                $section = ilRecSysModelScript::fetchByMaterialSectionID($section_id);
                break;
            case "video":
                $section = ilRecSysModelVideo::fetchByMaterialSectionID($section_id);
                break;
            case "presentation":
                $section = ilRecSysModelPresentation::fetchByMaterialSectionID($section_id);
                break;
            case "webr":
                $section = ilRecSysModelWeblink::fetchByMaterialSectionID($section_id);
                break;
            case "exc":
            case "excsheet":
                $section = ilRecSysModelExercise::fetchByMaterialSectionID($section_id);
                break;
        }
        if ($section == null){
            throw new Exception("Section ".$section_id." is null for material_type ".$material_type);
        }
        return $section;
    }

    public static function getMaterialTagEntries($obj_id, $material_type) {
        //In shape [[section_id, [tag1, tag2, tag3], [from, to]], ...] 
       
        $material_tag_entries = array();
        switch ($material_type){
            case "script":
                $sections = ilRecSysModelScript::fetchAllSectionsWithObjID($obj_id);
                break;
            case "video":
                $sections = ilRecSysModelVideo::fetchAllSectionsWithObjID($obj_id);
                break;
            case "presentation":
                $sections = ilRecSysModelPresentation::fetchAllSectionsWithObjID($obj_id);
                break;
            case "webr":
                $sections = ilRecSysModelWeblink::fetchByObjID($obj_id, null);
                $sections = $sections != null ? array($sections) : null;
                break;
            case "exc":
            case "excsheet":
                $sections = ilRecSysModelExercise::fetchAllSectionsWithObjID($obj_id);
                break;
        }
        $tag_handler = ilRecSysModelTagHandler::getInstance();
        if($sections != null){
            foreach($sections as $section){
                $tags = $tag_handler->getAllTagsForSectionMaterial($section->getSectionID(), ilRecSysPageUtils::MATERIAL_TYPE_TO_INDEX[$material_type]);
                $from_to = $section->getFromTo();
                if(!empty($tags)){
                    $tag_names = array_map(function($tag){return $tag->getTag_name();}, $tags);
                    $material_tag_entries[] = array($section->getSectionID(), $tag_names, $from_to);
                }

            }
        }
        //check if empty array
        if(empty($material_tag_entries)){
            $material_tag_entries = array(array(-1, array(), ""));
        }
        ilRecSysPageUtils::debug_to_console($material_tag_entries, "loaded material_tag_entries");
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