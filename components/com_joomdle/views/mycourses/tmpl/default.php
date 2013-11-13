<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
$linkto = $this->params->get( 'mycourses_linkto' );
$default_itemid = $this->params->get( 'default_itemid' );
$joomdle_itemid = $this->params->get( 'joomdle_itemid' );
$courseview_itemid = $this->params->get( 'courseview_itemid' );
if ($linkto == 'moodle')
{
    if ($default_itemid)
        $itemid = $default_itemid;
    else
        $itemid = JoomdleHelperContent::getMenuItem();
}
else if ($linkto == 'detail')
{
        $itemid = JoomdleHelperContent::getMenuItem();

        if ($joomdle_itemid)
            $itemid = $joomdle_itemid;
}
else
{
        $itemid = JoomdleHelperContent::getMenuItem();

        if ($joomdle_itemid)
            $itemid = $joomdle_itemid;
        if ($courseview_itemid)
            $itemid = $courseview_itemid;
}

$linkstarget = $this->params->get( 'linkstarget' );
if ($linkstarget == "new")
	 $target = " target='_blank'";
 else $target = "";
?>

<div class="joomdle-mycourses<?php echo $this->pageclass_sfx;?>">
    <?php if ($this->params->get('show_page_heading', 1)) : ?>
    <h1>
    <?php echo $this->escape($this->params->get('page_heading')); ?>
    </h1>
    <?php endif; ?>

    <div class="joomdle_mycourses">
    <ul>
    <?php
    $lang = JoomdleHelperContent::get_lang ();
    if (is_array ($this->my_courses))
        foreach ($this->my_courses as $id => $curso) :  ?>
            <li>
                <?php
                    if ($linkto == 'moodle')
                    {
                        $link = $this->jump_url."&mtype=course&id=".$curso['id']."&Itemid=$itemid";
                        if ($lang)
                            $link .= "&lang=$lang";
                        echo "<a $target href=\"$link\">".$curso['fullname']."</a>";
                    }
                    else if ($linkto == 'detail')
                    {
                        // Link to detail view
                        $redirect_url = JRoute::_("index.php?option=com_joomdle&view=detail&course_id=".$curso['id'].':'.JFilterOutput::stringURLSafe($curso['fullname'])."&Itemid=$itemid");
                        echo "<a href=\"".$redirect_url."\">".$curso['fullname']."</a>";
                    }
                    else
                    {
                        // Link to course view
                        $redirect_url = JRoute::_("index.php?option=com_joomdle&view=course&course_id=".$curso['id'].':'.JFilterOutput::stringURLSafe($curso['fullname'])."&Itemid=$itemid");
                        echo "<a href=\"".$redirect_url."\">".$curso['fullname']."</a>";
                    }
                ?>
            </li>
        <?php endforeach; ?>
    </ul>
    </div>
</div>
