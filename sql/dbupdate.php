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
  * ui_uihk_recsys_tags_per_section:                   ui_uihk_recsys_t_p_s
  * ui_uihk_recsys_material_section_file_script:       ui_uihk_recsys_m_s_f_s
  * ui_uihk_recsys_material_section_file_presentation: ui_uihk_recsys_m_s_f_p
  * ui_uihk_recsys_material_section_file_video:        ui_uihk_recsys_m_s_f_v
  * ui_uihk_recsys_material_section_file_picture:      ui_uihk_recsys_m_s_pic
  * ui_uihk_recsys_material_section_weblink:           ui_uihk_recsys_m_s_w
  * ui_uihk_recsys_material_section_file_bibliography: ui_uihk_recsys_m_s_bib
  * ui_uihk_recsys_material_section_exercise           ui_uihk_recsys_m_s_e
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
 *  crs_id:             identifying number of the course, which is assigend for the recommender system plugin
 *  tag_description:    a quick description of the tag (maybe what it represents, key messages etc.)
 *  tag_count:          how often the tag was used 
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
        'crs_id' => array(  
            'type'      => 'integer',
            'length'    => 8,
            'notnull'   => true),
        'tag_description' => array(
            'type' => 'text',
            'length' => 1000,
            'notnull' => true),
        'tag_count' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true)
    );
    $ilDB->createTable('ui_uihk_recsys_tags', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_tags', array("tag_id"));
    $ilDB->createSequence('ui_uihk_recsys_tags');
}

/**
 *  This table holds a list of specific material sections and their material type, to which the tag was assigned
 * 
 *  tag_id:             identifier of the tag
 *  material_type:      type of material (script, presentation, video, picture, weblink, bibliography, test, forum_entry) for each material a tag can be assigned to there is a table in the following
 *  section_id:         identifier of the section
 */
if(!$ilDB->tableExists('ui_uihk_recsys_t_p_s')){
    $fields = array(
        'tag_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'material_type' => array(  // for example 0: script, 1: presentation, 2: video, 3: picture, 4: ....
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'section_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
    );
    $ilDB->createTable('ui_uihk_recsys_t_p_s', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_t_p_s', array("tag_id", "material_type", "section_id"));
    $ilDB->createSequence('ui_uihk_recsys_t_p_s');
}

/**
 *  This table represents sections of a script (pdf, docx, etc.) that are taged
 * 
 *  Earlier: ui_uihk_recsys_material_section_file_script
 * 
 *  script_id:          identifier for the script (must coincide with material_id of tag for type "content_file_script")
 *  obj_id:             object identifier of ilias object
 *  start_page:         page the tag(/topic) assignation starts at
 *  end_page:           page the tag(/topic) assignation ends at
 *  difficulty:         percieved difficulty of this material snippet
 *  rating_count:       number of users that rated this material snippet
 *  no_tags:            counts the number tags that use/tag the section
 *  teach_diff:         percieved difficulty of section for the teacher
 */
if(!$ilDB->tableExists('ui_uihk_recsys_m_s_f_s')){
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
        'no_tags' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'teach_diff' => array(
            'type' => 'float',
            'notnull' => true)
    );
    $ilDB->createTable('ui_uihk_recsys_m_s_f_s', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_m_s_f_s', array("script_id"));
    $ilDB->createSequence('ui_uihk_recsys_m_s_f_s');
}

/**
 *  This table represents sections of a presentation (pptx, ppt, etc.) that are tagged
 * 
 * ui_uihk_recsys_material_section_file_presentation
 * 
 *  presentation_id:    identifier for the presentation (must coincide with material_id of tag for type "content_file_presentation")
 *  obj_id:             object identifier of ilias object
 *  start_page:         the slide the tag(/topic) assignation starts at
 *  end_page:           the slide the tag(/topic) assignation ends at
 *  difficulty:         percieved difficulty of this material snippet
 *  rating_count:       number of users that rated this material snippet
 *  no_tags:            counts the number tags that use/tag the section
 *  teach_diff:         percieved difficulty of section for the teacher
 */
if(!$ilDB->tableExists('ui_uihk_recsys_m_s_f_p')){
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
        'no_tags' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'teach_diff' => array(
            'type' => 'float',
            'notnull' => true)
    );
    $ilDB->createTable('ui_uihk_recsys_m_s_f_p', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_m_s_f_p', array("presentation_id"));
    $ilDB->createSequence('ui_uihk_recsys_m_s_f_p');
}

/**
 *  This table holds records of sections of a videos (mp4, etc.) that are tagged
 *
 *  ui_uihk_recsys_material_section_file_video
 * 
 *  video_id:           identifier for the video (must coincide with material_id of tag for type "content_file_video")
 *  obj_id:             object identifier of ilias object
 *  start_min:          the minute the tag(/topic) assignation starts at
 *  end_min:            the minute the tag(/topic) assignation ends at
 *  difficulty:         percieved difficulty of this material snippet
 *  rating_count:       number of users that rated this material snippet
 *  no_tags:            counts the number tags that use/tag the section
 *  teach_diff:         percieved difficulty of section for the teacher
 */
if(!$ilDB->tableExists('ui_uihk_recsys_m_s_f_v')){
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
            'type' => 'integer',
            'notnull' => true),
        'end_min' => array(
            'type' => 'integer',
            'notnull' => true),
        'start_sec' => array(
            'type' => 'integer',
            'notnull' => true),
        'end_sec' => array(
            'type' => 'integer',
            'notnull' => true),
        'difficulty' => array(
            'type' => 'float',
            'notnull' => true),
        'rating_count' => array(
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),
        'no_tags' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'teach_diff' => array(
            'type' => 'float',
            'notnull' => true)
    );
    $ilDB->createTable('ui_uihk_recsys_m_s_f_v', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_m_s_f_v', array("video_id"));
    $ilDB->createSequence('ui_uihk_recsys_m_s_f_v');
}

