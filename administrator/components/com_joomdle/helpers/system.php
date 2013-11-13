<?php
/**
 * @version		
 * @package		Joomdle
 * @copyright	Copyright (C) 2008 - 2010 Antonio Duran Terres
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.user.helper');

/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomdle
 * @since 1.5
 */
class JoomdleHelperSystem
{
	function check_system ()
	{
		$mainframe = JFactory::getApplication();
		$comp_params = &JComponentHelper::getParams( 'com_joomdle' );

		// If profiles types enabled, check at least 1 is defined to be created in moodle
		$use_profiletypes = $comp_params->get( 'use_profiletypes' );
		if ($use_profiletypes)
		{
			$profiles = JoomdleHelperProfiletypes::get_profiletypes_to_create ();
			if (count ($profiles) < 1)
			{
				$msg = JText::_('COM_JOOMDLE_NO_PROFILES_TO_CREATE_IN_MOODLE');
				$mainframe->enqueueMessage($msg);
			}
		}
	}

	function send_registration_email ($username, $password)
	{
		$config = JFactory::getConfig();
        $params = JComponentHelper::getParams('com_users');
        $useractivation = $params->get('useractivation');

		JPlugin::loadLanguage('com_users', JPATH_SITE);

		$data['fromname']   = $config->get('fromname');
        $data['mailfrom']   = $config->get('mailfrom');
        $data['sitename']   = $config->get('sitename');
        $data['siteurl']    = JUri::base();

		$user_id = JUserHelper::getUserId($username);
        $user = JFactory::getUser ($user_id);

		$data['username'] = $username;
		$data['password_clear'] = $password;
		$data['name'] = $user->name;
		$data['email'] = $user->email;
		$data['activation'] = $user->activation;

        // Handle account activation/confirmation emails.
        if ($useractivation == 2)
        {
            // Set the link to confirm the user email.
            $uri = JURI::getInstance();
            $base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
            $data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

            $emailSubject   = JText::sprintf(
                'COM_USERS_EMAIL_ACCOUNT_DETAILS',
                $data['name'],
                $data['sitename']
            );

            $emailBody = JText::sprintf(
                'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY',
                $data['name'],
                $data['sitename'],
                $data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
                $data['siteurl'],
                $data['username'],
                $data['password_clear']
            );
        }
        else if ($useractivation == 1)
        {
            // Set the link to activate the user account.
            $uri = JURI::getInstance();
            $base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
            $data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

            $emailSubject   = JText::sprintf(
                'COM_USERS_EMAIL_ACCOUNT_DETAILS',
                $data['name'],
                $data['sitename']
            );

            $emailBody = JText::sprintf(
                'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
                $data['name'],
                $data['sitename'],
                $data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
                $data['siteurl'],
                $data['username'],
                $data['password_clear']
            );
        } else {

            $emailSubject   = JText::sprintf(
                'COM_USERS_EMAIL_ACCOUNT_DETAILS',
                $data['name'],
                $data['sitename']
            );

            $emailBody = JText::sprintf(
                'COM_USERS_EMAIL_REGISTERED_BODY',
                $data['name'],
                $data['sitename'],
                $data['siteurl']
            );
        }

        // Send the registration email.
		$mail_class = JMail::getInstance ();
        $return = $mail_class->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);
	}

	/* Gets the first superadmin user id */
    function get_admin_id ()
    {
             $db           =& JFactory::getDBO();
             $query = 'SELECT user_id' .
                             ' FROM #__user_usergroup_map' .
                             " WHERE group_id = '8' LIMIT 1";
            $db->setQuery( $query );
            $result = $db->loadResult();

            return $result;
    }

	function fix_text_format ($str)
	{
		  $params = &JComponentHelper::getParams( 'com_joomdle' );
		  $moodle_version = $params->get( 'moodle_version');

		  if ($moodle_version == '19')
			  $str = nl2br ($str);

		  return $str;
	}

    function get_icon_url ($mod, $type)
    {
        //$url = JURI::root(true).'/components/com_joomdle/assets/icons/';
        $url = JURI::root(true).'/media/joomdle/images/';

        switch ($mod)
        {
            case 'resource':
                switch ($type)
                {
                    case 'pdf':
                        $filename = 'pdf.png';
                        break;
                    case 'avi':
                    case 'flash':
                    case 'video':
                        $filename = 'video.png';
                        break;
                    case 'audio':
                        $filename = 'audio.png';
                        break;
                    case 'image':
                        $filename = 'image.png';
                        break;
                    case 'page': // moodle 1.9
                    case 'web': // moodle 1.9
                        $filename = 'page.png';
                        break;
                    default:
                        $filename = 'unknown.png';
                        break;
                }
                break;

            case 'quiz':
                $filename = 'question.png';
                break;
            case 'forum':
                $filename = 'forum.png';
                break;
            case 'page':
            case 'url':
                $filename = 'page.png';
                break;
            case 'assignment':
                $filename = 'assignment.png';
                break;
            case 'folder':
                $filename = 'folder.png';
                break;
            case 'lesson':
                $filename = 'lesson.png';
                break;
            case 'glossary':
                $filename = 'glossary.png';
                break;
            case 'survey':
                $filename = 'survey.png';
                break;
            case 'chat':
                $filename = 'chat.png';
                break;
            case 'choice':
                $filename = 'choice.png';
                break;
            case 'data':
                $filename = 'data.png';
                break;
            case 'scorm':
                $filename = 'scorm.png';
                break;
            case 'wiki':
                $filename = 'wiki.png';
                break;
            case 'workshop':
                $filename = 'workshop.png';
                break;
            case 'certificate':
                $filename = 'certificate.png';
                break;
			default:
				$filename = 'default.png';
		//		return $filename;
        }

        return $url.$filename;
    }

    function get_mtype ($mod)
    {
        $mtype = '';
        switch ($mod)
        {
            case 'resource':
            case 'quiz':
            case 'page':
            case 'forum':
            case 'url':
            case 'assignment':
            case 'label':
            case 'folder':
                $mtype = $mod;
                break;
            default:
                $mtype = $mod;
                break;
        }

        return $mtype;
    }

    function get_direct_link ($mod, $course_id, $mod_id, $type)
    {
        $link = '';
        $params = &JComponentHelper::getParams( 'com_joomdle' );

        switch ($mod)
        {
            case 'resource':
                if ($type == 'page') //1.9
                {
                    if  ($params->get( 'use_page_view'))
                    {
                        $itemid = JoomdleHelperContent::getMenuItem();
                        if (!$itemid)
                            $itemid =  $params->get( 'joomdle_itemid' );
                        $link = JRoute::_("index.php?option=com_joomdle&view=page&course_id=$course_id&page_id=$mod_id&itemid=$itemid");
                    }
                }
				else if ($type == 'folder') //1.9
                {
                    $link ='';
                    break;
                }
                else
                    $link = $params->get( 'MOODLE_URL' ) . '/mod/resource/view.php?redirect=1&id='.$mod_id;
                break;
            case 'url':
                $link = $params->get( 'MOODLE_URL' ) . '/mod/url/view.php?redirect=1&id='.$mod_id;
                break;
            case 'page':
                if  ($params->get( 'use_page_view'))
                {
                    $itemid = JoomdleHelperContent::getMenuItem();
                    if (!$itemid)
                        $itemid =  $params->get( 'joomdle_itemid' );
                   // $link = JRoute::_("index.php?option=com_joomdle&view=page&course_id=$course_id&page_id=$mod_id&itemid=$itemid");
                    $link = JRoute::_("index.php?option=com_joomdle&view=page&course_id=$course_id&page_id=$mod_id");
                }
                break;
            case 'forum':
				$itemid = JoomdleHelperContent::getMenuItem();
				if (!$itemid)
					$itemid =  $params->get( 'joomdle_itemid' );
				// Deal with news forum
				if ($type == 'news')
				{
					// If is news forum, link to coursenews view instead of forum
					//$link = JRoute::_("index.php?option=com_joomdle&view=coursenews&course_id=$course_id&itemid=$itemid");
					$link = JRoute::_("index.php?option=com_joomdle&view=coursenews&course_id=$course_id");
					break;
				}
                if  ($params->get( 'use_kunena_forums'))
                {
					require_once (JPATH_ADMINISTRATOR . '/components/com_joomdle/helpers/forum.php');

					$forum_id = JoomdleHelperForum::get_kunena_forum_id ($course_id, $mod_id);

					if ($forum_id)
						$link = JRoute::_("index.php?option=com_kunena&func=showcat&catid=$forum_id&course_id=$course_id&Itemid=$itemid");
					else
						$link = '';
                }
                break;
			case 'label':
				$link = 'none';
				break;
            case 'certificate':
                $link = $params->get( 'MOODLE_URL' ) . '/mod/certificate/view.php?certificate=1&id='.$mod_id.'&action=review';
                break;
        }

        return $link;
    }



    function get_course_itemid ($course_id)
    {
         $db           =& JFactory::getDBO();
         $query = 'SELECT id' .
                         ' FROM #__menu' .
                         " WHERE menutype = 'joomdlecourses'" .
                         " AND params LIKE '%course_id=$course_id%'";
        $db->setQuery( $query );
        $result = $db->loadResult();

        return $result;
    }


	   function actionbutton ( $course_info, $free_courses_button = 'enrol', $paid_courses_button = 'buy')
       {
			$course_id = $course_info['remoteid'];
			$user = & JFactory::getUser();
			$username = $user->username;
			$is_enroled = $course_info['enroled'];
			$guest = $course_info['guest'];

			$params = JComponentHelper::getParams('com_joomdle');
			$show_experience = $params->get('show_detail_application_experience');
			$show_motivation = $params->get('show_detail_application_motivation');
			$goto_course_button = $params->get('goto_course_button');
			$linkstarget = $params->get('linkstarget');

			$html = "";
			if ((($is_enroled) || ($guest))) {


				if ($goto_course_button)
				{
					$button_text = JText::_('COM_JOOMDLE_GO_TO_COURSE');
					$url = JoomdleHelperContent::get_course_url ($course_id);

					if ($linkstarget == 'new')
					{
						   $html .= '<FORM>
				   <INPUT TYPE="BUTTON" VALUE="  '. $button_text.'  "
						ONCLICK="window.open (\''.$url.'\')">
				   </FORM>';
					}
					else
					{
						   $html .= '<FORM>
				   <INPUT TYPE="BUTTON" VALUE="  '. $button_text.'  "
						ONCLICK="window.location.href=\'  '.$url.'  \'">
				   </FORM>'; 
					}
				}
		   }
		   else if ((!array_key_exists ('cost', $course_info)) || (!$course_info['cost'])){
                       if ($free_courses_button == 'goto'){

                               $button_text = JText::_('COM_JOOMDLE_GO_TO_COURSE');
                               $url = JoomdleHelperContent::get_course_url ($course_id);

							if ($linkstarget == 'new')
							{
								   $html .= '<FORM>
						   <INPUT TYPE="BUTTON" VALUE="  '. $button_text.'  "
								ONCLICK="window.open (\''.$url.'\')">
						   </FORM>';
							}
							else
							{
								   $html .= '<FORM>
						   <INPUT TYPE="BUTTON" VALUE="  '. $button_text.'  "
								ONCLICK="window.location.href=\'  '.$url.'  \'">
						   </FORM>'; 
							}
                       }               else if ($free_courses_button == 'enrol'){

                               $button_text = JText::_('COM_JOOMDLE_ENROL_INTO_COURSE');
                               $url = ("index.php?option=com_joomdle&task=enrol&course_id=$course_id");
 //                              $can_enrol = JoomdleHelperContent::can_enrol ($course_id) && JoomdleHelperContent::in_enrol_date ($course_id);
							   $can_enrol = $course_info['self_enrolment'] && $course_info['in_enrol_date'];
                               if ( $can_enrol){

                                       $html .= '<FORM>
               <INPUT TYPE="BUTTON" VALUE="  '. $button_text.'  "
ONCLICK="window.location.href=\'  '.$url.'  \'">
               </FORM>';

                               }
                       }
                       else if ($free_courses_button == 'applicate'){

                               if (!$user->id){

                                       $html .= '<br>'.JText::_('COM_JOOMDLE_YOU_NEED_TO_LOGIN_TO_APPLICATE');
                               }else {

                                       $app                = JFactory::getApplication();
                                       $results = $app->triggerEvent('onShowRequestCourseButton',
array($course_id, $user->id, &$message));
                                       if (in_array (false, $results))  {
                                               $html .= $message;
                                       }else {
                                               $html .= '
               <br>
               <FORM action="index.php?option=com_joomdle" method="post"
id="josForm" name="josForm" class="form-validate">';
                                               if ($show_motivation != 'no'){
													   $html .= JText::_('COM_JOOMDLE_MOTIVATION');
													   if ($show_motivation == 'mandatory')
														   $html .= '*';
														$html .= '<br>';
                                                       $html .= '<textarea id="motivation" name="motivation" cols="60"
rows="4"></textarea><br>';
                                               }
                                               $html .= '<br>&nbsp;</br>';
                                               if ($show_experience != 'no'){
                                                       $html .= JText::_('COM_JOOMDLE_EXPERIENCE');
													   if ($show_experience == 'mandatory')
														   $html .= '*';
														$html .= '<br>';
                                                       $html .= '<textarea id="experience" name="experience" cols="60"
rows="4"></textarea><br>';
                                               }

											   if (($show_motivation == 'mandatory') || ($show_experience == 'mandatory'))
												   $html .= JText::_ ('COM_JOOMDLE_MARKED_FIELDS_MANDATORY') ."<br>";
                                               $html .= '<INPUT TYPE="SUBMIT" VALUE="  '.JText::_('COM_JOOMDLE_APPLICATE_FOR_COURSE').'">
               <INPUT TYPE="hidden" name="course_id" VALUE="  '.$course_id.'  ">
               <INPUT TYPE="hidden" name="option" VALUE="com_joomdle">
               <INPUT TYPE="hidden" name="task" VALUE="applicate">
               <input type="hidden" name="id" value="0" />
               <input type="hidden" name="gid" value="0" />';
                                               $html .= JHTML::_( 'form.token' );
                                               $html .= '</FORM>';
                                       }
                               }
                       }
               }

               else { //paid courses
				   require_once( JPATH_ADMINISTRATOR.'/components/com_joomdle/helpers/shop.php' );
                       if ($paid_courses_button == 'buy')
					   {
                               if (JoomdleHelperShop::is_course_on_sell ($course_info['remoteid'])){

                                       $button_text = JText::_('COM_JOOMDLE_BUY_COURSE');
                                       $url = JRoute::_(JoomdleHelperShop::get_sell_url ($course_info['remoteid']));
									   $can_enrol = $course_info['in_enrol_date'];
                                       if ( $can_enrol){
                                               $html .= '
												   <FORM>
												   <INPUT TYPE="BUTTON" VALUE="  '.$button_text.'  "
									ONCLICK="window.location.href=\'  '.$url.'  \'">
												   </FORM>';
                                       }
                               }
					   }
					   else if ($paid_courses_button == 'paypal'){

                                       $url = ("index.php?option=com_joomdle&view=buycourse&course_id=$course_id");
                                       $html .= '<br><a href="'.$url.'"><img
src="https://www.paypal.com/en_US/i/logo/PayPal_mark_60x38.gif"></a>';
                               }
                       }


               return $html;
	}

}
