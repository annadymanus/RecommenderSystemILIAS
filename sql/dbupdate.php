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

if (!$ilDB->tableExists('ui_uihk_recsys_topics'))
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
}
?>