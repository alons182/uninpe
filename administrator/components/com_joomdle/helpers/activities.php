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
class JoomdleHelperActivities
{

	function add_activity_course ($id, $name, $desc, $cat_id, $cat_name)
    {
        require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

        CFactory::load ( 'libraries', 'activities' );

        $cat_slug = JFilterOutput::stringURLSafe ($cat_name);
        $course_slug = JFilterOutput::stringURLSafe ($name);

        $mainframe =& JFactory::getApplication('site');
        $mainframe->initialise();

        /* Kludge para que no pete el call_method */
        if ($desc == ' ')
            $desc = '';

        $user_id = JoomdleHelperSystem::get_admin_id ();

        $act = new stdClass();

        $message                = JText :: _ ('COM_JOOMDLE_NEW_COURSE_AVAILABLE'). '';

        $link = ("index.php?option=com_joomdle&view=detail&cat_id=$cat_id:$cat_slug&course_id=$id:$course_slug");
        $message                .= ' <a href="' . $link .'">' . $name . '</a> ';

        $cat_link = ("index.php?option=com_joomdle&view=coursecategory&cat_id=$cat_id:$cat_slug");
        $message                .= JText::_('COM_JOOMDLE_IN_CATEGORY')." ";
        $message                .= ' <a href="' . $cat_link .'">' . $cat_name . '</a> ';

        $act->cmd               = 'joomdle.create';
        $act->actor     = $user_id;
        $act->access     = 0;
        $act->target    = 0;
        $act->title             = JText::_( $message );
        $act->content   = $desc;
        $act->app               = 'joomdle';
        $act->cid               = 0;
        CActivityStream::add( $act );

        return "OK";
    }

    function add_activity_course_enrolment ($username, $course_id, $course_name, $cat_id, $cat_name)
    {
        require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

        CFactory::load ( 'libraries', 'activities' );

        $course_slug = JFilterOutput::stringURLSafe ($course_name);

        $cat_slug = JFilterOutput::stringURLSafe ($cat_name);

        $user_id = JUserHelper::getUserId($username);

        $mainframe =& JFactory::getApplication('site');
        $mainframe->initialise();

        $act = new stdClass();

        $message                = JText::_('COM_JOOMDLE_USER_ENROLED_INTO_THE_COURSE').' ';


        $link = ("index.php?option=com_joomdle&view=detail&cat_id=$cat_id:$cat_slug&course_id=$course_id:$course_slug");
        $message                .= ' <a href="' . $link .'">' . $course_name . '</a> ';

        $act->cmd               = 'joomdle.enrolment';
        $act->actor     = $user_id;
        $act->access     = 0;
        $act->target    = 0;
        $act->title             = JText::_( $message );
        $act->content   = '';
        $act->app               = 'joomdle';
        $act->cid = 0;
        CActivityStream::add( $act );

        return "OK";
    }

	function add_activity_quiz_attempt ($username, $course_id, $course_name, $quiz_name)
    {
        require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

        CFactory::load ( 'libraries', 'activities' );

        $course_slug = JFilterOutput::stringURLSafe ($course_name);

        $user_id = JUserHelper::getUserId($username);

        $mainframe =& JFactory::getApplication('site');
        $mainframe->initialise();

        $act = new stdClass();

        $message                = JText::_('COM_JOOMDLE_USER_QUIZ_ATTEMPT_SUBMITTED').' ' . $quiz_name . ' ';
        $message                .= JText::_('COM_JOOMDLE_IN_COURSE')." ";
        $link = ("index.php?option=com_joomdle&view=detail&course_id=$course_id:$course_slug");
        $message                .= ' <a href="' . $link .'">' . $course_name . '</a> ';

        $act->cmd               = 'joomdle.quizattempt';
        $act->actor     = $user_id;
        $act->access     = 0;
        $act->target    = 0;
        $act->title             = JText::_( $message );
        $act->content   = '';
        $act->app               = 'joomdle';
        $act->cid = 0;
        CActivityStream::add( $act );

        return "OK";
    }

	function add_activity_course_completed ($username, $course_id, $course_name)
    {
        require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

        CFactory::load ( 'libraries', 'activities' );

        $course_slug = JFilterOutput::stringURLSafe ($course_name);

        $user_id = JUserHelper::getUserId($username);

        $mainframe =& JFactory::getApplication('site');
        $mainframe->initialise();

        $act = new stdClass();

        $message                = JText::_('COM_JOOMDLE_USER_COMPLETED_COURSE');
        $link = ("index.php?option=com_joomdle&view=detail&course_id=$course_id:$course_slug");
        $message                .= ' <a href="' . $link .'">' . $course_name . '</a> ';

        $act->cmd               = 'joomdle.coursecompleted';
        $act->actor     = $user_id;
        $act->access     = 0;
        $act->target    = 0;
        $act->title             = JText::_( $message );
        $act->content   = '';
        $act->app               = 'joomdle';
        $act->cid = 0;
        CActivityStream::add( $act );

        return "OK";
    }


}


?>
