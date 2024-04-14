<?php

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelScript.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelBibliography.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelPicture.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelPresentation.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelTest.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelVideo.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelWeblink.php');


/**
 * 
 * @author Joel Pflomm <joel.pflomm@students.uni-mannheim.de>
 * 
 *
 */

// for the sake of understandability
class MaterialType{
    const SCRIPT = 0;
    const PRESENTATION = 1;
    const VIDEO = 2;
    const PICTURE = 3;
    const WEBLINK = 4;
    const BIBLIOGRAPHY = 5;
    const EXERCISE = 6;
    const TEST = 7;
}


class ilRecSysModelTagHandler{

    // only instance of this class, should be called whenever tags are about to be managed
    private static $instance;

    // list of all material section counters. (Used to assign unique identifiers)
    private $script_counter;          
    private $presentation_counter;    
    private $video_counter;           
    private $picture_counter;
    private $weblink_counter;
    private $bibliography_counter;
    private $exercise_counter;
    private $test_counter;
    
    private $tag_counter;

    private $tags_map = []; 
    private $tags_user_map = [];

    // TODO: Implement a class that holds these maps but controlles their size, else they may get too big

    private $script_map = [];         // ilRecSysModelScript attributes: script_id, obj_id, start_page, end_page, difficulty, rating_count
    private $presentation_map = [];   // ilRecSysModelPresentation attributes: presentation_id, obj_id, start_slide, end_slide, difficulty, rating_count
    private $video_map = [];          // ilRecSysModelVideo attributes: video_id, obj_id, start_min, end_min, difficulty, rating_count
    private $picture_map = [];        // ilRecSysModelPicture attributes: picture_id, obj_id, difficulty, rating_count
    private $weblink_map = [];        // ilRecSysModelWeblink attributes: weblink_id, obj_id, difficulty, rating_count
    private $bibleography_map = [];   // ilRecSysModelBibliography attributes: bibl_id, obj_id, difficulty, rating_count         
    private $exercise_map = [];  
    
    //-------------------------------------------------------------
    // Constructor and instantiating functions

    private function __construct() {
        $this->script_counter = ilRecSysModelScript::getLastMaterialSectionId();
        $this->presentation_counter = ilRecSysModelPresentation::getLastMaterialSectionId();
        $this->video_counter = ilRecSysModelVideo::getLastMaterialSectionId();
        $this->picture_counter = ilRecSysModelPicture::getLastMaterialSectionId();
        $this->weblink_counter = ilRecSysModelWeblink::getLastMaterialSectionId();
        $this->bibliography_counter = ilRecSysModelBibliography::getLastMaterialSectionId();
        $this->exercise_counter = ilRecSysModelExercise::getLastMaterialSectionId();
        $this->tag_counter = ilRecSysModelTags::getLastTagId();
        $this->test_counter = ilRecSysModelTest::getLastTestId();

    }


    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function clone() {
        // Private clone method to prevent cloning of the instance
    }

    private function __wakeup() {
        // Private wakeup method to prevent unserialization of the instance
    }

    // -------------------------------------------------------------------------------------------------
    // frontend intersection functions

    public function deleteSection($section_id, $material_type){
        $this->deleteCompleteSectionBySectionID($section_id, $material_type);
    }

