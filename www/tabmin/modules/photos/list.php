<?php
ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
ob_start();
?>

<?php TemplateSet::begin('scripts'); ?>
    <link rel="stylesheet" href="/js/jquery-file-upload/css/jquery.fileupload.css" />

    <script type="text/javascript" src="/tabmin/modules/photos/js/module.js"></script>
<?php TemplateSet::end(); ?>

<?php TemplateSet::begin('body'); ?>

    <input type="text" id="s" placeholder="Find a Photo By Caption" value="<?php echo htmlentitiesUTF8(@$_GET['s']) ?>"
           onkeyup="listUpdateContent();" autocomplete="off" />

    <div id="throbber" style="float:right; display:none;">
        <img src="/images/throbber.gif" />
    </div>

    <div id="filter-search">
        <div id="filter-header" onclick="toggleFilterSearch();">
            Advanced Search
            <span id="filter-icon-close">&#x25B2;</span>
            <span id="filter-icon-open">&#x25BC;</span>
        </div>
        <div id="filter-search-body">


            <div id="filter-search-footer">
                <div class="filter-footer-section">
                    <div>
                        <!--<a href="javascript:" onclick="checkAll()">Check All</a> | -->
                        <a href="javascript:" onclick="checkNone()">Uncheck All</a>
                    </div>
                </div>
                <div class="filter-footer-section">
                    <div>
                        <input type="checkbox" id="untagged_only" name="untagged_only" onchange="viewUntaggedPhotosOnly($(this).prop('checked'))"/> View Untagged Photos Only
                    </div>
                </div>
            </div>

            <div class="clear"></div>

            <div class="filter-search-section">
                <div>
                    <strong>Characters</strong><br />
                    <form id="character-tag-form" >
                    <?php
                    $tags = Tags::loadCharacterTags($currentStory->get_series()->get_id());
                    if( count($tags) > 0 )
                    {
                        foreach($tags as $tag)
                        {
                            ?>
                            <input type="checkbox" name="character-tags[]" value="<?=$tag->get_object_id()?>" onchange="setFilter()"/>
                            <?=htmlentitiesUTF8($tag->get_object_name())?><br/>
                            <?php
                        }
                    }
                    else
                    {
                        echo '<em>No existing tags were found</em>';
                    }
                    ?>
                    </form>
                </div>
            </div>

            <div class="filter-search-section">
                <div>
                    <form id="setting-tag-form" >
                    <strong>Settings</strong><br />
                    <?php
                    $tags = Tags::loadSettingTags($currentStory->get_series()->get_id());
                    if( count($tags) > 0 )
                    {
                        foreach($tags as $tag)
                        {
                            ?>
                            <input type="checkbox" name="setting-tags[]" value="<?=$tag->get_object_id()?>" onchange="setFilter()"/>
                            <?=htmlentitiesUTF8($tag->get_object_name())?><br/>
                        <?php
                        }
                    }
                    else
                    {
                        echo '<em>No existing tags were found</em>';
                    }
                    ?>
                    </form>
                </div>
            </div>

            <div class="filter-search-section">
                <div>
                    <form id="plot-event-tag-form" >
                    <strong>Plot Events</strong><br />
                    <?php
                    $tags = Tags::loadPlotEventTags($currentStory->get_series()->get_id());
                    if( count($tags) > 0 )
                    {
                        foreach($tags as $tag)
                        {
                            ?>
                            <input type="checkbox" name="plot-event-tags[]" value="<?=$tag->get_object_id()?>" onchange="setFilter()"/>
                            <?=htmlentitiesUTF8($tag->get_object_name())?><br/>
                            <?php
                        }
                    }
                    else
                    {
                        echo '<em>No existing tags were found</em>';
                    }
                    ?>
                    </form>
                </div>
            </div>

        </div>
    </div>


    <div id="list-content" class="list-content">
        <?php //include('list-content.php'); ?>
    </div><!-- list-content -->

    <div id="add-photos-from-computer" title="Add Photos From Computer" class="hidden-dialog">
        <?php include('list-content-add-from-computer.php'); ?>
    </div>

    <div id="add-photos-from-internet" title="Add Photos From Internet" class="hidden-dialog">
        <?php include('list-content-add-from-internet.php'); ?>
    </div>

<?php TemplateSet::end() ?>

<?php TemplateSet::begin('scripts_footer') ?>
    <script type="text/javascript">
        listInit();
        listUpdateContent();
    </script>
    <script type="text/javascript" src="/js/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
    <script type="text/javascript" src="/js/jquery-file-upload/js/jquery.iframe-transport.js"></script>
    <script type="text/javascript" src="/js/jquery-file-upload/js/jquery.fileupload.js"></script>

    <script type="text/javascript" >
        fileUploadInit();
    </script>

<?php TemplateSet::end() ?>

<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php');