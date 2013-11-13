<?php

/**
 * This is view file for cpanel
 *
 * PHP version 5
 *
 * @category   JFusion
 * @package    ViewsFront
 * @subpackage Wrapper
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org

 * Mofified by Antonio Duran to work with Joomdle
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

?>
<script src="<?php echo JURI::root(); ?>/components/com_joomdle/js/autoheight.js" type="text/javascript"></script>


<div class="contentpane"> 
    <iframe 
    	id="blockrandom" 
        class="autoHeight"
        src="<?php echo $this->wrapper->url; ?>"
        width="<?php echo $this->params->get('width', '100%'); ?>"
        scrolling="<?php echo $this->params->get('scrolling', 'auto'); ?>"

		<?php if (!$this->params->get('autoheight', 1)) { ?>
            height="<?php echo $this->params->get('height', '500'); ?>"
        <?php
        }
        ?>

        align="top" 
        frameborder="0"
		<?php if ($this->params->get('autoheight', 1)) { ?>
            onload='itspower(this, false, true, 20)'
        <?php
        }
        ?>

 
        ></iframe>
</div>