    public function updateSection($crs_id, $obj_id, $section_id, $material_type, $tag_names, $from_to, $difficulty){
        //if($material_type == 1){
        //    throw new Exception(implode(",", array($crs_id, $obj_id, $section_id, $material_type, $tag_names, $from_to[0], $from_to[1])));
        //}
        //get all tags of course
        
        $tags = $this->getAllTagsForCourse($crs_id);
        //get all tagnames from these tags
        $course_tag_names = array();
        foreach($tags as $tag){
            array_push($course_tag_names, $tag->getTag_name());
        }
        //create tags for tag_names that are not in the course yet
        $new_tag_names = array_diff($tag_names, $course_tag_names);
        foreach($new_tag_names as $tag_name){
            $this->createNewTag($tag_name, "", $crs_id);
        }
        //get all tags for tag_names
        $tags = $this->getTagsByName($tag_names, $crs_id);
        $section_tags = $this->getAllTagsForSectionMaterial($section_id, $material_type);
        $section_tag_ids = array();
        $section_tag_names = array();
        foreach($section_tags as $tag){
            array_push($section_tag_names, $tag->getTag_name());
            array_push($section_tag_ids, $tag->getTag_id());
        }

        $section_exists = $this->getSectionMaterialByID($section_id, $material_type) == null ? false : true;
        //assign tags to section

        foreach($tags as $tag){
            $tag_id = $tag->getTag_id();
            //check if id in section_tag_ids
            if(!in_array($tag_id, $section_tag_ids) | !$section_exists){
               $section = $this->assignTagToSection($tag->getTag_id(), $material_type, $obj_id, $from_to[0], $from_to[1], $difficulty);
               $section_id = $section->getSectionID();
               $section_exists = true;
            }
        }
        //remove tags from section that are not in tag_names
        $tags_to_remove = array_diff($section_tag_names, $tag_names);
        $section = $this->getSectionMaterialByID($section_id, $material_type);
        foreach($tags_to_remove as $tag_name){
            $tag = $this->getTagByName($tag_name, $crs_id);
            $this->deleteTaggedSection($tag->getTag_id(), $section);
        }

        if($section == null){
            throw new Exception(implode(",", array($crs_id, $obj_id, $section_id, $material_type, $tag_names, $from_to)));
        }
        //Update FromTo information
        if ($from_to[0] != null){
            $this->updateSectionRange($section, $from_to);
        }
        //Update difficulty
        if ($difficulty != null){
            $this->updateDifficulty($section_id, $material_type, $difficulty, 0);
        }
    }


    // -------------------------------------------------------------------------------------------------
    // functions for handling the material specific ids

    /**
     * getter and increment functions for the specific section counter
     */
    private function incrementSectionCounter($materialType) {
        switch($materialType){
            case MaterialType::SCRIPT: $this->script_counter++;
                break;
            case MaterialType::PRESENTATION: $this->presentation_counter++;
                break;
            case MaterialType::VIDEO: $this->video_counter++;
                break;
            case MaterialType::PICTURE: $this->picture_counter++;
                break;
            case MaterialType::WEBLINK: $this->weblink_counter++;
                break;
            case MaterialType::BIBLIOGRAPHY: $this->bibliography_counter++;
                break;        
            case MaterialType::TEST: $this->test_counter++;
                break;
            case MaterialType::EXERCISE: $this->exercise_counter++;
                break;
            default:
                break;
        } 
    }

    private function incrementTagCounter() { $this->tag_counter++; }

    public function getMaterialSectionCounter($materialType) {
        switch($materialType){
            case MaterialType::SCRIPT: return $this->script_counter;
            case MaterialType::PRESENTATION: return $this->presentation_counter;
            case MaterialType::VIDEO: return $this->video_counter;
            case MaterialType::PICTURE: return $this->picture_counter;
            case MaterialType::WEBLINK: return $this->weblink_counter;
            case MaterialType::BIBLIOGRAPHY: return $this->bibliography_counter;
            case MaterialType::TEST: return $this->test_counter;
            case MaterialType::EXERCISE: return $this-> exercise_counter;
            default: return -1;
        } 
    }

    public function getTagCounter() {return $this->tag_counter; }

    //-------------------------------------------------------------
    // create functions:
    
