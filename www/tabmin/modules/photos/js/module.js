/*
    Form Button Handlers
    these are used as onclick calls to each button on the module form
    They should entirely replace the "onsubmit" functionality of forms
 */
function formSave(form, verb)
{
    throb('block');
    tinymce.triggerSave();
    var ajax_ok = handleAjaxForm(
        form,
        function(resp) {
            if( verb == 'edit' )
            {
                AlertSet.clear().addJSON(resp).showInCorner();
                throb('none');
            }
        },
        function(resp) {
            AlertSet.clear().addJSON(resp).showInCorner();
            throb('none');
        }
    );

    return ajax_ok;
}


/* formSectionShow() - show a different section of the form, "model, content, relationships..." */

function formSectionShow(section_name)
{
    $('tbody:not(#form_section_'+section_name+')').hide();
    $('#form_section_buttons').show();
    $('#form_section_'+section_name).show();

    $('#form_navigation li.current').removeClass('current');
    $('#menu_'+section_name).addClass('current');
}

/*
 *Init() functions run javascript to be run when a section is loaded or AJAX-refreshed
 the page is loaded/refreshed via *updateContent()
 */
function listInit()
{
    loadFileUploadForms(function(){
        listUpdateContent();
    });
}


function formInit()
{
    var addCharactersFormButtons = {
        "Add Characters": function() {
            var pictures_id = $('#pictures_id')[0].value;

            var bValid = true;
            if ( bValid ) {

                var new_characters_to_add = $( ".add-new-character" );
                var existing_characters_to_add = $( ".character-to-add:checked" );

                console.log(new_characters_to_add);
                console.log(existing_characters_to_add);

                if( new_characters_to_add.length > 0 )
                {
                    var new_characters_names = [];
                    $(new_characters_to_add).each(function(index, element)
                    {
                        new_characters_names[index] = element.value;
                    });

                    if( new_characters_names.length > 0 )
                    {
                        if( addNewCharactersToPicture(pictures_id, new_characters_names) )
                        {
                            //reset add characters form
                            console.log('addNodes yes');

                        }
                        else
                        {
                            //failed to add new nodes to relationship chart
                            console.log('failed to addNodes');
                        }
                    }
                }
                else
                {
                    //no new nodes to add to chart
                    console.log('No new characters added');
                }

                if( existing_characters_to_add.length > 0 )
                {
                    if( addCharactersToPicture(pictures_id, existing_characters_to_add) )
                    {
                        //reset add characters form
                        console.log('addNodes yes');

                    }
                    else
                    {
                        //failed to add new nodes to relationship chart
                        console.log('failed to addNodes');
                    }
                }
                else
                {
                    //no new nodes to add to chart
                    console.log('No existing characters selected');
                }

                $( this ).dialog( "close" );
            }
        }
    };

    var onclose = function() {
        $( ".character-to-add").removeAttr("checked");
        $( "label.selected").className = '';
        $( "a.remove").className = 'add';
    };

    loadQuickForm("#add-characters-to-picture-dialog" , addCharactersFormButtons, onclose);

    var addSettingsFormButtons = {
        "Add Settings": function() {
            var pictures_id = $('#pictures_id')[0].value;

            var bValid = true;
            if ( bValid ) {

                var new_settings_to_add = $( ".add-new-setting" );
                var existing_settings_to_add = $( ".setting-to-add:checked" );

                console.log(new_settings_to_add);
                console.log(existing_settings_to_add);

                if( new_settings_to_add.length > 0 )
                {
                    var new_settings_names = [];
                    $(new_settings_to_add).each(function(index, element)
                    {
                        new_settings_names[index] = element.value;
                    });

                    if( new_settings_names.length > 0 )
                    {
                        if( addNewSettingsToPicture(pictures_id, new_settings_names) )
                        {
                            //reset add characters form
                            console.log('addNodes yes');

                        }
                        else
                        {
                            //failed to add new nodes to relationship chart
                            console.log('failed to addNodes');
                        }
                    }
                }
                else
                {
                    //no new nodes to add to chart
                    console.log('No new characters added');
                }

                if( existing_settings_to_add.length > 0 )
                {
                    if( addSettingsToPicture(pictures_id, existing_settings_to_add) )
                    {
                        //reset add characters form
                        console.log('addNodes yes');

                    }
                    else
                    {
                        //failed to add new nodes to relationship chart
                        console.log('failed to addNodes');
                    }
                }
                else
                {
                    //no new nodes to add to chart
                    console.log('No existing characters selected');
                }

                $( this ).dialog( "close" );
            }
        }
    }

    loadQuickForm("#add-settings-to-picture-dialog" , addSettingsFormButtons, onclose)

    var addPlotEventsFormButtons = {
        "Add Plot Events": function() {
            var pictures_id = $('#pictures_id')[0].value;

            var bValid = true;
            if ( bValid ) {

                var new_plot_events_to_add = $( ".add-new-plot-event" );
                var existing_plot_events_to_add = $( ".plot-event-to-add:checked" );

                console.log(new_plot_events_to_add);
                console.log(existing_plot_events_to_add);

                if( new_plot_events_to_add.length > 0 )
                {
                    var new_plot_events_names = [];
                    $(new_plot_events_to_add).each(function(index, element)
                    {
                        new_plot_events_names[index] = element.value;
                    });

                    if( new_plot_events_names.length > 0 )
                    {
                        if( addNewPlotEventsToPicture(pictures_id, new_plot_events_names) )
                        {
                            //reset add characters form
                            console.log('addNodes yes');

                        }
                        else
                        {
                            //failed to add new nodes to relationship chart
                            console.log('failed to addNodes');
                        }
                    }
                }
                else
                {
                    //no new nodes to add to chart
                    console.log('No new events added');
                }

                if( existing_plot_events_to_add.length > 0 )
                {
                    if( addPlotEventsToPicture(pictures_id, existing_plot_events_to_add) )
                    {
                        //reset add characters form
                        console.log('addNodes yes');

                    }
                    else
                    {
                        //failed to add new nodes to relationship chart
                        console.log('failed to addNodes');
                    }
                }
                else
                {
                    //no new nodes to add to chart
                    console.log('No existing events selected');
                }

                $( this ).dialog( "close" );
            }
        }
    };

    var onclose = function() {
        $( ".setting-to-add").removeAttr("checked");
        $( "label.selected").className = '';
        $( "a.remove").className = 'add';
    };

    loadQuickForm("#add-plot-events-to-picture-dialog" , addPlotEventsFormButtons, onclose);
}

