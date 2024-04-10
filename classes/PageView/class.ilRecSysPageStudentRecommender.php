<?php

include_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Libraries/class.ilRecommenderSystemConst.php");
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelCourse.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelStudent.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelRecommender.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/PageView/class.ilRecSysPageUtils.php');



/**
 * Handles the Commands of the Student Page.
 *
 * @author  Anna Maria Eschbach-Dymanus <anna.maria.eschbach-dymanus@uni-mannheim.de> * 
 *
 */
class ilRecSysPageStudentRecommender {
    const PLUGIN_DIR = "./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem";
        
    //var $ctrl;
    private $crs_id;	// ref_id of the course
    private $il_crs_id;    // obj_id of course
    var $ilDB;

    private $course_items_map;
    private $all_course_items;
    private $RecSysCourse;
    private $RecSysStudent;
    private $plugin;
    private $tree;
    private $recommender;
    
    
    public function __construct( $crs_id, $RecSysCourse, $RecSysStudent) {
        global $ilCtrl;
        global $ilDB;

        $this->ctrl         = $ilCtrl;
        $this->crs_id       = $crs_id;     
        $this->ilDB = $ilDB;  
        $this->il_crs_id      = ilObject::_lookupObjectId($crs_id);
        $this->RecSysCourse   = $RecSysCourse;
        $this->RecSysStudent  = $RecSysStudent;
        $this->recommender = new ilRecSysModelRecommender($this->RecSysStudent->getUsr_id(), $this->crs_id);

        $courseObject = new ilObjCourse($crs_id);
        $this->all_course_items = ilRecSysPageUtils::getItemsOfCourse($courseObject);
        $course_items_map = array();
        foreach ($this->all_course_items as $item){
            $course_items_map[$item['obj_id']] = $item;
        }
        $this->debug_to_console($crs_id, "crs_id");
        $this->debug_to_console($course_items_map, "course_items_map");

        $this->course_items_map = $course_items_map;


        $this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'RecommenderSystem');
    }
    

    function debug_to_console($data, $context = 'Debug in Console') {

        // Buffering to solve problems frameworks, like header() in this and not a solid return.
        ob_start();
    
        $output  = 'console.info(\'' . $context . ':\');';
        $output .= 'console.log(' . json_encode($data) . ');';
        $output  = sprintf('<script>%s</script>', $output);
    
        echo $output;
    }

    public function get_non_recommended_materials($recommended_materials){
        $non_recommended_materials = array();
        foreach ($this->all_course_items as $item) {
            $file_type = null;
            if($item["type"] == "file"){
                $file_type = ilRecSysPageUtils::fileIsType($item['obj_id']);	
            }
            $material_tags = ilRecSysPageUtils::getMaterialTagEntries($item['obj_id'], $file_type == null ?  $$item["type"] : $file_type);
            //$material_tags = array(array(0,array("Semantic Knowledge", "Graphs and Networks", "Sublayer Performance Review Attacks"), array(3,5)), array(1,array("Topic2"), array(7,9)), array(2,array("Topic3","Topic4","Topic5"), array(11,13)));

            //if no tags given, skip material
            if($material_tags[0][1][0]==""){
                continue;
            }
            foreach ($material_tags as $material_tag) {
                $section_id = $material_tag[0];
                if ($recommended_materials == null || count($recommended_materials) == 0){
                    $non_recommended_materials[] = array($item['obj_id'], $section_id, $file_type == null ?  $$item["type"] : $file_type, $material_tag[1], $material_tag[2], 0);
                    continue;
                }
                foreach ($recommended_materials as $recommended_material) {
                    if($recommended_material[0] == $item['obj_id'] && $recommended_material[1] == $section_id){
                        continue 2;
                    }
                }
                $non_recommended_materials[] = array($item['obj_id'], $section_id, $file_type == null ?  $$item["type"] : $file_type, $material_tag[1], $material_tag[2], 0);                
            }
        }
        return $non_recommended_materials;
    }



    public function addModuleRecommendedMaterials($tplMain) {
        $tpl = new ilTemplate("tpl.student_recommender.html", true, true, self::PLUGIN_DIR); //set to true true later

        // array(obj_id, section_id, material_type, [tag1, tag2,...], from_to, matching_score);
        $materials = $this->recommender->getRecommendation();
        $non_recommended_materials = $this->get_non_recommended_materials($materials);
        if($materials == null){
            $materials = array();
        }
        $materials = array_merge($materials, $non_recommended_materials);


        //Sort materials by last entry (match)
        if ($materials == null){
            $tplMain->setVariable("RECOMMENDATIONS", "");
            return $tplMain;
        }
        usort($materials, function($a, $b) {
            if ($a[5] == $b[5]) {
                return 0;
            }
            return ($a[5] < $b[5]) ? 1 : -1;
        });
        
        $ilObjGUI = new ilObjCourseGUI("", $this->crs_id, true, false);
        $CourseContent = new ilRecSysListMaterials($ilObjGUI);
        
        foreach ($materials as $material) {
            
            //$material_tags = ilRecSysPageUtils::getMaterialTagEntries($item['obj_id'], $file_type == null ?  $material_type : $file_type);

            foreach ($material[3] as $subtag){
                $tpl->setCurrentBlock("Subtags");
                $tpl->setVariable("TAG", $subtag);
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("RecommendedMaterials");
            $tpl->setVariable("SECTION", $material[1]);
            $tpl->setVariable("FROM", $material[4][0]);
            $tpl->setVariable("TO", $material[4][1]);
            $tpl->setVariable("MATCH", round($material[5]));

            $this->debug_to_console($material[0], "MATERIAL_ID");
            $item = $this->course_items_map[$material[0]];
            $tpl->setVariable("ITEM_ID", $item["obj_id"]);
            $this->debug_to_console($item["obj_id"], "ITEM_ID");
            $tpl->setVariable("MATERIAL_TYPE", $item["type"]);
            $tpl->setVariable("FILE_TYPE", $material[2]);
            $itemHtml = $CourseContent->getHtmlItem($item);
            $tpl->setVariable("ITEM_HTML", $itemHtml);
            $tpl->parseCurrentBlock(); 
        }
        $tpl->setVariable("RECSYS_STUDENT_CLICK", $this->ctrl->getLinkTargetByClass('ilRecommenderSystemPageGUI', ilRecommenderSystemConst::CMD_STUDENT_CLICK));
        $tplMain->setVariable("RECOMMENDATIONS", $tpl->get());
        return $tplMain;
    }
}