    /**
     * creates tag and adds it to the database as well as into the tags_map
     * @return ilRecSysModelTags - object with the newly created tag
     */
    public function createNewTag($tag_name, $tag_description, $crs_id){
        // increment tag counter and create tag object
        if(ilRecSysModelTags::fetchTagByName($tag_name, $crs_id) != null){
            throw new Exception("A tag for the provided name already exists.");
        }
        $this->incrementTagCounter();
        $tag_id = $this->getTagCounter();
        $tag = new ilRecSysModelTags($tag_id, $tag_name, $crs_id, $tag_description, 0);
        $tag->addNewTag(); 
        $this->tags_map[$tag_id] = $tag;
        return $tag;
    }

    
    /**
     *  args        array of arguments the first 2 are defined the rest depends on the material type: 
     *  args[0]     material_type
     *  args[1]     obj_id
     *  args[2]     from (optional)
     *  args[3]     to (optional)
     */
    public function assignTagToSection($tag_id, $material_type, $obj_id, $from, $to, $difficulty) {
        
        // 1. check whether tag_id exists
        $tag = $this->getTagByID($tag_id);
        if ($tag == null){
            throw new Exception("The id that was provided for the tag is invalid. \nThe tag does not exist");
        }

        // 2. check whether a section already exists
        // 3. if not create a new section / else just increment the no_tags the section was tagged by
        switch($material_type) {
            case MaterialType::SCRIPT: 
                $from_to =[$from, $to];
                $script = ilRecSysModelScript::fetchByObjID($obj_id, $from_to);
                if($script == null){
                    // create new section
                    $this->incrementSectionCounter($material_type);
                    $script_id = $this->getMaterialSectionCounter($material_type);
                    $script = new ilRecSysModelScript($script_id, $obj_id, $from, $to, $difficulty, 0, 1, 0.0);
                    $script->createMaterialSection();
                    // add section to according map
                    $this->script_map[$script_id] = $script;
                } else {
                    // check if section was already connected/assigned to the tag
                    if (ilRecSysModelTagsPerSection::fetchTagsPerSection($script->getSectionID(), $tag_id, $material_type) != null){
                        throw new Exception("The tag has already been assigned to the section.");
                    }
                    
                    //if not increment no_tags
                    $script->incrementNoTags();

                }
                $section = $script;
                break;

            case MaterialType::PRESENTATION:
                $from_to =[$from, $to];
                $presentation = ilRecSysModelPresentation::fetchByObjID($obj_id, $from_to);
                if($presentation == null){
                    // create new section
                    $this->incrementSectionCounter($material_type);
                    $presentation_id = $this->getMaterialSectionCounter($material_type);
                    $presentation = new ilRecSysModelPresentation($presentation_id, $obj_id, $from, $to, $difficulty, 0, 1, 0.0);
                    $presentation->createMaterialSection();
                    // add section to according map
                    $this->presentation_map[$presentation_id] = $presentation;
                } else {
                    // check if section was already connected/assigned to the tag
                    if(ilRecSysModelTagsPerSection::fetchTagsPerSection($presentation->getSectionID(), $tag_id, $material_type) != null){
                        throw new Exception("The tag has already been assigned to the section.");
                    }
                    //if not increment no_tags
                    $presentation->incrementNoTags();
                }
                $section = $presentation;
                break;
            case MaterialType::VIDEO:
                $from_to =[$from, $to];
                $video = ilRecSysModelVideo::fetchByObjID($obj_id, $from_to);
                if($video == null){
                    // create new section
                    $this->incrementSectionCounter($material_type);
                    $video_id = $this->getMaterialSectionCounter($material_type);
                    $parsed_time = ilRecSysModelVideo::parseFromTo($from, $to);
                    $video = new ilRecSysModelVideo($video_id, $obj_id, $parsed_time[0], $parsed_time[1], $parsed_time[2], $parsed_time[3], $difficulty, 0, 1, 0.0);
                    $video->createMaterialSection();
                    // add section to according map
                    $this->video_map[$video_id] = $video;
                } else {
                    // check if section was already connected/assigned to the tag
                    if(ilRecSysModelTagsPerSection::fetchTagsPerSection($video->getSectionID(), $tag_id, $material_type) != null){
                        throw new Exception("The tag has already been assigned to the section.");
                    }
                    //if not increment no_tags
                    $video->incrementNoTags();
                }
                $section = $video;
                break;
            case MaterialType::PICTURE:
                $from_to =[];
                $picture = ilRecSysModelPicture::fetchByObjID($obj_id, $from_to);
                if($picture == null){
                    // create new section
                    $this->incrementSectionCounter($material_type);
                    $picture_id = $this->getMaterialSectionCounter($material_type);
                    $picture = new ilRecSysModelPicture($picture, $obj_id, $difficulty, 0, 1, 0.0);
                    $picture->createMaterialSection();
                    // add section to according map
                    $this->picture_map[$picture_id] = $picture;
                } else {
                    // check if section was already connected/assigned to the tag
                    if(ilRecSysModelTagsPerSection::fetchTagsPerSection($picture->getSectionID(), $tag_id, $material_type) != null){
                        throw new Exception("The tag has already been assigned to the section.");
                    }
                    //if not increment no_tags
                    $picture->incrementNoTags();
                }
                $section = $picture;
                break;
            case MaterialType::WEBLINK:
                $from_to =[];
                $weblink = ilRecSysModelWeblink::fetchByObjID($obj_id, $from_to);
                if($weblink == null){
                    // create new section
                    $this->incrementSectionCounter($material_type);
                    $weblink_id = $this->getMaterialSectionCounter($material_type);
                    $weblink = new ilRecSysModelWeblink($weblink_id, $obj_id, $difficulty, 0, 1, 0.0);
                    $weblink->createMaterialSection();
                    // add section to according map
                    $this->weblink_map[$weblink_id] = $weblink;
                } else {
                    // check if section was already connected/assigned to the tag
                    if(ilRecSysModelTagsPerSection::fetchTagsPerSection($weblink->getSectionID(), $tag_id, $material_type) != null){
                        throw new Exception("The tag has already been assigned to the section.");
                    }
                    //if not increment no_tags
                    $weblink->incrementNoTags();
                }
                $section = $weblink;
                break;
            case MaterialType::BIBLIOGRAPHY:
                $from_to =[];
                $bibliography = ilRecSysModelBibliography::fetchByObjID($obj_id, $from_to);
                if($bibliography == null){
                    // create new section
                    $this->incrementSectionCounter($material_type);
                    $bibliography_id = $this->getMaterialSectionCounter($material_type);
                    $bibliography = new ilRecSysModelBibliography($bibliography_id, $obj_id, $difficulty, 0, 1, 0.0);
                    $bibliography->createMaterialSection();
                    // add section to according map
                    $this->weblink_map[$bibliography_id] = $bibliography;
                } else {
                    // check if section was already connected/assigned to the tag
                    if(ilRecSysModelTagsPerSection::fetchTagsPerSection($bibliography->getSectionID(), $tag_id, $material_type) != null){
                        throw new Exception("The tag has already been assigned to the section.");
                    }
                    //if not increment no_tags
                    $bibliography->incrementNoTags();
                }
                $section = $bibliography;
                break;
            case MaterialType::EXERCISE:
                $task_subtask =[$from, $to];
                $exercise = ilRecSysModelExercise::fetchByObjID($obj_id, $task_subtask);
                if($exercise == null){
                    // create new section
                    $this->incrementSectionCounter($material_type);
                    $exercise_id = $this->getMaterialSectionCounter($material_type);
                    $exercise = new ilRecSysModelExercise($exercise_id, $obj_id, $from, $to, $difficulty, 0, 1, 0.0);
                    $exercise->createMaterialSection();

                    // add section to according map
                    $this->video_map[$exercise_id] = $exercise;
                } else {
                    // check if section was already connected/assigned to the tag
                    if(ilRecSysModelTagsPerSection::fetchTagsPerSection($exercise->getSectionID(), $tag_id, $material_type) != null){
                        throw new Exception("The tag has already been assigned to the section.");
                    }
                    //if not increment no_tags
                    $exercise->incrementNoTags();
                }
                $section = $exercise;
                break;
            default:
                throw new Exception("Provided material type ".$material_type." does not exists.");
        }

        //4. connect section to the tag
        $tag_to_material = new ilRecSysModelTagsPerSection($tag_id, $material_type, $section->getSectionID());
        $tag_to_material->addNewTagToSection();
        
        //5. update tag occurence 
        $tag->incrementCount();

        return $section;
    }
    
