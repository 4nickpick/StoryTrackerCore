<?php TemplateSet::begin('scripts') ?>

    <!-- tinyMCE -->
    <script src="/js/tinymce/tinymce.min.js"></script>
    <script src="/js/tinymce/tinymce-init.js"></script>
    <link rel="stylesheet" href="/css/tinymce-custom.css" />

    <script type="text/javascript" src="/tabmin/modules/stories/js/module.js"></script>
	<script type="text/javascript">
		AlertSet.handleRedirectWithAlert();
	</script>

<?php TemplateSet::end('scripts')?>

<h1><?=$verb=='add'? 'New Story' : 'Edit ' . htmlentitiesUTF8($story->get_name()) ?></h1>

<ul id="form_navigation">
    <li id="menu_details" class="current"><a href="javascript:" onclick="formSectionShow('details');">Details</a></li>
    <li id="menu_description"><a href="javascript:" onclick="formSectionShow('description');">Description</a></li>
    <li id="menu_synopsis"><a href="javascript:" onclick="formSectionShow('synopsis');">Plot Synopsis</a></li>
</ul>

<form id="story_form" action="/stories/ajax/" method="post" class="tabmin_form" autocomplete="off" enctype="multipart/form-data">
	<input type="hidden" name="verb" value="<?php echo $verb?>" />
	<input type="hidden" name="stories_id" value="<?php echo (@$story ? $story->get_id() : '')?>" />
	<input type="hidden" name="users_id" value="<?php echo ($currentUser->get_id())?>" />
	<?php echo XSRF::html()?>
	<table class="form_table">
		<tbody id="form_section_details">
			<tr>
				<th align="left" colspan="2"><h2>Basic Information</h2></th>
			</tr>
            <tr>
                <th align="left" width="150">Name:</th>
                <td><input type="text" name="name" value="<?php P::out(@$story ? $story->get_name() : '')?>" /></td>
            </tr>

            <tr>
                <th align="left" colspan="2">&nbsp;</th>
            </tr>

            <tr>
                <th align="left">Series:</th>
                <td>
                    <?php
                    if( $verb == 'add' )
                    {
                        ?>
                        <select name="series_id" onchange="if(this.value=='new_series'){$('#new_series_name_row').show();} else {$('#new_series_name_row').hide().find('input').val('');;} ">
                            <optgroup label="">
                                <option value="no_series">Not Part of a Series</option>
                                <option value="new_series">New Series</option>
                            </optgroup>
                            <?php
                            $loader = new SeriesLoader();
                            $series_all = $loader->loadByCurrentUser($currentUser->get_id());
                            if( count($series_all) > 0 )
                            {
                                ?>
                                <optgroup label="Existing Series">
                                    <?php
                                    foreach($series_all as $series)
                                    {
                                        $selected = '';
                                        if( isset($story) && (@$series->get_id() == @$story->get_series()->get_id()) )
                                        {
                                            $selected = 'selected="selected"';
                                        }
                                        ?>
                                        <option value="<?=$series->get_id()?>" <?= $selected ?>><?=htmlentitiesUTF8($series->get_name())?></option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                            <?php
                            }
                            ?>
                        </select>
                        <?php
                    }
                    else
                    {
                        if( $story->isPartOfSeries() )
                        {
                            echo htmlentitiesUTF8($story->get_series()->get_name());
                        }
                        else
                        {
                            echo 'Not Part of a Series';
                        }
                        ?>
                        <input type="hidden" name="series_id" value="<?=$story->get_series()->get_id()?>" />
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <tr id="new_series_name_row" style="display:none;">
                <th align="left">New Series Name:</th>
                <td>
                    <input type="text" name="series_name" value="" />
                </td>
            </tr>

        <!--<tr>
            <th align="left">Main Image</th>
            <td><input type="file" name="picture" /><?php P::out(@$story ? 'Leave blank to keep the same picture' : '')?></td>
        </tr>-->
        </tbody>
        <tbody id="form_section_description">
            <tr>
                <th align="left" colspan="2"><h2>Description</h2></th>
            </tr>
            <tr>
                <th align="left" colspan="2"><textarea class="content" id="description" name="description"><?php P::rint(@$story ? $story->get_description() : '')?></textarea></th>
            </tr>
        </tbody>

        <tbody id="form_section_synopsis">
            <tr>
                <th align="left" colspan="2">
                    <h2>Plot Synopsis</h2>
                    <p>The Plot Synopsis is an important piece of content - make sure it's up to par!</p>
                </th>
            </tr>
            <tr>
                <th align="left" colspan="2"><textarea class="content" id="synopsis" name="synopsis"><?php P::rint(@$story ? $story->get_synopsis() : '')?></textarea></th>
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
    formSectionShow('<?=isset($_GET['synopsis']) ? 'synopsis' : 'details'?>');
</script>
