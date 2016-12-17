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
					//if added a story, go to the story view screen
					var url = '/plot/edit/'+resp.id;
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
			function(resp) {window.location='/plot/view/'+resp.id},
			function(resp) {AlertSet.clear().addJSON(resp).showInCorner(); throb('none');}
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
		enableDragAndDrop("#sortable tbody", "td div.sort.action_button", '', listUpdatePriority, '', 'y', '');
        enableListClick();
	}

	function formInit(settings_id)
	{
        if( !!settings_id )
        {
            enableDragAndDrop("#photos", "a", '', photoGalleryUpdatePriority, '', '', '', settings_id);
        }
        var addCharactersFormButtons = {
            "Add Characters": function() {
                var plot_events_id = $('#plot_events_id')[0].value;

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
                            if( addNewCharactersToPlot(plot_events_id, new_characters_names) )
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
                        if( addCharactersToPlot(plot_events_id, existing_characters_to_add) )
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

        loadQuickForm("#add-characters-to-event-dialog" , addCharactersFormButtons, onclose);

        var addSettingsFormButtons = {
            "Add Settings": function() {
                var plot_events_id = $('#plot_events_id')[0].value;

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
                            if( addNewSettingsToPlot(plot_events_id, new_settings_names) )
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
                        if( addSettingsToPlot(plot_events_id, existing_settings_to_add) )
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
            $( ".setting-to-add").removeAttr("checked");
            $( "label.selected").className = '';
            $( "a.remove").className = 'add';
        };

        loadQuickForm("#add-settings-to-event-dialog" , addSettingsFormButtons, onclose);
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
			'/plot/list-content/', 's='+document.getElementById('s').value+
				'&sort='+sort_value, 
			'get', 
			document.getElementById('list-content'),
			function(){listInit();}, document.getElementById('throbber') );
	}

    function addNewCharactersToPlot(plot_events_id, character_names)
    {
        var data = {};
        data.verb = 'add_new_characters_to_plot';
        data.plot_events_id = plot_events_id;
        data.character_names = character_names;

        $.ajax({
            url: '/plot/ajax',
            type: "POST",
            async: true,
            data: $.param(data),
            success: function(data){

                var resp_data = JSON.parse(data);
                AlertSet.addJSON(resp_data);

                formSave(
                    document.getElementById('plot_events_form'),
                    document.getElementById('plot_events_form_verb'));
                window.location='';


            }
        });

        return true;
    }

    function addNewSettingsToPlot(plot_events_id, setting_names)
    {
        var data = {};
        data.verb = 'add_new_settings_to_plot';
        data.plot_events_id = plot_events_id;
        data.setting_names = setting_names;

        $.ajax({
            url: '/plot/ajax',
            type: "POST",
            async: true,
            data: $.param(data),
            success: function(data){

                var resp_data = JSON.parse(data);
                AlertSet.addJSON(resp_data);

                formSave(
                    document.getElementById('plot_events_form'),
                    document.getElementById('plot_events_form_verb'));
                window.location='';


            }
        });

        return true;
    }

    function addCharactersToPlot(plot_events_id, characters_to_add)
    {
        var data = {};
        data.verb = 'add_characters_to_plot';
        data.plot_events_id = plot_events_id;
        data.nodes = [];

        $.each(characters_to_add, function(index, character_to_add){
            var node = {};

            node.characters_id = $(character_to_add).attr("id").split('_')[1];
            node.plot_events_id = plot_events_id;

            data.nodes.push(node);
        });

        $.ajax({
            url: '/plot/ajax',
            type: "POST",
            async: true,
            data: $.param(data),
            success: function(data){

                var resp_data = JSON.parse(data);
                AlertSet.addJSON(resp_data);

                formSave(
                    document.getElementById('plot_events_form'),
                    document.getElementById('plot_events_form_verb'));
                window.location='';


            }
        });

        return true;
    }

    function addSettingsToPlot(plot_events_id, settings_to_add)
    {
        var data = {};
        data.verb = 'add_settings_to_plot';
        data.plot_events_id = plot_events_id;
        data.nodes = [];

        $.each(settings_to_add, function(index, setting_to_add){
            var node = {};

            node.settings_id = $(setting_to_add).attr("id").split('_')[1];
            node.plot_events_id = plot_events_id;

            data.nodes.push(node);
        });

        $.ajax({
            url: '/plot/ajax',
            type: "POST",
            async: true,
            data: $.param(data),
            success: function(data){

                var resp_data = JSON.parse(data);
                AlertSet.addJSON(resp_data);

                formSave(
                    document.getElementById('plot_events_form'),
                    document.getElementById('plot_events_form_verb'));
                window.location='';


            }
        });

        return true;
    }



    function removeTag(plot_events_id, object_type, objects_id)
    {
        var data = {};
        data.verb = 'remove-tag';
        data.plot_events_id = plot_events_id;
        data.object_type = object_type;

        if( object_type == 'characters' )
        {
            data.characters_id = objects_id;
        }
        else if( object_type == 'settings' )
        {
            data.settings_id = objects_id;
        }

        $.ajax({
            url: '/plot/ajax',
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
                        document.getElementById('character-tag-'+plot_events_id+'-'+objects_id).style.display='none';
                    }
                    else if( object_type == 'settings' )
                    {
                        document.getElementById('setting-tag-'+plot_events_id+'-'+objects_id).style.display='none';
                    }
                }
                else
                    console.log('could not delete node');

            }
        });

        return true;
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
		handleAjaxFromURL('/plot/ajax/', 'verb=list_update_priority&moved_element='+moved_element.index()+'&'+$( selector ).sortable("serialize"), 'post',
			function(resp){listUpdateContent();}, function(resp){AlertSet.addJSON(resp).show();});
			
		listMouseUp();
	}

    /*
     *ShowEdit()/ *HideEdit() methods are used to de-activate inline forms.
     */

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

    function enableListClick() {
        $('table.list_table tbody tr').each(function(index, element) {
            var data = $(element).attr("id");

            if( data )
                data = data.split("_");

            $(element)
                .find('td:not(td:last)')
                .each(function(index, element){
                    $(element).css('cursor', 'pointer');
                    element.onclick = function(e) {
                        if(e.target !== this)
                            return;
                        goTo('/'+data[0]+'/view/'+data[1]);
                    }
                });
        });
    }