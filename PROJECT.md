# Recipe Manager

## Project Definition

Recipe Manager is a public recipe website built with WordPress. The MVP will launch in one content language; English/Spanish content support is deferred.

The website presents a curated collection of recipes published by a single author, similar to a personal chef or cooking website. Visitors can browse, search, filter, read, scale, convert, and print recipes through a polished and responsive interface.

The project is primarily a portfolio and learning project intended to demonstrate practical WordPress development skills. It should favor simple, maintainable solutions over unnecessary custom development.

The administrative experience does not need to be highly customized because recipes will only be published by the site owner.

---

## Purpose

The project has two primary goals:

1. Create a useful and polished website for publishing personal recipes.
2. Demonstrate practical WordPress development skills suitable for real-world freelance projects.

The project should demonstrate skills including:

- Custom WordPress theme development
- Custom Post Types
- Advanced Custom Fields (ACF)
- Structured content modeling
- Responsive frontend development
- WordPress search and filtering
- Dynamic frontend interactions
- Translation-ready theme and plugin interface strings
- Recipe measurement conversion
- Recipe serving scaling
- Print-friendly layouts
- Elementor integration
- WordPress taxonomy management

The project should prioritize maintainability and development speed, using WordPress and suitable free plugins where they solve a problem well. All required plugins and extensions must be free. The site will not be monetized, so the implementation must not depend on premium licenses, paid services, or paid feature upgrades.

---

# V1 Scope

Version 1 will provide a complete public recipe browsing experience.

Only the site owner can create and manage recipes through the WordPress administration interface.

Visitors do not need accounts and cannot submit or modify content.

## Core Features

V1 will include:

- Public recipe website
- Custom Recipe post type
- Structured recipe data using ACF
- Recipe content in one language for the MVP
- Recipe categories
- Recipe tags
- Recipe search
- Recipe filtering
- Featured recipes
- Related recipes
- Ingredient serving scaling
- Mass/volume measurement switching
- Numbered recipe instructions
- Optional images for instruction steps
- Recipe notes and tips
- Print-friendly recipe pages
- Responsive design
- Custom WordPress theme
- Selective Elementor integration

---

# Out of Scope for V1

The following features are intentionally excluded from V1:

- Bilingual recipe content and visitor language switching (deferred)
- Custom multilingual infrastructure

- User registration
- User profiles
- User-submitted recipes
- Recipe editing by visitors
- Favorites or saved recipes
- Ratings
- Reviews
- Comments
- Meal planning
- Shopping lists
- Nutrition calculations
- Social features
- Mobile applications

These features may be considered for future versions but should not influence the architecture beyond avoiding decisions that would make reasonable future expansion unnecessarily difficult.

The goal of V1 is to ship a focused, polished recipe website as quickly as reasonably possible.

---

# Recipe Data Model

Recipes will use a dedicated WordPress `Recipe` Custom Post Type.

Recipe-specific structured information will primarily be managed using Advanced Custom Fields.

## Basic Information

Each recipe should contain:

### Title

The name of the recipe.

Example:

`Pollo en Salsa Jalapeña`

### Description

A short introduction or description of the recipe.

This content will be authored in the selected MVP language. Translated versions are deferred.

### Featured Image

One primary image representing the finished recipe.

Only one featured image is required in V1.

---

# Servings

Each recipe must specify its default number of servings.

Example:

`4 servings`

Visitors can change the desired number of servings.

Ingredient quantities should automatically scale according to:

`desired servings / default servings`

For example, if a recipe designed for four servings requires 500 g of chicken and the visitor selects eight servings, the displayed amount becomes 1000 g.

The original recipe data must not be modified by this interaction.

---

# Preparation and Cooking Time

Each recipe should contain:

- Preparation time
- Cooking time

The frontend may calculate and display total time automatically.

Example:

Preparation: 15 minutes
Cooking: 30 minutes
Total: 45 minutes

---

# Ingredients

