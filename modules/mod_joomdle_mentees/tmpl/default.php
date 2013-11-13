<?php // no direct access
defined('_JEXEC') or die('Restricted access');

if ($default_itemid)
	$itemid = $default_itemid;
else
	$itemid = JoomdleHelperContent::getMenuItem();


if ($linkstarget == "new")
	$target = " target='_blank'";
else $target = "";

if ($linkstarget == 'wrapper')
	$open_in_wrapper = 1;
else
	$open_in_wrapper = 0;

$lang = JoomdleHelperContent::get_lang ();

?>
    <ul class="joomdlementees<?php echo $moduleclass_sfx; ?>">
<?php


	if (is_array($mentees))
	foreach ($mentees as $id => $mentee) {
		$id = $mentee['id'];
			if ($username)
			{
				echo "<li><a $target href=\"".$moodle_auth_land_url."?username=$username&token=$token&mtype=user&id=$id&use_wrapper=$open_in_wrapper&create_user=1&Itemid=$itemid\">".$mentee['name']."</a></li>";
			   if ($lang)
                   $link .= "&lang=$lang";
			}
	}
?>
    </ul>