/*
    *UpdateContent() AJAX-refreshes the page's content
 */
function listUpdateContent()
{
    sort_value = '';
    if( document.getElementById('sort') )
        sort_value = document.getElementById('sort').value;

    var character_tags = $('#character-tag-form').serialize();
    var setting_tags = $('#setting-tag-form').serialize();
    var plot_event_tags = $('#plot-event-tag-form').serialize();

    var untagged_only = $('#untagged_only').prop('checked');
    if( !!untagged_only )
        untagged_only = '&untagged_only=true';
    else
        untagged_only = '';

    loadPieceByURL(
        '/photos/list-content/', 's='+document.getElementById('s').value+
            '&sort='+sort_value+
            '&'+character_tags+
            '&'+setting_tags+
            '&'+plot_event_tags+
            untagged_only,
        'get',
        document.getElementById('list-content'),
        function(){
            listInit();
            loadPictures();
        },
        document.getElementById('throbber') );
}

/*
    Manage Tags
*/
function addNewCharactersToPicture(pictures_id, character_names)
{
    var data = {};
    data.verb = 'add_new_characters_to_picture';
    data.pictures_id = pictures_id;
    data.character_names = character_names;

    $.ajax({
        url: '/photos/ajax',
        type: "POST",
        async: true,
        data: $.param(data),
        success: function(data){

            var resp_data = JSON.parse(data);
            AlertSet.addJSON(resp_data);

            formSave(
                document.getElementById('photo_form'),
                document.getElementById('photo_form_verb'));
            window.location='';


        }
    });

    return true;
}

function addNewSettingsToPicture(pictures_id, setting_names)
{
    var data = {};
    data.verb = 'add_new_settings_to_picture';
    data.pictures_id = pictures_id;
    data.setting_names = setting_names;

    $.ajax({
        url: '/photos/ajax',
        type: "POST",
        async: true,
        data: $.param(data),
        success: function(data){

            var resp_data = JSON.parse(data);
            AlertSet.addJSON(resp_data);

            formSave(
                document.getElementById('photo_form'),
                document.getElementById('photo_form_verb'));
            window.location='';


        }
    });

    return true;
}

function addNewPlotEventsToPicture(pictures_id, plot_event_names)
{
    var data = {};
    data.verb = 'add_new_plot_events_to_picture';
    data.pictures_id = pictures_id;
    data.plot_event_names = plot_event_names;

    $.ajax({
        url: '/photos/ajax',
        type: "POST",
        async: true,
        data: $.param(data),
        success: function(data){

            var resp_data = JSON.parse(data);
            AlertSet.addJSON(resp_data);

            formSave(
                document.getElementById('photo_form'),
                document.getElementById('photo_form_verb'));
            window.location='';


        }
    });

    return true;
}

function addCharactersToPicture(pictures_id, characters_to_add)
{
    var data = {};
    data.verb = 'add_characters_to_picture';
    data.pictures_id = pictures_id;
    data.nodes = [];

    $.each(characters_to_add, function(index, character_to_add){
        var node = {};

        node.characters_id = $(character_to_add).attr("id").split('_')[1];
        node.pictures_id = pictures_id;

        data.nodes.push(node);
    });

    $.ajax({
        url: '/photos/ajax',
        type: "POST",
        async: true,
        data: $.param(data),
        success: function(data){

            var resp_data = JSON.parse(data);
            AlertSet.addJSON(resp_data);

            formSave(
                document.getElementById('photo_form'),
                document.getElementById('photo_form_verb'));
            window.location='';


        }
    });

    return true;
}