    // ____________________________________________________________
    // get functions:
    
    /**
     *  get a Tag by its ID
     * 
     *  @param tag_id of the tag that is loaded
     *  @return tag if tag exists in database, else it returns null 
     */
    public function getTagByID($tag_id) {
        if(array_key_exists($tag_id, $this->tags_map)){
            return $this->tags_map[$tag_id];
        }
        $tag = ilRecSysModelTags::fetchTagById($tag_id); 
        if($tag == null){
            throw new Exception("The provided tag_id must be incorrect as there is no tag with that id.");
        } 
        $tags_map[$tag->getTag_id()] = $tag;
        return $tag;  
    }

    /**
     * returns the tags for the given tag_ids
     * 
     * @param tag_ids array
     * @return result_array that stores all tags that qualify, in case no tag qualifies the array is empty
     */
    public function getTags($tag_ids) {
        $result_array = array();
        //go through tags_ids to load them eighter from the map or the database
        foreach($tag_ids as $tag_id){
            $tag = $this->getTagByID($tag_id);
            if($tag != null){
                array_push($result_array, $tag);
            }
        }
        return $result_array;
    }

    /**
     *  get a Tag by its Name
     * 
     *  @param tag_name of the tag that is loaded
     *  @param crs_id of the course the tag belongs to
     *  @return tag if tag exists in database, else it returns null 
     *  @author Anna Eschbach-Dymanus, Joel Pflomm
     */
    public function getTagByName($tag_name, $crs_id)
    {
        $tag = ilRecSysModelTags::fetchTagByName($tag_name, $crs_id);
        if($tag == null){
            throw new Exception("The provided tag name is incorrect.");
        }
        return $tag;
    }

