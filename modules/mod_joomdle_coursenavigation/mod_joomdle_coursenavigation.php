<?php
/**
* @version		
* @package		Joomdle
* @copyright	Copyright (C) 2008 - 2010 Antonio Duran Terres
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_joomdle/helpers/content.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_joomdle/helpers/system.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_joomdle/helpers/groups.php');

// Include the whosonline functions only once
require_once (dirname(__FILE__).'/helper.php');

$linkto = $params->get( 'linkto' , 'moodle');

$comp_params = &JComponentHelper::getParams( 'com_joomdle' );

$joomdle_itemid = $comp_params->get( 'joomdle_itemid' );
$courseview_itemid = $comp_params->get( 'courseview_itemid' );
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

$course_id = JRequest::getVar ('course_id');

if (!$course_id)
    return;


require(JModuleHelper::getLayoutPath('mod_joomdle_coursenavigation'));