/**
 *  This table holds records of pictures (jpg, png, etc.) that are tagged
 * 
 * ui_uihk_recsys_material_section_file_picture
 * 
 *  picture_id:         identifier of the picture (must coincide with material_id of tag for type "content_file_picture")
 *  obj_id:             object identifier of ilias object
 *  difficulty:         percieved difficulty of this material snippet
 *  rating_count:       number of users that rated this material snippet
 *  no_tags:            counts the number tags that use/tag the section
 *  teach_diff:         percieved difficulty of section for the teacher
 */
if(!$ilDB->tableExists('ui_uihk_recsys_m_s_pic')){
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
        'no_tags' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'teach_diff' => array(
            'type' => 'float',
            'notnull' => true)
    );
    $ilDB->createTable('ui_uihk_recsys_m_s_pic', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_m_s_pic', array("picture_id"));
    $ilDB->createSequence('ui_uihk_recsys_m_s_pic');
}

/**
 *  This table holds records of weblinks that are tagged
 * 
 * ui_uihk_recsys_material_section_weblink
 * 
 *  weblink_id:         identifier for the weblink (must coincide with material_id of tag for type "content_weblink")
 *  obj_id:             object identifier of ilias object
 *  difficulty:         percieved difficulty of this material snippet
 *  rating_count:       number of users that rated this material snippet
 *  no_tags:            counts the number tags that use/tag the section
 *  teach_diff:         percieved difficulty of section for the teacher
 */
if(!$ilDB->tableExists('ui_uihk_recsys_m_s_w')){
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
        'no_tags' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'teach_diff' => array(
            'type' => 'float',
            'notnull' => true)
    );
    $ilDB->createTable('ui_uihk_recsys_m_s_w', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_m_s_w', array("weblink_id"));
    $ilDB->createSequence('ui_uihk_recsys_m_s_w');
}

/**
 *  This table represents the tags given to a bibliography
 * 
 *  ui_uihk_recsys_material_section_file_bibliography
 * 
 *  bibl_id:            identifier for the bibliography (must coincide with material_id of tag for type "content_bibliography")
 *  obj_id:             object identifier of ilias object
 *  difficulty:         percieved difficulty of this material snippet
 *  rating_count:       number of users that rated this material snippet
 *  no_tags:            counts the number tags that use/tag the section
 *  teach_diff:         percieved difficulty of section for the teacher
 */
if(!$ilDB->tableExists('ui_uihk_recsys_m_s_bib')){
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
        'no_tags' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'teach_diff' => array(
            'type' => 'float',
            'notnull' => true)
    );
    $ilDB->createTable('ui_uihk_recsys_m_s_bib', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_m_s_bib', array("bibl_id"));
    $ilDB->createSequence('ui_uihk_recsys_m_s_bib');
}

/**
 *  This table represents the tags given to an Exercise
 * 
 * ui_uihk_recsys_material_section_exercise
 * 
 *  exercise_id:        identifier for the test (must coincide with the material_id of tag for type "assessment_test")
 *  task_no             number of the task that was tagged
 *  subtask_no          number of the subtask that was tagged
 *  difficulty:         percieved difficulty of this material snippet
 *  rating_count:       number of users that rated this material snippet
 *  no_tags:            counts the number tags that use/tag the section
 *  teach_diff:         percieved difficulty of section for the teacher
 */
