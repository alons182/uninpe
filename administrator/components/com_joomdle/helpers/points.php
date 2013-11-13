<?php
/**
 * @version             
 * @package             Joomdle
 * @copyright   Copyright (C) 2008 - 2010 Antonio Duran Terres
 * @license             GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.user.helper');
jimport('joomla.filesystem.folder');
require_once(JPATH_ADMINISTRATOR.'/components/com_joomdle/helpers/content.php');



/**
 * Content Component Query Helper
 *
 * @static
  @package             Joomdle
 * @since 1.5
 */
class JoomdleHelperPoints
{
	function addPoints ($action, $username, $course_id, $course_name)
	{

		$comp_params = &JComponentHelper::getParams( 'com_joomdle' );
		$user_points = $comp_params->get( 'user_points' );

		switch ($user_points)
		{
			case 'jomsocial':
				JoomdleHelperPoints::addPoints_jomsocial ($action, $username, $course_id, $course_name);
				break;
			case 'ambra':
				JoomdleHelperPoints::addPoints_ambra ($action, $username, $course_id, $course_name);
				break;
			default:
				break;
		}
		return "OK";
	}

	function addPoints_jomsocial ($action, $username, $course_id, $course_name)
	{
		if (file_exists (JPATH_ADMINISTRATOR.'/components/com_community/tables/cache.php'))
            require_once(JPATH_ADMINISTRATOR.'components/com_community/tables/cache.php');
        require_once(JPATH_SITE.'/components/com_community/libraries/core.php');
        require_once(JPATH_SITE.'/components/com_community/libraries/userpoints.php');

		if( class_exists('CFactory') ){
            $userPoint = CFactory::getModel('userpoints');
        } else {
            $userPoint = new CommunityModelUserPoints();
        }

		$upObj  = $userPoint->getPointData( $action );
		$published  = $upObj->published;
		if ($published)
			$points = $upObj->points;
		else
			$points = 0;


		if ($points == 0)
			return 0;

		$user   = CFactory::getUser($username);
		$points += $user->getKarmaPoint();
		$user->_points = $points;
		$user->save();

		return $points;
	}

	function addPoints_ambra ($action, $username, $course_id, $course_name)
    {
        JLoader::register('Ambra', JPATH_ADMINISTRATOR.'/components/com_ambra/defines.php');
        JLoader::register('AmbraConfig', JPATH_ADMINISTRATOR.'/components/com_ambra/defines.php');
        JLoader::register('AmbraQuery', JPATH_ADMINISTRATOR.'/components/com_ambra/library/query.php');
        JLoader::register('AmbraHelperBase', JPATH_ADMINISTRATOR.'/components/com_ambra/helpers/_base.php');

		$user = JFactory::getUser ($username);
		$user_id = $user->id;
        $helper = AmbraHelperBase::getInstance('Point');
        return $helper->createLogEntry( $user_id, 'com_joomdle', $action );
        if ($helper->createLogEntry( $user_id, 'com_joomdle', $action ))
        {
            JFactory::getApplication()->enqueueMessage( $helper->getError() );
        }

		return $user_id;

    }

}


?>
