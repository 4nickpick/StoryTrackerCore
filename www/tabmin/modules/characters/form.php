<?php TemplateSet::begin('scripts') ?>

    <!-- tinyMCE -->
    <script src="/js/tinymce/tinymce.min.js"></script>
    <script src="/js/tinymce/tinymce-init.js"></script>
    <link rel="stylesheet" href="/css/tinymce-custom.css" />

	<script type="text/javascript" src="/tabmin/modules/characters/js/module.js"></script>
	<script type="text/javascript">
		AlertSet.handleRedirectWithAlert();
	</script>

<?php TemplateSet::end('scripts')?>

<?php
$add_existing_character_link = '';
if( $currentStory->isPartOfSeries() )
{
    $add_existing_character_link = ' <small><a href="/characters/add-existing">Add an Existing Character</a></small>';
}
?>
<h1><?=$verb=='add'? 'New Character' . $add_existing_character_link : 'Edit ' . htmlentitiesUTF8($character->get_full_name()) ?></h1>

<ul id="form_navigation">
	<li id="menu_details" class="current"><a href="javascript:" onclick="formSectionShow('details');">Details</a></li>
	<li id="menu_content"><a href="javascript:" onclick="formSectionShow('content');">Bio</a></li>
	<li id="menu_gallery"><a href="javascript:" onclick="formSectionShow('gallery');">Photo Gallery</a></li>
</ul>

<form id="character_form" action="/characters/ajax/" method="post" class="tabmin_form" autocomplete="off" enctype="multipart/form-data">
	<input type="hidden" name="verb" value="<?php echo $verb?>" />
	<input type="hidden" name="characters_id" value="<?php echo (@$character ? $character->get_id() : '')?>" />
	<input type="hidden" name="stories_id" value="<?php echo ($currentStory->get_id())?>" />
	<?php echo XSRF::html()?>
	<table class="form_table">
		<tbody id="form_section_details">
			<tr>
				<th align="left" colspan="2"><h2>Name</h2></th>
			</tr>
			<tr>
				<th align="left">Full Name:</th>
				<td><input type="text" name="full_name" value="<?php P::rint(@$character ? $character->get_full_name() : '')?>" /></td>
			</tr>
			<tr>
				<th align="left">Also Known As:</th>
				<td><input type="text" name="aliases" value="<?php P::rint(@$character ? $character->get_aliases() : '')?>" /></td>
			</tr>

            <tr>
                <th colspan="2">
                    &nbsp;
                </th>
            </tr>
            <tr>
                <th colspan="2">
                    Need to track more complex traits of your characters? <a href="/characters/model">Modify the Character Model</a>
                </th>
            </tr>
			<?php
				//Draw Module Form
				$charactersManager = new Characters();
				$characterModel=$charactersManager->loadModel($currentStory->get_series()->get_id(), (@$character ? $character->get_id() : NULL));
				if( count($characterModel->get_groups()) > 0 )
                {
                    foreach($characterModel->get_groups() as $group)
                    {
                        ?>
                        <tr class="model_row">
                            <th align="left" colspan="2"><h2><?=htmlentitiesUTF8($group->get_name());?></h2></th>
                        </tr>
                        <?
                        foreach($group->get_fields() as $field)
                        {
                            ?>
                            <tr>
                                <th align="left"><?=htmlentitiesUTF8($field->get_name())?></th>
                                <td><input type="text" name="fields[<?=htmlentitiesUTF8($field->get_id());?>]" value="<?=htmlentitiesUTF8($field->get_value())?>" /></td>
                            </tr>
                            <?
                        }
                        ?>
                            <tr>
                                <th align="left" colspan="2">&nbsp;</th>
                            </tr>
                        <?php
                    }
                }
			?>
		</tbody>
		<tbody id="form_section_content">
			<tr>
				<th align="left" colspan="2"><h2>Bio Content</h2></th>
			</tr>
			<tr>
				<th align="left" colspan="2"><textarea class="content" id="content" name="content"><?php P::rint(@$character ? $character->get_content() : '')?></textarea></th>
			</tr>
		</tbody>
        <tbody id="form_section_gallery">
            <tr>
                <th align="left" colspan="2"><h2>Photo Gallery</h2></th>
            </tr>
            <tr>
                <td align="left" colspan="2">
                    <?php
                        if( !isset($character) )
                        {
                            echo '<p>You must add this character in order to manage photos.</p>';
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
        formInit('<?=isset($character) ? $character->get_id() : ''?>');
        loadFileUploadForms(
            function(){
                formSave(document.getElementById('character_form'), 'edit');
                goTo('/characters/edit/<?=isset($character) ? $character->get_id() : ''?>/?gallery')
            }
        );
        fileUploadInit('characters', '<?=isset($character) ? $character->get_id() : ''?>');
    </script>

<?php TemplateSet::end() ?>