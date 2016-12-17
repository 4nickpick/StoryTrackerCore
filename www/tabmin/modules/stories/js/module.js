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
					var url = '/stories/view/'+resp.id;
					AlertSet.redirectWithAlert(url, resp);
                    // don't clear throbber, redirecting...
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
			function(resp) {
                window.location='/stories/view/'+resp.id;
                // don't clear throbber, redirecting...
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
		enableDragAndDrop("#sortable tbody", "td div.sort.action_button", '', listUpdatePriority, '', 'y', '');
        enableListClick();
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
			'/stories/list-content/', 's='+document.getElementById('s').value+
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
		handleAjaxFromURL('/stories/ajax/', 'verb=list_update_priority&moved_element='+moved_element.index()+'&'+$( selector ).sortable("serialize"), 'post',
			function(resp){listUpdateContent();}, function(resp){AlertSet.addJSON(resp).show();});
			
		listMouseUp();
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
                        //allow click on series_name span, as well as rest of td
                        if(!$(e.target).hasClass('series_name') && e.target !== this)
                            return;
                        loadStory(data[1]);
                    }
                });
        });
    }