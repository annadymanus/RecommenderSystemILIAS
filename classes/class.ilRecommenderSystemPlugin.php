<?php

include_once("./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php");
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/classes/Model/class.ilRecSysModelUtils.php');

//is this include necessary? i dont understand what it does...
//include_once ("./Services/EventHandling/classes/class.ilEventHookPlugin.php");


/**
 * User interface hook class
 * 
 * @package ILIAS\Plugins\RecommenderSystem
 * @subpackage UserInterfaceHook
 * @author Anna Eschbach-Dymanus <anna.maria.eschbach-dymanus@students.uni-mannheim.de>
 */
class ilRecommenderSystemPlugin extends ilUserInterfaceHookPlugin
{

    function getPluginName()
    {
        return "RecommenderSystem";
    }

    protected function init()
    {
        // do nothing
    }

    public function handleEvent($a_component, $a_event, $a_parameter)
    {
        // a_component : "Services/Object"
        // a_event     : "toTrash"
        // a_parameter looks like:
        /*
           Array
           (
               [obj_id] => 387
               [ref_id] => 116
               [old_parent_ref_id] => 1
           )
        */

        // Check if its the delete event
        //TODO: REWRITE SO THAT IT ALSO DELETES RECSYS MODEL ENTRIES FOR DELETED OBJECTS
        
        if ($a_event != "toTrash")
            return True;
        
        // Check if the parameter is an array and if it has a crs_status. If it has no crs_status, it is not a course
        
        if (!is_array($a_parameter) || (!array_key_exists("crs_status", $a_parameter)))
            return True;
        
        // Check if the ref_id is numeric. If it is not numeric, it is not a course
        
        if (!is_numeric($a_parameter["crs_status"]))
            return True;

        //So now we know its a course that is to be deleted...
        global $ilDB;
        $queryResult = $ilDB->query("DELETE FROM ui_uihk_recsys_courses where crs_id = " . $a_parameter["crs_id"]);
        $queryResult = $ilDB->query("DELETE FROM ui_uihk_recsys_user where crs_id = " . $a_parameter["crs_id"]);
        return True;


        //handle delete events here for file objects
    }

	protected function afterActivation()
	{
		// do nothing
	}
	
	/**
	 * Remove all DataBase Entries
	 * @see ilPlugin::beforeUninstall()
	 */
	protected function beforeUninstall()
	{
	    $log = ilLoggerFactory::getLogger('ilRecommenderSystemPlugin');
	    global $ilDB;
	    
         /**
         * ui_uihk_recsys_config:                             no change
        * ui_uihk_recsys_user:                               no change
        * ui_uihk_recsys_courses:                            no change
        * ui_uihk_recsys_tags:                               no change
        * ui_uihk_recsys_tags_per_section:                   ui_uihk_recsys_t_p_s
        * ui_uihk_recsys_tags_user:                          ui_uihk_recsys_t_u
        * ui_uihk_recsys_section_tag_user:                   ui_uihk_recsys_s_t_u
        * ui_uihk_recsys_material_section_file_script:       ui_uihk_recsys_m_s_f_s
        * ui_uihk_recsys_material_section_file_presentation: ui_uihk_recsys_m_s_f_p
        * ui_uihk_recsys_material_section_file_video:        ui_uihk_recsys_m_s_f_v
        * ui_uihk_recsys_material_section_file_picture:      ui_uihk_recsys_m_s_pic
        * ui_uihk_recsys_material_section_weblink:           ui_uihk_recsys_m_s_w
        * ui_uihk_recsys_material_section_file_bibliography: ui_uihk_recsys_m_s_bib
        * ui_uihk_recsys_material_section_exercise           ui_uihk_recsys_m_s_e
        *
        */
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_config");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_config_seq");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_user");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_user_seq");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_courses");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_courses_seq");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_tags");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_tags_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_t_p_s");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_t_p_s_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_t_u");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_t_u_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_s_t_u");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_s_t_u_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_s_f_s");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_s_f_s_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_s_f_p");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_s_f_p_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_s_f_v");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_s_f_v_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_s_pic");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_s_pic_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_s_w");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_s_w_seq");
	    $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_s_bib");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_s_bib_seq");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_s_e");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_m_s_e_seq");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_u_r_a_c");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_u_r_a_c_seq");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_q_a_t");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_q_a_t_seq");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_u_q");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_u_q_seq");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_u_c");
        $queryResult = $ilDB->query("DROP TABLE IF EXISTS ui_uihk_recsys_u_c_seq");

	    $log->info("Uninstalled Recsys");	    
	    return true; // false would indicate that anything went wrong	    
	}

}
?>
