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
            if( verb == 'add' )
            {
                //if added a character, go to that character's edit screen, and show add success message
                var url = '/characters/edit/'+resp.id+'';
                AlertSet.redirectWithAlert(url, resp);
                //don't hide throbber, redirecting...

            }
            else
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

function formView(form)
{
    throb('block');

    tinymce.triggerSave();
    var ajax_ok = handleAjaxForm(
        form,
        function(resp) {window.location='/characters/view/'+resp.id},
        function(resp) {AlertSet.clear().addJSON(resp).showInCorner(); throb('none');}
    );

    return ajax_ok;
}

function groupAdd(form)
{
    handleAjaxForm(
        form,
        function(resp){modelUpdateContent(); AlertSet.clear().addJSON(resp).showInCorner();},
        function(resp){AlertSet.clear().addJSON(resp).show();}
    );

    return false;
}

function groupEdit(form)
{
    handleAjaxForm(
        form,
        function(resp){modelUpdateContent();AlertSet.clear().addJSON(resp).showInCorner();},
        function(resp){AlertSet.clear().addJSON(resp).show();}
    );

    return false;
}

function eventAdd(form)
{
    handleAjaxForm(
        form,
        function(resp){timelineListUpdateContent(resp.characters_id); AlertSet.clear().addJSON(resp).showInCorner();},
        function(resp){AlertSet.clear().addJSON(resp).show();}
    );

    return false;
}

function eventDelete(form)
{
    console.log(form);
    AlertSet.confirm('Are you sure you want to delete this event?',
        function(){
            handleAjaxForm(form,
                function(resp){
                    timelineListUpdateContent(resp.characters_id)
                },
                function(resp){
                    console.log('delete failed.');
                    AlertSet.clear().addJSON(resp).show();
                }
            );
        }.bind(this));

    return false;
}

function fieldAdd(form)
{
    handleAjaxForm(
        form,
        function(resp){modelUpdateContent(); AlertSet.clear().addJSON(resp).showInCorner();},
        function(resp){AlertSet.clear().addJSON(resp).show();}
    );

    return false;
}

function fieldEdit(form)
{
    handleAjaxForm(
        form,
        function(resp){modelUpdateContent();AlertSet.clear().addJSON(resp).showInCorner();},
        function(resp){AlertSet.clear().addJSON(resp).show();}
    );

    return false;
}

function chartListAdd(form)
{
    /* passes resp.id, if it exists, to chartListUpdateContent, which triggers the edit form to appear */
    handleAjaxForm(
        form,
        function(resp){chartListUpdateContent(resp.id); AlertSet.clear().addJSON(resp).showInCorner();},
        function(resp){AlertSet.clear().addJSON(resp).show();}
    );

    return false;
}

function chartEdit(form)
{
    handleAjaxForm(
        form,
        function(resp){chartListUpdateContent();AlertSet.clear().addJSON(resp).showInCorner();},
        function(resp){AlertSet.clear().addJSON(resp).show();}
    );

    return false;
}

function eventEdit(form)
{
    handleAjaxForm(
        form,
        function(resp){timelineListUpdateContent(resp.characters_id);AlertSet.clear().addJSON(resp).showInCorner();},
        function(resp){AlertSet.clear().addJSON(resp).show();}
    );

    return false;
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
    enableDragAndDrop("#sortable tbody", "td div.sort.action_button", '', listUpdatePriority, '', 'y', '');
    enableListClick();
}

function formInit(characters_id)
{
    enableDragAndDrop("#photos", "a", '', photoGalleryUpdatePriority, '', '', '', characters_id);
}

function timelineListInit(characters_id)
{
    enableDragAndDrop("#sortable tbody", "td div.sort.action_button", '', timelineListUpdatePriority, '', 'y', '', characters_id);
    enableListClick();
}

function modelInit()
{
    enableDragAndDrop("#sortable_groups > tbody", "thead .drag_icon", '', groupUpdatePriority, '> tr', 'y', '');
    enableDragAndDrop(".sortable_field > tbody", "", '', fieldUpdatePriority, '', 'y', '.sortable_field tbody');
}

function chartListInit()
{
    enableDragAndDrop("#sortable tbody", "td div.sort.action_button", '', chartListUpdatePriority, '', 'y', '');
    enableListClick();
}

function addExistingListInit()
{
    /*enableDragAndDrop("#sortable tbody", "td div.sort.action_button", '', addExistingListUpdatePriority, '', 'y', '');*/
    enableListClick();
}

