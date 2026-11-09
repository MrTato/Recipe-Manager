/* Save the existing featured flag directly from the recipe list. */
(() => {
	'use strict';
	document.querySelectorAll('.rm-featured-toggle').forEach(toggle => {
		toggle.disabled = false;
		toggle.addEventListener('change', async () => {
			const desired = toggle.checked;
			const status = toggle.nextElementSibling;
			toggle.disabled = true;
			status.textContent = rmFeatured.saving;
			try {
				const response = await fetch(rmFeatured.url, {
					method: 'POST',
					credentials: 'same-origin',
					body: new URLSearchParams({
						action: 'rm_set_featured',
						recipe_id: toggle.dataset.recipeId,
						_ajax_nonce: toggle.dataset.nonce,
						featured: desired ? '1' : '0'
					})
				});
				const result = await response.json();
				if (!response.ok || !result.success) throw new Error('Save failed');
				status.textContent = rmFeatured.saved;
			} catch (error) {
				toggle.checked = !desired;
				status.textContent = rmFeatured.error;
			} finally {
				toggle.disabled = false;
			}
		});
	});
})();
