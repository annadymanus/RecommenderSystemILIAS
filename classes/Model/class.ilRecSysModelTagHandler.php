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

class ilRecSysModelTagHandler{
    /**
     * new implementation Joel
     */

    // only instance of this class, should be called whenever tags are about to be managed
    private static $instance;
    // list of all material
    private $script_counter;          
    private $presentation_counter;    
    private $video_counter;           
    private $picture_counter;
    private $weblink_counter;
    private $bibliography_counter;
    private $test_counter;
    private $exercise_counter;
    
    private $tag_counter;
    private $tags_map = []; 
    private $tags_user_map = [];

    private $script_map = [];         // ilRecSysModelScript attributes: script_id, obj_id, start_page, end_page, difficulty, rating_count
    private $presentation_map = [];   // ilRecSysModelPresentation attributes: presentation_id, obj_id, start_slide, end_slide, difficulty, rating_count
    private $video_map = [];          // ilRecSysModelVideo attributes: video_id, obj_id, start_min, end_min, difficulty, rating_count
    private $picture_map = [];        // ilRecSysModelPicture attributes: picture_id, obj_id, difficulty, rating_count
    private $weblink_map = [];        // ilRecSysModelWeblink attributes: weblink_id, obj_id, difficulty, rating_count
    private $bibleography_map = [];   // ilRecSysModelBibliography attributes: bibl_id, obj_id, difficulty, rating_count
    private $test_map = [];           
    private $exercise_map = [];  
    

    // Attributes:
    /*
    private $tags;                  //ilRecSysModelTags attributes: tag_id, tag_name, tag_description, tag_occurence;
    private $tag_names;                  
    private $tagsPerMaterial;       //ilRecSysModelTagsPerMaterial attributes: tag_id, material_type, material_id
    private $overallTagsUser;       //ilRecSysModelOverallTagsUser attributes: tag_id, usr_id, priority, tag_counter
    private $specificTagsUser;      //ilRecSysModelSpecificTagsUser attributes: usr_id, material_type, material_id
    private $obj_id;
    private $material;
    private $material_id;
    private $material_type;
    private $from_to;
    private $difficulty;
    private $rating_count;*/
    //-------------------------------------------------------------
    // Constructor and instantiating functions

    // Constructor:
    //written by @Anna Eschbach-Dymanus
    /*public function __construct($obj_id, $material_id, $tag_names, $material_type, $from_to=null)
    {
        $this->tag_names = $tag_names;
        $this->obj_id = $obj_id;
        $this->material_id = $material_id;
        $this->material_type = $material_type;
        $this->from_to = $from_to;   
        $this->difficulty = 0; //future feature
        $this->rating_count = 0;    //future feature 
    }*/

