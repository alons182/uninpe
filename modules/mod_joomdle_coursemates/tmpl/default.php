<?php // no direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR.'/components/com_joomdle/helpers/mappings.php');
JHTML::_('behavior.tooltip');

$user = JFactory::getUser();

if ($user->id)
	$logged_user = $user->username;
else
	$logged_user = '';

?>

<div class="joomdlecoursemates<?php echo $moduleclass_sfx; ?>">
    <?php
    if( !empty( $users ) )
    {
    ?>
        <div>
            <ul style="list-style:none; margin: 0 0 10px; padding: 0;">
            <?php
			$i = 0;
            foreach( $users as $data )
            {
				$i++;
				if ($i > $limit)
					break;

				if ($logged_user)
				{
					if ($logged_user == $data['username'])
						continue;
				}

				$user_info = JoomdleHelperMappings::get_user_info_for_joomla ($data['username']);

				$user_id = JUserHelper::getUserId ($data['username']);
                $user       = JFactory::getUser($user_id);
				if (array_key_exists ('profile_url', $user_info))
					$userLink   = $user_info['profile_url'];
				else
					$userLink = '';
				$userName = $user_info['name'];

				// Use thumbs if available
				if ((array_key_exists ('thumb_url', $user_info)) && ($user_info['thumb_url'] != ''))
					$user_info['pic_url'] = $user_info['thumb_url'];

                $html  = '<li style="display: inline; padding: 0 3px 3px 0; background: none;">';
                if($tooltips)
                {
                    $html .= '<span class="editlinktip hasTip" title="'.$user_info['name'].'">';
                    if ($userLink)
                        $html .= '  <a href="'.$userLink.'">';
                    $html .= '<img width="32" src="'.$user_info['pic_url']. '" style="padding: 2px; border: solid 1px #ccc;" /></span>';
                }
                else
                {
                    if ($userLink)
                        $html .= '  <a href="'.$userLink.'">';
                    $html .= '  <img width="32" src="'.$user_info['pic_url'].'" alt="'. $userName.'" title="'.$userName.'" style="padding: 2px; border: solid 1px #ccc;" />';
                }
                if ($userLink)
                    $html .= '</a>';
                $html .= '</li>';
                echo $html;

            }
            ?>
            </ul>
        </div>
        <div>
			<a  style='float:right;' href="index.php?option=com_joomdle&view=coursemates&course_id=<?php echo $course_id; ?>"><?php echo JText::_ ('COM_JOOMDLE_SEE_ALL'); ?></a>
        </div>
    <?php
    }
    else
    {
        echo JText::_('COM_JOOMDLE_NO_STUDENTS_YET');
    }
    ?>
    <div style='clear:both;'></div>
</div>