Ingredients must use structured data rather than a single text field.

Each ingredient should contain at minimum:

- Ingredient name
- Quantity
- Measurement unit

Example:

| Ingredient | Quantity | Unit |
| :---- | ----: | :---- |
| Chicken breast | 500 | g |
| Milk | 250 | ml |
| Garlic | 10 | g |

This structure is necessary to support serving scaling and measurement conversion.

## Ingredient Entry and Unit Catalog

Ingredient names are free text, entered directly when editing a recipe. The
author does not need to create or select an ingredient from a separate catalog.
Ingredient presets, suggested units, and ingredient-specific unit restrictions
are not part of the MVP.

Measurement units must be selected from a predefined catalog maintained in the
core plugin. Store stable unit codes separately from their display labels and
group units by measurement type:

| Type | Example units |
| :---- | :---- |
| Mass | g, kg |
| Volume | ml, l, tsp, tbsp, cup |
| Count | piece, clove, slice |
| Informal | pinch, handful |

The author can select any supported unit for any ingredient. Do not infer unit
restrictions or conversion factors from a free-text ingredient name. Define
the volume represented by each supported spoon/cup unit explicitly so
conversions are consistent.

Store ingredient rows in their authored order as structured recipe post
metadata (`_rm_ingredients`). Each row contains a free-text name, an optional
group label, and separate `mass_quantity`/`mass_unit`,
`volume_quantity`/`volume_unit`, and `optional_quantity`/`optional_unit` fields.
At least one complete measurement is required; multiple measurements may be
supplied as author-provided equivalent amounts. Previously saved preparation
notes are preserved but are no longer edited in this form.
Each supplied measurement needs a positive decimal quantity and a unit
from the corresponding mass or volume catalog, or a free-text `optional_unit`
paired with a positive `optional_quantity` (for example, 2 cloves of garlic).
An optional unit measurement can be the only measurement. Unmeasured entries
are not accepted by this editor. Entirely blank rows are ignored.

Serving adjustments use each original quantity multiplied by desired servings
divided by default servings. Recalculate from the original value and round only
for display.

Ingredients may optionally be organized into groups using a free-text group
name on each ingredient row. The editor shows a heading when the group changes
between adjacent rows. Group inputs do not display suggestion popups.
Authors can reorder rows to keep related ingredients together. Blank group
names remain ungrouped. Group labels are saved with the ingredient rows;
the public recipe page renders adjacent groups with headings.

Example:

**For the chicken**

- Chicken breast
- Salt
- Pepper

**For the sauce**

- Milk
- Jalapeño
- Garlic

---

# Measurement Conversion

Where appropriate, visitors should be able to switch ingredient measurements between:

- Mass measurements
- Volumetric measurements

Examples include conversions between measurements such as:

- grams
- kilograms
- milliliters
- liters
- teaspoons
- tablespoons
- cups

Conversions should preserve reasonable cooking precision.

## Author-Provided Alternate Measurements

The recipe author can optionally provide two equivalent measurements for an
ingredient: one by volume and one by mass. For example, the author might enter
2 cups of flour and an alternate measurement of 240 grams for the same amount.

When a visitor switches between volume and mass measurements, the recipe shows
the corresponding amount supplied by the author. The author is responsible for
providing an accurate equivalence for that ingredient; the website does not
guess its weight from its name or assume that every ingredient weighs the same
per cup.

Both measurements follow serving adjustments. Doubling the example recipe
shows either 4 cups or 480 grams of flour, depending on the visitor's selection.

Alternate measurements are optional. If an ingredient has no alternate
measurement, it keeps its original measurement type when the visitor switches.
Its quantity still adjusts with the number of servings. Supported conversions
within the same measurement type, such as grams to kilograms, remain available
without an alternate measurement.

---

# Instructions

Recipe instructions should consist of ordered steps.

Each step contains:

- Step number/order
- Instruction text
- Optional image

Example:

