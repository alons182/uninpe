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
require_once(JPATH_SITE.'/components/com_joomdle/helpers/content.php');

$comp_params = &JComponentHelper::getParams( 'com_joomdle' );
$joomdle_itemid = $comp_params->get( 'joomdle_itemid' );

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

$linkto = $params->get( 'linkto' ,'courses');
$start_chars = $params->get( 'start_chars', 'abc' );

$chars_array = explode (',', $start_chars);

require(JModuleHelper::getLayoutPath('mod_joomdle_abc'));
