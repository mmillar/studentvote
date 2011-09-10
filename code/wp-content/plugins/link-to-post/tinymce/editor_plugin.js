/**
 * $Id: editor_plugin.js
 *
 * @author Julien Appert
 */

(function() {
	tinymce.PluginManager.requireLangPack('link2post');
	tinymce.create('tinymce.plugins.link2post', {
		init : function(ed, url) {
			this.editor = ed;
			// Register commands
			ed.addCommand('mceAddPostLink', function() {
				var se = ed.selection;
				// No selection and not in link
				if (se.isCollapsed() && !ed.dom.getParent(se.getNode(), 'A'))
					return;
				var content = ed.selection.getContent();
				var re=/(<\/?p)(?:\s[^>]*)?(>)|<[^>]*>/gi;
				content = content.replace(re,'');				
				ed.windowManager.open({
					file : url + '/linktopost.php?validate=1&tri='+content + '&where=both&category=-1',
					width : 600 + parseInt(ed.getLang('link2post.delta_width', 0)),
					height : 500 + parseInt(ed.getLang('link2post.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('post_link', {
				title : 'link2post.linkPost',
				image : url + '/../post_link.png',
				cmd : 'mceAddPostLink'
			});
			ed.onNodeChange.add(function(ed, cm, n, co) {
				cm.setDisabled('post_link', co && n.nodeName != 'A');
				cm.setActive('post_link', n.nodeName == 'A' && !n.name);
			});
		},

		getInfo : function() {
			return {
				longname : 'link2post',
				author : 'Julien Appert',
				authorurl : 'http://ajcrea.com',
				infourl : '',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('link2post', tinymce.plugins.link2post);
})();