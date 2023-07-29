/**
 * 
 * @author Joel Pflomm <joel.pflomm@students.uni-mannheim.de>
 * 
 *
 */

<#1>
<?php
global $ilDB;

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
if (!$ilDB->tableExists('ui_uihk_recsys_courses'))
{
	$fields = array(
	    'crs_id' => array(  
	        'type'      => 'integer',
	        'length'    => 8,
	        'notnull'   => true,),
	    'il_crs_id' => array(
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
    $ilDB->createTable('ui_uihk_recsys_tags');
    $ilDB->addPrimaryKey('ui_uihk_recsys_tags', array("tag_id"));
    $ilDB->createSequence('ui_uihk_recsys_tags');
}

if(!$ilDB->tableExists('ui_uihk_recsys_tagsPerMaterial')){
    $fields = array(
        'tag_id' => array(
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
    $ilDB->createTable('ui_uihk_recsys_tagsPerMaterial');
    $ilDB->addPrimaryKey('ui_uihk_recsys_tagsPerMaterial', array("tag_id", "material_type", "material_id"));
    $ilDB->createSequence('ui_uihk_recsys_tagsPerMaterial');
}

if(!$ilDB->tableExists('ui_uihk_recsys_tagsPerUser')){
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
            'type' => 'double',
            'length' => 8,
            'notnull' => true),
        'tag_counter' => array( //how often the same tag was assigned to one user (can be used as factor for importance of tag to usr)
            'type' => 'integer',
            'length' => 4,
            'notnull' => true)
    );
    $ilDB->createTable('ui_uihk_recsys_tagsPerUser', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_tagsPerUser', array("tag_id", "usr_id"));
    $ilDB->createSequence('ui_uihk_recsys_tagsPerUser');
}


//material_content_file_script
if(!$ilDB->tableExists('ui_uihk_recsys_material_content_file_script')){
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
            'type' => 'double',
            'length' => 8,
            'notnull' => true),
        'rating_count' => array( //can be used to measure relevance or to calculate difficulty
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),
    );
    $ilDB->createTable('ui_uihk_recsys_material_content_file_script', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_material_content_file_script', array("script_id"));
    $ilDB->
    $ilDB->createSequence('ui_uihk_recsys_material_content_file_script');
}

//material_content_file_presentation
if(!$ilDB->tableExists('ui_uihk_recsys_material_content_file_presentation')){
    $fields = array(
        'presentation_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'obj_id' => array( //link to the ilias database object
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
            'type' => 'double',
            'length' => 8,
            'notnull' => true),
        'rating_count' => array(
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),
    );
    $ilDB->createTable('ui_uihk_recsys_material_content_file_presentation', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_material_content_file_presentation', array("presentation_id"));
    $ilDB->createSequence('ui_uihk_recsys_material_content_file_presentation');
}

//material_content_file_video
if(!$ilDB->tableExists('ui_uihk_recsys_material_content_file_video')){
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
            'type' => 'double',
            'length' => 8,
            'notnull' => true),
        'rating_count' => array(
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),
    );
    $ilDB->createTable('ui_uihk_recsys_material_content_file_video', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_material_content_file_video', array("video_id"));
    $ilDB->createSequence('ui_uihk_recsys_material_content_file_video');
}

//material_content_file_picture
if(!$ilDB->tableExists('ui_uihk_recsys_material_content_file_picture')){
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
            'type' => 'double',
            'length' => 8,
            'notnull' => true),
        'rating_count' => array(
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),
    );
    $ilDB->createTable('ui_uihk_recsys_material_content_file_picture', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_material_content_file_picture', array("picture_id"));
    $ilDB->createSequence('ui_uihk_recsys_material_content_file_picture');
}

//material_content_weblink
if(!$ilDB->tableExists('ui_uihk_recsys_material_content_weblink')){
    $fields = array(
        'weblink_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'difficulty' => array(
            'type' => 'double',
            'length' => 8,
            'notnull' => true),
        'rating_count' => array( 
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),

        //TODO: find out how weblinks are stored to connect this to the db
    );
    $ilDB->createTable('ui_uihk_recsys_material_content_weblink', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_material_content_weblink', array("weblink_id"));
    $ilDB->createSequence('ui_uihk_recsys_material_content_weblink');
}

