/*
	enableDragandDrop initializes a list with jquery 'sortable'.
	
	selector - the jquery selector string to the tbody or ul you want to drag and drop
	handle - the jquery selector to the elements you want to be able to drag each element with 
		(this way you can prevent being able to drag on actions and icons)
	stop_callback - is a function that is called when an item has been dropped, this callback 
		should have two arguments, selector and moved_item
	axis - the direction an element can be dragged in, defaults to x and y, can limit to x or y. 
*/

function enableDragAndDrop(selector, handle, start_callback, stop_callback, items, axis, connectWith, data_id)
{
	if( items.length == 0 )
		items = '> *';
		
	$(function() {
		$( selector ).sortable({
			helper: fixHelper,
            placeholder: "ui-state-highlight"
		});
		
		if( handle.length != 0 )
			$( selector ).sortable( "option", "handle", handle );

        $( selector ).sortable( "option", "start", function(event, ui) {
            if( start_callback.length != 0 )
                start_callback(selector, $(ui.item), ui.item.attr('id'), data_id)
            }
        );

        $( selector ).sortable( "option", "update",
            function(event, ui) {
                throb('inline');
                if( stop_callback.length != 0 )
                    stop_callback(selector, $(ui.item), ui.item.attr('id'), data_id);
                throb('none');
            }
        );

		if( items.length != 0 )
			$( selector ).sortable( "option", "items", items );
		if( axis.length != 0 )
		{
			$( selector ).sortable( "option", "axis", axis );
			if( axis == 'y')
				$( selector ).sortable( "option", "cursor", 's-resize' );
			else if( axis == 'x')
				$( selector ).sortable( "option", "cursor", 'w-resize' );
			else 
				$( selector ).sortable( "option", "cursor", 'move' );
		}
		if( connectWith.length != 0 )
			$( selector ).sortable( "option", "connectWith", [connectWith] );
	});
}

/* prevents widths from breaking while dragging items */
var fixHelper = function(e, ui) {
	ui.children().each(function() {
		$(this).width($(this).width());
	});
	return ui;
};

