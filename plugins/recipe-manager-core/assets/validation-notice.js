/* Surface validation failures even when recipe meta boxes are below the fold. */
(() => {
	'use strict';
	const notice = document.getElementById('rm-recipe-validation-notice');
	if (!notice) return;
	const editorSelector = '#rm-ingredient-editor, #rm-instruction-editor';
	let pending = false;

	document.addEventListener('invalid', event => {
		if (!event.target.closest(editorSelector) || pending) return;
		pending = true;
		// Wait until all invalid fields have displayed their inline errors.
		requestAnimationFrame(() => {
			pending = false;
			notice.hidden = false;
		});
	}, true);

	function updateNotice(event) {
		if (!event.target.closest(editorSelector)) return;
		requestAnimationFrame(() => {
			const hasErrors = Array.from(document.querySelectorAll(editorSelector))
				.some(editor => editor.querySelector('[aria-invalid="true"]'));
			if (!hasErrors) notice.hidden = true;
		});
	}
	document.addEventListener('input', updateNotice);
	document.addEventListener('change', updateNotice);
	document.addEventListener('click', updateNotice);
})();