    /**
     *  This function has the purpose of getting all Tags for a provided list of tag names and the according course_id
     * 
     *  @return tags (array)
     *  @author Anna Eschbach-Dymanus, Joel Pflomm
     */
    public function getTagsByName($tag_names, $crs_id)
    {
        $tags = array();
        foreach($tag_names as $tag_name){
            $tag = $this->getTagByName($tag_name, $crs_id);
            array_push($tags, $tag);
        }
        return $tags;
    }
    
    /**
     * get the section for a tag given the material type and the id of that section (e.g. script_id)
     * 
     * @return section instance (this is possible due to polymorphism)
     */
    public function getSectionMaterialByID($section_id, $material_type) {
        // check whether material is already in the according map

        // else, the material tag was not loaded in one map yet
        switch($material_type){
            case MaterialType::SCRIPT:
                if(array_key_exists($section_id, $this->script_map)){
                    $section = $this->script_map[$section_id];
                } else {
                    $section = ilRecSysModelScript::fetchByMaterialSectionID($section_id);
                    if($section != null){
                        $this->script_map[$section_id];
                    }
                }
                break;
            case MaterialType::PRESENTATION: 
                if(array_key_exists($section_id, $this->presentation_map)){
                    $section = $this->presentation_map[$section_id];
                } else {
                    $section = ilRecSysModelPresentation::fetchByMaterialSectionID($section_id);
                    if($section != null){
                        $this->presentation_map[$section_id];
                    }
                } 
                break;
            case MaterialType::VIDEO:
                if(array_key_exists($section_id, $this->video_map)){
                    $section = $this->video_map[$section_id];
                } else {
                    $section = ilRecSysModelVideo::fetchByMaterialSectionID($section_id);
                    if($section != null){
                        $this->video_map[$section_id];
                    }
                }
                break;
            case MaterialType::PICTURE:
                if(array_key_exists($section_id, $this->picture_map)) {
                    $section = $this->picture_map[$section_id];
                } else {
                    $section = ilRecSysModelPicture::fetchByMaterialSectionID($section_id);
                    if($section != null){
                        $this->picture_map[$section_id];
                    }
                }
                break;
            case MaterialType::WEBLINK: 
                if(array_key_exists($section_id, $this->weblink_map)) {
                    $section = $this->weblink_map[$section_id];
                } else {
                    $section = ilRecSysModelWeblink::fetchByMaterialSectionID($section_id);
                    if($section != null){
                        $this->weblink_map[$section_id];
                    }
                }
                break;
            case MaterialType::BIBLIOGRAPHY:
                if(array_key_exists($section_id, $this->bibleography_map)) {
                    $section = $this->bibleography_map[$section_id];
                } else {
                    $section = ilRecSysModelBibliography::fetchByMaterialSectionID($section_id);
                    if($section != null){
                        $this->bibleography_map[$section_id];
                    }
                }
                break;
            case MaterialType::EXERCISE:
                if(array_key_exists($section_id, $this->exercise_map)) {
                    $section = $this->exercise_map[$section_id];
                } else {
                    $section = ilRecSysModelExercise::fetchByMaterialSectionID($section_id);
                    if($section != null){
                        $this->exercise_map[$section_id];
                    }
                }
                break;
            default: 
                throw new Exception("The provided material type does not exist");
        }
        return $section;
    }