function addSettingsToPicture(pictures_id, settings_to_add)
{
    var data = {};
    data.verb = 'add_settings_to_picture';
    data.pictures_id = pictures_id;
    data.nodes = [];

    $.each(settings_to_add, function(index, setting_to_add){
        var node = {};

        node.settings_id = $(setting_to_add).attr("id").split('_')[1];
        node.pictures_id = pictures_id;

        data.nodes.push(node);
    });

    $.ajax({
        url: '/photos/ajax',
        type: "POST",
        async: true,
        data: $.param(data),
        success: function(data){

            var resp_data = JSON.parse(data);
            AlertSet.addJSON(resp_data);

            formSave(
                document.getElementById('photo_form'),
                document.getElementById('photo_form_verb'));
            window.location='';


        }
    });

    return true;
}

function addPlotEventsToPicture(pictures_id, plot_events_to_add)
{
    var data = {};
    data.verb = 'add_plot_events_to_picture';
    data.pictures_id = pictures_id;
    data.nodes = [];

    $.each(plot_events_to_add, function(index, plot_events_to_add){
        var node = {};

        node.plot_events_id = $(plot_events_to_add).attr("id").split('_')[1];
        node.pictures_id = pictures_id;

        data.nodes.push(node);
    });

    $.ajax({
        url: '/photos/ajax',
        type: "POST",
        async: true,
        data: $.param(data),
        success: function(data){

            var resp_data = JSON.parse(data);
            AlertSet.addJSON(resp_data);

            formSave(
                document.getElementById('photo_form'),
                document.getElementById('photo_form_verb'));
            window.location='';


        }
    });

    return true;
}



function removeTag(pictures_id, object_type, objects_id)
{
    var data = {};
    data.verb = 'remove-tag';
    data.pictures_id = pictures_id;
    data.object_type = object_type;

    if( object_type == 'characters' )
    {
        data.characters_id = objects_id;
    }
    else if( object_type == 'settings' )
    {
        data.settings_id = objects_id;
    }
    else if( object_type == 'plot_events' )
    {
        data.plot_events_id = objects_id;
    }

    $.ajax({
        url: '/photos/ajax',
        type: "POST",
        async: true,
        data: $.param(data),
        success: function(data){

            var resp_data = JSON.parse(data);
            AlertSet.addJSON(resp_data);

            if( resp_data.success )
            {
                if( object_type == 'characters' )
                {
                    document.getElementById('character-tag-'+pictures_id+'-'+objects_id).style.display='none';
                }
                else if( object_type == 'settings' )
                {
                    document.getElementById('setting-tag-'+pictures_id+'-'+objects_id).style.display='none';
                }
                else if( object_type == 'plot_events' )
                {
                    document.getElementById('plot-event-tag-'+pictures_id+'-'+objects_id).style.display='none';
                }
            }
            else
                console.log('could not delete node');

        }
    });

    return true;
}


// Advanced Photo Search

function toggleFilterSearch() {
    $('#filter-search-body').toggle();
    $('#filter-icon-close').toggle();
    $('#filter-icon-open').toggle();
}

/*
function checkAll()
{
    $('.filter-search-section input[type=checkbox]').prop('checked', true);
    listUpdateContent();
}
*/

function checkNone()
{
    $('.filter-search-section input[type=checkbox]').prop('checked', false);
    renderNewFilter();
}

function viewUntaggedPhotosOnly(checked)
{
    if( !!checked )
    {
        $('.filter-search-section input[type=checkbox]').prop('checked', false);
    }
    renderNewFilter();
}

function setFilter()
{
    $('#untagged_only').prop('checked', false);
    renderNewFilter();
}

function renderNewFilter()
{
    //collect checked character boxes
    var character_search = $('#character-tag-form input:checkbox:checked').map(
        function() {
            return this.value;
        }
    ).get();

    var setting_search = $('#setting-tag-form input:checkbox:checked').map(
        function() {
            return this.value;
        }
    ).get();

    var plot_event_search = $('#plot-event-tag-form input:checkbox:checked').map(
        function() {
            return this.value;
        }
    ).get();

    //collect photo wrappers whose data attribute contains values in character_search
    var photo_wrappers = $('.photo-wrapper');
    var results = photo_wrappers.map(
        function() {
            var character_tags = $(this).data('character_tags');
            var setting_tags = $(this).data('setting_tags');
            var plot_event_tags = $(this).data('plot_event_tags');

            var untagged_photos_only = $('#untagged_only').is(':checked');

            console.log(untagged_photos_only);
            console.log(character_tags);
            console.log(setting_tags);
            console.log(plot_event_tags);
            if( untagged_photos_only )
            {
                if( character_tags.length == 0
                    && setting_tags.length == 0
                    && plot_event_tags.length == 0 )
                {
                    return this;
                }
                else
                {
                    return null;
                }
            }

            if( ( character_search.length == 0 || inArray(character_tags, character_search) )
                && ( setting_search.length == 0 || inArray(setting_tags, setting_search) )
                && ( plot_event_search.length == 0 || inArray(plot_event_tags, plot_event_search) ) )
            {
                return this;
            }
            else
            {
                return null;
            }
        }
    ).get();
    $(photo_wrappers).hide();
    $(results).show();

    console.log(results);

    $('#picture_count').text(''+results.length);

}