1. Cut the chicken into bite-sized pieces.
2. Season the chicken.
3. Heat a pan over medium-high heat.
4. Cook the chicken until browned.
5. Prepare the sauce.

Instruction images are optional.

---

# Notes

Recipes can contain a notes/tips section.

This can be used for information such as:

- Ingredient substitutions
- Storage recommendations
- Cooking tips
- Serving suggestions
- Lessons learned while preparing the recipe
- Additional context that does not belong in the instructions

---

# Categories

Recipes will primarily be organized using broad food or main-ingredient categories.

Examples:

- Chicken
- Beef
- Pork
- Fish
- Pasta
- Rice
- Soups
- Desserts

A recipe may belong to one or more categories where appropriate.

---

# Tags

Tags provide additional flexible classification.

Examples:

- Quick
- Spicy
- Cheap
- High-protein
- Nicaraguan
- One-pan
- Oven
- Comfort food

Tags should complement rather than duplicate the primary categories.

---

# Language Strategy

The MVP will publish recipes and general website content in one language,
chosen by the site owner before content entry. English/Spanish content and
visitor language switching are deferred until the core recipe experience is complete.

Theme and plugin interface strings must use standard WordPress translation
functions and consistent text domains. Avoid hard-coded assumptions that would
make later translation unnecessarily difficult.

Do not build a custom language routing system or duplicate English/Spanish
fields for every recipe in the MVP. Before adding bilingual content, evaluate
a free multilingual plugin and verify compatibility with the custom recipe
metadata, taxonomies, and static pages. A paid multilingual integration must
not become a requirement.

Future translated recipe content will be authored by the owner; automatic
machine translation is not a requirement.

---

# Search

Visitors should be able to search the recipe collection.

Search should make it easy to discover recipes based on relevant recipe information such as:

- Recipe title
- Description
- Ingredients
- Categories
- Tags

---

# Filtering

The recipe archive should provide filters that help visitors narrow the recipe collection.

Filtering may include:

- Category
- Ingredient
- Tags
- Cooking/preparation time where practical

Difficulty filtering is explicitly excluded.

Filtering should remain simple enough that it does not significantly delay V1.

---

# Related Recipes

Individual recipe pages should display related recipes.

Related recipes can be determined primarily using shared categories and/or tags.

The implementation should favor a simple WordPress-based solution rather than a complex recommendation system.

---

# Printing

Recipe pages should provide a Print Recipe action.

The print layout should remove unnecessary website elements such as:

- Navigation
- Footer
- Related recipes
- Decorative UI
- Unnecessary images

The printed version should focus on:

- Recipe title
- Servings
- Times
- Ingredients
- Instructions
- Notes

---

# Basic User Journey

A typical visitor journey is:

1. Visitor arrives on the homepage.
2. Visitor sees featured recipes and a prominent recipe search.
3. Visitor searches for a recipe or browses the recipe collection.
4. Visitor optionally filters recipes.
5. Visitor selects a recipe.
6. Visitor reads the recipe.
7. Visitor adjusts the number of servings.
8. Ingredient quantities update automatically.
9. Visitor optionally changes the displayed measurement system.
10. Visitor follows the recipe instructions.
11. Visitor may print the recipe or explore a related recipe.

No authentication should be required at any point.

---

# Pages

## Home

The homepage should emphasize recipe discovery.

Primary content:

- Search bar
- Featured recipes

Additional sections can be introduced as the collection grows, but the initial homepage should remain focused and uncluttered.

---

## All Recipes

Displays the complete recipe collection.

Should support:

- Search
- Filtering
- Recipe cards
- Pagination when necessary

---

## Recipe Page

The primary content page of the website.

Should display:

- Featured image
- Recipe title
- Description
- Preparation time
- Cooking time
- Total time
- Servings
- Serving adjustment controls
- Measurement conversion controls
- Ingredients
- Instructions
- Instruction images
- Notes/tips
- Categories
- Tags
- Print Recipe action
- Related recipes

---

## Category Archive