    /**
     *  Collects an array of all section materials that were marked for a given tag
     *  
     *  @return sectionMaterials for given tag, or an empty array if there are none
     */
    public function getAllSectionMaterialsForTag($tag_id) {
        //get materials that are already stored in hashmap
        $sectionMaterials = array();
        $material_type_id_pair = ilRecSysModelTagsPerSection::getAllSectionIDsForTag($tag_id);
        foreach($material_type_id_pair as $pair){
            $section = $this->getSectionMaterialByID($pair[0], $pair[1]);
            if($section != null){
                array_push($sectionMaterials, $section);
            } 
        }
        return $sectionMaterials;
    }

    public function getSectionMaterialByObjID($material_type, $obj_id, $from, $to){
        $from_to =[$from, $to];
        switch($material_type) {
            case MaterialType::SCRIPT:
                return ilRecSysModelScript::fetchByObjID($obj_id, $from_to);
            case MaterialType::PRESENTATION:
                return ilRecSysModelPresentation::fetchByObjID($obj_id, $from_to);
            case MaterialType::VIDEO:
                return ilRecSysModelVideo::fetchByObjID($obj_id, $from_to);
            case MaterialType::PICTURE:
                return ilRecSysModelPicture::fetchByObjID($obj_id, array());
            case MaterialType::WEBLINK:
                return ilRecSysModelWeblink::fetchByObjID($obj_id, array());
            case MaterialType::BIBLIOGRAPHY:
                return ilRecSysModelBibliography::fetchByObjID($obj_id, array());
            case MaterialType::EXERCISE:
                return ilRecSysModelExercise::fetchByObjID($obj_id, $from_to);
            default:
                throw new Exception("Provided material type does not exists");
        }
    }

    public function getAllSectionsForObjID($material_type, $obj_id){
        $result_array = array();
        switch($material_type) {
            case MaterialType::SCRIPT:
                return ilRecSysModelScript::fetchAllSectionsWithObjID($obj_id);
            case MaterialType::PRESENTATION:
                return ilRecSysModelPresentation::fetchAllSectionsWithObjID($obj_id);
            case MaterialType::VIDEO:
                return ilRecSysModelVideo::fetchAllSectionsWithObjID($obj_id);
            case MaterialType::PICTURE:
                $picture = ilRecSysModelPicture::fetchByObjID($obj_id, array());
                array_push($result_array, $picture);
                return $result_array;
            case MaterialType::WEBLINK:
                $weblink = ilRecSysModelWeblink::fetchByObjID($obj_id, array());
                array_push($result_array, $weblink);
                return $result_array;
            case MaterialType::BIBLIOGRAPHY:
                $bibliography = ilRecSysModelBibliography::fetchByObjID($obj_id, array());
                array_push($result_array, $bibliography);
                return $result_array;
            case MaterialType::EXERCISE:
                return ilRecSysModelExercise::fetchAllSectionsWithObjID($obj_id);
            default:
                throw new Exception("Provided material type does not exists");
        }
    }

    /**
     *  Collects all valid tag ids to specific section 
     *  
     *  @return tags array
     */
    public function getAllTagsForSectionMaterial($section_id, $material_type){
        $tag_ids = ilRecSysModelTagsPerSection::getAllTagIdsForSection($section_id, $material_type);
        //this can be empty
        $result_array = $this->getTags($tag_ids);
        return $result_array;
    }

