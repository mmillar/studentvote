tinyMCEPopup.requireLangPack();

function insertPostLink(elem,nofollow,shortcode){
	elem = $(elem);
	var ed = tinyMCEPopup.editor, dom = ed.dom, n = ed.selection.getNode();
	e = dom.getParent(n, 'A');
	if(e == null){
		if(shortcode == 'on'){
			tinyMCEPopup.execCommand("mceInsertContent", false, "[link2post id=\"" + elem.attr('id') + "\"]" + ed.selection.getContent() + "[/link2post]");
		}
		else{
			tinyMCEPopup.execCommand("CreateLink", false, "#mce_temp_url#", {skip_undo : 1});
			tinymce.each(ed.dom.select("a"), function(n) {
				if (ed.dom.getAttrib(n, 'href') == '#mce_temp_url#') {
					e = n;
					ed.dom.setAttribs(e, {
						title : elem.text()
					});
					ed.dom.setAttribs(e, {
						href : elem.attr('href')
					});
					if(nofollow == 'on'){
						ed.dom.setAttribs(e, {
							rel : 'nofollow'
						});				
					}
				}
			});
		}
	}
	else{
			ed.dom.setAttribs(e, {
				title : elem.text()
			});
			ed.dom.setAttribs(e, {
				href : elem.attr('href')
			});	
			if(nofollow == 'on'){
				ed.dom.setAttribs(e, {
					rel : 'nofollow'
				});				
			}
	}
	tinyMCEPopup.close();
	return false;
}
function showFilter(){
	$('.showFilter').css('display','none');
	$('.filter').css('display','block');
}
function hideFilter(){
	$('.showFilter').css('display','block');
	$('.filter').css('display','none');
}