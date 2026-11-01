# Recipe Manager

## Project Definition

Recipe Manager is a bilingual public recipe website built with WordPress.

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
- Multilingual content  
- Recipe measurement conversion  
- Recipe serving scaling  
- Print-friendly layouts  
- Elementor integration  
- WordPress taxonomy management

The project should prioritize maintainability and development speed rather than implementing functionality from scratch when WordPress, Elementor, ACF, or another appropriate solution already handles it well.

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
- English and Spanish recipe content  
- Language switching between English and Spanish  
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

This content should be available in both English and Spanish.

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

Ingredients may optionally be organized into groups when useful.

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

Because volume-to-mass conversion depends on ingredient density, conversions that require ingredient-specific information should only be performed when reliable conversion information exists for that ingredient.

The system should not assume that a cup of every ingredient has the same mass.

This feature should therefore be based on structured units and explicitly supported conversions rather than arbitrary mathematical conversion between every possible unit.

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

# Multilingual Content

The website will support:

- English  
- Spanish

The author will provide both versions of recipe content.

Visitors should be able to switch between languages.

The language system should apply to both general website content and recipe content.

The website should not depend on automatic machine translation for recipe content.

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
7. Visitor optionally switches between English and Spanish.  
8. Visitor adjusts the number of servings.  
9. Ingredient quantities update automatically.  
10. Visitor optionally changes the displayed measurement system.  
11. Visitor follows the recipe instructions.  
12. Visitor may print the recipe or explore a related recipe.

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

The theme should be responsible for functionality and presentation where custom development provides meaningful value, particularly:

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

Repeatable structures such as ingredients and instruction steps should use appropriate repeatable fields where available.

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

V1 is complete when the site owner can publish structured recipes in English and Spanish and a visitor can:

1. Discover recipes through the homepage.  
2. Browse all recipes.  
3. Search recipes.  
4. Filter recipes.  
5. Open and read a polished recipe page.  
6. Switch between English and Spanish.  
7. Adjust the number of servings.  
8. See ingredient quantities scale accordingly.  
9. Switch supported ingredient measurements between mass and volume.  
10. View instruction steps and optional step images.  
11. Read recipe notes.  
12. Print a clean version of the recipe.  
13. Navigate to related recipes.

The website must work responsively and provide a polished public experience suitable for inclusion in a professional web development portfolio.