    /**
     *  Given a crs_id provide all Tags that were created for that course
     */
    public function getAllTagsForCourse($crs_id){
        $tag_ids = ilRecSysModelTags::fetchAllTagIDsForCourse($crs_id);
        //this can be empty
        $result_array = $this->getTags($tag_ids);
        return $result_array;
    }
    
    // ____________________________________________________________
    // update functions:

    /**
     *  Adds a new Rating to the given tag and thereby adjusts the difficulty as well as the rating-count
     */
    public function addNewRatingToDifficulty($section_id, $material_type, $rating){
        $section = $this->getSectionMaterialByID($section_id, $material_type);
        if($section == null){
            throw new Exception("No material tag is found under the given id.");
            return;
        }
        if($section->isRatingValid()){
            $section->addNewRating($rating);
        } else {
            throw new Exception("The provided rating exceeds the rating boundaries.");
        }
        
    }

    /**
     *  Updates the difficulty as long as it is within the rating intervall.
     *  Carefull!!! This completely overrides the given difficulty and rating_count attributes.
     */
    public function updateDifficulty($section_id, $material_type, $new_difficulty, $new_rating_count){
        $material = $this->getSectionMaterialByID($section_id, $material_type);
        if($material == null){
            throw new Exception("No material tag is found under the given id.");
            return;
        }
        //THIS attribute doesnt exist?
        //if($material->isRatingValid){
        $material->updateSectionDifficulty($new_difficulty, $new_rating_count);
        //} else {
        //    throw new Exception("The provided rating exceeds the rating boundaries");
        //}
    }

    public function updateTeacherDifficulty($section_id, $material_type, $new_teacher_difficulty) {
        $material = $this->getSectionMaterialByID($section_id, $material_type);
        if($material == null){
            throw new Exception("No material tag is found under the given id.");
            return;
        }
        $material->setTeacherDifficulty($new_teacher_difficulty);
    }

    public function updateSectionRangeForTagBySectionID($material_type, $section_id, $from, $to){
        $section = $this->getSectionMaterialByID($section_id, $material_type);
        if($section == null){
            throw new Exception("No material tag is found under the given id.");
        }
        $from_to =[$from, $to];
        $this->updateSectionRange($section, $from_to);
    }

    public function updateSectionRangeForTagByObjID($material_type, $obj_id, $from, $to){
        $section = $this->getSectionMaterialByObjID($material_type, $obj_id, $from, $to);
        if($section == null){
            throw new Exception("No material tag is found under the given id.");
        }
        $from_to =[$from, $to];
        $this->updateSectionRange($section, $from_to);
    }


    private function updateSectionRange($section, $from_to) {
        
        //2. check materialType because this function only supports those material sections that can be updated
        $material_type = $section->getMaterialType();
        if($material_type == MaterialType::SCRIPT ){
            $section = ilRecSysModelScript::fetchByMaterialSectionID($section->getSectionID());
            $section->updateStartEndPage($from_to[0], $from_to[1]);
        }
        else if($material_type == MaterialType::PRESENTATION){
            $section = ilRecSysModelPresentation::fetchByMaterialSectionID($section->getSectionID());
            $section->updateStartEndSlide($from_to[0], $from_to[1]);
        }
        else if($material_type == MaterialType::VIDEO){
            $section = ilRecSysModelVideo::fetchByMaterialSectionID($section->getSectionID());
            $parsed_time = ilRecSysModelVideo::parseFromTo($from_to);
            $from_min = $parsed_time[0];
            $from_sec = $parsed_time[1];
            $to_min = $parsed_time[2];
            $to_sec = $parsed_time[3];
            $section->updateTimeInterval($from_min, $from_sec, $to_min, $to_sec);
        }
        else if($material_type == MaterialType::EXERCISE) {
            $section = ilRecSysModelExercise::fetchByMaterialSectionID($section->getSectionID());
            $section = $section->updateTask($from_to[0], 0); //No subtask implementation (yet?)
        }            
        else {
            throw new Exception("The given material_type is not supported by this function.");
        }
    }
    //give feedback
    
