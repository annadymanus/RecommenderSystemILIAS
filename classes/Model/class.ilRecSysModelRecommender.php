<?php

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/PageView/class.ilRecSysPageUtils.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Client/class.ilRecSysClientML.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Client/Payload/class.ilRecSysTrainInput.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Client/Payload/class.ilRecSysMLInput.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Client/Payload/class.ilRecSysMLOutput.php');


//@author Eschbach-Dymanus Anna
//Recommendations are made individually for each student. Use topics for cold-starting problems.
class ilRecSysModelRecommender{
    var $ilDB;
    private $crs_id;
    private $student;
    private $usr_id; //user id of the student
    public $recommenderModels; //list of recommender models with components that can be slelected for recommendation
    private $selectedRecommenderModel; //selected recommender model with components that are used for recommendation
    private $clientML;

    public function __construct($usr_id, $crs_id)
    {
        global $ilDB;
        $this->ilDB = $ilDB;
        $this->crs_id = $crs_id;
        $this->usr_id = $usr_id;
        $this->recommenderModels = array("PythonML" => array(
                                                        array(
                                                            array("tag", "Encodes the tags occuring among material sections the user queried recommendations for."), 
                                                            array("recquery", "Encodes the material sections the user queried recommendations for."), 
                                                            array("pastquery", "Encodes all the users' past queried material sections."), 
                                                            array("pastclicked", "Encodes all recommended material sections the user clicked on in the past.")), 
                                                        "A neural network consiting of various optional single layer feed forward encoders and one single layer feed forward decoder. If training data from user interaction is sparse, the model is pretrained to predict material sections with tags that occur among queried items."), 
                                         "TagFiltering" => array(array(), "A simple filter based approach that recommends material sections with the same tags as the ones the user queried recommendations for."));
        $this->selectedRecommenderModel = $this->loadSelectedModelFromDB();
        $this->clientML = new ilRecSysClientML();
    }