function jsPlumbInit()
{
    $.ajax({url:'/js/jsPlumb/lib/jsBezier-0.6.js', dataType:'script', async: false, cache: true});
    $.ajax({url:'/js/jsPlumb/src/util.js', dataType:'script', async: false, cache: true});
    $.ajax({url:'/js/jsPlumb/src/dom-adapter.js', dataType:'script', async: false, cache: true});
    $.ajax({url:'/js/jsPlumb/src/jsPlumb.js', dataType:'script', async: false, cache: true});
    $.ajax({url:'/js/jsPlumb/src/endpoint.js', dataType:'script', async: false, cache: true});
    $.ajax({url:'/js/jsPlumb/src/connection.js', dataType:'script', async: false, cache: true});
    $.ajax({url:'/js/jsPlumb/src/anchors.js', dataType:'script', async: false, cache: true});
    $.ajax({url:'/js/jsPlumb/src/defaults.js', dataType:'script', async: false, cache: true});
    $.ajax({url:'/js/jsPlumb/src/connectors-bezier.js', dataType:'script', async: false, cache: true});
    $.ajax({url:'/js/jsPlumb/src/connectors-statemachine.js', dataType:'script', async: false, cache: true});
    $.ajax({url:'/js/jsPlumb/src/connectors-flowchart.js', dataType:'script', async: false, cache: true});
    $.ajax({url:'/js/jsPlumb/src/renderers-svg.js', dataType:'script', async: false, cache: true});
    $.ajax({url:'/js/jsPlumb/src/renderers-canvas.js', dataType:'script', async: false, cache: true});
    $.ajax({url:'/js/jsPlumb/src/renderers-vml.js', dataType:'script', async: false, cache: true});
    $.ajax({url:'/js/jsPlumb/src/jquery.jsPlumb.js', dataType:'script', async: false, cache: true});

    jsPlumb.bind("ready", function() {

        if( jsPlumb.DemoList )
            jsPlumb.DemoList.init();

        // render mode
        var resetRenderMode = function(desiredMode) {
            var newMode = jsPlumb.setRenderMode(desiredMode);
            $(".rmode").removeClass("selected");
            $(".rmode[mode='" + newMode + "']").addClass("selected");

            $(".rmode[mode='canvas']").attr("disabled", !jsPlumb.isCanvasAvailable());
            $(".rmode[mode='svg']").attr("disabled", !jsPlumb.isSVGAvailable());
            $(".rmode[mode='vml']").attr("disabled", !jsPlumb.isVMLAvailable());

            jsPlumbDemo.init();
        };

        $(".rmode").bind("click", function() {
            var desiredMode = $(this).attr("mode");
            if (jsPlumbDemo.reset) jsPlumbDemo.reset();
            jsPlumb.reset();
            resetRenderMode(desiredMode);
        });

        resetRenderMode(jsPlumb.SVG);

    });

    (function() {

    window.jsPlumbDemo = {

        init :function() {

            // setup some defaults for jsPlumb.
            jsPlumb.importDefaults({
                Anchor: ["Top", "Right", "Bottom", "Left"],
                Endpoint : ["Dot", {radius:4}],
                PaintStyle: { strokeStyle: "#41C8D9", lineWidth:4, outlineColor:"transparent", outlineWidth:0},
                HoverPaintStyle : { strokeStyle:"#1e8151" },
                deleteEndpointsOnDetach:true,
                ConnectionOverlays : [
                [ "Label", { id:"label", cssClass:"aLabel" }]
            ]
            });

            var windows = $(".w");

            $.each(windows, function(index, window) {
                jsPlumbDemo.initWindow(window)
            });

            // double click a connection
            jsPlumb.bind("dblclick", function(c) {
                ;
            });

            // double click an endpoint
            jsPlumb.bind("endpointClick", function(c) {
                ;
            });
        },

        initWindow: function(window)
        {
            // initialise draggable elements.
            jsPlumb.draggable(window, {
                containment:$('#main'),
                stop: function()
                {
                    var chart_id = $('#charts_id')[0].value;
                    if( chart_id )
                        saveRelationshipChart(chart_id);
                    else
                        console.log('save relationship charts failed.');
                }
            });

            // initialise all '.w' elements as sources.
            jsPlumb.makeSource(window, {
                filter:".ep",				// only supported by jquery
                connector:[ "Straight", {} ]
            });

            // initialise all '.w' elements as connection targets.
            jsPlumb.makeTarget(window, {
                dropOptions:{ hoverClass:"dragHover" }
            });

            $(window).dblclick(function(e){
                // dblclick a node
                    console.log(e);
                    var chart_id = $('#charts_id')[0].value;
                    var data = {};
                    data.verb = 'get_node_dialog';
                    data.charts_id = chart_id;
                    data.nodes_id = e.target.id;

                    $.ajax({
                        url: '/characters/ajax',
                        type: "POST",
                        async: false,
                        data: $.param(data),
                        success: function(data){

                            data = JSON.parse(data);

                            var character_name = document.createElement('div');
                            $(character_name).append(document.createTextNode(data.characters_name));
                            $(character_name).addClass('edit-node-header');

                            var view_profile_link = document.createElement('a');
                            view_profile_link.href='/characters/view/'+data.characters_id;
                            $(view_profile_link).text('View Profile');

                            var delete_link = document.createElement('a');
                            delete_link.href='javascript:;';
                            $(delete_link).text('Delete From Chart');
                            $(delete_link).addClass('delete-link');
                            delete_link.onclick=
                                function() {
                                    AlertSet.confirm(
                                        'Are you sure you\'d like to delete this node?',
                                        function() {
                                            $( "#edit-node" ).dialog( "destroy" );
                                            deleteNodeFromChart(chart_id, data.nodes_id);
                                        },
                                        function() {
                                            // do nothing
                                        }
                                    );
                                };

                            var edit_node_contents = $("#edit-node");

                            edit_node_contents
                                .empty()
                                .addClass('transparent-dialog')
                                .append(character_name)
                                .append(view_profile_link)
                                .append(delete_link)
                        }
                    });

                    $( "#edit-node" ).dialog({
                        autoOpen: true,
                        height: 300,
                        width: 250,
                        modal: true,
                        dialogClass: 'transparent-dialog',
                        hide: {
                            effect: 'fade',
                            duration: 500
                        },
                        open: function (event, ui){
                            var edit_node_dialog = $(this).parent();
                            edit_node_dialog
                                .mouseover(function(){
                                    $(this).css({ opacity: 1 });
                                });

                            edit_node_dialog
                                .mouseout( function(){
                                    $(this).css({ opacity:.45 });
                                });
                        },
                        buttons: {/*
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
                            },*/
                            Cancel: function() {
                                $( this ).dialog( "destroy" );
                            }
                        },
                        close: function() {

                        }
                    });

                    $('.ui-widget-overlay').remove();

                /*AlertSet.confirm('Are you sure you want to remove this character and all of their relationships from this chart?',
                    function(){
                        var chart_id = $('#charts_id')[0].value;
                        deleteNodeFromChart(chart_id, window.id);
                    },
                    function() {
                        ; // do nothing
                    }
                );*/
            })
        },

        deleteEmptyEndpoints: function(element)
        {
            var endpoints_to_delete = [];
            jsPlumb.selectEndpoints({source:element}).each(function(endpoint) {
                if( !!endpoint && endpoint.connections.length <= 0 )
                {
                    endpoints_to_delete.push(endpoint);
                }
            });

            $.each(endpoints_to_delete, function(index, endpoint) {
                jsPlumb.deleteEndpoint(endpoint);

            });
        }
    };
})();
}

