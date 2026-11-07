/* Read-only enhancement: always calculate from the author's original amounts. */
(function () {
	'use strict';

	function measurement(row, preference, targets, ratio, units) {
		const types = preference === 'volume' ? ['volume', 'mass', 'optional'] : ['mass', 'volume', 'optional'];
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
	const preference = recipe.querySelector('#recipe-measurement');
	const mass = recipe.querySelector('#recipe-mass-unit');
	const volume = recipe.querySelector('#recipe-volume-unit');
	const rows = Array.from(recipe.querySelectorAll('[data-ingredient]'), element => ({ element, original: JSON.parse(element.dataset.ingredient) }));

	function update() {
		if (!servings.checkValidity() || !Number.isSafeInteger(servings.valueAsNumber) || servings.valueAsNumber < 1) return;
		const ratio = servings.valueAsNumber / originalServings;
		const amounts = rows.map(({ original }) => measurement(original, preference.value, { mass: mass.value, volume: volume.value }, ratio, rmRecipeConfig.units));
		if (amounts.some(amount => !amount)) return;
		rows.forEach(({ element }, index) => {
			const amount = amounts[index];
			element.querySelector('.ingredient-amount').textContent = formatQuantity(amount.quantity) + ' ' + amount.unit;
		});
		recipe.querySelector('[data-servings-display]').textContent = formatQuantity(servings.valueAsNumber);
		recipe.querySelector('[data-status]').textContent = rmRecipeConfig.updated.replace('%s', formatQuantity(servings.valueAsNumber));
	}

	[servings, preference, mass, volume].forEach(control => control.addEventListener('change', update));
	recipe.querySelector('[data-reset]').addEventListener('click', () => {
		servings.value = originalServings;
		preference.value = mass.value = volume.value = 'original';
		update();
	});
	recipe.querySelector('[data-print]').addEventListener('click', () => {
		if (!servings.reportValidity()) return;
		update();
		window.print();
	});
	recipe.querySelector('[data-controls]').hidden = false;
})();