if(!$ilDB->tableExists('ui_uihk_recsys_m_s_e')){
    $fields = array(
        'exercise_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'obj_id' => array( 
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
        'no_tags' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'teach_diff' => array(
            'type' => 'float',
            'notnull' => true)
    );
    $ilDB->createTable('ui_uihk_recsys_m_s_e', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_m_s_e', array("exercise_id"));
    $ilDB->createSequence('ui_uihk_recsys_m_s_e');
}


/**
 *  This table represents the users spefic information to give them recommendations. The idea of the table is to save 
 *  the materials and the corresponding tags for which a user asked for a recommendation.
 * 
 *  ui_uihk_recsys_user_query
 * 
 *  The table holds the following attributes:
 * 
 *  user_id:                    identifier of the user that wants a recommendation
 * crs_id:                     identifier of the course the user utilizes the Recommender System for
 *  material_type:              type of material (script, presentation, video, picture, weblink, bibliography, test, forum_entry)
 *  material_id:                identifier of the material that a user chooses to get a recommendation for
 *  timestamp:                  unix timestamp in seconds of the request
 *  */
if (!$ilDB->tableExists('ui_uihk_recsys_u_q'))
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
        'material_type' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'material_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'timestamp' => array(
            'type' => 'integer', // or 'datetime'
            'notnull' => true),
    );
    $ilDB->createTable("ui_uihk_recsys_u_q", $fields);
    $ilDB->addPrimaryKey("ui_uihk_recsys_u_q", array("usr_id", "material_type", "material_id", "timestamp"));
    $ilDB->createSequence('ui_uihk_recsys_u_q');    
}

/**
 *  This table represents the materials that a user was interested in (clicked on).
 * 
 *  ui_uihk_recsys_user_clicks
 * 
 *  The table holds the following attributes:
 * 
 *  user_id:                    identifier of the user that wants a recommendation
 * crs_id:                     identifier of the course the user utilizes the Recommender System for
 *  material_type:              type of material (script, presentation, video, picture, weblink, bibliography, test, forum_entry)
 *  material_id:                identifier of the material that a user clicked on
 *  timestamp:                  unix timestamp in seconds of the click
 *  */
if (!$ilDB->tableExists('ui_uihk_recsys_u_c'))
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
        'material_type' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'material_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'timestamp' => array(
            'type' => 'integer', // or 'datetime'
            'notnull' => true),
    );
    $ilDB->createTable("ui_uihk_recsys_u_c", $fields);
    $ilDB->addPrimaryKey("ui_uihk_recsys_u_c", array("usr_id", "material_type", "material_id", "timestamp"));
    $ilDB->createSequence('ui_uihk_recsys_u_c');    
}


?>

<#2>
<?php
if(!$ilDB->tableExists('ui_uihk_recsys_recmod')){
    $fields = array(
        'crs_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'model_name' => array(
            'type' => 'text',
            'length' => 128,
            'notnull' => true),
        'components' => array(
            'type' => 'text',
            'length' => 1000,
            'notnull' => true),
    );
    $ilDB->createTable('ui_uihk_recsys_recmod', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_recmod', array("crs_id"));
    $ilDB->createSequence('ui_uihk_recsys_recmod');
}

?>

<#3>
<?php
//write migration adding teach_diff column to all material section tables if not already present

if(!$ilDB->tableColumnExists('ui_uihk_recsys_m_s_f_s', 'teach_diff')){
    $ilDB->addTableColumn('ui_uihk_recsys_m_s_f_s', 'teach_diff', array(
        'type' => 'float',
        'notnull' => true
    ));
}

if(!$ilDB->tableColumnExists('ui_uihk_recsys_m_s_f_p', 'teach_diff')){
    $ilDB->addTableColumn('ui_uihk_recsys_m_s_f_p', 'teach_diff', array(
        'type' => 'float',
        'notnull' => true
    ));
}

if(!$ilDB->tableColumnExists('ui_uihk_recsys_m_s_f_v', 'teach_diff')){
    $ilDB->addTableColumn('ui_uihk_recsys_m_s_f_v', 'teach_diff', array(
        'type' => 'float',
        'notnull' => true
    ));
}

if(!$ilDB->tableColumnExists('ui_uihk_recsys_m_s_pic', 'teach_diff')){
    $ilDB->addTableColumn('ui_uihk_recsys_m_s_pic', 'teach_diff', array(
        'type' => 'float',
        'notnull' => true
    ));
}

if(!$ilDB->tableColumnExists('ui_uihk_recsys_m_s_w', 'teach_diff')){
    $ilDB->addTableColumn('ui_uihk_recsys_m_s_w', 'teach_diff', array(
        'type' => 'float',
        'notnull' => true
    ));
}

if(!$ilDB->tableColumnExists('ui_uihk_recsys_m_s_bib', 'teach_diff')){
    $ilDB->addTableColumn('ui_uihk_recsys_m_s_bib', 'teach_diff', array(
        'type' => 'float',
        'notnull' => true
    ));
}

if(!$ilDB->tableColumnExists('ui_uihk_recsys_m_s_e', 'teach_diff')){
    $ilDB->addTableColumn('ui_uihk_recsys_m_s_e', 'teach_diff', array(
        'type' => 'float',
        'notnull' => true
    ));
}



?>