function loadStory(story_id, module, action)
{
    if( isNaN(parseInt(story_id)) || module == 'undefined' )
        goTo('/stories/load/');
    else
    {
        if( action == 'edit' || action == 'view')
            action = 'list';

        goTo('/stories/load/'+story_id+'/?module='+module+'&action='+action);
    }
}

/* Quick Forms */
function loadQuickForm(div_selector, buttons, onclose)
{
    if( !buttons )
    {
        buttons = {};
    }

    if( !buttons.Cancel )
    {
        buttons.Cancel = function() {
            $(this).dialog("close");
        }
    }

    if( typeof(onclose) != 'function' )
    {
        onclose = function() {};
    }

    $( div_selector )
        .dialog({
            autoOpen: false,
            height: 400,
            width: 550,
            modal: true,
            buttons: buttons,
            close: function() {
                onclose();

                $( this ).dialog( "destroy" );
                loadQuickForm(div_selector, buttons, onclose);
            }
        });
}


function showQuickForm(div_selector)
{
    $( div_selector ).dialog("open");
}

function addNewObjectInQuickForm(object_type)
{
    var timestamp = new Date().getTime();

    $('<a/>', {
        href: 'javascript:;',
        class: 'add-new label ' + timestamp,
        text: 'New Name:'
    }).appendTo('fieldset#add-new-form');

    $('<input/>', {
        name: 'add-new-'+object_type+'[]',
        class: 'add-new-'+object_type+' ' + timestamp
    }).appendTo('fieldset#add-new-form');

    $('<img/>', {
        src: '/tabmin/icons/delete.png',
        style: 'cursor:pointer',
        class: '' + timestamp
    })
        .click(function()
        {
            $('.' + timestamp).remove();
        })
        .appendTo('fieldset#add-new-form')
}

function toggleObject(object_type, button, characters_id)
{
    var checkbox = $("input#"+object_type+"-check_"+characters_id);
    var label = $("label[for="+object_type+"-check_"+characters_id+"]");
    if(button.innerHTML == 'Add')
    {
        button.className = 'cancel';
        button.innerHTML = 'Cancel';
        label[0].className = 'selected';
        checkbox.attr("checked", 'checked');
    }
    else
    {
        button.className = 'add';
        button.innerHTML = 'Add';
        label[0].className = '';
        checkbox.removeAttr("checked");
    }
}

