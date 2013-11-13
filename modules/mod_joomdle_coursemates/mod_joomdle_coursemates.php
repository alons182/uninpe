<?php
/**
* @package		Joomdle
* @copyright	Copyright (C) 2009 - 2010 Antonio Duran Terres
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (dirname(__FILE__).'/helper.php');

$limit = $params->get( 'limit' );
$tooltips               = $params->get('use_tooltips', 1);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

$course_id = JRequest::getVar ('course_id', 0, 'int');
if (!$course_id)
	$course_id = JRequest::getVar ('id', 0, 'int');

// Don't show if no course id selected
if (!$course_id)
	return;

$users = JoomdleHelperContent::call_method('get_course_students', (int) $course_id);


require(JModuleHelper::getLayoutPath('mod_joomdle_coursemates'));
