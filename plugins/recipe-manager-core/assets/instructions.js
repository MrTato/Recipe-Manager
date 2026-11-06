/* Instruction array order is the only source of step numbers. */
(() => {
	'use strict';
	const editor = document.getElementById('rm-instruction-editor');
	if (!editor) return;
	const rows = editor.querySelector('[data-step-rows]');
	const template = document.getElementById('rm-step-template');
	const add = editor.querySelector('[data-step-action="add"]');

	function refreshGroups() {
		let previousGroup = null;
		Array.from(rows.children).forEach(row => {
			const group = row.querySelector('[data-step-field="group"]').value.trim();
			const heading = row.querySelector('[data-step-group-heading]');
			heading.textContent = group || editor.querySelector('[data-step-ungrouped-label]').textContent;
			heading.hidden = group === previousGroup || (!group && previousGroup === null);
			previousGroup = group;
		});

	}

	function validate(row) {
		const text = row.querySelector('[data-step-field="text"]');
		const error = row.querySelector('[data-step-error]');
		const invalid = !text.value.trim() && (Number(row.querySelector('[data-step-field="image_id"]').value) > 0 || row.querySelector('[data-step-field="group"]').value.trim() !== '');
		text.setCustomValidity(invalid ? error.textContent : '');
		error.hidden = !invalid || row.dataset.validationAttempted !== 'true';
		if (!error.hidden) {
			text.setAttribute('aria-invalid', 'true');
			text.setAttribute('aria-describedby', error.id);
		} else {
			text.removeAttribute('aria-invalid');
			text.removeAttribute('aria-describedby');
		}
	}

	function refresh() {
		refreshGroups();
		Array.from(rows.children).forEach((row, index) => {
			row.querySelector('[data-step-number]').textContent = String(index + 1);
			row.querySelector('[data-step-error]').id = `rm-step-error-${index}`;
			row.querySelectorAll('[data-step-field]').forEach(input => {
				input.name = `rm_steps[${index}][${input.dataset.stepField}]`;
			});
			row.querySelector('[data-step-action="up"]').setAttribute('aria-disabled', String(index === 0));
			row.querySelector('[data-step-action="down"]').setAttribute('aria-disabled', String(index === rows.children.length - 1));
			validate(row);
		});
	}

	editor.addEventListener('invalid', event => {
		const row = event.target.closest('.rm-step-row');
		if (!row) return;
		event.preventDefault();
		row.dataset.validationAttempted = 'true';
		validate(row);
	}, true);
	editor.addEventListener('input', event => {
		const row = event.target.closest('.rm-step-row');
		if (row) validate(row);
		if (event.target.dataset.stepField === 'group') refreshGroups();
	});

	editor.addEventListener('change', event => {
		if (event.target.dataset.stepField === 'group') {
			validate(event.target.closest('.rm-step-row'));
			refreshGroups();
		}
	});

	editor.addEventListener('click', event => {
		const button = event.target.closest('[data-step-action]');
		if (!button || !editor.contains(button) || button.getAttribute('aria-disabled') === 'true') return;
		const action = button.dataset.stepAction;
		if (action === 'add') {
			rows.append(template.content.cloneNode(true));
			refresh();
			rows.lastElementChild.querySelector('textarea').focus();
			return;
		}
		const row = button.closest('.rm-step-row');
		if (!row) return;
		if (action === 'image') {
			const frame = wp.media({
				title: editor.dataset.mediaTitle,
				button: { text: editor.dataset.mediaButton },
				library: { type: 'image' },
				multiple: false,
			});
			frame.on('open', () => {
				const id = Number(row.querySelector('[data-step-field="image_id"]').value);
				if (id) frame.state().get('selection').add(wp.media.attachment(id));
			});
			frame.on('select', () => {
				if (!rows.contains(row)) return;
				const image = frame.state().get('selection').first().toJSON();
				if (image.type !== 'image') return;
				row.querySelector('[data-step-field="image_id"]').value = String(image.id);
				const preview = row.querySelector('[data-step-preview]');
				preview.src = image.sizes?.medium?.url || image.url;
				preview.hidden = false;
				row.querySelector('[data-step-action="clear-image"]').hidden = false;
				validate(row);
			});
			frame.on('close', () => { if (rows.contains(row)) button.focus(); });
			frame.open();
			return;
		}
		if (action === 'clear-image') {
			row.querySelector('[data-step-field="image_id"]').value = '0';
			const preview = row.querySelector('[data-step-preview]');
			preview.removeAttribute('src');
			preview.hidden = true;
			button.hidden = true;
			row.querySelector('[data-step-action="image"]').focus();
			validate(row);
			return;
		}
		if (action === 'remove') {
			const next = row.nextElementSibling || row.previousElementSibling;
			row.remove();
			refresh();
			(next ? next.querySelector('textarea') : add).focus();
			return;
		}
		if (action === 'up' && row.previousElementSibling) {
			rows.insertBefore(row, row.previousElementSibling);
		} else if (action === 'down' && row.nextElementSibling) {
			rows.insertBefore(row.nextElementSibling, row);
		} else {
			return;
		}
		refresh();
		button.focus();
		editor.querySelector('[data-step-status]').textContent = editor.querySelector('[data-step-order-message]').textContent;
	});
	refresh();
})();
