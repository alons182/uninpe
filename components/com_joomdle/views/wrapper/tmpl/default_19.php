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
<script language="javascript" type="text/javascript">
    function getElement(aID)
    {
        return (document.getElementById) ?
            document.getElementById(aID) : document.all[aID];
    }

    function getIFrameDocument(aID)
    {
        var rv = null;
        var frame=getElement(aID);
        // if contentDocument exists, W3C compliant (e.g. Mozilla)

        if (frame.contentDocument)
            rv = frame.contentDocument;
        else // bad IE  ;)

            rv = document.frames[aID].document;
        return rv;
    }


function adjustMyFrameHeight() {
       var h = 0;
       if ( !document.all ) {
               document.getElementById('blockrandom').style.height = h + 'px'; //needed to work in safari and chrome
               h = document.getElementById('blockrandom').contentDocument.height;
               document.getElementById('blockrandom').style.height = h + 60 + 'px';
       } else if( document.all ) {
               h = document.frames('blockrandom').document.body.scrollHeight;
               document.all.blockrandom.style.height = h + 20 + 'px';
       }
}

    function xadjustMyFrameHeight()
    {
        var frame = getElement("blockrandom");
        var frameDoc = getIFrameDocument("blockrandom");
	//	var frameDoc = (frame.contentDocument) ? frame.contentDocument : frame.contentWindow.document;
 
//		var height = Math.max( body.scrollHeight, body.offsetHeight, 
//				                       html.clientHeight, html.scrollHeight, html.offsetHeight );

		height = "XX";
		frame.height = 0;
	//	alert (height);
		frameDoc1 = frame.contentDocument;
//		alert ("x" +  frameDoc1.contentWindow);
	//	alert (window.frames["blockrandom"]); //.document.body.scrollHeight);
	//	alert (frameDoc.body.scrollHeight);
	//	alert (frameDoc.body.offsetHeight);
	//	alert ("X");
	//	alert ("X" + document.getElementById('blockrandom').contentDocumen t.body.scrollHeight);
	//	alert ("X" + frameDoc.body.offsetHeight);
		//alert (frameDoc.body);
		//alert(print_r (frameDoc.body,4, ' '));
     //   frame.height = frameDoc.body.offsetHeight + 50; //XXX works for me with this margin, withoout it, bars are shown, even in Jfusion
        frame.height = frameDoc.body.scrollHeight + 50; //XXX works for me with this margin, withoout it, bars are shown, even in Jfusion
    }



</script>
<div class="contentpane">
<iframe

<?php if ($this->params->get('autoheight', 1)) { ?>
    onload="adjustMyFrameHeight();"
<?php 
} 
?>

id="blockrandom"
name="iframe"
src="<?php echo $this->wrapper->url; ?>"
width="<?php echo $this->params->get('width', '100%'); ?>"

<?php if (!$this->params->get('autoheight', 1)) { ?>
height="<?php echo $this->params->get('height', '500'); ?>"
<?php 
} 
?>

scrolling="<?php echo $this->params->get('scrolling', 'auto'); ?>"
<?php if ($this->params->get('transparency')) { ?>
    allowtransparency="true"
    <?php
} else { ?>
    allowtransparency="false"
    <?php
} ?>

align="top" frameborder="0" class="wrapper">
<?php echo JText::_('OLD_BROWSER'); ?>
</iframe>
</div>