function chartInit(chart_id)
{
    jsPlumbInit();

    // a new connection has been established
    jsPlumb.bind("beforeDrop", function(info) {

        if( info.connection.sourceId == info.targetId )
            return false;

        var prompt_text = FriendOrFoe(info, chart_id);

        return true;
    });

    // click a connection
    jsPlumb.bind("click", function(c) {
        var data = {};
        data.verb = 'get_connection_dialog';
        data.charts_id = chart_id;
        data.source_id = c.sourceId;
        data.target_id = c.targetId;

        $.ajax({
            url: '/characters/ajax',
            type: "POST",
            async: false,
            data: $.param(data),
            success: function(data){

                data = JSON.parse(data);

                var connections_id_input = document.createElement('input');
                connections_id_input.id = 'connections_id_input';
                connections_id_input.type = 'hidden';
                connections_id_input.name = 'connections_id';
                connections_id_input.value = data.connections_id;

                var relationship_names = document.createElement('div');
                $(relationship_names).append(document.createTextNode(data.names));
                $(relationship_names).addClass('edit-relationship-header');

                var relationship_type_wrapper = document.createElement('div');
                $(relationship_type_wrapper).addClass('select-relationship-type');

                var relationship_type_select = document.createElement('select');
                (relationship_type_select).id = 'connections_type';
                (relationship_type_select).name = 'connections_type';

                var options = [];
                options[0] = "Allied";
                options[1] = "Conflicted";
                options[2] = "Romantic";

                $.each(options, function(index,value) {

                    index = index + 1;

                    var option = document.createElement('option');
                    $(option).attr({"value": (index)}).text(value);

                    $(relationship_type_select).append(option);

                    if( data.connections_type == index )
                        (relationship_type_select).value = index;

                });
                $(relationship_type_wrapper).append(relationship_type_select);

                var delete_link = document.createElement('a');
                delete_link.href='javascript:;';
                $(delete_link).text('Delete Relationship');
                $(delete_link).addClass('delete-link');
                delete_link.onclick=
                    function() {
                        AlertSet.confirm(
                            'Are you sure you\'d like to delete this relationship?',
                            function() {
                                $( "#edit-connection" ).dialog( "destroy" );
                                deleteConnection(chart_id, data.connections_id);
                            },
                            function() {
                                // do nothing
                            }
                        );
                    };


                var content_ta = document.createElement('textarea');
                content_ta.id = 'connections_content';
                content_ta.name = 'relationship_content';
                content_ta.value = data.content;

                var edit_connection_contents = $("#edit-connection");

                edit_connection_contents
                    .empty()
                    .addClass('transparent-dialog')
                    .append(connections_id_input)
                    .append(relationship_names)
                    .append(relationship_type_wrapper)
                    .append(delete_link)
                    .append(content_ta);
            }
        });

        $( "#edit-connection" ).dialog({
            autoOpen: true,
            height: 500,
            width: 350,
            modal: true,
            dialogClass: 'transparent-dialog',
            hide: {
                effect: 'fade',
                duration: 500
            },
            open: function (event, ui){
                var edit_connection_dialog = $(this).parent();
                edit_connection_dialog
                    .mouseover(function(){
                        $(this).css({ opacity: 1 });
                    });

                edit_connection_dialog
                    .mouseout( function(){
                        $(this).css({ opacity:.45 });
                    });
            },
            buttons: {
                "Save Changes": function() {
                    var bValid = true;

                    if ( bValid ) {

                        console.log(document.getElementById('connections_type'));
                        console.log(document.getElementById('connections_type').value);
                        var connections_id = document.getElementById('connections_id_input').value;
                        var connections_type = document.getElementById('connections_type').value;
                        var content = document.getElementById('connections_content').value;
                        editConnection(connections_id, connections_type, content);
                        $( this ).dialog( "destroy" );
                        viewChart(chart_id);
                    }
                },
                Cancel: function() {
                    $( this ).dialog( "destroy" );
                }
            },
            close: function() {
                $( ".character-to-add").removeAttr("checked");
                $( "label.selected").className = '';
                $( "a.remove").className = 'add';
            }
        });

        $('.ui-widget-overlay').remove();

    });

    var data = {};
    data.verb = 'get_chart_data';
    data.charts_id = chart_id;

    $.ajax({
        url: '/characters/ajax',
        type: "POST",
        async: true,
        dataType: "json",
        data: $.param(data),
        success:
            function(resp){

                $(resp.nodes).each(function(index, node){

                    var new_window = document.createElement('div');
                    $(new_window).attr('class', 'w');
                    $(new_window).attr('id', parseInt(node.id));
                    $(new_window).data('characters-id', parseInt(node.characters_id));
                    $(new_window).css('left', parseInt(node.left));
                    $(new_window).css('top', parseInt(node.top));

                    var ep = document.createElement('div');
                    $(ep).attr('class', 'ep');

                    $(new_window).append(node.characters_name);
                    $(new_window).append(ep);

                    jsPlumbDemo.initWindow(new_window);
                    console.log(new_window)

                    $('#main').append(new_window);
                });

                $(resp.connections).each(function(index, connection){
                    var id = connection.id;
                    var sourceNode = connection.sourceNode + '';
                    var targetNode = connection.targetNode + '';
                    var paintStyle = jQuery.extend(true, {}, jsPlumb.Defaults.PaintStyle);
                    var hoverPaintStyle = jQuery.extend(true, {}, jsPlumb.Defaults.HoverPaintStyle);
                    var cssClass = "aLabel";

                    switch(parseInt(connection.type))
                    {
                        case 1:
                            paintStyle.strokeStyle = "green";
                            cssClass = cssClass + " happyLabel";
                            break;
                        case 2:
                            paintStyle.strokeStyle = "red";
                            cssClass = cssClass + " sadLabel";
                            break;
                        case 3:
                            paintStyle.strokeStyle = "purple";
                            cssClass = cssClass + " romanticLabel";
                            break;
                    }
                    console.log(cssClass);

                    if( !!sourceNode && !!targetNode )
                    {
                        var conn = jsPlumb.connect({
                            source:sourceNode,
                            target:targetNode,
                            paintStyle: paintStyle,
                            hoverPaintStyle: hoverPaintStyle,
                            overlays:[
                                [ "Label", {
                                    label:"",
                                    id:"label",
                                    cssClass:cssClass
                                }]
                            ]
                        });
                    }

                    paintStyle = null;
                    hoverPaintStyle = null;
                });
            },

        error:  function(resp){console.log('connections failed to load.')}
    });

    var addCharactersFormButtons = {
        "Add Characters": function() {
            var chart_id = $('#charts_id')[0].value;

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
                        if( addNewCharacterNodesViaChart(chart_id, new_characters_names) )
                        {
                            //reset add characters form
                            console.log('addNodes yes');
                            chartUpdateContentAddNodesForm(chart_id);
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
                    if( addNodes(chart_id, existing_characters_to_add) )
                    {
                        //reset add characters form
                        console.log('addNodes yes');
                        chartUpdateContentAddNodesForm(chart_id);
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

    loadQuickForm("#add-character-to-chart-dialog" , addCharactersFormButtons, onclose);
}

/*
 *StartUpdateCountdown() sets a timer to trigger *updateContent()
 If *StartUpdateCountdown() is called a second time before the first timer is up,
 that timer is cleared.
 */
var timeoutHandle = ''; //defined as a global so it can be unset if this function is called again
function listStartUpdateCountdown()
{
    if( timeoutHandle != '' )
        window.clearTimeout(timeoutHandle);

    timeoutHandle = window.setTimeout(function(){listUpdateContent()},500);
}

/*
 *UpdateContent() AJAX-refreshes the page's content
 */
function listUpdateContent()
{
    sort_value = '';
    if( document.getElementById('sort') )
        sort_value = document.getElementById('sort').value;

    loadPieceByURL(
        '/characters/list-content/', 's='+document.getElementById('s').value+
            '&sort='+sort_value,
        'get',
        document.getElementById('list-content'),
        function(){
            listInit();
            loadPictures();
        },
        document.getElementById('throbber') );
}

function modelUpdateContent()
{
    sort_value = '';
    if( document.getElementById('sort') )
        sort_value = document.getElementById('sort').value;

    loadPieceByURL(
        '/characters/model-content/', '',
        'get',
        document.getElementById('model-content'),
        function(){modelInit();}, document.getElementById('throbber') );
}

function chartListUpdateContent(chart_id)
{
    sort_value = '';
    if( document.getElementById('sort') )
        sort_value = document.getElementById('sort').value;

    loadPieceByURL(
        '/characters/relationships-list-content/', '',
        'get',
        document.getElementById('relationships-list'),
        function(){
            chartListInit();
            if( chart_id )
                chartShowEdit(chart_id);

        }, document.getElementById('throbber') );

}

function timelineListUpdateContent(character_id)
{
    var sort_value = '';
    if( document.getElementById('sort') )
        sort_value = document.getElementById('sort').value;

    loadPieceByURL(
        '/characters/timeline-content/', 'characters_id='+character_id+
            '&s='+document.getElementById('s').value+'&sort='+sort_value,
        'get',
        document.getElementById('timeline-content'),
        function(){
            timelineListInit(character_id);
        }, document.getElementById('throbber') );

}

function chartUpdateContent(chart_id)
{
    //delete old checkboxes, they're getting replaced
    $('.character-to-add').remove();

    loadPieceByURL(
        '/characters/relationships-content/', 'charts_id='+chart_id,
        'get',
        document.getElementById('relationships-content'),
        function(){chartInit(chart_id);}, document.getElementById('throbber') );


}

function chartUpdateContentAddNodesForm(chart_id)
{
    loadPieceByURL(
        '/characters/quick-form/', 'charts_id='+chart_id,
        'get',
        document.getElementById('add-character-to-chart-dialog'),
        function(){/*chartInit(chart_id);*/}, document.getElementById('throbber') );


}

function addExistingListUpdateContent()
{
    sort_value = '';
    if( document.getElementById('sort') )
        sort_value = document.getElementById('sort').value;

    loadPieceByURL(
        '/characters/add-existing-content/', 's='+document.getElementById('s').value+
            '&sort='+sort_value,
        'get',
        document.getElementById('list-content'),
        function(){listInit();}, document.getElementById('throbber') );
}

/*
 Drag and Drop Callbacks
 These are used by enableDragAndDrop, as callbacks, they should have two parameters:
 selector - the selector to the draggable elements
 moved_element - the place in the list the element was just moved to
 dropped_id - element id which was just dropped
 */

function listUpdatePriority(selector, moved_element, dropped_id)
{
    handleAjaxFromURL('/characters/ajax/', 'verb=list_update_priority&moved_element='+moved_element.index()+'&'+$( selector ).sortable("serialize"), 'post',
        function(resp){listUpdateContent();}, function(resp){AlertSet.addJSON(resp).show();});

    listMouseUp();
}

function groupUpdatePriority(selector, moved_element, dropped_id)
{
    handleAjaxFromURL('/characters/ajax/', 'verb=group_update_priority&moved_element='+moved_element.index()+'&'+$( selector ).sortable("serialize"), 'post',
        function(resp){modelUpdateContent();}, function(resp){AlertSet.addJSON(resp).show();});

    groupMouseUp();
}

function chartListUpdatePriority(selector, moved_element, dropped_id)
{
    handleAjaxFromURL('/characters/ajax/', 'verb=chart_update_priority&moved_element='+moved_element.index()+'&'+$( selector ).sortable("serialize"), 'post',
        function(resp){chartListUpdateContent();}, function(resp){AlertSet.addJSON(resp).show();});

    groupMouseUp();
}

function timelineListUpdatePriority(selector, moved_element, dropped_id, characters_id)
{
    handleAjaxFromURL('/characters/ajax/', 'verb=timeline_update_priority&moved_element='+moved_element.index()+'&'+$( selector ).sortable("serialize")+'&characters_id='+characters_id, 'post',
        function(resp){console.log(resp); timelineListUpdateContent(resp.characters_id);}, function(resp){AlertSet.addJSON(resp).show();});

    listMouseUp();
}

function fieldUpdatePriority(selector, moved_element, dropped_id)
{
    var order ='';
    $(selector).each(function(i,el) {
        var fields_array = $(el).sortable("toArray");
        var fields_string = $(el).sortable("serialize");
        if( $.inArray(dropped_id, fields_array ) != -1 )
        {
            order = fields_string + '&';
            return false;
        }
    });

    var groups_id = $('#'+dropped_id).parent().parent().attr('id').split("_").pop();

    handleAjaxFromURL('/characters/ajax/', 'verb=field_update_priority&groups_id='+groups_id+'&moved_element='+moved_element.index()+'&'+order, 'post',
        function(resp){modelUpdateContent();}, function(resp){AlertSet.addJSON(resp).show();});
}

function addExistingListUpdatePriority(selector, moved_element, dropped_id)
{
    handleAjaxFromURL('/characters/ajax/', 'verb=list_update_priority&moved_element='+moved_element.index()+'&'+$( selector ).sortable("serialize"), 'post',
        function(resp){listUpdateContent();}, function(resp){AlertSet.addJSON(resp).show();});

    listMouseUp();
}

/*
 *ShowEdit()/ *HideEdit() methods are used to de-activate inline forms.
 */

function groupShowEdit(group_id)
{
    //hide any existing inline forms
    //and show their views
    $('tr th.group_name .edit').hide();
    $('tr th.group_name .view').show();

    //hide the current view
    $('tr#groups_'+group_id+' th.group_name .view').hide();

    //show the current inline form
    $('tr#groups_'+group_id+' th.group_name .edit').show();
    $('tr#groups_'+group_id+' th.group_name .edit input[type="text"]').focus();

    //set cursor to end of input
    var tmp_val = $('tr#groups_'+group_id+' th.group_name .edit input[type="text"]').val();
    $('tr#groups_'+group_id+' th.group_name .edit input[type="text"]').val('');
    $('tr#groups_'+group_id+' th.group_name .edit input[type="text"]').val(tmp_val);
}

function groupHideEdit(group_id)
{
    //hide any existing inline forms
    $('tr#groups_'+group_id+' th.group_name .edit').hide();

    //show the current view
    $('tr#groups_'+group_id+' th.group_name .view').show();
}

function listMouseUp()
{
    //reset the list coloring
    $( "table.list_table tbody tr" ).each(function( index ) {
        if( index % 2 == 0 )
            $(this).attr('class', 'dark');
        else
            $(this).attr('class', 'light');
    });
}

function groupMouseDown(element)
{
    $(element).parent().parent().parent().parent().find("tbody").hide();
}

function groupMouseUp(element)
{
    //show all of them, in case mouse up event happens off of element
    $("table.sortable_field tbody").show();
}

function groupEditMouseDown(element)
{
    $(element).click(function(event){
        event.stopPropagation();
    });
}

function fieldShowEdit(field_id)
{
    //hide any existing inline forms
    //and show their views
    $('tr td.field_name .edit').hide();
    $('tr td.field_name .view').show();

    //hide the current view
    $('tr#fields_'+field_id+' td.field_name .view').hide();

    //show the current inline form
    $('tr#fields_'+field_id+' td.field_name .edit').show();
    $('tr#fields_'+field_id+' td.field_name .edit input[type="text"]').focus();

    //set cursor to end of input
    var tmp_val = $('tr#fields_'+field_id+' td.field_name .edit input[type="text"]').val();
    $('tr#fields_'+field_id+' td.field_name .edit input[type="text"]').val('');
    $('tr#fields_'+field_id+' td.field_name .edit input[type="text"]').val(tmp_val);
}

function fieldHideEdit(field_id)
{
    //hide any existing inline forms
    $('tr#fields_'+field_id+' td.field_name .edit').hide();

    //show the current view
    $('tr#fields_'+field_id+' td.field_name .view').show();
}

function viewChart(chart_id, chart_name)
{
    chartUpdateContent(chart_id);
    $('#relationships-list').hide();

    if( !!chart_name )
        $('#chart_title')[0].innerHTML = chart_name + '';

    $('#charts_id')[0].value = chart_id;
}

function chartShowEdit(chart_id)
{
    //hide any existing inline forms
    //and show their views
    $('tr td.chart_name .edit').hide();
    $('tr td.chart_name .view').show();

    //hide the current view
    $('tr#charts_'+chart_id+' td.chart_name .view').hide();

    //show the current inline form
    $('tr#charts_'+chart_id+' td.chart_name .edit').show();
    $('tr#charts_'+chart_id+' td.chart_name .edit input[type="text"]').focus();

    //set cursor to end of input
    var tmp_val = $('tr#charts_'+chart_id+' td.chart_name .edit input[type="text"]').val();
    $('tr#charts_'+chart_id+' td.chart_name .edit input[type="text"]').val('');
    $('tr#charts_'+chart_id+' td.chart_name .edit input[type="text"]').val(tmp_val);
}

function chartHideEdit(chart_id)
{
    //hide any existing inline forms
    $('tr#charts_'+chart_id+' td.chart_name .edit').hide();

    //show the current view
    $('tr#charts_'+chart_id+' td.chart_name .view').show();
}

function eventShowEdit(event_id)
{
    //hide any existing inline forms
    //and show their views
    $('tr td .edit').hide();
    $('tr td .view').show();

    //hide the current view
    $('tr#events_'+event_id+' td .view').hide();

    //show the current inline form
    $('tr#events_'+event_id+' td .edit').show();
    $('tr#events_'+event_id+' td .edit input[type="text"].event_new_name_input').focus();

    //set cursor to end of input
    var tmp_val = $('tr#events_'+event_id+' td .edit input[type="text"].event_new_name_input').val();
    $('tr#events_'+event_id+' td .edit input[type="text"].event_new_name_input').val('');
    $('tr#events_'+event_id+' td .edit input[type="text"].event_new_name_input').val(tmp_val);
}

function eventHideEdit(event_id)
{
    //hide any existing inline forms
    $('tr#events_'+event_id+' td .edit').hide();

    //show the current view
    $('tr#events_'+event_id+' td .view').show();
}

function groupHideEdit(chart_id)
{
    //hide any existing inline forms
    $('tr#groups_'+chart_id+' th.group_name .edit').hide();

    //show the current view
    $('tr#groups_'+chart_id+' th.group_name .view').show();
}

/* relationship-chart-specific code */
function addTimelineEventForm(characters_id)
{
    $( "#add-event-dialog" ).dialog({
        autoOpen: true,
        height: 300,
        width: 350,
        modal: true,
        dialogClass: 'transparent-dialog',
        hide: {
            effect: 'fade',
            duration: 500
        },
        open: function (event, ui){
            var edit_connection_dialog = $(this).parent();
            edit_connection_dialog
                .mouseover(function(){
                    $(this).css({ opacity: 1 });
                });

            edit_connection_dialog
                .mouseout( function(){
                    $(this).css({ opacity:.45 });
                });
        },
        buttons: {
            "Save Changes": function() {
                var bValid = true;

                if ( bValid ) {
                    $( this ).dialog( "destroy" );
                    eventAdd(document.getElementById('eventAdd'));
                }
            },
            Cancel: function() {
                $( this ).dialog( "destroy" );
            }
        },
        close: function() {
        }
    });

}

function addNodes(chart_id, characters_to_add)
{
    var data = {};
    data.verb = 'add_nodes';
    data.nodes = [];

    /* organize new node data */
    var new_top = 0;
    var new_left = 0;
    $.each(characters_to_add, function(index, character_to_add){
        var node = {};

        node.charts_id = chart_id;
        node.characters_id = $(character_to_add).attr("id").split('_')[1];
        node.characters_name = $(character_to_add).data("characters-name");
        node.top = new_top;
        node.left = new_left;

        data.nodes.push(node);

        new_top = new_top + 40;
        new_left = new_left + 40;

        if( new_top > 250 )
        {
            new_top = 0;
            new_left = 100;
        }
    });

    /* add new nodes to database */
    $.ajax({
        url: '/characters/ajax',
        type: "POST",
        async: true,
        data: $.param(data),
        success:
            function(resp){
                AlertSet.clear().addJSON(resp).showInCorner();

                resp = JSON.parse(resp);

                /* add new nodes to interface */
                $.each(resp.new_ids, function(index, new_id){
                    data.nodes[index].id = new_id;
                });

                $.each(data.nodes, function(index, node){

                    var new_window = document.createElement('div');
                    $(new_window).attr('class', 'w');
                    $(new_window).attr('id', node.id);
                    $(new_window).data('characters-id', node.characters_id);
                    $(new_window).css('left', node.left);
                    $(new_window).css('top', node.top);

                    var ep = document.createElement('div');
                    $(ep).attr('class', 'ep');

                    $(new_window).append(node.characters_name);
                    $(new_window).append(ep);

                    jsPlumbDemo.initWindow(new_window);

                    $('#main').append(new_window);
                });
            },

        error:  function(resp){console.log(resp); AlertSet.addJSON(resp).show();}
    });

    return true;
}

function addConnection(chart_id, info)
{
    var connection_data = info.connection;

    var data = {};
    data.verb = 'add_connection';
    data.connections = [];

    /* organize new connection data */
    var connection = {};

    connection.charts_id = chart_id;
    connection.nodes1_id = connection_data.sourceId;
    connection.nodes2_id = info.targetId;
    connection.type = connection_data.type;

    if( connection.nodes1_id != connection.nodes2_id )
    {
        data.connections.push(connection);

        /* add a new connection to database */
        $.ajax({
            url: '/characters/ajax',
            type: "POST",
            async: true,
            data: $.param(data),
            success:
                function(resp){
                    AlertSet.clear().addJSON(resp).showInCorner();

                    resp = JSON.parse(resp);

                    /* add id from database to active connection */
                    $.each(resp.new_ids, function(index, new_id){
                        data.connections[0].id = new_id;
                    });

                },

            error:  function(resp){AlertSet.clear().addJSON(resp).show();}
        });
    }
}

function editConnection(connections_id, connections_type, content)
{
    var data = {};
    data.verb = 'edit_connection';
    data.connections_id = connections_id;
    data.connections_type = connections_type;
    data.content = content;

    /* edit connection in database */
    $.ajax({
        url: '/characters/ajax',
        type: "POST",
        async: true,
        data: $.param(data),
        success:
            function(resp){
                AlertSet.clear().addJSON(resp).showInCorner();
            },

        error:  function(resp){AlertSet.clear().addJSON(resp).show();}
    });

}

function deleteConnection(chart_id, connections_id)
{
    var data = {};
    data.verb = 'delete_connection';
    data.connections_id = connections_id;

    /* add a new connection to database */
    $.ajax({
        url: '/characters/ajax',
        type: "POST",
        async: true,
        data: $.param(data),
        success:
            function(resp){
                AlertSet.clear().addJSON(resp).showInCorner();
                chartUpdateContent(chart_id);
            },

        error:  function(resp){AlertSet.clear().addJSON(resp).show();}
    });

}

function saveRelationshipChart(chart_id) {

    throb('inline');
    AlertSet.hide();

    var data = {};
    data.verb = 'save_chart';
    data.nodes = [];
    data.relationships = [];

    //collect nodes to save to db
    $('.relationship_chart #main .w').each(function(index,window){

        var node = {};

        node.nodes_id = $(window).attr("id");
        node.charts_id = chart_id;
        node.characters_id = $(window).data("characters-id");
        node.top = $(window).position().top;
        node.left = $(window).position().left;

        data.nodes.push(node);

    });

    //collect connections to save to db
    var connectionList = jsPlumb.getConnections();
    $(connectionList).each(function(index,connection){

        var relationship = {};

        relationship.id = connection.sourceId;
        relationship.nodes1_id = connection.sourceId;
        relationship.nodes2_id = connection.targetId;
        relationship.charts_id = chart_id;

        data.relationships.push(relationship);
    });

    $.ajax({
        url: '/characters/ajax',
        type: "POST",
        data: $.param(data),
        success: function(resp){/*chartUpdateContent();*/ AlertSet.clear().addJSON(resp).showInCorner();throb('none');},
        error:  function(resp){AlertSet.clear().addJSON(resp).show();throb('none');}
    });

    return false;
}

function deleteOrRemove(form)
{
    AlertSet
        .clear()
        .add(
            new AlertSet.Question(
                'Do you want to remove this character from the story, or delete it permanently?'
            )
        )
        .add(
            new AlertSet.Button(
                'Remove From Story',
                function(){
                    AlertSet.hide();
                    $(form).find("input[name='verb']").val('remove-from-story');
                    handleAjaxForm(
                        form,
                        function(){
                            listUpdateContent();
                        },
                        function(resp){
                            console.log(resp);
                            AlertSet.clear().addJSON(resp).show();
                        }
                    );
                }
            )
        )
        .add(
            new AlertSet.Button(
                'Delete Permanently',
                function(){
                    AlertSet.hide();
                    $(form).find("input[name='verb']").val('delete');
                    AlertSet.confirm(
                        'Are you sure you want to permanently delete this character?',
                        function(){
                            handleAjaxForm(
                                form,
                                function(){
                                    listUpdateContent();
                                },
                                function(resp){
                                    console.log(resp);
                                    AlertSet.clear().addJSON(resp).show();
                                }
                            );
                        },
                        function(){
                            AlertSet.hide();
                        }
                    );
                }
            )
        )
        .add(
            new AlertSet.Button(
                'Cancel',
                function(){
                    AlertSet.hide();
                }
            )
        )
        .show();
    return false;
}

function FriendOrFoe(info, chart_id)
{
    return AlertSet
        .clear()
        .add(
            new AlertSet.Question(
                'Allied, Conflicted or Romantic?'
            )
        )
        .add(
            new AlertSet.Button(
                'Allied',
                function(){
                    AlertSet.hide();
                    info.connection.setPaintStyle({
                            strokeStyle:"green" }
                    );
                    info.connection.type = 1;
                    info.connection.removeOverlay('label');
                    info.connection.addOverlay(
                        [ "Label", { id:"label", cssClass:"aLabel happyLabel" }]
                    );
                    addConnection($('#charts_id')[0].value, info);
                }
            )
        )
        .add(
            new AlertSet.Button(
                'Conflicted',
                function(){
                    AlertSet.hide();
                    info.connection.setPaintStyle({
                            strokeStyle:"red" }
                    );
                    info.connection.type = 2;
                    info.connection.removeOverlay('label');
                    info.connection.addOverlay(
                        [ "Label", { id:"label", cssClass:"aLabel sadLabel" }]
                    );
                    addConnection($('#charts_id')[0].value, info);

                }
            )
        )
        .add(
            new AlertSet.Button(
                'Romantic',
                function(){
                    AlertSet.hide();
                    info.connection.setPaintStyle({
                            strokeStyle:"purple" }
                    );
                    info.connection.type = 3;
                    info.connection.removeOverlay('label');
                    info.connection.addOverlay(
                        [ "Label", { id:"label", cssClass:"aLabel romanticLabel" }]
                    );
                    addConnection($('#charts_id')[0].value, info);
                }
            )
        )
        .add(
            new AlertSet.Button(
                'Cancel',
                function(){
                    AlertSet.hide();
                    var chart_name = $('#chart_title')[0].innerHTML;
                    viewChart(chart_id, chart_name);
                }
            )
        )
        .show();
}

function addNewCharacterNodesViaChart(chart_id, character_names)
{
    var data = {};
    data.verb = 'add_new_characters_via_chart';
    data.chart_id = chart_id;
    data.character_names = character_names;

    $.ajax({
        url: '/characters/ajax',
        type: "POST",
        async: true,
        data: $.param(data),
        success: function(data){

            var resp_data = JSON.parse(data);
            AlertSet.addJSON(resp_data);

            /* add new nodes to interface */
            $.each(resp_data.new_nodes, function(index, new_node){

                console.log(new_node);
                var new_window = document.createElement('div');
                $(new_window).attr('class', 'w');
                $(new_window).attr('id', new_node.id);
                $(new_window).data('characters-id', new_node.characters_id);
                $(new_window).css('left', new_node.left);
                $(new_window).css('top', new_node.top);
                $(new_window).css('top', new_node.top);

                var ep = document.createElement('div');
                $(ep).attr('class', 'ep');

                $(new_window).append(new_node.characters_name);
                $(new_window).append(ep);

                jsPlumbDemo.initWindow(new_window);

                $('#main').append(new_window);

            });
        }
    });

    return true;
}

function deleteNodeFromChart(chart_id, nodes_id)
{
    var data = {};
    data.verb = 'delete_node_and_connections';
    data.chart_id = chart_id;
    data.nodes_id = nodes_id;

    $.ajax({
        url: '/characters/ajax',
        type: "POST",
        async: false,
        data: $.param(data),
        success: function(data){

            var resp_data = JSON.parse(data);
            AlertSet.addJSON(resp_data);

            viewChart(chart_id);
        }
    });

    return true;
}

function addToCurrentStory(form)
{
    AlertSet
        .clear()
        .confirm(
            'Do you want to add this character to the current story?',
            function() {
                handleAjaxForm(
                    form,
                    function(){
                        addExistingListUpdateContent()
                    },
                    function(resp){
                        AlertSet.clear().addJSON(resp).show();
                    }
                );
            }
        )
    return false;
}

function enableListClick() {
    $('table.list_table tbody tr').each(function(index, element) {
        var data = $(element).attr("id");

        if( data )
            data = data.split("_");

        var chart_name = ($(element).data('chart_name'));

        $(element)
            .find('td:not(td:last)')
            .each(function(index, element){

                $(element).css('cursor', 'pointer');

                element.onclick = function(e) {

                    if(e.target !== this)
                        return;

                    if( data[0] == 'characters-add-existing' ) {
                        var form = document.getElementById(data[0]+'-form_'+data[1]);
                        addToCurrentStory(form);
                    }
                    else if ( data[0] == 'charts' )
                    {
                        if( !chart_name )
                            console.log('Error: Chart Name is missing.');
                        else
                            viewChart(data[1], chart_name);
                    }
                    else if( data[0] == 'events' ) {
                        console.log('show event in timeline here');
                    }
                    else
                        goTo('/'+data[0]+'/view/'+data[1]);
                }
            });
    });
}