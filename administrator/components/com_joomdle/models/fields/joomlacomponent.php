<?php
/**
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Qexan
 */
class JFormFieldJoomlacomponent extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Joomlacomponent';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getOptions()
	{
		// Initialise variables.
		$options = array();

		$option['value'] = 'jomsocial';
		$option['text'] = 'Jomsocial';
		$options[] = $option;

		$option['value'] = 'joomla16';
		$option['text'] = 'Joomla User profiles';
		$options[] = $option;

		$option['value'] = 'cb';
		$option['text'] = 'Community Builder';
		$options[] = $option;

		$option['value'] = 'hikashop';
		$option['text'] = 'Hikashop';
		$options[] = $option;

		$option['value'] = 'virtuemart2';
		$option['text'] = 'Virtuemart2';
		$options[] = $option;

		return $options;
	}
}