    public function saveSelectedModelToDB(){
        //save the selected recommender model to the database
        $query = "SELECT * FROM ui_uihk_recsys_recmod WHERE crs_id = %s;";
        $result = $this->ilDB->queryF($query, array("integer"), array($this->crs_id));
        if($result->numRows() == 0){
            $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_recmod"
                ."(crs_id, model_name, components)"
                ." VALUES (%s,%s,%s)",
                array("integer","text","text"),
                array($this->crs_id, $this->selectedRecommenderModel[0], implode(",", $this->selectedRecommenderModel[1])));
        }
        else{
            $this->ilDB->manipulateF("UPDATE ui_uihk_recsys_recmod SET model_name = %s, components = %s WHERE crs_id = %s",
                array("text","text","integer"),
                array($this->selectedRecommenderModel[0], implode(",", $this->selectedRecommenderModel[1]), $this->crs_id));
        }
    }

    public function loadSelectedModelFromDB(){
        //load the selected recommender model from the database
        //if no model is selected, select the default model
        $query = "SELECT * FROM ui_uihk_recsys_recmod WHERE crs_id = %s;";
        $result = $this->ilDB->queryF($query, array("integer"), array($this->crs_id));
        if($result->numRows() == 0){
            return array("PythonML", array("tag", "recquery", "pastquery", "pastclicked"));
        }
        $entry = $this->ilDB->fetchAssoc($result);
        return array($entry['model_name'], explode(",", $entry['components']));
    }

    public function setRecommenderModel($recommenderModel){
        $this->selectedRecommenderModel = $recommenderModel;
        $this->saveSelectedModelToDB();

        if ($recommenderModel[0] == "PythonML"){
            $train_input = new TrainInput($this->crs_id, $recommenderModel[1]);
            $this->clientML->putTrainRequest($train_input);
        }
        else if ($recommenderModel[0] == "TagFiltering"){
            //do nothing
        }
    }

    public function getRecommenderModel(){
        return $this->selectedRecommenderModel;
    }

    function debug_to_console($data, $context = 'Debug in Console') {

        // Buffering to solve problems frameworks, like header() in this and not a solid return.
        ob_start();
    
        $output  = 'console.info(\'' . $context . ':\');';
        $output .= 'console.log(' . json_encode($data) . ');';
        $output  = sprintf('<script>%s</script>', $output);
    
        echo $output;
    }   

    public function getRecommendation(){
        if ($this->selectedRecommenderModel[0] == "PythonML"){
            return $this->getPythonMLRecommend();
        }
        else if ($this->selectedRecommenderModel[0] == "TagFiltering"){
            return $this->getTagOnlyRecommend();
        }
    }

    public function getPythonMLRecommend(){
        //Get called when somebody presses the "Recommend" button
        //@Joel entry point
        #Contains the rows in the ui_uihk_recsys_u_q table that belong to the most recent recommendation query for this student in this course
        $most_recent_query = $this->getMostRecentRecommendationQuery($this->crs_id, $this->usr_id);
        if($most_recent_query == null){
            return null;
        }
        $material_ids = array();
        $material_types = array();
        $timestamp = 0;
        foreach($most_recent_query as $query){
            $material_ids[] = (int)$query['material_id'];
            $material_types[] = (int)$query['material_type'];
            $timestamp = $query['timestamp'];
        }

        $ml_input = new MLInput($this->usr_id, $this->crs_id, $timestamp, $material_ids, $material_types, $this->selectedRecommenderModel[1]);
        $ml_output = $this->clientML->postPredictionRequest($ml_input);
        
        $recommendations = array();
        foreach($ml_output->predictions as $prediction){
            $material_type = ilRecSysPageUtils::MATERIAL_INDEX_TO_TYPE[$prediction->material_type];
            $section = ilRecSysPageUtils::getSectionBySectionIDAndMaterialType($prediction->section_id, $material_type);
            $from_to = $section->getFromTo();
            $obj_id = $section->getObId();
            $recommendations[] = array($obj_id, $prediction->section_id, $material_type, array(), $from_to, $prediction->score*100);
        }
        return $recommendations;

        //should return a list of such tuples that can be directly used to display results in frontend (will be used in ilRecSysPageStudentRecommender::addModuleRecommendedMaterials):
        // array(obj_id, section_id, material_type, [tag1, tag2,...], from_to, matching_score);
    }

    public function getTagOnlyRecommend(){
        $most_recent_query = $this->getMostRecentRecommendationQuery($this->crs_id, $this->usr_id);
        if($most_recent_query == null){
            return null;
        }

        $section_mattype_tuples = array();
        foreach($most_recent_query as $query){
            $section_mattype_tuples[] = array($query['material_id'], $query['material_type']);
        }
        $tag_ids = $this->getUniqueTags($section_mattype_tuples);
        $materials = array();
        foreach($tag_ids as $tag_id){
            $matching_mats = ilRecSysModelTagsPerSection::getAllSectionIDsForTag($tag_id);
            $materials = array_merge($materials, $matching_mats);
        }
        $materials = array_filter($materials);
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

    private function getUniqueTags($section_mattype_tuples){
        $tag_ids = array();
        foreach($section_mattype_tuples as $section_mattype){
            $tag_ids = array_merge($tag_ids, ilRecSysModelTagsPerSection::getAllTagIdsForSection($section_mattype[0], $section_mattype[1]));
        }
        $tag_ids = array_unique($tag_ids);
        $tag_ids = array_filter($tag_ids);
        return $tag_ids;
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

    public function setClickedItem($section_mattype_tuple){
        global $ilDB;
        $time = time();
        $this->ilDB->manipulateF("INSERT INTO ui_uihk_recsys_u_c"
            ."(usr_id, crs_id, material_id, material_type, timestamp)"
            ." VALUES (%s,%s,%s,%s,%s)",
            array("integer","integer","integer","integer","integer"),
            array($this->usr_id, $this->crs_id, $section_mattype_tuple[0], $section_mattype_tuple[1], $time));
        return;
    }

}






?>