Displays recipes belonging to a specific category.

Example:

`/recipes/chicken/`

---

## Search Results

Displays recipes matching a visitor's search query.

Where practical, recipe-specific results should be prioritized over unrelated WordPress content.

---

## About

A simple page explaining the author and purpose of the website.

This page can be created using Elementor rather than requiring a custom template.

---

# WordPress Architecture

## Custom Theme

The project will use a lightweight custom WordPress theme.

The theme should be responsible for presentation and browser interactions where custom development provides meaningful value, particularly:

- Recipe templates
- Recipe archive
- Recipe cards
- Recipe-specific frontend interactions
- Responsive styling
- Integration with WordPress content and taxonomies

The theme should not recreate functionality already solved well by WordPress or established plugins without a clear reason.

---

## Elementor

Elementor may be used alongside the custom theme.

It is primarily intended for pages where visual content editing is more valuable than custom development, such as:

- Homepage
- About page
- Other future marketing/static pages

Recipe functionality should remain based on structured WordPress data rather than being manually constructed using Elementor for every recipe.

This provides a practical demonstration of both custom WordPress development and page-builder integration.

---

## Advanced Custom Fields

ACF will be used to manage structured recipe information.

Likely field groups include:

- Recipe information
- Times
- Servings
- Ingredients
- Instruction steps
- Notes

Use **ACF Free** for basic fields such as description, default servings,
preparation time, cooking time, featured status, and notes. Use native WordPress
fields for the title and featured image. Keep field definitions in version
control, using PHP registration in the core plugin or another deliberately
managed export workflow.

ACF Free does not include the Repeater field. Ingredients and instruction
steps will therefore use custom repeatable admin fields supplied by the core
plugin. Do not depend on ACF Pro or paid repeater add-ons.

The custom editor should provide simple Add, Remove, and ordering controls.
Ingredients must preserve separate quantity, unit, and name values, with an
optional group label. Steps must preserve their order, instruction text, and
an optional WordPress media attachment ID. Store these collections as
structured post metadata rather than presentation HTML.

Keep the editor practical for a single author. Validate and sanitize saved
values, check permissions and nonces, handle autosaves/revisions deliberately,
and escape values when rendering them.

---

# Free Plugin Policy

All required third-party plugins must be usable in their free editions for the
features this project needs. Additional implementation work is acceptable when
it avoids a paid dependency. No premium license should be needed to install,
run, or reproduce the MVP.

| Component | Choice | Responsibility |
| :---- | :---- | :---- |
| Structured basic fields | ACF Free | Standard recipe editing fields |
| Static page builder | Elementor Free, optional | About and optionally Home |
| Recipe functionality | Custom Recipe Manager Core plugin | Content model, repeatable fields, reusable logic |
| Public presentation | Custom Recipe Manager theme | Templates, styles, browser controls |
| Multilingual plugin | Deferred | Reassess free options after the MVP |

Do not add a separate CPT registration plugin, paid recipe plugin, ACF Pro,
or a paid multilingual integration. Evaluate extra plugins only when a
concrete requirement justifies them; avoid overlapping responsibilities.

# Custom Development Responsibilities

## Recipe Manager Core Plugin

The `plugins/recipe-manager-core/` plugin owns the content model independently
of the active theme:

- Register the Recipe custom post type and recipe category/tag taxonomies.
- Register the basic ACF fields and provide repeatable ingredient/step editors.
- Save and validate structured recipe metadata.
- Own reusable unit definitions, conversion rules, and supported ingredient
  density data.
- Provide recipe-specific query/search integration where required.

Changing themes must not remove registration of the recipe content model.
The theme reads this data and presents it; it does not register the post type.

## Recipe Manager Theme

The `themes/recipe-manager/` theme owns:

- Recipe single pages, archives, cards, navigation, and responsive styling.
- Search/filter forms and pagination.
- Serving controls and browser-side quantity updates.
- Measurement controls using supported conversion data from the core plugin.
- Related recipe presentation and a print stylesheet/button.
- Compatibility with normal WordPress page content and optional Elementor pages.

