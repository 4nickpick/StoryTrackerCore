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
					var url = '/settings/edit/'+resp.id;
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
			function(resp) {window.location='/settings/view/'+resp.id},
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
        enableDragAndDrop("#photos", "a", '', photoGalleryUpdatePriority, '', '', '', settings_id);
    }

    function modelInit()
    {
        enableDragAndDrop("#sortable_groups > tbody", "thead .drag_icon", '', groupUpdatePriority, '> tr', 'y', '');
        enableDragAndDrop(".sortable_field > tbody", "", '', fieldUpdatePriority, '', 'y', '.sortable_field tbody');
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
			'/settings/list-content/', 's='+document.getElementById('s').value+
				'&sort='+sort_value, 
			'get', 
			document.getElementById('list-content'),
			function(){listInit();}, document.getElementById('throbber') );
	}

    function modelUpdateContent()
    {
        sort_value = '';
        if( document.getElementById('sort') )
            sort_value = document.getElementById('sort').value;

        loadPieceByURL(
            '/settings/model-content/', '',
            'get',
            document.getElementById('model-content'),
            function(){modelInit();}, document.getElementById('throbber') );
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
		handleAjaxFromURL('/settings/ajax/', 'verb=list_update_priority&moved_element='+moved_element.index()+'&'+$( selector ).sortable("serialize"), 'post',
			function(resp){listUpdateContent();}, function(resp){AlertSet.addJSON(resp).show();});
			
		listMouseUp();
	}

    function groupUpdatePriority(selector, moved_element, dropped_id)
    {
        handleAjaxFromURL('/settings/ajax/', 'verb=group_update_priority&moved_element='+moved_element.index()+'&'+$( selector ).sortable("serialize"), 'post',
            function(resp){modelUpdateContent();}, function(resp){AlertSet.addJSON(resp).show();});

        groupMouseUp();
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

        handleAjaxFromURL('/settings/ajax/', 'verb=field_update_priority&groups_id='+groups_id+'&moved_element='+moved_element.index()+'&'+order, 'post',
            function(resp){modelUpdateContent();}, function(resp){AlertSet.addJSON(resp).show();});
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