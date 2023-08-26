<?php

include_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecommenderSystemConst.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelCourse.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelStudent.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelFeedback.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecSysCoreDB.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/util/class.ilRecSysListMaterials.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/PageView/class.ilRecSysPageStudentRecommender.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/class.ilRecommenderSystemPageGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelTags.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelScript.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelTagHandler.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelTagsPerMaterial.php');


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
    
    
    public function __construct( $crs_id ) {
        global $ilCtrl, $ilUser;
        
        $this->ctrl         = $ilCtrl;
        $this->crs_id       = $crs_id;       
        $this->il_crs_id    = ilObject::_lookupObjectId($crs_id);
        $this->CourseObject = new ilObjCourse($crs_id);
        $this->ilUser       = $ilUser;


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
        foreach ($_POST as $key => $value) {
            //parse what is posted
            list($obj_id, $section_id, $column) = explode("_", $key);
            
            if(!ctype_digit($obj_id)){
                //Here obj_id is material type and $section_id the obj_id
                if($obj_id == "file"){
                    if($column == "isscript"){
                        $types[$section_id] = "script";
                    }
                    else if($column == "isexercise"){
                        $types[$section_id] = "exc_sheet";
                    }
                }
                continue;
            }
            if (!array_key_exists($obj_id, $material_tags)){
                $material_tags[$obj_id] = array();
            }
            if (!array_key_exists($section_id, $material_tags[$obj_id])){
                $material_tags[$obj_id][$section_id] = array();
                $material_tags[$obj_id][$section_id]["tags"] = array();
            }
            //this pushed value is the comment field
            if ($column == 'desc'){
                $material_tags[$obj_id][$section_id]["desc"] = $value;
            }
            //this value is a tag
            else if(ctype_digit($column)){
                array_push($material_tags[$obj_id][$section_id]["tags"], $value);
            }
        }

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
                if ($section_id < 0 && empty($desc_tags["tags"])){
                    unset($material_tags[$obj_id][$section_id]);
                }
            }
        }

        //$some_model = new ilRecSysModelSomeModel();

        //iterate over materials
        foreach ($material_tags as $obj_id => $sections){
            //iterate over sections
            foreach ($sections as $section_id => $desc_tags){
                //save the tag in the database
                $this->debug_to_console($section_id, "$section_id");
                if ($desc_tags["desc"] == ""){
                    $from_to = null;
                }
                else{
                    $from_to = explode(" ",  $desc_tags["desc"]); //temporal solution 
                }
                $this->debug_to_console(array($obj_id, $section_id, $desc_tags["tags"], $types[$obj_id], $from_to), "create taghandler");
                $this->debug_to_console("end", "end");
                $tag_handler = new ilRecSysModelTagHandler($obj_id, $section_id, $desc_tags["tags"], $types[$obj_id], $from_to);
                $tag_handler->update_db();
            }
        }
        
        //I dont know why this is necessary ...
        //$this->CoreDB = new ilRecSysCoreDB("admin");
        //$this->CoreDB->createCourse( $this->RecSysCourse );
        //$this->CoreDB->checkLastConnection();

        ilUtil::sendSuccess($this->plugin->txt("recsys_saved_sucessfully"), true);
        
        $this->show_course();
    }

    private function fileIsType($obj_id){
        $material = ilRecSysModelScript::fetchByObjID($obj_id);
        if($material != null){
            return "script";
        }
        //continue try erroring other types

    }

    private function getMaterialTagEntries($obj_id, $material_type) {
        //In shape [[section_id, [tag1, tag2, tag3], from_to], ...] 
        //where from_to is the information e.g. for scripts it would be start_page, end_page, etc.
        //Right now, from_to is displayed just as a text input, but later on should be displayed with appropriate input fields
        //...Try at first to make it work for scripts and later for other material types. Start by parsing from_to into string for comment field and later parse comment back into proper values (ofc later replace with appropriate fields)
        
        $material_tag_entries = array();

        
        if($material_type == "file"){
            $material_type = $this->fileIsType($obj_id);
        }
        $this->debug_to_console($material_type, "material_type");
        switch ($material_type){
            case "script":
                $materials = ilRecSysModelScript::fetchByObjID($obj_id);
                $this->debug_to_console($materials, "materials");
                foreach($materials as $material){
                    $from_to = $material->getStart_page()." ".$material->getEnd_page();
                    $tags = ilRecSysModelTagsPerMaterial::getAllTagIds($material->get_id(), $material_type);
                    if(!empty($tags)){
                        $tag_names = array_map(function($tag){return ilRecSysModelTags::fetchTagById($tag)->getTag_name();}, $tags);
                        $material_tag_entries[] = array($material->get_id(), $tag_names, $from_to);
                    }

                }
                break;
        }
        $this->debug_to_console($material_tag_entries, "material_tag_entries");
        $this->debug_to_console($material_type, "material_type");
        //check if empty array
        if(empty($material_tag_entries)){
            $material_tag_entries = array(array(-1, array(), ""));
        }
        #$material_tag_entries = 
        return $material_tag_entries;
    }

    private function getAllValidObjTypes(){
        //get all material types that are supported by recsys
        $material_types = array("file"); //"exc", "file", "link", "lm", "tst");
        return $material_types;
    }

    private function getTeacherMaterialTemplate() {
        $tpl = new ilTemplate("tpl.teacher.html", true, true, self::PLUGIN_DIR);
        
        $material_types = $this->getAllValidObjTypes();
        foreach ($material_types as $material_type){

            
            // Get Materials
            $materials = $this->getItemsOfCourse($this->il_crs_id, $material_type);


            $ilObjGUI = new ilObjCourseGUI("", $this->crs_id, true, false);
            $CourseContent = new ilRecSysListMaterials($ilObjGUI);
            
            // If no Material exists: cancel here        
            if(!count($materials)) {
                continue;
            }
            $all_tags = ilRecSysModelTags::fetchAllTagNames();
            foreach ($materials as $item) {
                $material_tags = $this->getMaterialTagEntries($item['obj_id'], $material_type);
                
                $is_script = false;
                if($material_type == "file" && $this->fileIsType($item['obj_id']) == "script"){
                    $is_script = true;
                }
                $is_exc_sheet = false;
                if($material_type == "file" && $this->fileIsType($item['obj_id']) == "exc_sheet"){
                    $is_exc_sheet = true;
                }

                //replace row with actual database row ids instead of just an iterator
                foreach ($material_tags as $tags) {
                    $counter = 0;
                    $row = $tags[0];
                    foreach ($tags[1] as $subtag){
                        $tpl->setCurrentBlock("Subtags");
                        foreach ($all_tags as $all_tag){
                            $tpl->setCurrentBlock("Alltags");
                            $tpl->setVariable("ALL_TAG", $all_tag);
                            $tpl->setVariable("TAG_SELECTED", $all_tag == $subtag ? "selected" : "");
                            $tpl->parseCurrentBlock();
                        }
                        //get index of tag in all tags for selection default
                        $tpl->setVariable("TAG", $subtag);
                        $tpl->setVariable("ITEM_ID", $item['obj_id']);
                        $tpl->setVariable("I", $counter);
                        $tpl->setVariable("ROW", $row);
                        $tpl->parseCurrentBlock();
                        $counter++;
                    }
                    $tpl->setCurrentBlock("Tags");
                    $tpl->setVariable("I", $counter);
                    $tpl->setVariable("ADD_TAG", "Add Tag");#$this->plugin->txt("recsys_add_tag"));
                    $tpl->setVariable("NEW_TAG", "New Tag");#$this->plugin->txt("recsys_new_tag"));
                    $tpl->setVariable("ROW", $row);
                    $tpl->setVariable("COMMENT", $tags[2]);
                    $tpl->setVariable("ITEM_ID", $item['obj_id']);
                    $tpl->setVariable("SAVE", "Save");#$this->plugin->txt("recsys_save"));
                    $tpl->setVariable("ADD_ROW", "Add Row");#$this->plugin->txt("recsys_new_row"));
                    $tpl->setVariable("MATERIAL_TYPE", $material_type);
                    $tpl->setVariable("IS_SCRIPT", $is_script ? "checked" : "");
                    $tpl->setVariable("IS_EXERCISE", $is_exc_sheet ? "checked" : "");

                    $tpl->parseCurrentBlock();
                    $row++; //remove later
                }
                $tpl->setCurrentBlock("Materials");
                $tpl->setVariable("MATERIAL_TYPE", $material_type);
                $tpl->setVariable("IS_SCRIPT", $is_script ? "checked" : "");
                $tpl->setVariable("IS_EXERCISE", $is_exc_sheet ? "checked" : "");
                $itemHtml = $CourseContent->getHtmlItem($item);
                $tpl->setVariable("ROW", -1);
                $tpl->setVariable("ITEM_ID", $item['obj_id']); //TODO: check if this is correct
                $tpl->setVariable("ITEM_HTML", $itemHtml);
                $tpl->parseCurrentBlock();                
            }
            $tpl->setCurrentBlock("Types");
            $tpl->setVariable("MATERIAL_TYPE", $material_type);
            $tpl->parseCurrentBlock(); 
        }
        $tpl->setVariable("SAVE", $this->plugin->txt("recsys_teacher_save"));
        $tpl->setVariable("RECSYS_TEACHER_SAVE", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_TEACHER_SAVE));
        return $tpl;
    }

    
    private function getItemsOfCourse($crs_ref_id, $type=null)
    {
        $courseObjects = $this->CourseObject->getSubItems();
        
        $courseObjects = $courseObjects['_all'];                // get all Items of this course
        $courseObjects = $this->getAllSubItems($courseObjects, $type); // get also Items of folders
        return $courseObjects;
    }
    
    private function getAllSubItems($container, $type = null)
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
                $items = array_merge($items, $this->getAllSubItems($objects, $type));
            } else if ($item['type'] == 'grp') {
                $ilObjGroup = new ilObjGroup($item['ref_id']);
                $objects = $ilObjGroup->getSubItems();
                $objects = $objects['_all'];            
                $items = array_merge($items, $this->getAllSubItems($objects, $type));
            }
        }
        return $items;
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

    public function delete_row(){
        
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
            $tpl->setVariable("TXT_MODULE_BUTTON_DELETE_ROW", $this->plugin->txt("recsys_button_delete_row"));
            $tpl->setVariable("TXT_MODULE_ADD_ROW", $this->plugin->txt("recsys_button_add_row"));        
            
            // Buttons
            $tpl->setVariable("RECSYS_TAG_SAVE", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_SAVE_TAGS)); 
            $tpl->setVariable("RECSYS_TAG_DELETE", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_DELETE_ROW));
            $tpl->setVariable("RECSYS_TAG_ADD", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_ADD_ROW));
            
            // Default visibility (goal template is not shown)
            $tpl->setVariable("GOAL_TEMPLATE_VISIBILITY", "hidden");          
            
            $tpl->parseCurrentBlock();
        }

        $tplMain->setVariable("MOD_ROW", $tpl->get());
        return $tplMain;
    }
    */
}
?>