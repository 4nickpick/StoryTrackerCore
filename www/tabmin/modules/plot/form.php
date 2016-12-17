<?php TemplateSet::begin('scripts') ?>

<!-- tinyMCE -->
    <script src="/js/tinymce/tinymce.min.js"></script>
    <script src="/js/tinymce/tinymce-init.js"></script>
    <link rel="stylesheet" href="/css/tinymce-custom.css" />

	<script type="text/javascript" src="/tabmin/modules/plot/js/module.js"></script>
	<script type="text/javascript">
		AlertSet.handleRedirectWithAlert();
	</script>

<?php TemplateSet::end('scripts')?>

<?php
$add_existing_setting_link = '';
/*if( $currentStory->isPartOfSeries() )
{
    $add_existing_setting_link = ' <small><a href="/plot/add-existing">Add an Existing Setting</a></small>';
}*/
?>
<h1><?=$verb=='add'? 'New Plot Event' . $add_existing_setting_link : 'Edit ' . htmlentitiesUTF8($plot_event->get_event()) ?></h1>

<ul id="form_navigation">
	<li id="menu_details" class="current"><a href="javascript:" onclick="formSectionShow('details');">Details</a></li>
	<li id="menu_summary"><a href="javascript:" onclick="formSectionShow('summary');">Summary</a></li>
    <li id="menu_gallery"><a href="javascript:" onclick="formSectionShow('gallery');">Photo Gallery</a></li>
</ul>

<form id="plot_events_form" action="/plot/ajax/" method="post" class="tabmin_form" autocomplete="off" enctype="multipart/form-data">
	<input type="hidden" id="plot_events_form_verb" name="verb" value="<?php echo $verb?>" />
	<input type="hidden" id="plot_events_id" name="plot_events_id" value="<?php echo (@$plot_event ? $plot_event->get_id() : '')?>" />
	<input type="hidden" name="stories_id" value="<?php echo ($currentStory->get_id())?>" />
	<?php echo XSRF::html()?>
	<table class="form_table">
		<tbody id="form_section_details">
			<tr>
				<th align="left" colspan="2"><h2>Basic Information</h2></th>
			</tr>
			<tr>
				<th align="left">Event:</th>
				<td><input type="text" name="event" value="<?php P::rint(@$plot_event ? $plot_event->get_event() : '')?>" /></td>
			</tr>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            <?php
                if( $verb != 'add' )
                {
                    ?>
                    <tr>
                        <th align="left">Characters:</th>
                        <td>
                            <?php
                                $characters = $plot_event->get_characters();
                                if( count($characters) > 0 )
                                {
                                    foreach($characters as $i=>$character)
                                    {
                                        ?>
                                            <span id="character-tag-<?=$plot_event->get_id()?>-<?=$character->get_id()?>" class="tag">
                                                <?=htmlentitiesUTF8($character->get_full_name())?>
                                                <a id="character-tag-<?=$plot_event->get_id()?>-<?=$character->get_id()?>" class="remove-tag" href="javascript:" onclick="AlertSet.confirm('Are you sure you want to remove <?=addslashes($character->get_full_name())?> from this event?', function(resp){removeTag('<?=$plot_event->get_id()?>', 'characters', '<?=$character->get_id()?>')}, function(resp){AlertSet.addJSON(resp).show();});">
                                                    X
                                                </a>
                                            </span>
                                        <?php
                                    }
                                }
                            ?>
                            <a href="javascript:" onclick="showQuickForm('#add-characters-to-event-dialog');">Add Characters</a>
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
                                $settings = $plot_event->get_settings();
                                if( count($settings) > 0 )
                                {
                                    foreach($settings as $i=>$setting)
                                    {
                                        ?>
                                            <span id="setting-tag-<?=$plot_event->get_id()?>-<?=$setting->get_id()?>" class="tag">
                                                <?=htmlentitiesUTF8($setting->get_full_name())?>
                                                <a id="setting-tag-<?=$plot_event->get_id()?>-<?=$setting->get_id()?>" class="remove-tag" href="javascript:" onclick="AlertSet.confirm('Are you sure you want to remove <?=addslashes($setting->get_full_name())?> from this event?', function(resp){removeTag('<?=$plot_event->get_id()?>', 'settings', '<?=$setting->get_id()?>')}, function(resp){AlertSet.addJSON(resp).show();});">
                                                    X
                                                </a>
                                            </span>
                                        <?php
                                    }
                                }
                            ?>
                            <a href="javascript:" onclick="showQuickForm('#add-settings-to-event-dialog');">Add Settings</a>
                        </td>
                    </tr>
                    <?php
                }
                else
                {
                    ?>
                    <tr>
                        <th align="left">Characters and Settings:</th>
                        <td>
                            <small class="gray">You must save this event's title to attach Characters and Settings.</small>
                        </td>
                    <?php
                }
            ?>
		</tbody>
		<tbody id="form_section_summary">
			<tr>
				<th align="left" colspan="2"><h2>Event Summary</h2></th>
			</tr>
			<tr>
				<th align="left" colspan="2"><textarea class="content" id="summary" name="summary"><?php P::rint(@$plot_event ? $plot_event->get_summary() : '')?></textarea></th>
			</tr>
		</tbody>
		<tbody id="form_section_outline">
			<tr>
				<th align="left" colspan="2"><h2>Event Outline</h2></th>
			</tr>
			<tr>
				<th align="left" colspan="2"><textarea class="content" id="outline" name="outline"><?php P::rint(@$plot_event ? $plot_event->get_outline() : '')?></textarea></th>
			</tr>
		</tbody>
        <tbody id="form_section_gallery">
            <tr>
                <th align="left" colspan="2"><h2>Photo Gallery</h2></th>
            </tr>
            <tr>
                <td align="left">
                    <?php
                    if( !isset($plot_event) )
                    {
                        echo '<p>You must add this plot event in order to manage photos.</p>';
                    }
                    else
                    {
                        include('photo-gallery.php');
                    }
                    ?>
                </td>
            </tr>
        </tbody>
		<tbody id="form_section_buttons">
			<tr>
                <td id="throbber_wrapper">
                    <div id="throbber" style="display:none;"><img src="/images/throbber.gif"/></div>
                </td>
				<td><input class="save button" type="button" value="Save" onclick="formSave(this.form, '<?=$verb?>');"/></td>
				<td><input class="view button" type="button" value="View" <?= $verb == 'add' ? 'disabled="disabled"' : '' ?> onclick="formView(this.form);" /></td>
				<td>
					<input class="cancel button" type="button" value="Cancel" onclick="
						AlertSet.confirm('Are you sure? You will lose any unsaved data.', 
						function(){window.location='/<?=$module?>/list/';});" />
				</td>
			</tr>
		</tbody>
	</table>
