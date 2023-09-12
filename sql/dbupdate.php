/**
 * 
 * @author Joel Pflomm <joel.pflomm@students.uni-mannheim.de>
 * 
 *
 */

<#1>
<?php
global $ilDB;

/** 
 *  This table holds records with information for the configuration of the Recommender System 
 *  
 *  item:           configuration items, used for administration data and assigning rights (these are: "enabled_users", "recsys_apiurl", "recsys_username", "recsys_password", "recsys_tracking_username", "recsys_tracking_password")
 *  value:          value of the configuration items
 */ 

 /**
  * ui_uihk_recsys_config:                             no change
  * ui_uihk_recsys_user:                               no change
  * ui_uihk_recsys_courses:                            no change
  * ui_uihk_recsys_tags:                               no change
  * ui_uihk_recsys_tags_per_material:                  ui_uihk_recsys_t_p_m
  * ui_uihk_recsys_overall_tags_user:                  ui_uihk_recsys_o_t_u
  * ui_uihk_recsys_specific_tags_user:                 ui_uihk_recsys_s_t_u
  * ui_uihk_recsys_material_content_file_script:       ui_uihk_recsys_s_t_u
  * ui_uihk_recsys_material_content_file_presentation: ui_uihk_recsys_m_c_f_p
  * ui_uihk_recsys_material_content_file_video:        ui_uihk_recsys_m_c_f_v
  * ui_uihk_recsys_material_content_file_picture:      ui_uihk_recsys_m_c_pic
  * ui_uihk_recsys_material_content_weblink:           ui_uihk_recsys_m_c_w
  * ui_uihk_recsys_material_content_file_bibliography: ui_uihk_recsys_m_c_bib
  * ui_uihk_recsys_material_assessment_test:           ui_uihk_recsys_m_a_t
  *
  */
if (!$ilDB->tableExists('ui_uihk_recsys_config'))
{
	$table_config = array(
	    'item' => array(
	            'type'      => 'text',
	            'length'    => 40,
			),
	    'value' => array(
	            'type'      => 'text',
	            'length'    => 1000,
			),
	);
	$ilDB->createTable("ui_uihk_recsys_config", $table_config);
}

/**
 *  This table holds information on users that use the Recommender System 
 *  
 *  usr_id:         identifying number of the 
 *  crs_id:         id of the course the user utilizes the Recommender System for
 *  usr_status:     status of the user. 3 options: INACTIVE, ANONYM, ACTIVE (define whether tracking is active or not)
 *  updates:        an array of the last 10 times the userstatus was changed (timestamp (timeformat: Y-m-d H:i:s) + status)
 *  last_visit:     the last time the user was assigned a tag
 */
if (!$ilDB->tableExists('ui_uihk_recsys_user'))
{
    $fields = array(
    	'usr_id' => array(
            'type' => 'integer',
   			'length' => 8,
   			'notnull' => true ),
        'crs_id' =>  array(  
            'type' => 'integer',
            'length' => 8,
            'notnull' => true ),
        'usr_status' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true, ),
        'updates' => array(  
            'type' => 'text',
            'length' => 1000,
            'notnull' => true ),
        'lastvisit' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false ),
    );
    $ilDB->createTable("ui_uihk_recsys_user", $fields);
    $ilDB->addPrimaryKey("ui_uihk_recsys_user", array("usr_id", "crs_id"));
    $ilDB->createSequence('ui_uihk_recsys_user');    
}

/**
 *  This table holds the ilias courses that offer the option of activating the Recommender System.
 *  
 *  crs_id:                 identifying number of the course, which is assigend for the recommender system plugin
 *  obj_id:                 using the crs_id as a reference, one can find the courses object id for the object of the course that is represented in ilias (ilObject::_lookupObjectId($crs_id))
 *  crs_status:             teacher setting: 0 (default) = recsys Plugin not active | 1: plugin active 
 *  mod_tracking:           teacher setting: 0 = tracking option not active | 1 (default) = tracking option active
 *  mod_lo:                 teacher setting: 0 = learning objectives not enabled | 1 (default) = learning objectives enabled
 *  mod_ig:                 teacher setting: 0 (default) = individual goals not active | 1 = individual goals enabled
 *  mod_recommendations:    teacher setting: 0 (default) = recommendations not active | 1 = recommendations active
 *  opt_default:            student setting: 0 (default) = tracking status is set to "none", 1 = tracking status is set to "active", 2 = tracking status is set to "anonymous"
 *  opt_out:                student setting: 0 = teacher does not give student the option to deactivate tracking | 1 (default) = teacher gives student the option
 *  opt_anonym:             student setting: 0 (default) = teacher does not give student the option to set tracking to anonymous | 1 = teacher gives student the option
 *  opt_active:             student setting: 0 = teacher does not give student the option to activate tracking | 1 (defautl) = teacher gives student the option
 */