    // ------------------------------------------------------------------------------------------------------------------------------------------
    // delete functions:



    //delet Tag
    /**
     *  deletes a tag by its id and decrements the tag count (resolves the conection) for every section that was tagged with it.
     */
    public function deleteTagByID($tag_id) {
        // 1. decrement or delete sections
        $sectionMaterials = $this->getAllSectionMaterialsForTag($tag_id);
        foreach($sectionMaterials as $section){
            $this->deleteTaggedSection($tag_id, $section);
        }
        // 2. delete Tag
        $tag = $this->getTagByID($tag_id);
        if ($tag != null){
            $tag->deleteTag();
        }
    }

    public function deleteTagByName($tag_name, $crs_id){
        // 1. get tag id
        $tag = $this->getTagByName($tag_name, $crs_id);
        // delete tag by using the above function
        $this->deleteTagByID($tag->getTag_id());
    }

    //delete Crs
    public function deleteCourseTags($crs_id) {
        $tags = $this->getAllTagsForCourse($crs_id);
        foreach($tags as $tag){
            $this->deleteTagByID($tag->getTag_id());
        }
    }

    //delete tag from section 
    public function deleteTaggedSection($tag_id, $section) {
        // check number of Tags
        $no_tags = $section->getNoTags();
        $tag = $this->getTagByID($tag_id);
        $tagcount = $tag->getTag_count();
        if($no_tags < 1 or $tagcount < 1){
            throw new Exception("Internal Server Error: number of tags has reached a state with a negative value.");
        } 
        if($no_tags == 1){
            // delete section
            $section->deleteSection();    
        } else {
            //4. decrement $no_tags
            $section->decrementNoTags();
        }
        if($tagcount == 1){
            // delete tag
            $tag->deleteTag();
        } else {
            //5. decrement $tagcount
            $tag->decrementCount();
        }
        $tagsPerSection = ilRecSysModelTagsPerSection::fetchTagsPerSection($section->getSectionID(), $tag_id, $section->getMaterialType());
        $tagsPerSection->deleteTagPerSection();
    }

    public function deleteCompleteSectionBySectionID($section_id, $material_type) {
        $section = $this->getSectionMaterialByID($section_id, $material_type);
        $this->deleteCompleteSection($section);
    }

    public function deleteCompleteSectionByObjID($obj_id, $from_to) {
        
    }

    private function deleteCompleteSection($section) {
        $material_id = $section->getSectionID();
        switch($section->getMaterialType()){
            case MaterialType::SCRIPT:
                unset($this->script_map[$material_id]);
                break;
            case MaterialType::PRESENTATION:
                unset($this->presentation_map[$material_id]);
                break;
            case MaterialType::VIDEO: 
                unset($this->video_map[$material_id]);
                break;
            case MaterialType::PICTURE:
                unset($this->picture_map[$material_id]);
                break;
            case MaterialType::WEBLINK: 
                unset($this->weblink_map[$material_id]);
                break;
            case MaterialType::BIBLIOGRAPHY:
                unset($this->bibleography_map[$material_id]);
                break;
            case MaterialType::EXERCISE:
                unset($this->exercise_map[$material_id]);
                break;
            default:
                throw new Exception("The provided material type does not exist");
        }
        // update tag occurences for every tag that tagged this section
        $tags = $this->getAllTagsForSectionMaterial($section->getSectionID(), $section->getMaterialType());
        foreach($tags as $tag) {
            $this->deleteTaggedSection($tag->getTag_id(), $section);
        }
        $section->deleteSection();
    }

    // ------------------------------------------------------------------------------------------------------------------------------------------
    
    // further functions:

    // ------------------------------------------------------------------------------------------------------------------------------------------

    function debug_to_console($data, $context = 'Debug in Console') {

        // Buffering to solve problems frameworks, like header() in this and not a solid return.
        ob_start();
        
        $output  = 'console.info(\'' . $context . ':\');';
        $output .= 'console.log(' . json_encode($data) . ');';
        $output  = sprintf('<script>%s</script>', $output);
        
        echo $output;
    }
}
?>