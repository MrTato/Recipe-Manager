/* Read-only enhancement: always calculate from the author's original amounts. */
(function () {
	'use strict';

	function measurement(row, preference, targets, ratio, units) {
		const types = preference === 'original' ? ['optional', 'mass', 'volume'] : preference === 'volume' ? ['volume', 'mass', 'optional'] : ['mass', 'volume', 'optional'];
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

	function formatQuantity(quantity, unit = null) {
		const language = typeof document === 'undefined' ? undefined : document.documentElement.lang || undefined;
		// Keep this display-only rule in sync with rm_theme_quantity in PHP.
		if (unit !== null && !['g', 'kg', 'ml', 'l'].includes(unit.trim().toLowerCase())) {
			const whole = Math.floor(quantity);
			const fractions = [[1/8, '⅛'], [1/4, '¼'], [1/3, '⅓'], [3/8, '⅜'], [1/2, '½'], [5/8, '⅝'], [2/3, '⅔'], [3/4, '¾'], [7/8, '⅞']];
			const match = fractions.find(([fraction]) => Math.abs(quantity - whole - fraction) <= Math.min(0.001, quantity * 0.01));
			if (match) return (whole > 0 ? new Intl.NumberFormat(language).format(whole) : '') + match[1];
		}
		const decimals = quantity < 0.01 ? Math.min(12, Math.max(3, Math.ceil(-Math.log10(quantity)) + 1)) : 2;
		return new Intl.NumberFormat(language, { maximumFractionDigits: decimals }).format(quantity);
	}

	// Keep calculation independently testable without a browser or dependencies.
	if (typeof module !== 'undefined' && module.exports) module.exports = { measurement, formatQuantity };
	if (typeof document === 'undefined') return;
	const recipe = document.querySelector('[data-recipe]');
	if (!recipe || typeof rmRecipeConfig === 'undefined') return;
	const originalServings = Number(recipe.dataset.servings);
	const servings = recipe.querySelector('#recipe-servings');
	const modes = ['original', 'mass', 'volume'];
	let selectedMode = 0;
	let displayedServings = originalServings;
	const cycle = recipe.querySelector('[data-measurement-cycle]');
	const card = recipe.querySelector('[data-ingredient-card]');
	let shuffleAnimations = [];
	let outgoingCard;
	function shuffleCard(change) {
		shuffleAnimations.forEach(animation => animation.cancel());
		shuffleAnimations = [];
		if (outgoingCard) outgoingCard.remove();
		if (!card || !card.animate || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			change();
			return;
		}
		const outgoing = card.cloneNode(true);
		outgoing.removeAttribute('data-ingredient-card');
		outgoing.removeAttribute('role');
		outgoing.removeAttribute('tabindex');
		outgoing.setAttribute('aria-hidden', 'true');
		outgoing.inert = true;
		outgoing.classList.add('ingredient-card--outgoing');
		outgoingCard = outgoing;
		card.parentElement.append(outgoing);
		change();
		const departure = outgoing.animate([
			{ transform: 'translate(0, 0) rotate(0)', zIndex: 2 },
			{ transform: 'translate(calc(100% + 40px), -18px) rotate(4deg)', zIndex: 2, offset: .42 },
			{ transform: 'translate(calc(100% + 40px), -18px) rotate(4deg)', zIndex: 2, offset: .54 },
			{ transform: 'translate(calc(100% + 40px), -18px) rotate(4deg)', zIndex: -2, offset: .55 },
			{ transform: 'translate(0, 0) rotate(0)', zIndex: -2 }
		], { duration: 650, easing: 'ease-in-out', fill: 'forwards' });
		const arrival = card.animate([
			{ transform: 'translate(3px, 4px) rotate(-1deg) scale(.98)' },
			{ transform: 'translate(-10px, -8px) rotate(-2deg) scale(1.02)', offset: .45 },
			{ transform: 'translate(-10px, -8px) rotate(-2deg) scale(1.02)', offset: .6 },
			{ transform: 'translate(0, 0) rotate(0) scale(1)' }
		], { duration: 650, easing: 'ease-in-out' });
		shuffleAnimations = [departure, arrival];
		departure.onfinish = () => outgoing.remove();
	}
	recipe.querySelectorAll('.ingredient-actions button').forEach(button => {
		let bounce;
		button.addEventListener('click', () => {
			if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !button.animate) return;
			if (bounce) bounce.cancel();
			bounce = button.animate([
				{ transform: 'translateY(0) scale(1)' },
				{ transform: 'translateY(2px) scale(.96)', offset: .2 },
				{ transform: 'translateY(-4px) scale(1.02)', offset: .55 },
				{ transform: 'translateY(0) scale(1)' }
			], { duration: 280, easing: 'ease-out' });
		});
	});
	const rows = Array.from(recipe.querySelectorAll('[data-ingredient]'), element => ({
		element, original: JSON.parse(element.dataset.ingredient)
	}));

	function amountText(amount) {
		return formatQuantity(amount.quantity, amount.unit) + (amount.unit ? ' ' + amount.unit + ' ' + rmRecipeConfig.ingredientOf : '');
	}

	function update() {
		if (!servings.checkValidity() || !Number.isSafeInteger(servings.valueAsNumber) || servings.valueAsNumber < 1) return;
		const ratio = servings.valueAsNumber / originalServings;
		const amounts = rows.map(row => measurement(row.original, modes[selectedMode], {}, ratio, rmRecipeConfig.units));
		if (amounts.some(amount => !amount)) return;
		displayedServings = servings.valueAsNumber;
		rows.forEach((row, index) => {
			const current = amountText(amounts[index]);
			row.element.querySelector('.ingredient-amount').textContent = current;
		});
		const currentMode = rmRecipeConfig.measurementModes[modes[selectedMode]];
		const nextMode = rmRecipeConfig.measurementModes[modes[(selectedMode + 1) % modes.length]];
		cycle.querySelector('[data-measurement-label]').textContent = currentMode;
		cycle.title = rmRecipeConfig.nextMeasurement.replace('%s', nextMode);
		cycle.setAttribute('aria-label', rmRecipeConfig.cycleLabel.replace('%1$s', currentMode).replace('%2$s', nextMode));
		if (card) card.setAttribute('aria-label', cycle.getAttribute('aria-label'));
		recipe.querySelector('[data-servings-display]').textContent = formatQuantity(servings.valueAsNumber);
		recipe.querySelector('[data-servings-decrease]').disabled = servings.valueAsNumber <= 1;
		recipe.querySelector('[data-servings-increase]').disabled = servings.valueAsNumber >= Number.MAX_SAFE_INTEGER;
		recipe.querySelector('[data-status]').textContent = rmRecipeConfig.updated.replace('%s', formatQuantity(servings.valueAsNumber));
	}

	cycle.addEventListener('click', () => {
		if (!servings.reportValidity()) return;
		selectedMode = (selectedMode + 1) % modes.length;
		shuffleCard(update);
		recipe.querySelector('[data-status]').textContent = cycle.getAttribute('aria-label');
	});
	if (card) {
		card.setAttribute('role', 'button');
		card.tabIndex = 0;
		card.setAttribute('aria-describedby', 'measurement-help');
		card.setAttribute('aria-label', cycle.getAttribute('aria-label') || cycle.textContent.trim());
		card.addEventListener('click', () => {
			if (!window.getSelection()?.toString()) cycle.click();
		});
		card.addEventListener('keydown', event => {
			if (event.key === 'Enter' || event.key === ' ') {
				event.preventDefault();
				cycle.click();
			}
		});
	}
	cycle.hidden = false;
	recipe.querySelector('[data-measurement-help]').hidden = false;

	function updateServings() {
		if (!servings.checkValidity() || !Number.isSafeInteger(servings.valueAsNumber) || servings.valueAsNumber < 1 || servings.valueAsNumber === displayedServings) return;
		shuffleCard(update);
	}
	servings.addEventListener('input', updateServings);
	servings.addEventListener('change', updateServings);
	['decrease', 'increase'].forEach(direction => {
		recipe.querySelector('[data-servings-' + direction + ']').addEventListener('click', () => {
			if (!servings.reportValidity()) return;
			if (direction === 'decrease') servings.stepDown(); else servings.stepUp();
			updateServings();
		});
	});
	recipe.querySelector('[data-reset]').addEventListener('click', () => {
		servings.value = originalServings;
		selectedMode = 0;
		shuffleCard(update);
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
