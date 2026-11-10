/* Repeatable ingredient rows. DOM order is the saved order. */
(() => {
	'use strict';
	const editor = document.getElementById('rm-ingredient-editor');
	if (!editor) return;
	const rows = editor.querySelector('[data-ingredient-rows]');
	const template = document.getElementById('rm-ingredient-template');
	const add = editor.querySelector('[data-ingredient-action="add"]');

	function refreshGroups() {
		let previousGroup = null;
		Array.from(rows.children).forEach(row => {
			const group = row.querySelector('[data-field="group"]').value.trim();
			const heading = row.querySelector('[data-group-heading]');
			heading.textContent = group || editor.querySelector('[data-ungrouped-label]').textContent;
			heading.hidden = group === previousGroup || (!group && previousGroup === null);
			previousGroup = group;
		});

	}

	function validateRow(row) {
		const fields = Object.fromEntries(Array.from(row.querySelectorAll('[data-field]'), input => [input.dataset.field, input]));
		const errors = {};
		Object.entries(fields).forEach(([field, input]) => {
			input.setCustomValidity('');
			if (!input.validity.valid) errors[field] = input.validationMessage;
			input.removeAttribute('aria-invalid');
			if (field !== 'group') input.removeAttribute('aria-describedby');
			const error = row.querySelector(`[data-field-error="${field}"]`);
			if (error) error.hidden = true;
		});
		const nonempty = Object.values(fields).some(input => input.value.trim() !== '' || input.validity.badInput);
		if (nonempty) {
			if (!fields.name.value.trim()) errors.name = 'Enter an ingredient name.';
			let complete = false;
			['mass', 'volume', 'optional'].forEach(type => {
				const quantity = fields[`${type}_quantity`];
				const unit = fields[`${type}_unit`];
				if (!quantity.value && !unit.value.trim() && !quantity.validity.badInput) return;
				if (!quantity.value && !errors[`${type}_quantity`]) errors[`${type}_quantity`] = 'Enter a quantity for this measurement.';
				if (quantity.value && (!/^(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)$/.test(quantity.value) || !Number.isFinite(Number(quantity.value)) || Number(quantity.value) <= 0)) {
					errors[`${type}_quantity`] = 'Enter a positive decimal quantity greater than zero.';
				}
				if (!unit.value.trim()) errors[`${type}_unit`] = type === 'optional' ? 'Choose or type a Default unit, such as cloves.' : 'Select a unit for this measurement.';
				if (quantity.value && unit.value.trim() && quantity.validity.valid && !errors[`${type}_quantity`]) complete = true;
			});
			if (!complete && !errors.mass_quantity && !errors.volume_quantity && !errors.optional_quantity) {
				const message = editor.querySelector('[data-measurement-message]').textContent;
				errors.mass_unit = message;
				errors.volume_unit = message;
				errors.optional_unit = message;
			}
		}
		Object.entries(errors).forEach(([field, message]) => {
			fields[field].setCustomValidity(message);
			if (row.dataset.validationAttempted !== 'true') return;
			const error = row.querySelector(`[data-field-error="${field}"]`);
			error.textContent = message;
			error.hidden = false;
			fields[field].setAttribute('aria-invalid', 'true');
			fields[field].setAttribute('aria-describedby', error.id);
		});
	}

	// Keep native form blocking, but replace all ingredient bubbles with inline errors.
	editor.addEventListener('invalid', event => {
		const row = event.target.closest('.rm-ingredient-row');
		if (!row) return;
		event.preventDefault();
		row.dataset.validationAttempted = 'true';
		validateRow(row);
	}, true);

	editor.addEventListener('input', event => {
		const row = event.target.closest('.rm-ingredient-row');
		if (row) validateRow(row);
		if (event.target.dataset.field === 'group') refreshGroups();
	});
	editor.addEventListener('change', event => {
		const row = event.target.closest('.rm-ingredient-row');
		if (row) validateRow(row);
		if (event.target.dataset.field === 'group') refreshGroups();
	});

	function refresh() {
		refreshGroups();
		Array.from(rows.children).forEach((row, index) => {
			row.querySelectorAll('[data-field-error]').forEach(error => {
				error.id = `rm-measurement-error-${index}-${error.dataset.fieldError}`;
			});
			validateRow(row);
			row.querySelectorAll('[data-field]').forEach((input) => {
				input.name = `rm_ingredients[${index}][${input.dataset.field}]`;
			});
			// Keep boundary buttons focusable when a moved row reaches an edge.
			row.querySelector('[data-ingredient-action="up"]').setAttribute('aria-disabled', String(index === 0));
			row.querySelector('[data-ingredient-action="down"]').setAttribute('aria-disabled', String(index === rows.children.length - 1));
		});
	}

	editor.addEventListener('click', (event) => {
		const button = event.target.closest('[data-ingredient-action]');
		if (!button || !editor.contains(button) || button.getAttribute('aria-disabled') === 'true') return;
		const action = button.dataset.ingredientAction;
		if (action === 'add') {
			rows.append(template.content.cloneNode(true));
			refresh();
			rows.lastElementChild.querySelector('[data-field="name"]').focus();
			return;
		}
		const row = button.closest('.rm-ingredient-row');
		if (!row) return;
		if (action === 'remove') {
			const next = row.nextElementSibling || row.previousElementSibling;
			row.remove();
			refresh();
			(next ? next.querySelector('[data-field="name"]') : add).focus();
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
		editor.querySelector('[data-ingredient-status]').textContent = editor.querySelector('[data-order-message]').textContent;
	});
	refresh();
})();