if (!$ilDB->tableExists('ui_uihk_recsys_courses'))
{
	$fields = array(
	    'crs_id' => array(  
	        'type'      => 'integer',
	        'length'    => 8,
	        'notnull'   => true,),
        'obj_id' => array(  
            'type'      => 'integer',
            'length'    => 8,
            'notnull'   => true,),
        'crs_status' => array(
            'type' => 'integer',
            'length' => 1,
            'default' => 0,
            'notnull' => true,),
	    'mod_tracking'  => array(  
	        'type'      => 'integer',
	        'length'    => 1,
	        'default'   => 0,
	        'notnull'   => true,),
	    'mod_lo' => array(  
	        'type'      => 'integer',
	        'length'    => 1,
	        'default'   => 0,
	        'notnull'   => true,),
	    'mod_ig' => array(  
	        'type'      => 'integer',
	        'length'    => 1,
	        'default'   => 0,
	        'notnull'   => true,),
	    'mod_recommendations' => array(  
	        'type'      => 'integer',
	        'length'    => 1,
	        'default'   => 0,
	        'notnull'   => true,),
	    'opt_default' => array(  
	        'type'      => 'integer',
	        'length'    => 1,
	        'default'   => 1,
	        'notnull'   => true,),
	    'opt_out' => array(  
	        'type'      => 'integer',
	        'length'    => 1,
	        'default'   => 1,
	        'notnull'   => true,),
	    'opt_anonym' => array(  
	        'type'      => 'integer',
	        'length'    => 1,
	        'default'   => 1,
	        'notnull'   => true,),
	    'opt_active' => array(  
	        'type'      => 'integer',
	        'length'    => 1,
	        'default'   => 1,
	        'notnull'   => true,),
	);
	$ilDB->createTable("ui_uihk_recsys_courses", $fields);
	$ilDB->addPrimaryKey("ui_uihk_recsys_courses", array("crs_id"));
	$ilDB->createSequence('ui_uihk_recsys_courses');
}



/**
 *  This table holds all tags that where assigned.
 * 
 *  tag_id:             identifier of the tag
 *  tag_name:           name of the tag (should be brief but meaningfull)
 *  tag_description:    a quick description of the tag (maybe what it represents, key messages etc.)
 *  occurence:          how often the tag was used 
 */
if(!$ilDB->tableExists('ui_uihk_recsys_tags')){
    $fields = array(
        'tag_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'tag_name' => array(
            'type' => 'text',
            'length' => 128,
            'notnull' => true),
        'tag_description' => array(
            'type' => 'text',
            'length' => 1000,
            'notnull' => true),
        'tag_occurence' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true)
    );
    $ilDB->createTable('ui_uihk_recsys_tags', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_tags', array("tag_id"));
    $ilDB->createSequence('ui_uihk_recsys_tags');
}

/**
 *  This table holds a list of specific materials and their type, for which a tag was assigned
 *  tags per material 
 * 
 *  tag_id:             identifier of the tag
 *  material_type:      type of material (script, presentation, video, picture, weblink, bibliography, test, forum_entry) for each material a tag can be assigned to there is a table in the following
 *  material_id:        identifier of the material
 */