Use a small amount of JavaScript for interactive controls. Render readable
original quantities and instructions on the server so recipes remain usable
without JavaScript. Browser interactions must not modify stored recipe data.

## Search and Filtering Approach

Start with normal WordPress requests, query APIs, and server-rendered forms.
Taxonomy filters and pagination should work without JavaScript. Search must
explicitly account for relevant recipe metadata and taxonomy terms where
needed; default WordPress search does not automatically search ACF fields or
custom ingredient collections.

Begin with a straightforward implementation suitable for a small collection.
Avoid a custom search service or extra filtering plugin unless measured needs
justify one. Optional time filters and richer ingredient filtering may follow
the essential search/category/tag experience.

## Measurement Approach

Implement serving scaling from immutable original quantities. Define a small,
explicit set of supported units and conversions. Same-dimension conversions
such as grams to kilograms use known factors. Switching between mass and volume
uses the equivalent measurements provided by the author; leave an ingredient in
its original measurement type when no alternate is provided. Display sensible cooking precision
and preserve nonnumeric quantities such as “to taste” without scaling them.

Do not infer ingredient density from free-text names or promise arbitrary
mass-to-volume conversion. Author-provided alternate measurements apply to the
specific ingredient in that recipe. Serving scaling and supported
same-dimension conversions work independently of alternate measurements.

# Implementation Sequence

1. Build the core plugin and register the Recipe post type and taxonomies.
2. Define ACF Free fields and custom ingredient/instruction editors.
3. Publish a representative recipe and build an accessible, responsive recipe page.
4. Add serving scaling, supported conversions, and print styles.
5. Build the archive, search, filters, and simple related recipe queries.
6. Complete Home/About, optionally using Elementor Free, and featured recipes.
7. Verify the complete visitor journey and owner publishing workflow.

Use representative content to check missing images, optional notes, ingredient
groups, fractional quantities, unsupported conversions, and long instructions.
Check mobile layouts, keyboard controls, printing, and the experience without
JavaScript. Keep the MVP in one content language throughout this sequence.

---

# Development Principles

## Keep V1 Small

Features that do not directly improve the core recipe browsing and cooking experience should be deferred.

## Structured Data Over Presentation Data

Recipe information should be stored structurally whenever functionality depends on it.

For example, an ingredient should not simply be stored as:

`500 g chicken`

when the application needs to manipulate the quantity.

Instead, quantity, unit, and ingredient should be separate values.

## Progressive Enhancement

A recipe should remain understandable even if frontend JavaScript fails.

JavaScript can enhance functionality such as serving scaling and unit conversion, but the underlying recipe content should still be accessible.

## Prefer Existing Solutions

Use WordPress, ACF, Elementor, and suitable lightweight plugins when they solve a problem cleanly.

Custom development should primarily be used where it demonstrates useful WordPress development skills or where existing solutions do not meet the project's requirements.

## Optimize the Visitor Experience

Administrative interfaces can remain simple because the site has a single author.

Development effort should prioritize the quality of the public-facing experience.

## Ship Before Expanding

V1 should be completed before implementing social, account, planning, or community functionality.

Future ideas should be documented rather than added to the V1 scope.

---

# Definition of V1 Complete

V1 is complete when the site owner can publish structured recipes in the selected content language using only free dependencies and a visitor can:

1. Discover recipes through the homepage.
2. Browse all recipes.
3. Search recipes.
4. Filter recipes.
5. Open and read a polished recipe page.
6. Adjust the number of servings.
7. See ingredient quantities scale accordingly.
8. Switch supported ingredient measurements between mass and volume.
9. View instruction steps and optional step images.
10. Read recipe notes.
11. Print a clean version of the recipe.
12. Navigate to related recipes.

The website must work responsively and provide a polished public experience suitable for inclusion in a professional web development portfolio.
