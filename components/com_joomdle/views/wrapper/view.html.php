<?php
/**
 * Joomdle
 *
 * @author Antonio Durán Terrés
 * @package Joomdle
 * @license GNU/GPL
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Joomdle component
 */
class JoomdleViewWrapper extends JViewLegacy {
	function display($tpl = null) {
	global $mainframe;

	$app                = JFactory::getApplication();
	$params = $app->getParams();
	$this->assignRef('params',              $params);

	$this->wrapper->load = '';

	$mtype =  JRequest::getVar( 'moodle_page_type' );
	if (!$mtype)
		$mtype = $params->get( 'moodle_page_type' );
	$id = $params->get( 'course_id' );
	if (!$id)
		$id =  JRequest::getVar( 'id' );
	$day =  JRequest::getVar( 'day' );
	$mon =  JRequest::getVar( 'mon' );
	$year =  JRequest::getVar( 'year' );

	$lang =  JRequest::getVar( 'lang' );

	$topic =  JRequest::getVar( 'topic' );
	$redirect =  JRequest::getVar( 'redirect' );

	switch ($mtype)
	{
		case "course" :
			$path = '/course/view.php?id=';
			$this->wrapper->url = $params->get( 'MOODLE_URL' ).$path.$id;
			if ($topic)
				$this->wrapper->url .= '&topic='.$topic;
			break;
		case "news" :
			$path = '/mod/forum/discuss.php?d=';
			$this->wrapper->url = $params->get( 'MOODLE_URL' ).$path.$id;
			break;
		case "forum" :
			$path = '/mod/forum/view.php?id=';
			$this->wrapper->url = $params->get( 'MOODLE_URL' ).$path.$id;
			break;
		case "event" :
		//	$path = "/calendar/view.php?view=day&course=$id&cal_d=$day&cal_m=$mon&cal_y=$year";
			$path = "/calendar/view.php?view=day&cal_d=$day&cal_m=$mon&cal_y=$year";
			$this->wrapper->url = $params->get( 'MOODLE_URL' ).$path;
			break;
		case "user" :
			$path = '/user/view.php?id=';
			$this->wrapper->url = $params->get( 'MOODLE_URL' ).$path.$id;
			break;
        case "edituser" :
            $user = JFactory::getUser ();
            $id = JoomdleHelperContent::call_method ('user_id', $user->username);
            $path = '/user/edit.php?&course_id=1&id=';
            $this->wrapper->url = $params->get( 'MOODLE_URL' ).$path.$id;
            break;
		case "resource" :
			$path = '/mod/resource/view.php?id=';
			$this->wrapper->url = $params->get( 'MOODLE_URL' ).$path.$id;
			break;
		case "quiz" :
			$path = '/mod/quiz/view.php?id=';
			$this->wrapper->url = $params->get( 'MOODLE_URL' ).$path.$id;
			break;
		case "page" :
			$path = '/mod/page/view.php?id=';
			$this->wrapper->url = $params->get( 'MOODLE_URL' ).$path.$id;
			break;
		case "assignment" :
			$path = '/mod/assignment/view.php?id=';
			$this->wrapper->url = $params->get( 'MOODLE_URL' ).$path.$id;
			break;
		case "folder" :
			$path = '/mod/folder/view.php?id=';
			$this->wrapper->url = $params->get( 'MOODLE_URL' ).$path.$id;
			break;
		case "moodle" :
			$path = '/?a=1';
			$this->wrapper->url = $params->get( 'MOODLE_URL' ).$path;
			break;
		default:
			if ($mtype)
			{
				$path = '/mod/'.$mtype.'/view.php?id=';
				$this->wrapper->url = $params->get( 'MOODLE_URL' ).$path.$id;
				break;
			}
			else
			{
				$path = '/?a=1';
				$this->wrapper->url = $params->get( 'MOODLE_URL' ).$path;
			}
			break;
	}

	if ($lang)
		$this->wrapper->url .= "&lang=$lang";

	if ($redirect)
		$this->wrapper->url .= "&redirect=$redirect";

        parent::display($tpl);
    }
}
?>
