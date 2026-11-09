/* Submit the combined search/filter form when a filter changes. */
(() => {
	'use strict';
	const form = document.querySelector('.browse-layout');
	if (!form) return;
	let pending;
	const update = () => {
		clearTimeout(pending);
		if (form.checkValidity()) form.requestSubmit();
	};
	form.querySelectorAll('input[type="checkbox"], select').forEach(control => {
		control.addEventListener('change', update);
	});
	form.querySelectorAll('input[type="number"]').forEach(control => {
		control.addEventListener('input', () => {
			clearTimeout(pending);
			pending = setTimeout(update, 700);
		});
		control.addEventListener('change', update);
	});
	form.addEventListener('submit', () => clearTimeout(pending));
})();