    /**
     * new implementation Joel
     */
    private function __construct() {
        $this->script_counter = self::getLastScriptId();
        $this->presentation_counter = self::getLastPresentationId();
        $this->video_counter = self::getLastVideoId();
        $this->picture_counter = self::getLastPictureId();
        $this->weblink_counter = self::getLastWeblinkId();
        $this->bibliography_counter = self::getLastBiblId();
        $this->test_counter = self::getLastTestId();
        $this->exercise_counter = self::getLastExerciseId();
        $this->tag_counter = self::getLastTagId();
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
    // functions for handling the material specific ids
    
    /**
     * class function that gets the lastScriptId-attribute from the table ui_uihk_recsys_m_c_s
     */
    private static function getLastScriptId() {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT script_id FROM ui_uihk_recsys_m_c_f_s ORDER BY script_id DESC LIMIT 1");
        if ($ilDB->numRows($queryResult) === 0) {
            $last_script_id = 0;
        } else {
            $last_script_id = $ilDB->fetchAssoc($queryResult);
            $last_script_id = $last_script_id['script_id'];
        }
        return $last_script_id;
    }

    /**
     * class function that gets the lastPresentationId-attribute from the table ui_uihk_recsys_m_c_p
     */
    private static function getLastPresentationId() {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT presentation_id FROM ui_uihk_recsys_m_c_f_p ORDER BY presentation_id DESC LIMIT 1");
        if ($ilDB->numRows($queryResult) === 0) {
            $last_presentation_id = 0;
        } else {
            $last_presentation_id = $ilDB->fetchAssoc($queryResult);
            $last_presentation_id = $last_presentation_id['presentation_id'];
        }
        return $last_presentation_id;
    }

    /**
     * class function that gets the lastVideoId-attribute from the table ui_uihk_recsys_m_c_f_v
     */
    private static function getLastVideoId() {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT video_id FROM ui_uihk_recsys_m_c_f_v ORDER BY video_id DESC LIMIT 1");
        if ($ilDB->numRows($queryResult) === 0) {
            $last_video_id = 0;
        } else {
            $last_video_id = $ilDB->fetchAssoc($queryResult);
            $last_video_id = $last_video_id['video_id'];
        }
        return $last_video_id;
    }

    /**
     * class function that gets the lastPictureId-attribute from the table ui_uihk_recsys_m_c_pic
     */
    private static function getLastPictureId() {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT picture_id FROM ui_uihk_recsys_m_c_pic ORDER BY picture_id DESC LIMIT 1");
        if ($ilDB->numRows($queryResult) === 0) {
            $last_picture_id = 0;
        } else {
            $last_picture_id = $ilDB->fetchAssoc($queryResult);
            $last_picture_id = $last_picture_id['picture_id'];
        }
        return $last_picture_id;
    }

    /**
     * class function that gets the lastWeblinkId-attribute from the table ui_uihk_recsys_m_c_w
     */
    private static function getLastWeblinkId() {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT weblink_id FROM ui_uihk_recsys_m_c_w ORDER BY weblink_id DESC LIMIT 1");
        if ($ilDB->numRows($queryResult) === 0) {
            $last_weblink_id = 0;
        } else {
            $last_weblink_id = $ilDB->fetchAssoc($queryResult);
            $last_weblink_id = $last_weblink_id['weblink_id'];
        }
        return $last_weblink_id;
    }

    /**
     * class function that gets the lastBiblId-attribute from the table ui_uihk_recsys_m_c_bib
     */
    private static function getLastBiblId() {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT bibl_id FROM ui_uihk_recsys_m_c_bib ORDER BY bibl_id DESC LIMIT 1");
        if ($ilDB->numRows($queryResult) === 0) {
            $last_bibl_id = 0;
        } else {
            $last_bibl_id = $ilDB->fetchAssoc($queryResult);
            $last_bibl_id = $last_bibl_id['bibl_id'];
        }
        return $last_bibl_id;
    }

    /**
     * class function that gets the lastTestId-attribute from the table ui_uihk_recsys_m_a_t
     */
    private static function getLastTestId() {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT test_id FROM ui_uihk_recsys_m_a_t ORDER BY test_id DESC LIMIT 1");
        if ($ilDB->numRows($queryResult) === 0) {
            $last_test_id = 0;
        } else {
            $last_test_id = $ilDB->fetchAssoc($queryResult);
            $last_test_id = $last_test_id['test_id'];
        }
        return $last_test_id;
    }

    /**
     * class function that gets the lastExerciseId-attribute from the table ui_uihk_recsys_m_a_e
     */
    private static function getLastExerciseId() {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT exercise_id FROM ui_uihk_recsys_m_a_e ORDER BY exercise_id DESC LIMIT 1");
        if ($ilDB->numRows($queryResult) === 0) {
            $last_exercise_id = 0;
        } else {
            $last_exercise_id = $ilDB->fetchAssoc($queryResult);
            $last_exercise_id = $last_exercise_id['exercise_id'];
        }
        return $last_exercise_id;
    }

    private static function getLastTagId() {
        global $ilDB;
        $queryResult = $ilDB->query("SELECT tag_id FROM ui_uihk_recsys_tags ORDER BY tag_id DESC LIMIT 1");
        if($ilDB->numRows($queryResult) === 0) {
            $last_tag_id = 0;
        } else {
            $last_tag_id = $ilDB->fetchAssoc($queryResult);
            $last_tag_id = $last_tag_id['tag_id'];
        }
        return $last_tag_id;
    }

    /**
     * getter and increment functions for the specific matirial counter
     */
    public function incrementScriptCounter() { $this->script_counter++; }

    public function getScriptCounter() { return $this->script_counter; }

    public function incrementPresentationCounter() { $this->presentation_counter++; }

    public function getPresentationCounter() { return $this->presentation_counter; }

    public function incrementVideoCounter() { $this->video_counter++; }

    public function getVideoCounter() { return $this->video_counter; }

    public function incrementPictureCounter() { $this->picture_counter++; }

    public function getPictureCounter() { return $this->picture_counter; }

    public function incrementWeblinkCounter() { $this->weblink_counter++; }

    public function getWeblinkCounter() { return $this->weblink_counter; }

    public function incrementBiblCounter() { $this->bibliography_counter++; }

    public function getBibliographyCounter() { return $this->bibliography_counter; }

    public function incrementTestCounter() { $this->test_counter++; }

    public function getTestCounter() { return $this->test_counter; }

    public function incrementExerciseCounter() { $this->exercise_counter++; }

    public function getExerciseCounter() { return $this->exercise_counter; }

    public function incrementTagCounter() { $this->tag_counter++; }

    public function getTagCounter() {return $this->tag_counter; }


    //written by @Anna Eschbach-Dymanus
    /*
    private function get_material()
    {
        switch ($this->material_type){
            case "script":
                //a real material_id, an existing material
                if($this->material_id >= 0){ 
                    $material = ilRecSysModelScript::fetchByMaterialID($this->material_id);
                    $material->setStart_page($this->from_to[0]);
                    $material->setEnd_page($this->from_to[1]);
                    $material->setDifficulty($this->difficulty);
                    //$material->rating_count = $this->rating_count;
                    $material->updateMaterial();
                }
                //a new material
                else{
                    $material = new ilRecSysModelScript(null, $this->obj_id, $this->from_to[0], $this->from_to[1], 0, 0); //set difficulty and rating_count to null for now
                    $material->addNewMaterial();
                }
                break;
            // Add other materials           
        }
        return $material;
    }*/

    //-------------------------------------------------------------
    // create functions:
    
    /**
     * creates tag and adds it to the database as well as into the tags_map
     * @return ilRecSysModelTags - object with the newly created tag
     */
    public function createNewTag($tag_name, $tag_description){
        // increment tag counter and create tag object
        $this->incrementTagCounter();
        $tag_id = $this->getTagCounter();
        $tag = new ilRecSysModelTags($tag_id, $tag_name, $tag_description, 0);
        $tag->addNewTag(); // TODO: change this in Model class
        $this->tags_map[$tag_id] = $tag;
        return $tag;
    }

    /**
     *  args        array of arguments the first 2 are defined the rest depends on the matirial type: 
     *  args[0]      material_type
     *  args[1]      obj_id
     *  args[2]      
     * ...          (depends on material_type)
     */
    public function createMaterialTag($tag_id, array $args){
        // check whether tag_id exists

        switch($args[0]){
            case 0:     // script
                // 1. get unique counter and increment it by one
                $this->incrementScriptCounter();
                $script_id = $this->getScriptCounter();
                
                // 2. create ilRecSysModelScript "Material" class
                $script = new ilRecSysModelScript($script_id, $args[1], $args[2], $args[3], 0.0, 0);
                $script->createScript();
                $this->script_map[$script_id] = $script;
                
                // 3. add script in the tagToMaterial table
                $tag_to_material = new ilRecSysModelTagsPerMaterial($tag_id, $args[0], $script_id);
                $tag_to_material->addNewTagToMaterial();
                
                break;

            case 1:     // presentation
                // 1. get unique counter and increment it by one
                $this->incrementPresentationCounter();
                $presentation_id = $this->getPresentationCounter();
                
                // 2. create ilRecSysModelPresentation "Material" class
                $presentation = new ilRecSysModelPresentation($presentation_id, $args[1], $args[2], $args[3], 0.0, 0);
                $presentation->createPresentation();
                $this->presentation_map[$presentation_id] = $presentation;
                
                // 3. add script in the tagToMaterial table
                $tag_to_material = new ilRecSysModelTagsPerMaterial($tag_id, $args[0], $presentation_id);
                $tag_to_material->addNewTagToMaterial();
                
                break;
                
            case 2:     // video
                // 1. get unique counter and increment it by one
                $this->incrementVideoCounter();
                $video_id = $this->getVideoCounter();
                
                // 2. create ilRecSysModelPresentation "Material" class
                $video = new ilRecSysModelVideo($video_id, $args[1], $args[2], $args[3], 0.0, 0);
                $video->createVideo();
                $this->video_map[$video_id] = $video;
                
                // 3. add script in the tagToMaterial table
                $tag_to_material = new ilRecSysModelTagsPerMaterial($tag_id, $args[0], $video_id);
                $tag_to_material->addNewTagToMaterial();
                break;
            case 3:     // picture
                break;
            case 4:     // weblink
                // add weblink in weblink table
                $this->incrementWeblinkCounter();
                $weblink_id = $this->getWeblinkCounter();
                $weblink = new ilRecSysModelWeblink($weblink_id, $args[1], 0.0, 0);
                $weblink->createWeblink();
                $this->weblink_map[$weblink_id] = $weblink;
                
                //add weblink in tagToMaterial table
                $tag_to_material = new ilRecSysModelTagsPerMaterial($tag_id, $args[0], $weblink_id);
                $tag_to_material->addNewTagToMaterial();
                         
                break;

            case 5:     // bibliography
                break;
            case 6:     // test
                break;
            case 7:     // exercise
                // 1. get unique counter and increment it by one
                $this->incrementExerciseCounter();
                $exercise_id = $this->getExerciseCounter();
                
                // 2. create ilRecSysModelExercise "Material" class
                $exercise = new ilRecSysModelExercise($exercise_id, $args[1], $args[2], $args[3], 0.0, 0);
                $exercise->createExercise();
                $this->exercise_map[$exercise_id] = $exercise;
                
                // 3. add script in the tagToMaterial table
                $tag_to_material = new ilRecSysModelTagsPerMaterial($tag_id, $args[0], $exercise_id);
                $tag_to_material->addNewTagToMaterial();
                break;
            default:    // default case nothing happens. new entries must be inserted here
                return;
        }
        
        // update tag occurence 
        $tag = $this->getTag($tag_id);
        $tag->incrementOccurrence();  
    }
    
    // ____________________________________________________________
    // get functions:
    
    //written by @Anna Eschbach-Dymanus
    private function getTagsByName($tag_names)
    {
        $tags = array();
        foreach($tag_names as $tag_name){
            $tag = ilRecSysModelTags::fetchTagByName($tag_name);
            if($tag == null){
                $tag = $this -> createNewTag($tag_name, "");
            }
            $tags[] = $tag;
        }
        return $tags;
    }
    
    /**
     *  get a Tag
     *  @param tag_id of the tag that is loaded
     *  @return tag if tag exists in database, else it returns null 
     */
    public function getTag($tag_id) {
        if(array_key_exists($tag_id, $this->tags_map)){
            return $this->tags_map[$tag_id];
        }
        return ilRecSysModelTags::fetchTagById($tag_id);  
    }

    /**
     * returns the tags for which the tag_id array is given
     * @param tag_ids array
     * @return result_array that stores all tags that qualify
     */
    public function getTags($tag_ids) {
        $result_array = array();
        //go through tags_ids to load them eighter from the map or the database
        foreach($tag_ids as $tag_id){
            $tag = $this->getTag($tag_id);
            if($tag != null){
                array_push($result_array, $tag);
            }
        }
        return $result_array;
    }
    
    /**
     * get the Material for a Tag given the material type and the id of that material (e.g. script_id)
     * @return material 
     */
    public function getMaterialTag($material_id, $material_type) {
        switch($material_type){
            case 0: //skript
                // check wether script is in Map
                if(array_key_exists($material_id, $this->script_map)){
                    $script = $this->script_map[$material_id];
                } else {
                    $script = ilRecSysModelScript::fetchByMaterialID($material_id);
                    //check wether this is null
                    if($script == null){
                        return null;
                    }
                    $this->script_map[$material_id];
                }
                return $script;
            case 1: //presentation
                // check whether presentation is in Map
                if(array_key_exists($material_id, $this->presentation_map)){
                    $presentation = $this->presentation_map[$material_id];
                } else {
                    $presentation = ilRecSysModelPresentation::fetchByMaterialID($material_id);
                    //check wether this is null
                    if($presentation == null){
                        return null;
                    }
                    $this->presentation_map[$material_id];
                }
                return $presentation;
            case 2: // video
                // check whether video is in Map
                if(array_key_exists($material_id, $this->video_map)){
                    $video = $this->video_map[$material_id];
                } else {
                    $video = ilRecSysModelVideo::fetchByMaterialID($material_id);
                    //check wether this is null
                    if($video == null){
                        return null;
                    }
                    $this->video_map[$material_id];
                }
                return $video;
            case 3: // picture
            case 4: // weblink
            case 5: // bibliography
            case 6: // test
            case 7: // exercise
                // check whether exercise is in Map
                if(array_key_exists($material_id, $this->exercise_map)){
                    $exercise = $this->exercise_map[$material_id];
                } else {
                    $exercise = ilRecSysModelExercise::fetchByMaterialID($material_id);
                    //check wether this is null
                    if($exercise == null){
                        return null;
                    }
                    $this->exercise_map[$material_id];
                }
                return $exercise;
            default: 
                return;
        }
    }

    public function getAllMaterialsForTag($tag_id) {
        //get materials that are already stored in hashmap
        $result_array = array();
        $material_type_id_pair = ilRecSysModelTagsPerMaterial::getAllMaterialIDsForTag($tag_id);
        foreach($material_type_id_pair as $pair){
            array_push($result_array, $this->getMaterialTag($pair[1], $pair[0]));
        }
        //null value if empty
        return $result_array;
    }

    public function getTagsForMaterial($material_id, $material_type){
        $result_array = array();
        $tag_ids = ilRecSysModelTagsPerMaterial::getAllTagIdsForMaterial($material_id, $material_type);
        foreach($tag_ids as $tag_id){
            array_push($result_array, $this->getTag($tag_id));
        }
        //null value if empty
        return $result_array;
    }


    
    // ____________________________________________________________
    // update functions:

   //written by @Anna Eschbach-Dymanus
   public function update_db($obj_id, $material_id, $tag_names, $material_type, $from_to=null)
   {
       //get and create new tag if necessary
       $tags = $this->getTagsByName($tag_names);
       $tag_ids = array();
       foreach($tags as $tag){
           array_push($tag_ids, $tag->getTag_id());
       }
       
       //if real material id, get the material
       $this->debug_to_console($material_id, "material_id");
       if($material_id >= 0){
           //getMaterialTag has an odd function name since it doesnt do anything with tag
           $material = $this->getMaterialTag($material_id, $material_type);
           if($from_to != null){
               $material->setStart_page($from_to[0]);
               $material->setEnd_page($from_to[1]);
           }
           else{
               //Todo: set description of material
           }
           $material->update();
       }
       else{
           if($from_to == null){
               $material = $this->createMaterial([$material_type, $obj_id]);
           }
           else{
               $this->debug_to_console("creates Material");
               $material = $this->createMaterial([$material_type, $obj_id, $from_to[0], $from_to[1]]);
           }
           $material_id = $material->get_id();
       }
       //update Material in datbase
       $this->debug_to_console("material updated");

       //create new tagsPerMaterial
       $all_tags_per_material_ids = ilRecSysModelTagsPerMaterial::getAllTagIdsForMaterial($material_id , $material_type);
       //get new and deleted tags
       $new_tags_ids = array_diff($tag_ids, $all_tags_per_material_ids);
       $deleted_tags_ids = array_diff($all_tags_per_material_ids, $tag_ids);
       //delete tagsPerMaterial
       foreach($deleted_tags_ids as $deleted_tag_id){
           $deleted_tag_to_mat = ilRecSysModelTagsPerMaterial::fetchTagsToMaterial($material_id, $deleted_tag_id, $material_type);
           $deleted_tag_to_mat->deleteTagToMaterial();
           $this->getTag($deleted_tag_id)->decrementOccurrence();

       }
       foreach($new_tags_ids as $new_tag_id){
           $new_tag_to_mat = new ilRecSysModelTagsPerMaterial($new_tag_id, $material_type, $material_id);
           $new_tag_to_mat->addNewTagToMaterial();
           $this->getTag($new_tag_id)->incrementOccurrence();
       }
   }

    //give feedback
    
    // ------------------------------------------------------------------------------------------------------------------------------------------
    // delete functions:

    /**
     *  deletes a tag by its id and removes every associated material in the according tables
     */
    public function saftyDeletionTag($tag_id) {
        
    }

    /**
     *  deletes a tag in the tag_table. 
     *  Carefull, it does not delete the associated material entries in the according tables
     */
    public function deleteTag($tag_id) {
        //get Tag either from map or table

    }

    /**
     *  delete a piece of Material that was tagged and decrements the tagoccurence
     */
    public function deleteSingleMaterialTag($tag_id, $material_id, $material_type) {
        $this->deleteMaterial($material_id, $material_type);
        // update tag occurence 
        $tag = $this->getTag($tag_id);
        $tag->decrementOccurrence();
    }

    /**
     *  delete a piece of material that was tagged
     */
    private function deleteMaterial($tag_id, $material_id, $material_type) {
        $materialTag = $this->getMaterialTag($material_id, $material_type);
        //if material Tag is empty return 

        switch($material_type){
            case 0: //skript
                $materialTag->deleteMaterial($material_id);
                unset($this->script_map[$material_id]);
            case 1: //presentation
                $materialTag->deletePresentation($material_id);
                unset($this->presentation_map[$material_id]);
            case 2: // video
                $materialTag->deleteVideo($material_id);
                unset($this->video_map[$material_id]);
            case 3: // picture
                $materialTag->deletePicture($material_id);
                unset($this->picture_map[$material_id]);
            case 4: // weblink
                $materialTag->deleteWeblink();
                unset($this->weblink_map[$material_id]);
            case 5: // bibliography
                $materialTag->deleteBibliography();
                unset($this->bibleography_map[$material_id]);
            case 6: // test
            case 7: // exercise
                $materialTag->deleteExercise();
                unset($this->exercise_map[$material_id]);
            default: 
                return;
        }
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