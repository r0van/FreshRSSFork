// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL-3.0
'use strict';
/* globals context, init_load_more, init_posts, init_stream */

let panel_loading = false;

function load_panel(link) {
	if (panel_loading) {
		return;
	}

	panel_loading = true;

	const req = new XMLHttpRequest();
	req.open('GET', link + '&ajax=1', true);
	req.responseType = 'document';
	req.onload = function () {
		if (this.status != 200) {
			return;
		}
		const html = this.response;
		const foreign = html.querySelectorAll('.nav_menu, #stream .day, #stream .flux, #stream-footer, #stream.prompt');
		const panel = document.getElementById('panel');
		foreign.forEach(function (el) {
			panel.appendChild(document.adoptNode(el));
		});
		panel.querySelectorAll('.nav_menu > :not([id="nav_menu_read_all"])').forEach(function (el) {
			el.remove();
		});

		init_load_more(panel);
		init_posts();

		document.getElementById('overlay').classList.add('visible');
		panel.classList.add('visible');
		document.documentElement.classList.add('slider-active');

		panel.scrollTop = 0;
		document.documentElement.scrollTop = 0;

		panel.addEventListener('click', function (ev) {
			const b = ev.target.closest('#nav_menu_read_all button, #bigMarkAsRead');
			if (b) {
				const req2 = new XMLHttpRequest();
				req2.open('POST', b.formAction, false);
				req2.setRequestHeader('Content-Type', 'application/json; charset=utf-8');
				req2.send(JSON.stringify({
					_csrf: context.csrf,
				}));
				if (req2.status == 200) {
					location.reload(false);
					return false;
				}
			}
		});

		panel_loading = false;
	};
	req.send();
}

function init_close_panel() {
	const panel = document.getElementById('panel');
	document.querySelector('#overlay .close').onclick = function () {
		panel.innerHTML = '';
		panel.classList.remove('visible');
		document.getElementById('overlay').classList.remove('visible');
		document.documentElement.classList.remove('slider-active');
		return false;
	};
	document.addEventListener('keydown', ev => {
		const k = (ev.key.trim() || ev.code).toUpperCase();
		if (k === 'ESCAPE' || k === 'ESC') {
			document.querySelector('#overlay .close').click();
		}
		return false;
	});
}

function init_global_view() {
	document.querySelectorAll('.box a:not(.entry_link)').forEach(function (a) {
		a.onclick = function (ev) {
			load_panel(a.href);
			return false;
		};
	});

	document.querySelectorAll('.nav_menu #nav_menu_read_all, .nav_menu .toggle_aside').forEach(function (el) {
		el.remove();
	});

	const panel = document.getElementById('panel');
	init_stream(panel);

	// Kategorie filtrování
	const buttons = document.querySelectorAll('.category-btn');
	const boxes = document.querySelectorAll('.box.category');
	if (buttons.length && boxes.length) {
		buttons.forEach(btn => {
			btn.addEventListener('click', () => {
				const selected = btn.dataset.category;
				boxes.forEach(box => {
					if (selected === 'all') {
						box.style.display = '';
					} else {
						box.style.display = box.classList.contains(selected) ? '' : 'none';
					}
				});
			});
		});
	}
}

function init_all_global_view() {
	if (!window.context) {
		if (window.console) {
			console.log('FreshRSS Global view waiting for JS…');
		}
		window.setTimeout(init_all_global_view, 50);
		return;
	}
	init_global_view();
	init_close_panel();
}

if (document.readyState && document.readyState !== 'loading') {
	init_all_global_view();
} else {
	document.addEventListener('DOMContentLoaded', function () {
		init_all_global_view();
	}, false);
}
// @license-end:
