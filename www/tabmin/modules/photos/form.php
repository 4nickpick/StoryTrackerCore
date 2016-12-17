<?php TemplateSet::begin('scripts') ?>

    <!-- tinyMCE -->
    <script src="/js/tinymce/tinymce.min.js"></script>
    <script src="/js/tinymce/tinymce-init.js"></script>
    <link rel="stylesheet" href="/css/tinymce-custom.css" />

	<script type="text/javascript" src="/tabmin/modules/photos/js/module.js"></script>
	<script type="text/javascript">
		AlertSet.handleRedirectWithAlert();
	</script>

<?php TemplateSet::end('scripts')?>

<?php TemplateSet::begin('scripts_footer') ?>
    <script type="text/javascript">
        formInit();
    </script>
<?php TemplateSet::end() ?>

<h1>Edit Photo</h1>

<ul id="form_navigation">
	<li id="menu_details" class="current"><a href="javascript:" onclick="formSectionShow('details');">Details</a></li>
</ul>

<form id="photo_form" action="/photos/ajax/" method="post" class="tabmin_form" autocomplete="off" enctype="multipart/form-data">
	<input id=photo_form_verb" type="hidden" name="verb" value="<?php echo $verb?>" />
	<input id="pictures_id" type="hidden" name="pictures_id" value="<?php echo (@$picture ? $picture->get_id() : '')?>" />
	<?php echo XSRF::html()?>
	<table class="form_table">
		<tbody id="form_section_details">
			<tr>
				<th align="left" colspan="2"><h2>Details</h2></th>
			</tr>
			<tr>
				<th align="left">Preview:</th>
				<td>
                    <a class="fancybox" rel="group" href="/show-picture.php?pictures_id=<? echo $picture->get_id(); ?>">
                        <img class="photo" src="/show-picture.php?pictures_id=<? echo $picture->get_id(); ?>&w=180&h=180" />
                    </a>
                </td>
			</tr>
			<tr>
				<th align="left">Caption:</th>
				<td><textarea class="caption" name="caption" ><?php P::rint(@$picture ? $picture->get_caption() : '')?></textarea>
			</tr>

            <tr>
                <th colspan="2">
                    &nbsp;
                </th>
            </tr>
            <tr>
                <th align="left">Characters:</th>
                <td>
                    <?php
                    $characters = $picture->get_characters();
                    if( count($characters) > 0 )
                    {
                        foreach($characters as $i=>$character)
                        {
                            ?>
                            <span id="character-tag-<?=$picture->get_id()?>-<?=$character->get_id()?>" class="tag">
                                            <?=htmlentitiesUTF8($character->get_full_name())?>
                                <a id="character-tag-<?=$picture->get_id()?>-<?=$character->get_id()?>" class="remove-tag" href="javascript:" onclick="AlertSet.confirm('Are you sure you want to remove <?=addslashes($character->get_full_name())?> from this photo?', function(resp){removeTag('<?=$picture->get_id()?>', 'characters', '<?=$character->get_id()?>')}, function(resp){AlertSet.addJSON(resp).show();});">
                                    X
                                </a>
                                        </span>
                        <?php
                        }
                    }
                    ?>
                    <a href="javascript:" onclick="showQuickForm('#add-characters-to-picture-dialog');">Add Characters</a>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    &nbsp;
                </td>
            </tr>
            <tr>
                <th align="left">Settings:</th>
                <td>
                    <?php
                    $settings = $picture->get_settings();
                    if( count($settings) > 0 )
                    {
                        foreach($settings as $i=>$setting)
                        {
                            ?>
                            <span id="setting-tag-<?=$picture->get_id()?>-<?=$setting->get_id()?>" class="tag">
                                            <?=htmlentitiesUTF8($setting->get_full_name())?>
                                <a id="setting-tag-<?=$picture->get_id()?>-<?=$setting->get_id()?>" class="remove-tag" href="javascript:" onclick="AlertSet.confirm('Are you sure you want to remove <?=addslashes($setting->get_full_name())?> from this event?', function(resp){removeTag('<?=$picture->get_id()?>', 'settings', '<?=$setting->get_id()?>')}, function(resp){AlertSet.addJSON(resp).show();});">
                                    X
                                </a>
                                        </span>
                        <?php
                        }
                    }
                    ?>
                    <a href="javascript:" onclick="showQuickForm('#add-settings-to-picture-dialog');">Add Settings</a>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    &nbsp;
                </td>
            </tr>
            <tr>
                <th align="left">Plot Events:</th>
                <td>
                    <?php
                    $plot_events = $picture->get_plot_events();
                    if( count($plot_events) > 0 )
                    {
                        foreach($plot_events as $i=>$plot_event)
                        {
                            ?>
                            <span id="plot-event-tag-<?=$picture->get_id()?>-<?=$plot_event->get_id()?>" class="tag">
                                            <?=htmlentitiesUTF8($plot_event->get_event())?>
                                <a id="plot-event-tag-<?=$picture->get_id()?>-<?=$plot_event->get_id()?>" class="remove-tag" href="javascript:" onclick="AlertSet.confirm('Are you sure you want to remove <?=htmlentitiesUTF8($plot_event->get_event())?> from this event?', function(resp){removeTag('<?=$picture->get_id()?>', 'plot_events', '<?=$plot_event->get_id()?>')}, function(resp){AlertSet.addJSON(resp).show();});">
                                    X
                                </a>
                                        </span>
                        <?php
                        }
                    }
                    ?>
                    <a href="javascript:" onclick="showQuickForm('#add-plot-events-to-picture-dialog');">Add Plot Events</a>
                </td>
            </tr>
        </tbody>

		</tbody>
		<tbody id="form_section_buttons">
			<tr>
                <td id="throbber_wrapper">
                    <div id="throbber" style="display:none;"><img src="/images/throbber.gif"/></div>
                </td>
				<td><input class="save button" type="button" value="Save" onclick="formSave(this.form, '<?=$verb?>');"/></td>
				<td>
					<input class="cancel button" type="button" value="Back to Gallery" onclick="
						AlertSet.confirm('Are you sure? You will lose any unsaved data.', 
						function(){window.location='/<?=$module?>/list/';});" />
				</td>
                <td>

                </td>
			</tr>
		</tbody>
	</table>
</form>

<script>formSectionShow('details');</script>


<div id="add-characters-to-picture-dialog" title="Add Characters To Picture" class="hidden-dialog quick-form">
    <?php include('../characters/quick-form.php'); ?>
</div>

<div id="add-settings-to-picture-dialog" title="Add Settings To Picture" class="hidden-dialog quick-form">
    <?php include('../settings/quick-form.php'); ?>
</div>

<div id="add-plot-events-to-picture-dialog" title="Add Plot Events To Picture" class="hidden-dialog quick-form">
    <?php include('../plot/quick-form.php'); ?>
</div>