</form>

<div id="add-characters-to-event-dialog" title="Add Characters To Event" class="hidden-dialog quick-form">
    <?php include('../characters/quick-form.php'); ?>
</div>

<div id="add-settings-to-event-dialog" title="Add Settings To Event" class="hidden-dialog quick-form">
    <?php include('../settings/quick-form.php'); ?>
</div>

<script>
    formSectionShow('<?=isset($_GET['gallery']) ? 'gallery' : 'details'?>');
</script>

<div id="add-photos-from-computer" title="Add Photos From Computer" class="hidden-dialog">
    <?php include('../photos/list-content-add-from-computer.php'); ?>
</div>

<div id="add-photos-from-internet" title="Add Photos From Internet" class="hidden-dialog">
    <?php include('../photos/list-content-add-from-internet.php'); ?>
</div>

<?php TemplateSet::begin('scripts_footer') ?>
<script type="text/javascript" src="/js/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script type="text/javascript" src="/js/jquery-file-upload/js/jquery.iframe-transport.js"></script>
<script type="text/javascript" src="/js/jquery-file-upload/js/jquery.fileupload.js"></script>

<script type="text/javascript" >
    formInit('<?=isset($plot_event) ? $plot_event->get_id() : ''?>');
    loadFileUploadForms(
        function(){
            formSave(document.getElementById('plot_events_form'), 'edit');
            goTo('/plot/edit/<?=isset($plot_event) ? $plot_event->get_id() : ''?>/?gallery')
        }
    );
    fileUploadInit('plot_events', '<?=isset($plot_event) ? $plot_event->get_id() : ''?>');
</script>


<?php TemplateSet::end() ?>