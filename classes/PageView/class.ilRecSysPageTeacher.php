<?php

include_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecommenderSystemConst.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelCourse.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelStudent.php');
#require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelFeedback.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecSysCoreDB.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/util/class.ilRecSysListMaterials.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/PageView/class.ilRecSysPageStudentRecommender.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/class.ilRecommenderSystemPageGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelTags.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelScript.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelExercise.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelVideo.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelPresentation.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelWeblink.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelTagHandler.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelTagsPerSection.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/PageView/class.ilRecSysPageUtils.php');
/**
 * Handles the Commands of the Student Overview.
 *
 * @author  Anna Eschbach-Dymanus <anna.maria.eschbach-dymanus@uni-mannheim.de> * 
 *
 */
class ilRecSysPageTeacher {
    const PLUGIN_DIR = "./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem";
        
    //var $ctrl;
    private $crs_id;	// ref_id of the course
    private $il_crs_id;    // obj_id of course
    
    private $CourseObject;
    private $ilUser;
    private $RecSysCourse;
    #private $CoreDB;
    private $plugin;
    private $tag_handler;
    
    
    public function __construct( $crs_id ) {
        global $ilCtrl, $ilUser;
        
        $this->ctrl         = $ilCtrl;
        $this->crs_id       = $crs_id;       
        $this->il_crs_id    = ilObject::_lookupObjectId($crs_id);
        $this->CourseObject = new ilObjCourse($crs_id);
        $this->ilUser       = $ilUser;
        $this->tag_handler  = ilRecSysModelTagHandler::getInstance();

        $this->RecSysCourse   = ilRecSysModelCourse::getOrCreateRecSysCourse($crs_id);
        #$this->CoreDB          = null;//new ilRecSysCoreDB("admin");
        //to @Joel: sth bugs out in RecSysCoreDbdriverLibrary, it is sth with the init function of the db driver
        
        $this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'RecommenderSystem');
    }
    
    // ---------------------------------------------------------------------------
    
    public function show_course()
    {
        $this->debug_to_console("show_course");
        global $tpl;
     
        $tplRecSys = $this->getTeacherMaterialTemplate();
        $htmlContent = $tplRecSys->get();
        $tpl->setContent($htmlContent);
    }

    function debug_to_console($data, $context = 'Debug in Console') {

        // Buffering to solve problems frameworks, like header() in this and not a solid return.
        ob_start();
    
        $output  = 'console.info(\'' . $context . ':\');';
        $output .= 'console.log(' . json_encode($data) . ');';
        $output  = sprintf('<script>%s</script>', $output);
    
        echo $output;
    }   



    // --- Materials -----------------------------------------------------------------------

    public function save_material_tags() {
        $status = $_POST['form_recsys'];

        //create empty map in map of obj_id to section_id to tags
        $material_tags = array();
        $types = array();
        $deletions = array();
        foreach ($_POST as $key => $value) {
            $this->debug_to_console($key, 'key');
            $this->debug_to_console($value, 'value');

            if($key == "DELETE"){
                $item_sections = explode("__", $value);
                foreach ($item_sections as $item_section){
                    if($item_section == ""){
                        continue;
                    }
                    $item_section = explode("_", $item_section);
                    $obj_id = $item_section[0];
                    $section_id = $item_section[1];
                    $material_type = $item_section[2];
                    if (!array_key_exists($obj_id, $deletions)){
                        $deletions[$obj_id] = array();
                    }
                    array_push($deletions[$obj_id], array($section_id, $material_type));
                }
                continue;
            }

            //parse what is posted
            list($column_1, $column_2, $column_3) = explode("_", $key);

            //Here column_2 is obj id and $column_1 the material type and $column_3 the file type if mat is file
            if($column_3 == "filetype"){
                $this->debug_to_console($value, 'got here');
                $types[$column_2] = $value;
            }
            else if(ctype_digit($column_1) & ctype_digit(trim($column_2, "-"))){
                //Here column_1 is obj_id and column_2 is section_id
                if (!array_key_exists($column_1, $material_tags)){
                    $material_tags[$column_1] = array();
                }
                if (!array_key_exists($column_2, $material_tags[$column_1])){
                    $material_tags[$column_1][$column_2] = array();
                    $material_tags[$column_1][$column_2]["tags"] = array();
                }
                //this pushed value is the fromto/desc field
                if (str_contains($column_3, 'desc')){
                    if ($column_3 == "descfrom"){
                        if (array_key_exists("fromto", $material_tags[$column_1][$column_2])){
                            $material_tags[$column_1][$column_2]["fromto"][0] = $value;
                        }
                        else{
                            $material_tags[$column_1][$column_2]["fromto"] = array($value, "0");
                        }
                    }
                    else if($column_3 == "descto"){
                        if (array_key_exists("fromto", $material_tags[$column_1][$column_2])){
                            $material_tags[$column_1][$column_2]["fromto"][1] = $value;
                        }
                        else{
                            $material_tags[$column_1][$column_2]["fromto"] = array("0", $value);
                        }
                    } //TODO: FIX THIS CODE TO PROPERLY ENCODE INFO
                    else if($column_3 == "descfrommin"){
                        if (array_key_exists("fromto", $material_tags[$column_1][$column_2])){
                            $material_tags[$column_1][$column_2]["fromto"][0] = $value.$material_tags[$column_1][$column_2]["fromto"][0];
                        }
                        else{
                            $material_tags[$column_1][$column_2]["fromto"] = array($value, "");
                        }
                    }
                    else if($column_3 == "descfromsec"){
                        if (array_key_exists("fromto", $material_tags[$column_1][$column_2])){
                            $material_tags[$column_1][$column_2]["fromto"][0] = $material_tags[$column_1][$column_2]["fromto"][0].":".$value;
                        }
                        else{
                            $material_tags[$column_1][$column_2]["fromto"] = array($value, "");
                        }
                    }
                    else if($column_3 == "desctomin"){
                        if (array_key_exists("fromto", $material_tags[$column_1][$column_2])){
                            $material_tags[$column_1][$column_2]["fromto"][1] = $value.$material_tags[$column_1][$column_2]["fromto"][1];
                        }
                        else{
                            $material_tags[$column_1][$column_2]["fromto"] = array("", $value);
                        }
                    }
                    else if($column_3 == "desctosec"){
                        if (array_key_exists("fromto", $material_tags[$column_1][$column_2])){
                            $material_tags[$column_1][$column_2]["fromto"][1] = $material_tags[$column_1][$column_2]["fromto"][1].":".$value;
                        }
                        else{
                            $material_tags[$column_1][$column_2]["fromto"] = array("", $value);
                        }
                    }
                    else{
                        $material_tags[$column_1][$column_2]["fromto"] = array($value, $value);
                    }
                }
                //this value is a tag
                else if(ctype_digit($column_3)){
                    if($value != ""){
                        array_push($material_tags[$column_1][$column_2]["tags"], $value);
                    }
                }
                else if(str_contains($column_3, 'difficulty')){
                    $material_tags[$column_1][$column_2]["difficulty"] = $value;
                }
            }
            
        }

        $this->debug_to_console($deletions, 'deletions');


        //fill in missing types
        foreach ($material_tags as $obj_id => $sections){
            if (!array_key_exists($obj_id, $types)){
                //get obj from obj_id
                $obj = ilObjectFactory::getInstanceByObjId($obj_id);
                $types[$obj_id] = $obj->type;
            }
        }

        //remove negative section ids without tags from material_tags
        foreach ($material_tags as $obj_id => $sections){
            foreach ($sections as $section_id => $desc_tags){
                if (empty($desc_tags["tags"])){
                    unset($material_tags[$obj_id][$section_id]);
                }
            }
        }

        $this->debug_to_console($material_tags, 'saved material_tag_entries');
        $this->debug_to_console($types, 'saved types');

        //remove deleted sections
        foreach ($deletions as $obj_id => $sections){
            $material_type = $types[$obj_id];
            foreach ($sections as $section_id_and_type){
                $section_id = $section_id_and_type[0];
                $material_type = ilRecSysPageUtils::MATERIAL_TYPE_TO_INDEX[$section_id_and_type[1]];
                $this->tag_handler->deleteSection($section_id, $material_type);
            }
        }


        //iterate over materials and create new
        foreach ($material_tags as $obj_id => $sections){
            //iterate over sections
            foreach ($sections as $section_id => $desc_tags){
                //save the tag in the database
                if(!array_key_exists("fromto", $desc_tags)){
                    $desc_tags["fromto"] = null;
                }
                if(!array_key_exists("difficulty", $desc_tags)){
                    $desc_tags["difficulty"] = null;
                }
                $type = ilRecSysPageUtils::MATERIAL_TYPE_TO_INDEX[$types[$obj_id]];
                $this->tag_handler->updateSection($this->crs_id, $obj_id, $section_id, $type, $desc_tags["tags"], $desc_tags["fromto"], $desc_tags["difficulty"]);
            }
        }

        ilUtil::sendSuccess($this->plugin->txt("recsys_saved_sucessfully"), true);
        
        $this->show_course();
    }

       

    private function getTeacherMaterialTemplate() {
        $tpl = new ilTemplate("tpl.teacher.html", true, true, self::PLUGIN_DIR);
        
        $material_types = ilRecSysPageUtils::getAllValidObjTypes();
        foreach ($material_types as $material_type){

            
            // Get Materials
            $materials = ilRecSysPageUtils::getItemsOfCourse($this->CourseObject, $material_type);


            $ilObjGUI = new ilObjCourseGUI("", $this->crs_id, true, false);
            $CourseContent = new ilRecSysListMaterials($ilObjGUI);
            
            // If no Material exists: cancel here        
            if(!count($materials)) {
                continue;
            }


            $all_tags = ilRecSysModelTags::fetchAllTagIDsForCourse($this->crs_id);
            //get names
            $all_tags = array_map(function($tag_id){
                return ilRecSysModelTags::fetchTagById($tag_id)->getTag_name();
            }, $all_tags);
            $all_tags[] = "";


            foreach ($materials as $item) {
                
                $file_type = null;
                if($material_type == "file"){
                    $file_type = ilRecSysPageUtils::fileIsType($item['obj_id']);	
                }

                $material_tags = ilRecSysPageUtils::getMaterialTagEntries($item['obj_id'], $file_type == null ?  $material_type : $file_type);
                
                //replace section with actual database section ids instead of just an iterator
                foreach ($material_tags as $tags) {
                    $counter = 0;
                    $section = $tags[0];
                    foreach ($tags[1] as $subtag){
                        foreach ($all_tags as $all_tag){
                            $tpl->setCurrentBlock("Alltags");
                            $tpl->setVariable("ALL_TAG", $all_tag);
                            $tpl->setVariable("TAG_SELECTED", $all_tag == $subtag ? "selected" : "");
                            $tpl->parseCurrentBlock();
                        }
                        //get index of tag in all tags for selection default
                        $tpl->setCurrentBlock("Subtags");

                        $tpl->setVariable("TAG", $subtag);
                        $tpl->setVariable("ITEM_ID", $item['obj_id']);
                        $tpl->setVariable("I", $counter);
                        $tpl->setVariable("SECTION", $section);
                        $tpl->parseCurrentBlock();
                        $counter++;
                    }
                    foreach ($all_tags as $all_tag){
                        $tpl->setCurrentBlock("TemplateAlltags");
                        $tpl->setVariable("ALL_TAG", $all_tag);
                        $tpl->setVariable("TAG_SELECTED", $all_tag == "" ? "selected" : "");
                        $tpl->parseCurrentBlock();
                    }
                    $difficulty = $tags[3];                    
                    $star_list = []; 
                    for ($i = 0; $i < 5; $i++){
                        if ($i <= $difficulty){
                            array_push($star_list, "");
                        }
                        else{
                            array_push($star_list, "-empty");
                        }
                    }
                    $star_counter = 0;
                    foreach ($star_list as $star){
                        $tpl->setCurrentBlock("Stars");
                        $tpl->setVariable("ITEM_ID", $item['obj_id']);
                        $tpl->setVariable("SECTION", $section);
                        $tpl->setVariable("STAR_EMPTY", $star);
                        $tpl->setVariable("STAR_COUNT", $star_counter);
                        $tpl->parseCurrentBlock();
                        $star_counter++;
                    }
                    $tpl->setCurrentBlock("Tags");
                    $tpl->setVariable("I", $counter);
                    $tpl->setVariable("ADD_TAG", "Add Tag");#$this->plugin->txt("recsys_add_tag"));
                    $tpl->setVariable("NEW_TAG", "New Tag");#$this->plugin->txt("recsys_new_tag"));
                    $tpl->setVariable("SECTION", $section);
                    $tpl->setVariable("FROM", $tags[2][0]);
                    $tpl->setVariable("TO", $tags[2][1]);
                    $tpl->setVariable("ITEM_ID", $item['obj_id']);
                    $tpl->setVariable("SAVING", $this->plugin->txt("recsys_teacher_save"));
                    $tpl->setVariable("ADD_SECTION", "Add Row");#$this->plugin->txt("recsys_new_section"));
                    $tpl->setVariable("MATERIAL_TYPE", $material_type);
                    $tpl->setVariable("FILE_TYPE", $file_type);

                    $tpl->parseCurrentBlock();
                }
                $tpl->setCurrentBlock("Materials");
                $tpl->setVariable("MATERIAL_TYPE", $material_type);
                
                //Maybe solve with iterator in future
                $tpl->setVariable("IS_SCRIPT", $file_type=="script" ? "selected" : "");
                $tpl->setVariable("IS_EXERCISE", $file_type=="exc" ? "selected" : "");
                $tpl->setVariable("IS_VIDEO", $file_type=="video" ? "selected" : "");
                $tpl->setVariable("IS_PRESENTATION", $file_type=="presentation" ? "selected" : "");

                $itemHtml = $CourseContent->getHtmlItem($item);
                $tpl->setVariable("SECTION", $material_tags[0][0] == -1 ? -2 : -1); //set to -2 if empty entry already exists
                $tpl->setVariable("ITEM_ID", $item['obj_id']); //TODO: check if this is correct
                $tpl->setVariable("ITEM_HTML", $itemHtml);
                $tpl->parseCurrentBlock();
                //$item_count++;                
            }
            $tpl->setCurrentBlock("Types");
            //$tpl->setVariable("MATERIAL_TYPE", $material_type);
            $tpl->setVariable("MATERIAL_TYPE",  $this->plugin->txt($material_type));

            $tpl->parseCurrentBlock(); 
        }
        $tpl->setVariable("SAVING", $this->plugin->txt("recsys_teacher_save"));
        $tpl->setVariable("RECSYS_TEACHER_SAVE", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_TEACHER_SAVE));
        return $tpl;
    }

    
    

    /*
    public function save_tags(){
        $Tag = new ilRecSysModelTags($_POST['tag_id'], $this->RecSysCourse->getRef_id(), $_POST['tag'], $_POST['comment']);
        $Tag->setTag_name();
        $Tag->setTag_comment();
        $Tag->setPriority();
        //TO DO: Parse model course etc.
        //TO DO: Save tags in the materials database or sth 
        $Tag->save();
    }

    public function delete_section(){
        
        if (isset($_POST['tag_id']) && is_numeric($_GET['tag_id'])){
            $tag_id = $_GET['tag_id'];
            $Tag = new ilRecSysModelTags($_POST['tag_id'], $this->RecSysCourse->getRef_id(), $_POST['tag'], $_POST['comment']);
            $Tag->delete();
        }

    }
    */
    /*
    public function addNewRow($tplMain) {
        $tpl = $this->plugin->getTemplate("tpl.teacher.html");
        
        // Static Data
        #$tpl->setVariable("TXT_MODULE_IG_BUTTON_NEW_LABEL", $this->plugin->txt("leap_student_ig_button_new_label"));
        #$tpl->setVariable("TXT_MODULE_IG_BUTTON_ADD_LABEL", $this->plugin->txt("leap_student_ig_button_add_label"));
        #$tpl->setVariable("TXT_MODULE_IG_BUTTON_RESET_LABEL", $this->plugin->txt("leap_student_ig_button_reset_label"));    
        
        // Dynamic Data        
        #$tags = ilRecSysModelTags::getRecSysTags($this->RecSysCourse->getRef_id());
        
        $tags[] = $this->addRowTemplate(); // adds an empty default Template
        
        foreach ($tags as $tag) {            
            // Static Texts
          
            $tpl->setVariable("TXT_MODULE_BUTTON_SAVE_TAGS", $this->plugin->txt("recsys_button_save_tags"));
            $tpl->setVariable("TXT_MODULE_BUTTON_DELETE_SECTION", $this->plugin->txt("recsys_button_delete_section"));
            $tpl->setVariable("TXT_MODULE_ADD_SECTION", $this->plugin->txt("recsys_button_add_section"));        
            
            // Buttons
            $tpl->setVariable("RECSYS_TAG_SAVE", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_SAVE_TAGS)); 
            $tpl->setVariable("RECSYS_TAG_DELETE", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_DELETE_SECTION));
            $tpl->setVariable("RECSYS_TAG_ADD", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_ADD_SECTION));
            
            // Default visibility (goal template is not shown)
            $tpl->setVariable("GOAL_TEMPLATE_VISIBILITY", "hidden");          
            
            $tpl->parseCurrentBlock();
        }

        $tplMain->setVariable("MOD_SECTION", $tpl->get());
        return $tplMain;
    }
    */
}
?>