if(!$ilDB->tableExists('ui_uihk_recsys_t_p_m')){
    $fields = array(
        'tag_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'material_type' => array(  // for example 0: script, 1: presentation, 2: video, 3: picture, 4: ....
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'material_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
    );
    $ilDB->createTable('ui_uihk_recsys_t_p_m', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_t_p_m', array("tag_id", "material_type", "material_id"));
    $ilDB->createSequence('ui_uihk_recsys_t_p_m');
}

/**
 *  This table is for holding records on which tags the user has to work on. For that purpose it stores the following attributes:
 *  Formerly known as ui_uihk_recsys_overall_tags_user 
 * 
 *  tag_id:         identifier of the tag
 *  usr_id:         identifier of the user that the tag was assigned to
 *  priority:       priority of the tag (using some kind of heuristic)
 *  tag_count:      counts how often the tag was assigned to the user (can be used for the priority-heuristic)
 */
if(!$ilDB->tableExists('ui_uihk_recsys_o_t_u')){
    $fields = array(
        'tag_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'usr_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'priority' => array(
            'type' => 'float',
            'notnull' => true),
        'tag_counter' => array( //how often the same tag was assigned to one user (can be used as factor for importance of tag to usr)
            'type' => 'integer',
            'length' => 4,
            'notnull' => true)
    );
    $ilDB->createTable('ui_uihk_recsys_o_t_u', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_o_t_u', array("tag_id", "usr_id"));
    $ilDB->createSequence('ui_uihk_recsys_o_t_u');
}

/**
 *  This table holds the specific subtasks a user has to fullfill in order to get rid of a tag (/accomplish to learn all recomended materials).
 *  It is also used to for the recommendation of specific materials (not just a topic).
 * 
 *  Earlier ui_uihk_recsys_specific_tags_user
 * 
 *  usr_id:             identifier of the user that the tag was assigned to
 *  material_type:      type of material
 *  material_id:        identifier of the material        
 */
if(!$ilDB->tableExists('ui_uihk_recsys_s_t_u')){
    $fields = array(
        'usr_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'material_type' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'material_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
    );
    $ilDB->createTable('ui_uihk_recsys_s_t_u', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_s_t_u', array("usr_id", "material_type", "material_id"));
    $ilDB->createSequence('ui_uihk_recsys_s_t_u');
}

/**
 *  This table represents the tags given to a script (pdf, docx, etc.)
 * 
 *  Earlier: ui_uihk_recsys_material_content_file_script
 * 
 *  script_id:          identifier for the script (must coincide with material_id of tag for type "content_file_script")
 *  obj_id:             object identifier of ilias object
 *  start_page:         page the tag(/topic) assignation starts at
 *  end_page:           page the tag(/topic) assignation ends at
 *  difficulty:         percieved difficulty of this material snippet
 *  rating_count:       number of users that rated this material snippet
 */
if(!$ilDB->tableExists('ui_uihk_recsys_m_c_f_s')){
    $fields = array(
        'script_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'obj_id' => array( //link to the ilias database object (Todo find out the obj_id usage rules)
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'start_page' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'end_page' => array(
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),
        'difficulty' => array(
            'type' => 'float',
            'notnull' => true),
        'rating_count' => array( //can be used to measure relevance or to calculate difficulty
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),
    );
    $ilDB->createTable('ui_uihk_recsys_m_c_f_s', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_m_c_f_s', array("script_id"));
    $ilDB->createSequence('ui_uihk_recsys_m_c_f_s');
}

/**
 *  This table represents the tags given to a presentation (pptx, ppt, etc.)
 * 
 * ui_uihk_recsys_material_content_file_presentation
 * 
 *  presentation_id:    identifier for the presentation (must coincide with material_id of tag for type "content_file_presentation")
 *  obj_id:             object identifier of ilias object
 *  start_page:         the slide the tag(/topic) assignation starts at
 *  end_page:           the slide the tag(/topic) assignation ends at
 *  difficulty:         percieved difficulty of this material snippet
 *  rating_count:       number of users that rated this material snippet
 */
if(!$ilDB->tableExists('ui_uihk_recsys_m_c_f_p')){
    $fields = array(
        'presentation_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'obj_id' => array(          //link to the ilias database object
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'start_slide' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'end_slide' => array(
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),
        'difficulty' => array(
            'type' => 'float',
            'notnull' => true),
        'rating_count' => array(
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),
    );
    $ilDB->createTable('ui_uihk_recsys_m_c_f_p', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_m_c_f_p', array("presentation_id"));
    $ilDB->createSequence('ui_uihk_recsys_m_c_f_p');
}

/**
 *  This table represents the tags given to a video (mp4, etc.)
 *
 *  ui_uihk_recsys_material_content_file_video
 * 
 *  video_id:           identifier for the video (must coincide with material_id of tag for type "content_file_video")
 *  obj_id:             object identifier of ilias object
 *  start_min:          the minute the tag(/topic) assignation starts at
 *  end_min:            the minute the tag(/topic) assignation ends at
 *  difficulty:         percieved difficulty of this material snippet
 *  rating_count:       number of users that rated this material snippet
 */
if(!$ilDB->tableExists('ui_uihk_recsys_m_c_f_v')){
    $fields = array(
        'video_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'obj_id' => array( //link to the ilias database object 
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'start_min' => array(
            'type' => 'timestamp',
            'notnull' => true),
        'end_min' => array(
            'type' => 'timestamp',
            'notnull' => true),
        'difficulty' => array(
            'type' => 'float',
            'notnull' => true),
        'rating_count' => array(
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),
    );
    $ilDB->createTable('ui_uihk_recsys_m_c_f_v', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_m_c_f_v', array("video_id"));
    $ilDB->createSequence('ui_uihk_recsys_m_c_f_v');
}

/**
 *  This table represents the tags given to a picture (jpg, png, etc.)
 * 
 * ui_uihk_recsys_material_content_file_picture
 * 
 *  picture_id:         identifier of the picture (must coincide with material_id of tag for type "content_file_picture")
 *  obj_id:             object identifier of ilias object
 *  difficulty:         percieved difficulty of this material snippet
 *  rating_count:       number of users that rated this material snippet
 */
if(!$ilDB->tableExists('ui_uihk_recsys_m_c_pic')){
    $fields = array(
        'picture_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'obj_id' => array( //link to the ilias database object 
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'difficulty' => array(
            'type' => 'float',
            'notnull' => true),
        'rating_count' => array(
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),
    );
    $ilDB->createTable('ui_uihk_recsys_m_c_pic', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_m_c_pic', array("picture_id"));
    $ilDB->createSequence('ui_uihk_recsys_m_c_pic');
}

/**
 *  This table represents the tags given to a weblink
 * 
 * ui_uihk_recsys_material_content_weblink
 * 
 *  weblink_id:         identifier for the weblink (must coincide with material_id of tag for type "content_weblink")
 *  obj_id:             object identifier of ilias object
 *  difficulty:         percieved difficulty of this material snippet
 *  rating_count:       number of users that rated this material snippet
 */
if(!$ilDB->tableExists('ui_uihk_recsys_m_c_w')){
    $fields = array(
        'weblink_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'obj_id' => array( //link to the ilias database object in ilObjLinkResourceItems
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),    
        'difficulty' => array(
            'type' => 'float',
            'notnull' => true),
        'rating_count' => array( 
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),

        //TODO: find out how weblinks are stored to connect this to the db
        //answ: class.ilObjLinkResource, class.ilObjLinkResourceGUI, class.ilObjLinkItems
    );
    $ilDB->createTable('ui_uihk_recsys_m_c_w', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_m_c_w', array("weblink_id"));
    $ilDB->createSequence('ui_uihk_recsys_m_c_w');
}

/**
 *  This table represents the tags given to a bibliography
 * 
 * ui_uihk_recsys_material_content_file_bibliography
 * 
 *  bibl_id:            identifier for the bibliography (must coincide with material_id of tag for type "content_bibliography")
 *  obj_id:             object identifier of ilias object
 *  difficulty:         percieved difficulty of this material snippet
 *  rating_count:       number of users that rated this material snippet
 */
if(!$ilDB->tableExists('ui_uihk_recsys_m_c_bib')){
    $fields = array(
        'bibl_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'obj_id' => array( //link to the ilias database object in ilObjLinkResourceItems
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),  
        'difficulty' => array(
            'type' => 'float',
            'notnull' => true),
        'rating_count' => array(
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),

        //TODO: find out how weblinks are stored to connect this to the db
        //answ: class.ilObjBibliographicGUI
    );
    $ilDB->createTable('ui_uihk_recsys_m_c_bib', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_m_c_bib', array("bibl_id"));
    $ilDB->createSequence('ui_uihk_recsys_m_c_bib');
}

/**
 *  This table represents the tags given to a test
 * 
 * ui_uihk_recsys_material_assessment_test
 * 
 *  test_id:            identifier for the test (must coincide with the material_id of tag for type "assessment_test")
 *  ilias_test_id:      object identifier of ilias test object
 *  question_no         number of the question that was taged
 *  difficulty:         percieved difficulty of this material snippet
 *  rating_count:       number of users that rated this material snippet
 */
//material_assessment_test (core bases of the recommendation system)
if(!$ilDB->tableExists('ui_uihk_recsys_m_a_t')){
    $fields = array(
        'test_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'obj_id' => array( // TODO: link to the ilias test object
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'question_no' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'difficulty' => array(
            'type' => 'float',
            'notnull' => true),
        'rating_count' => array(
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),
    );
    $ilDB->createTable('ui_uihk_recsys_m_a_t', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_m_a_t', array("test_id"));
    $ilDB->createSequence('ui_uihk_recsys_m_a_t');
}

/**
 *  This table represents the tags given to an Exercise
 * 
 * ui_uihk_recsys_material_assessment_exercise
 * 
 *  exercise_id:        identifier for the test (must coincide with the material_id of tag for type "assessment_test")
 *  ilias_exercise_id:  object identifier of ilias test object
 *  task_no             number of the task that was tagged
 *  subtask_no          number of the subtask that was tagged
 *  difficulty:         percieved difficulty of this material snippet
 *  rating_count:       number of users that rated this material snippet
 */
//material_assessment_test (core bases of the recommendation system)
if(!$ilDB->tableExists('ui_uihk_recsys_m_a_e')){
    $fields = array(
        'exercise_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'obj_id' => array( // TODO: link to the ilias test object
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'task_no' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'subtask_no' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'difficulty' => array(
            'type' => 'float',
            'notnull' => true),
        'rating_count' => array(
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),
    );
    $ilDB->createTable('ui_uihk_recsys_m_a_e', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_m_a_e', array("exercise_id"));
    $ilDB->createSequence('ui_uihk_recsys_m_a_e');
}
/** 
 * This table can later be added in case tags are also implemented for forum entries
 * 
 * //material_forum_entry
 * if(!$ilDB->tableExists('ui_uihk_recsys_material_forum_entry')){
 *     $fields = array(
 *         'entry_id' => array(
 *             'type' => 'integer',
 *             'length' => 8,
 *             'notnull' => true),
 *         'ilias_forum_entry_id' => array( //TODO: link to the ilias forum entry
 *             'type' => 'integer',
 *             'length' => 8,
 *             'notnull' => true),
 *     );
 *     $ilDB->createTable('ui_uihk_recsys_material_forum_entry', $fields);
 *     $ilDB->addPrimaryKey('ui_uihk_recsys_material_forum_entry', array("entry_id"));
 *     $ilDB->createSequence('ui_uihk_recsys_material_content_forum_entry');
 * }
 */


/**
 *  This table can be used later as inspiration for a feedback system
 * if (!$ilDB->tableExists('ui_uihk_recsys_feedback')) {
 *     $fields = array(
 *         'feed_id' => array(
 *             'type' => 'integer',
 *             'length' => 8,
 *             'notnull' => true ),        
 *         'topic_id' => array(
 *             'type' => 'integer',
 *             'length' => 8,
 *             'notnull' => true ),
 *         'usr_id' => array(
 *             'type' => 'integer',
 *    		   'length' => 8,
 *    		   'notnull' => true ),
 *         'crs_id' =>  array(  
 *             'type' => 'integer',
 *             'length' => 8,
 *             'notnull' => true ),
 *         'rating' => array(  
 *             'type' => 'integer',
 *             'length' => 4,
 *             'notnull' => true ),
 *         'text' => array(
 *             'type' => 'text',
 *             'length' => 1000,
 *             'notnull' => true,),
 *         'lastupdate' => array(
 *             'type' => 'integer',
 *             'length' => 4,
 *             'notnull' => true ),
 *     );
 *     $ilDB->createTable("ui_uihk_recsys_feedback", $fields);
 *     $ilDB->addPrimaryKey("ui_uihk_recsys_feedback", array("feed_id"));
 *     $ilDB->createSequence('ui_uihk_recsys_feedback');    
 * } 
 */















?>