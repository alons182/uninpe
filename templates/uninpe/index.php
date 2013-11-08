<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Uninpe
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;



$app = JFactory::getApplication();
$doc = JFactory::getDocument();
$this->language = $doc->language;


$itemid   = $app->input->getCmd('Itemid', '');

// Add JavaScript Frameworks
//JHtml::_('bootstrap.framework');

// Add Stylesheets
$doc->addStyleSheet('templates/'.$this->template.'/css/normalize.min.css');
$doc->addStyleSheet('templates/'.$this->template.'/css/main.css');



?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" >
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<jdoc:include type="head" />
     <link rel="icon" type="image/png" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/img/favicon_32x32.ico">
	 <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
      <?php if ($itemid == 101):?>
            <style type="text/css">
                
                #contenido{
                    
                    background: #FFF !important;
                }
            </style>
        <?php endif; ?>
     <script src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/js/vendor/modernizr-2.6.2.min.js"></script>
     
     
</head>

<body class="<?php echo ($itemid ? ' bgid-' . $itemid : '')?>">
	
	<div id="wrapper">
            <header>
                <div id="logo"><a href="<?php echo $this->baseurl ?>"><img src="<?php echo $this->baseurl ?>/templates/uninpe/img/logo.png" alt="UNINPE"></a></div>
                <div id="info-header">
                    <div id="info">
                        <jdoc:include type="modules" name="info" style="none" />
                    </div>
                    <div id="buscador">
                        <jdoc:include type="modules" name="buscador" style="none" />
                    </div>

                </div>
                <nav id="menu">
                     <jdoc:include type="modules" name="menu" style="none" />
                </nav>

            </header>

            <?php if ($this->countModules('banner')) : ?>
            <section id="banner">
               <jdoc:include type="modules" name="banner" style="none" />
            </section>
            <?php endif; ?>
            <?php if ($this->countModules('middle-content')) : ?>
            <section id="middle-content">
                <jdoc:include type="modules" name="middle-content" style="none" />
            </section>
            <?php endif; ?>
            
            <section id="contenido">
                <section id="center-content">
                    <?php if ($this->countModules('left-content')) : ?>
                    <div class="content left-content">
                        <jdoc:include type="modules" name="left-content" style="xhtml" />
                    </div>
                     <?php endif; ?>
                     <?php if ($this->countModules('center-content')) : ?>
                    <div class="content center-content">
                        <jdoc:include type="modules" name="center-content" style="xhtml" />
                    </div>
                    <?php endif; ?>
                    <?php if ($this->countModules('testimonials')) : ?>
                    <div class="content testimonials">
                       <jdoc:include type="modules" name="testimonials" style="xhtml" />
                    </div>
                    <?php endif; ?>
                </section>
                <?php if ($this->countModules('breadcumbs')) : ?>
                <div id="breadcumbs">
                    <jdoc:include type="modules" name="breadcumbs" style="none" />
                </div>
                <?php endif; ?>
                <?php if ($this->countModules('title')) : ?>
                <div id="title"><jdoc:include type="modules" name="title" style="none" /></div>
                <?php endif; ?>
                <?php if ($this->countModules('left')) : ?>
                <div id="left">
                    <jdoc:include type="modules" name="left" style="xhtml" />
                </div>
                <?php endif; ?>
                
               
                    <jdoc:include type="component" />

                <?php if ($this->countModules('right')) : ?>
                <div id="right">
                    <jdoc:include type="modules" name="right" style="xhtml" />
                </div>
                <?php endif; ?>
                 <?php if ($this->countModules('bottom-content')) : ?>
                    <div class="bottom-content">
                        <jdoc:include type="modules" name="bottom-content" style="xhtml" />
                    </div>
                 <?php endif; ?>
               
            </section>
            <footer>
               
                <div class="menu-footer">
                    <jdoc:include type="modules" name="menu-footer" style="none" />
                    
                </div>
                <div id="copyright">
                    <jdoc:include type="modules" name="copyright" style="none" />
                </div>
                
            </footer>

        </div>


    
      
        
        <script src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/js/vendor/jquery-1.10.1.min.js"></script>
        <script src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/js/vendor/jquery.validate.min.js"></script>
        <script src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/js/vendor/jquery.cycle2.min.js"></script>
        <script src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/js/vendor/jquery.cycle2.carousel.min.js"></script>
        <script src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/js/main.js"></script>

        <script>
            /*var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
            (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
            g.src='//www.google-analytics.com/ga.js';
            s.parentNode.insertBefore(g,s)}(document,'script'));*/
        </script>

	<jdoc:include type="modules" name="debug" style="none" />
</body>
</html>