function slideSwitch(forward) {
    var $slides = $('div.photo-slider a.slide');

    if( $slides.length <= 1 )
        return;

    var $active = $('div.photo-slider a.active-slide');

    var $next;
    if( !!forward )
        $next = $active.next('div.photo-slider a.slide');
    else
        $next = $active.prev('div.photo-slider a.slide');

    if( $next.length == 0 )
    {
        if( !!forward )
            $next = $('div.photo-slider a.slide').first();
        else
            $next = $('div.photo-slider a.slide').last();
    }

    $next.removeClass('inactive-slide');
    $next.addClass('active-slide');

    $active.removeClass('active-slide');
    $active.addClass('inactive-slide');

    return;
}

/*
 File uploader functions
 */
function toggleAddPhotoMenu()
{
    $('#add_photo_menu').toggle();
}

function togglePhotoMenu(pictures_id)
{
    var menu_is_hidden;

    if( pictures_id !== undefined )
        menu_is_hidden = $('#photo-menu-'+pictures_id).is(':hidden');
    else
        menu_is_hidden = null;

    $('.photo-menu').hide();

    if( pictures_id !== undefined )
    {
        if( menu_is_hidden )
        {
            $('#photo-menu-'+pictures_id).show();
        }
        else
        {
            $('#photo-menu-'+pictures_id).hide();
        }
    }
}

function photoWrapperMouseOut(event)
{
    var e = event.toElement || event.relatedTarget;
    if (e.parentNode == this || e == this) {
        return;
    }

    togglePhotoMenu();
}

function addPhotosFromComputer() {
    $('#add_photo_menu').hide();
    showQuickForm('#add-photos-from-computer');
    $('#fileupload').trigger('click');
}

function addPhotosFromInternet() {
    $('#add_photo_menu').hide();
    showQuickForm('#add-photos-from-internet');
}

function addPhotosFromInternetSubmit(form) {
    $('#add-photos-from-internet #instructions').html('<p>Downloaded image from link... please wait...</p>');
    return handleAjaxForm(form,
        function(resp) {
            $('#add-photos-from-internet #results').html('<p>Download completed!</p>');
            $('#add-photos-from-internet #instructions').html('<p>Paste a link to an image on the internet below...</p>');
            $('#add-photos-from-internet input[type="text"]').val('');
        },
        function(resp) {
            AlertSet.addJSON(resp).show();
            $('#add-photos-from-internet #results').html('<p>Download failed.</p>');
            $('#add-photos-from-internet #instructions').html('<p>Paste a link to an image on the internet below...</p>');
        }
    );
}

function downloadPhoto(pictures_id)
{
    goTo(
        '/show-picture.php?pictures_id='+pictures_id+'&download=true',
        true
    );
    togglePhotoMenu();
}

function editPhoto(pictures_id) {
    goTo(
        '/photos/edit/'+pictures_id
    )
}

function deletePhoto(pictures_id)
{
    return AlertSet.confirm(
        'Are you sure you want to delete this photo?',
        function(){

            var data = {};
            data.verb = 'delete';
            data.pictures_id = pictures_id;

            $.ajax({
                url: '/photos/ajax',
                type: "POST",
                async: true,
                data: $.param(data),
                success:
                    function(resp){
                        AlertSet.clear().addJSON(resp).showInCorner();
                        listUpdateContent();
                    },

                error:  function(resp){console.log(resp); AlertSet.addJSON(resp).show();}
            });
        },
        function() {
            ;
        }
    )
}

function makeCoverPhoto(pictures_id, objects_id, object_type)
{
    return AlertSet.confirm(
        'Are you sure you want to make this the cover photo?',
        function(){

            var data = {};
            data.verb = 'make-cover-photo';
            data.pictures_id = pictures_id;
            data.objects_id = objects_id;
            data.object_type = object_type;

            $.ajax({
                url: '/photos/ajax',
                type: "POST",
                async: true,
                data: $.param(data),
                success:
                    function(resp){
                        goTo('/'+object_type+'/edit/'+objects_id+'?gallery');

                        //remove cover photo class from all photos
                        //apply to current photo
                    },

                error:  function(resp){console.log(resp); AlertSet.addJSON(resp).show();}
            });
        },
        function() {
            ;
        }
    )
}

