<?php

use function PHPSTORM_META\type;

/**
 * Class ilRecSysModelUtils
 * This class contains utility functions for the recommender system
 */
class ilRecSysModelUtils{
    public static function getMaterialFromObjID($obj_id){
        
        $item = ilObject::_lookupObjectId($obj_id);
        $type = $item['type'];

        //TODO: Add remaining cases!
        if($type == 'exc'){
            $materials = ilRecSysModelExercise::fetchByObjID($obj_id);
        }
        else if($type == 'webr'){
            $materials = ilRecSysModelWeblink::fetchByObjID($obj_id);
        }
        else if($type == "file"){
            //Maybe rewrite with Try Except Blocks
            $possible_materials = ilRecSysModelScript::fetchByObjID($obj_id);
            if($possible_materials != null){
                $materials = $possible_materials;
            }
            $possible_materials = ilRecSysModelVideo::fetchByObjID($obj_id);
            if($materials != null){
                $materials = $possible_materials;
            }
            $possible_materials = ilRecSysModelPresentation::fetchByObjID($obj_id);
            if($materials != null){
                $materials = $possible_materials;
            }
            $possible_materials = ilRecSysModelExercise::fetchByObjID($obj_id);
            if($materials != null){
                $materials = $possible_materials;
            }
        }
        else{
            throw new Exception("No RecSys Class matches object type: ".$type);
        }
        
    }
}


?>