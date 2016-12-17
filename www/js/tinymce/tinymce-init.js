tinymce.init(
	{
		plugins: "hr fullscreen",
        content_css: '/css/tinymce-custom.css',
        theme_advanced_font_sizes: "10px,12px,13px,14px,16px,18px,20px",
        font_size_style_values : "10px,12px,13px,14px,16px,18px,20px",
        browser_spellcheck : true,
        contextmenu: false,
		selector: 'textarea.content',
		menubar : false,
		statusbar : false,
		cleanup: true,
		height: '350px',
		toolbar: "undo redo | h1 h2 | bold italic | link | bullist numlist hr",
		setup : function(ed){
			ed.addButton('h1', // name to add to toolbar button list
			{
				title : 'Heading 1', // tooltip text seen on mouseover
				image: '/images/tinymce/h1.gif',
				onclick : function()
				{
					ed.execCommand('FormatBlock', false, 'h1');
				}
			});
			
			ed.addButton('h2', // name to add to toolbar button list
			{
				title : 'Heading 2', // tooltip text seen on mouseover
				image: '/images/tinymce/h2.gif',
				onclick : function()
				{
					ed.execCommand('FormatBlock', false, 'h2');
				}
			});
		}
	}
);