/* Category-only media picker; the server also enforces the size limit. */
(() => {
	'use strict';
	document.querySelectorAll('.rm-category-image-picker').forEach(picker => {
		const input = picker.querySelector('input');
		const preview = picker.querySelector('.rm-category-image-preview');
		const remove = picker.querySelector('.rm-category-image-remove');
		const error = picker.querySelector('.rm-category-image-error');
		let frame;
		const reset = () => {
			input.value = '';
			preview.replaceChildren();
			remove.hidden = true;
			error.textContent = '';
		};
		remove.addEventListener('click', reset);
		picker.querySelector('.rm-category-image-select').addEventListener('click', () => {
			if (!frame) {
				frame = wp.media({ title: rmCategoryImage.title, button: { text: rmCategoryImage.button }, library: { type: 'image' }, multiple: false });
				frame.on('uploader:ready', () => {
					const uploader = frame.uploader?.uploader;
					if (uploader?.param) uploader.param('rm_category_image_upload', '1');
				});
				frame.on('select', () => {
					const image = frame.state().get('selection').first().toJSON();
					const width = image.originalImageWidth || image.width;
					const height = image.originalImageHeight || image.height;
					if (!width || !height || width > 512 || height > 512) {
						error.textContent = rmCategoryImage.error;
						return;
					}
					input.value = image.id;
					const img = document.createElement('img');
					img.src = image.sizes?.thumbnail?.url || image.url;
					img.alt = '';
					img.style.cssText = 'max-width:128px;height:auto';
					preview.replaceChildren(img);
					remove.hidden = false;
					error.textContent = '';
				});
			}
			frame.open();
		});
		// WordPress adds categories via AJAX without replacing the add form.
		jQuery(document).ajaxSuccess((event, xhr, settings) => {
			if (!picker.closest('#addtag') || typeof settings.data !== 'string') return;
			const data = new URLSearchParams(settings.data);
			if (data.get('action') !== 'add-tag' || data.get('taxonomy') !== 'rm_recipe_category') return;
			if (xhr.responseXML?.querySelector('term_id') && !xhr.responseXML.querySelector('wp_error')) reset();
		});
	});
})();
