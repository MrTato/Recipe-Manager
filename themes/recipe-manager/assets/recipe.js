/* Read-only enhancement: always calculate from the author's original amounts. */
(function () {
	'use strict';

	function measurement(row, preference, targets, ratio, units) {
		const types = preference === 'optional' ? ['optional', 'mass', 'volume'] : preference === 'volume' ? ['volume', 'mass', 'optional'] : ['mass', 'volume', 'optional'];
		const type = types.find(key => Number.isFinite(row[key + '_quantity']) && row[key + '_quantity'] > 0);
		if (!type) return null;
		let quantity = row[type + '_quantity'] * ratio;
		let unit = row[type + '_unit'];
		const target = targets[type];
		if (type !== 'optional' && units[unit]?.type === type && units[target]?.type === type && units[target].to_base_factor > 0) {
			quantity = quantity * units[unit].to_base_factor / units[target].to_base_factor;
			unit = target;
		}
		return Number.isFinite(quantity) && quantity > 0 ? { quantity, unit } : null;
	}

	function formatQuantity(quantity) {
		const decimals = quantity < 0.01 ? Math.min(12, Math.max(3, Math.ceil(-Math.log10(quantity)) + 1)) : 2;
		return new Intl.NumberFormat(document.documentElement.lang || undefined, { maximumFractionDigits: decimals }).format(quantity);
	}

	// Keep calculation independently testable without a browser or dependencies.
	if (typeof module !== 'undefined' && module.exports) module.exports = { measurement };
	if (typeof document === 'undefined') return;
	const recipe = document.querySelector('[data-recipe]');
	if (!recipe || typeof rmRecipeConfig === 'undefined') return;
	const originalServings = Number(recipe.dataset.servings);
	const servings = recipe.querySelector('#recipe-servings');
	const modes = ['original', 'mass', 'volume', 'optional'];
	let selectedMode = 0;
	const cycle = recipe.querySelector('[data-measurement-cycle]');
	const rows = Array.from(recipe.querySelectorAll('[data-ingredient]'), element => ({
		element, original: JSON.parse(element.dataset.ingredient)
	}));

	function amountText(amount) {
		return formatQuantity(amount.quantity) + ' ' + amount.unit;
	}

	function update() {
		if (!servings.checkValidity() || !Number.isSafeInteger(servings.valueAsNumber) || servings.valueAsNumber < 1) return;
		const ratio = servings.valueAsNumber / originalServings;
		const amounts = rows.map(row => measurement(row.original, modes[selectedMode], {}, ratio, rmRecipeConfig.units));
		if (amounts.some(amount => !amount)) return;
		rows.forEach((row, index) => {
			const current = amountText(amounts[index]);
			row.element.querySelector('.ingredient-amount').textContent = current;
		});
		const currentMode = rmRecipeConfig.measurementModes[modes[selectedMode]];
		const nextMode = rmRecipeConfig.measurementModes[modes[(selectedMode + 1) % modes.length]];
		cycle.querySelector('[data-measurement-label]').textContent = currentMode;
		cycle.title = rmRecipeConfig.nextMeasurement.replace('%s', nextMode);
		cycle.setAttribute('aria-label', rmRecipeConfig.cycleLabel.replace('%1$s', currentMode).replace('%2$s', nextMode));
		recipe.querySelector('[data-servings-display]').textContent = formatQuantity(servings.valueAsNumber);
		recipe.querySelector('[data-servings-decrease]').disabled = servings.valueAsNumber <= 1;
		recipe.querySelector('[data-servings-increase]').disabled = servings.valueAsNumber >= Number.MAX_SAFE_INTEGER;
		recipe.querySelector('[data-status]').textContent = rmRecipeConfig.updated.replace('%s', formatQuantity(servings.valueAsNumber));
	}

	cycle.addEventListener('click', () => {
		if (!servings.reportValidity()) return;
		selectedMode = (selectedMode + 1) % modes.length;
		update();
		recipe.querySelector('[data-status]').textContent = cycle.getAttribute('aria-label');
	});
	cycle.hidden = false;
	recipe.querySelector('[data-measurement-help]').hidden = false;

	servings.addEventListener('input', update);
	servings.addEventListener('change', update);
	['decrease', 'increase'].forEach(direction => {
		recipe.querySelector('[data-servings-' + direction + ']').addEventListener('click', () => {
			if (!servings.reportValidity()) return;
			if (direction === 'decrease') servings.stepDown(); else servings.stepUp();
			update();
		});
	});
	recipe.querySelector('[data-reset]').addEventListener('click', () => {
		servings.value = originalServings;
		selectedMode = 0;
		update();
	});
	recipe.querySelector('[data-print]').addEventListener('click', () => {
		if (!servings.reportValidity()) return;
		update();
		window.print();
	});
	update();
	recipe.querySelector('[data-servings-display]').hidden = true;
	recipe.querySelector('[data-servings-control]').hidden = false;
	recipe.querySelector('[data-controls]').hidden = false;
})();
