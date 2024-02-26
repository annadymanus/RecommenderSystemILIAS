<?php

include_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecommenderSystemConst.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelCourse.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelStudent.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelRecommender.php');
/**
 * Handles the Recommender Model Setting and Training.
 *
 * @author  Anna Eschbach-Dymanus <anna.maria.eschbach-dymanus@uni-mannheim.de> * 
 *
 */
class ilRecSysPageRecommenderModel {
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
    private $recommenderModel;
    
    
    public function __construct( $crs_id ) {
        global $ilCtrl, $ilUser;
        
        $this->ctrl         = $ilCtrl;
        $this->crs_id       = $crs_id;       
        $this->il_crs_id    = ilObject::_lookupObjectId($crs_id);
        $this->CourseObject = new ilObjCourse($crs_id);
        $this->ilUser       = $ilUser;
        $this->tag_handler  = ilRecSysModelTagHandler::getInstance();

        $this->RecSysCourse   = ilRecSysModelCourse::getOrCreateRecSysCourse($crs_id);
        $this->recommenderModel = new ilRecSysModelRecommender($this->ilUser->getId(), $this->crs_id);
        
        $this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'RecommenderSystem');
    }
    
    // ---------------------------------------------------------------------------
    
    public function show_recommender_model()
    {
        global $tpl;
     
        $tplRecSys = $this->getRecommenderModelTemplate();
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



    // --- Save -----------------------------------------------------------------------

    public function save_recommender_model() {
       
        ilUtil::sendSuccess($this->plugin->txt("recsys_saved_sucessfully"), true);

        $components = array();
        $model = $_POST['models'];
        foreach ($_POST as $key => $value) {
            if (str_contains($key,$model) and $key != "models" and $value == 'on') {
                $components[] = explode('_', $key)[2];
            }
        }
        $selected = array($model, $components);
        $this->recommenderModel->setRecommenderModel($selected);

        $this->show_recommender_model();
    }


    private function getRecommenderModelTemplate() {
        $tpl = new ilTemplate("tpl.recommender_model.html", true, true, self::PLUGIN_DIR);
        
        $tpl->setVariable("SAVING", $this->plugin->txt("recsys_teacher_save"));
        $tpl->setVariable("TRAINING", $this->plugin->txt("recsys_model_train"));
        
        $models = $this->recommenderModel->recommenderModels;
        $selected = $this->recommenderModel->getRecommenderModel();
        $selected_model = $selected[0];


        $selected_components = $selected[1];
        
        foreach ($models as $model_name=>$model_content) {
            $tpl->setCurrentBlock("Models");
            $model_description = $model_content[1];
            $tpl->setVariable("MODEL_NAME", $model_name);
            $tpl->setVariable("MODEL_DESCRIPTION", $model_description);
            if ($selected_model == $model_name) {
                $tpl->setVariable("MODEL_SELECTED", "checked");
            }            
            $tpl->parseCurrentBlock();
        }
        foreach ($models as $model_name=>$model_content) {
            $components = $model_content[0];
            foreach ($components as $component) {
                $tpl->setCurrentBlock("Components");
                $tpl->setVariable("MODEL_NAME", $model_name);            
                $tpl->setVariable("COMPONENT_NAME", $component[0]);
                $tpl->setVariable("COMPONENT_DESCRIPTION", $component[1]);
                if (in_array($component[0], $selected_components) and $selected_model == $model_name) {
                    $tpl->setVariable("COMPONENT_SELECTED", "checked");
                }
                $tpl->parseCurrentBlock();
            }
        }

        $tpl->setVariable("RECSYS_MODEL_SAVE", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_SAVE_RECOMMENDER_MODEL));
        $tpl->setVariable("RECSYS_MODEL_TRAIN", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_SAVE_RECOMMENDER_MODEL));

        return $tpl;
    }
    
}
?>