//material_content_bibliography
if(!$ilDB->tableExists('ui_uihk_recsys_material_content_file_bibliography')){
    $fields = array(
        'bibl_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'difficulty' => array(
            'type' => 'double',
            'length' => 8,
            'notnull' => true),
        'rating_count' => array(
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),

        //TODO: find out how weblinks are stored to connect this to the db
    );
    $ilDB->createTable('ui_uihk_recsys_material_content_bibliography', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_material_content_bibliography', array("bibl_id"));
    $ilDB->createSequence('ui_uihk_recsys_material_content_bibliography');
}

//material_assessment_exercise (might be interesting later however most times the exercises are also stored in a different folder)

//material_assessment_test (core bases of the recommendation system)
if(!$ilDB->tableExists('ui_uihk_recsys_material_assessment_test')){
    $fields = array(
        'test_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'ilias_test_id' => array( // TODO: link to the ilias test object
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'question_nr' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'difficulty' => array(
            'type' => 'double',
            'length' => 8,
            'notnull' => true),
        'rating_count' => array(
            'type' => 'integer', 
            'length' => 4,
            'notnull' => true),
    );
    $ilDB->createTable('ui_uihk_recsys_material_assessment_test', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_material_assessment_test', array("test_id"));
    $ilDB->createSequence('ui_uihk_recsys_material_assessment_test');
}

//material_forum_entry
if(!$ilDB->tableExists('ui_uihk_recsys_material_forum_entry')){
    $fields = array(
        'entry_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
        'ilias_forum_entry_id' => array( //TODO: link to the ilias forum entry
            'type' => 'integer',
            'length' => 8,
            'notnull' => true),
    );
    $ilDB->createTable('ui_uihk_recsys_material_forum_entry', $fields);
    $ilDB->addPrimaryKey('ui_uihk_recsys_material_forum_entry', array("entry_id"));
    $ilDB->createSequence('ui_uihk_recsys_material_content_forum_entry');
}


/* if (!$ilDB->tableExists('ui_uihk_recsys_topics'))
{
    $fields = array(
        'topic_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true ),
    	'usr_id' => array(
            'type' => 'integer',
   			'length' => 8,
   			'notnull' => true ),
        'crs_id' =>  array(  
            'type' => 'integer',
            'length' => 8,
            'notnull' => true ),
        'priority' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true, ),
        'progress' => array(
            'type' => 'integer',
            'length' => 3,
            'notnull' => true, ),
        'difficulty' => array(
            'type' => 'integer',
            'length' => 1,
            'default' => 0,
            'notnull' => true,),
        'title' => array(  
            'type' => 'text',
            'length' => 100,
            'notnull' => true ),
        'text' => array(
            'type' => 'text',
            'length' => 1000,
            'notnull' => true ),
        'startdate' => array(
            'type' => 'text',
            'length' => 10,
            'notnull' => true ),
        'enddate' => array(
            'type' => 'text',
            'length' => 10,
            'notnull' => true ),
        'materials' => array(
            'type' => 'text',
            'length' => 100,
            'notnull' => true )
    );
    $ilDB->createTable("ui_uihk_recsys_topics", $fields);
    $ilDB->addPrimaryKey("ui_uihk_recsys_topics", array("topic_id"));
    $ilDB->createSequence('ui_uihk_recsys_topics');    
}

if (!$ilDB->tableExists('ui_uihk_recsys_feedback'))
{
    $fields = array(
        'feed_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true ),        
        'topic_id' => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true ),
    	'usr_id' => array(
            'type' => 'integer',
   			'length' => 8,
   			'notnull' => true ),
        'crs_id' =>  array(  
            'type' => 'integer',
            'length' => 8,
            'notnull' => true ),
        'rating' => array(  
            'type' => 'integer',
            'length' => 4,
            'notnull' => true ),
        'text' => array(
            'type' => 'text',
            'length' => 1000,
            'notnull' => true,),
        'lastupdate' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true ),
    );
    $ilDB->createTable("ui_uihk_recsys_feedback", $fields);
    $ilDB->addPrimaryKey("ui_uihk_recsys_feedback", array("feed_id"));
    $ilDB->createSequence('ui_uihk_recsys_feedback');    
} **/
?>