function fileUploadInit(object_type, object_id)
{
    $(function () {
        $('#fileupload').fileupload({
            dataType: 'json',
            formData: {
                verb: 'add',
                object_type: object_type,
                object_id: object_id
            },
            add: function(e, data) {
                $('#add-photos-from-computer #instructions').html('<p>Upload has started... Please wait...</p>');
                data.submit();
            },
            done: function (e, data) {
                AlertSet.clear().addJSON(data.result).showInCorner();

                $('#add-photos-from-computer #instructions').html('<p>Upload completed. </p>');
                $("button.ui-button span:contains('Cancel')" ).html('Continue');
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .bar').css(
                    'width',
                    progress + '%'
                );
            }
        });
    });
}

function loadFileUploadForms(onsuccess)
{
    var addPhotosFromComputerButtons = {
    };

    var onclose = function() {
        $( ".bar" ).width('1%');
        $( '#results' ).empty();
        $( '#instructions' ).html('<p>Select files from your computer...</p>');
        $("button.ui-button span:contains('Continue')" ).html('Cancel');

        if( !!onsuccess && typeof( onsuccess ) === 'function' )
            onsuccess();
    };

    loadQuickForm("#add-photos-from-computer" , addPhotosFromComputerButtons, onclose);


    var addPhotosFromInternetButtons = {
    };

    onclose = function() {
        $( '#add-photos-from-internet #results' ).empty();
        $( '#add-photos-from-internet #instructions' ).html('<p>Paste a link to an image on the internet below...</p>');

        if( !!onsuccess && typeof( onsuccess ) === 'function' )
            onsuccess();
    };

    loadQuickForm("#add-photos-from-internet" , addPhotosFromInternetButtons, onclose);
}

function photoGalleryUpdatePriority(selector, moved_element, dropped_id, objects_id)
{
    handleAjaxFromURL(
        '/photos/ajax/',
        'verb=update-photo-priority'+
            '&moved_element='+moved_element.index()+
            '&'+$( selector ).sortable("serialize")+
            '&objects_id='+objects_id,
        'post',
        function(resp){
            AlertSet.addJSON(resp).showInCorner();
        },
        function(resp){
            AlertSet.addJSON(resp).show();
        }
    );
}

function exportAccountData()
{
    throb('inline');
    handleAjaxFromURL(
        '/users/ajax/',
        'verb=export'+
            '',
        'post',
        function(resp){
            AlertSet.addJSON(resp).show();
            throb('none');
        },
        function(resp){
            AlertSet.addJSON(resp).show();
            throb('none');
        }
    );
}

function loadPictures()
{
    var picture_ids = $("a.photo-thumbnail")         // find spans with ID attribute
        .map(function() { return $(this).data('picture_id'); }) // convert to set of IDs
        .get(); // convert to instance of Array (optional)

    loadImagesOneByOne(picture_ids);
}

function loadImagesOneByOne(picture_ids, current_index) {
    console.log('loadImages is starting');
    if( picture_ids.length == 0 || picture_ids.length == current_index) return;
    if( typeof current_index == 'undefined' ) {
        console.log('current_id is undefined');
        current_index = 0;
    }

    var image_wrapper = document.getElementById('photo-thumbnail'+picture_ids[current_index]);
    if( $(image_wrapper).has('img.photo').length )
    {
        console.log($(image_wrapper).has('img.photo'));
        console.log('photo already exists');
        return;
    }

    var image = new Image();
    image_wrapper.appendChild(image);
    image.src = '/images/throbber.gif';

    console.log('setting image onload');
    image.onload = function() {
        waitToLoadImages(picture_ids, ++current_index);
    };
    image.onerror = function() {
        waitToLoadImages(picture_ids, ++current_index);
    };

    image.className = 'photo';
    console.log('new image is created');

    if( image_wrapper )
    {
        console.log('wrapper is found, adding image');
        //image.src = '/images/throbber.gif';
        image.src = '/show-picture.php?pictures_id='+picture_ids[current_index]+'&w=180&h=180';
    }
}

//loading images too quickly causes some to get trampled, stall out
function waitToLoadImages(picture_ids, current_index)
{
    window.setTimeout(function() {
        loadImagesOneByOne(picture_ids, current_index);
    }, 25)
}

//Header Menu
function toggleProfileMenu()
{
    $('#profile-menu').toggle();
}