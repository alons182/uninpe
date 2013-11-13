<?php
/**
 * @author Antonio Durán Terrés
 * @package Joomdle
 * @license GNU/GPL
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Import Joomla! libraries
jimport( 'joomla.application.component.view');
class JoomdleViewDefault extends JViewLegacy {


 function showButton( $link, $image, $text )
        {
                global $mainframe;
                $lang           =& JFactory::getLanguage();
                ?>
                        <div class="btn" style="width:140px;">
                                <a href="<?php echo $link; ?>">
					<?php //echo JHTML::_('image', 'joomdle/' . $image , NULL, NULL, $text ); ?>
					<?php //echo JHTML::image ('/media/joomdle/images/' . $image , $text, array ('align' => 'top'), NULL ); ?>
                    <?php echo JHTML::_('image', 'administrator/components/com_joomdle/assets/images/' . $image , NULL, NULL ); ?>

										</a>
										<br>
                                        <span><?php echo $text; ?></span>
                        </div>
                <?php
        }

 
 function renderAbout ()
 {
	$xmlfile = JPATH_ADMINISTRATOR.'/components/com_joomdle/manifest.xml';

	if (file_exists($xmlfile))
	{
		if ($data = JApplicationHelper::parseXMLInstallFile($xmlfile)) {
			$version =  $data['version'];
		}
	}


	 $output = '<div style="padding: 5px;">';
	 $output .= JText::sprintf('COM_JOOMDLE_ABOUT_TEXT_VERSION', $version);
	 $output .= '<P>'.JText::sprintf('COM_JOOMDLE_ABOUT_TEXT_PROVIDES');
	 $output .= '<P>'.JText::sprintf('COM_JOOMDLE_ABOUT_TEXT_SUPPORT');
	 $output .= '<P>'.JText::sprintf('COM_JOOMDLE_ABOUT_TEXT_DONATION');

	$amountLine = '<P>'.JText::_('COM_JOOMDLE_AMOUNT').':&nbsp;<input type="text" name="amount" size="4" maxlength="10" value="" style="text-align:right;" />';

	$fe_c = '<select name="currency_code">'."\n";

	$currencies = array ('USD', 'EUR', 'GBP', 'SGD');
	foreach($currencies as $row) {
				$fe_c .= '<option value="'.$row.'">'.$row.'</option>'."\n";
			}
	$fe_c .= '</select>'."\n";

	$langSite = 'en';
	 $output .= '
	 <form action="https://www.paypal.com/'.$langSite.'/cgi-bin/webscr" method="post" target="paypal">
	<input type="hidden" name="cmd" value="_donations" />
	<input type="hidden" name="business" value="paypal@joomdle.com" />
	<input type="hidden" name="return" value="" />
	<input type="hidden" name="undefined_quantity" value="0" />
	<input type="hidden" name="item_name" value="Joomdle donation" />
	 '.$amountLine .'&nbsp;'. $fe_c.'
	<input type="hidden" name="charset" value="utf-8" />
	<input type="hidden" name="no_shipping" value="1" />
	<input type="hidden" name="image_url" value="" />
	<input type="hidden" name="cancel_return" value="" />
	<input type="hidden" name="no_note" value="0" />
	<input type="image" src="http://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" name="submit" alt="PayPal secure payments."  height="25"/>
</form>';

	/*	<input type="image" src="http://www.paypal.com/<?php echo $params->get('locale'); ?>/i/btn/<?php echo $params->get('pp_image'); ?>" name="submit" alt="PayPal secure payments." />
*/	 
	$output .= '<P>'.JText::sprintf('COM_JOOMDLE_ABOUT_TEXT_JED');
	 $output .= '</div>';



	 return $output;

 }

    function display($tpl = null) {


        parent::display($tpl);
    }
}
?>
