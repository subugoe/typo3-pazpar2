/* Load this script using conditional IE comments if you need to support IE 7 and IE 6. */

window.onload = function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'subicons\'">' + entity + '</span>' + html;
	}
	var icons = {
			'subicon-web-resource_fg' : '&#xe01f;',
			'subicon-web-resource_bg' : '&#xe01e;',
			'subicon-video_fg' : '&#xe011;',
			'subicon-video_bg' : '&#xe010;',
			'subicon-unknown_fg' : '&#xe023;',
			'subicon-unknown_bg' : '&#xe022;',
			'subicon-ui-loupe_fg' : '&#xe055;',
			'subicon-ui-loupe_bg' : '&#xe054;',
			'subicon-ui-arrow-prev-page' : '&#xe051;',
			'subicon-ui-arrow-next-page' : '&#xe053;',
			'subicon-ui-arrow-go-to-result' : '&#xe052;',
			'subicon-research-data_fg' : '&#xe00d;',
			'subicon-research-data_bg' : '&#xe00c;',
			'subicon-newspaper_fg' : '&#xe009;',
			'subicon-newspaper_bg' : '&#xe008;',
			'subicon-music_fg' : '&#xe015;',
			'subicon-music_bg' : '&#xe014;',
			'subicon-multivolume-work_fg' : '&#xe003;',
			'subicon-multivolume-work_bg' : '&#xe002;',
			'subicon-multiple-mediatypes_fg' : '&#xe021;',
			'subicon-multiple-mediatypes_bg' : '&#xe020;',
			'subicon-microfiche_fg' : '&#xe01b;',
			'subicon-microfiche_bg' : '&#xe01a;',
			'subicon-map_fg' : '&#xe00f;',
			'subicon-map_bg' : '&#xe00e;',
			'subicon-manuscript_fg' : '&#xe019;',
			'subicon-manuscript_bg' : '&#xe018;',
			'subicon-letter_fg' : '&#xe017;',
			'subicon-letter_bg' : '&#xe016;',
			'subicon-journal_fg' : '&#xe007;',
			'subicon-journal_bg' : '&#xe006;',
			'subicon-image_fg' : '&#xe01d;',
			'subicon-image_bg' : '&#xe01c;',
			'subicon-book_fg' : '&#xe001;',
			'subicon-book_bg' : '&#xe000;',
			'subicon-audio_fg' : '&#xe013;',
			'subicon-audio_bg' : '&#xe012;',
			'subicon-article_fg' : '&#xe005;',
			'subicon-article_bg' : '&#xe004;',
			'subicon-file_fg' : '&#xe00b;',
			'subicon-file_bg' : '&#xe00a;'
		},
		els = document.getElementsByTagName('*'),
		i, attr, c, el;
	for (i = 0; ; i += 1) {
		el = els[i];
		if(!el) {
			break;
		}
		attr = el.getAttribute('data-icon');
		if (attr) {
			addIcon(el, attr);
		}
		c = el.className;
		c = c.match(/subicon-[^\s'"]+/);
		if (c && icons[c[0]]) {
			addIcon(el, icons[c[0]]);
		}
	}
};