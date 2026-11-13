'use strict';
const assert = require('node:assert/strict');
const { measurement, formatQuantity } = require('../assets/recipe.js');
const units = {
 g: {type:'mass',to_base_factor:1}, kg: {type:'mass',to_base_factor:1000},
 oz: {type:'mass',to_base_factor:28.349523125}, ml: {type:'volume',to_base_factor:1},
 cup: {type:'volume',to_base_factor:250}, tsp: {type:'volume',to_base_factor:5}
};
const row = {mass_quantity:240,mass_unit:'g',volume_quantity:2,volume_unit:'cup',optional_quantity:3,optional_unit:'handfuls'};
const calculate = (r, pref='original', ratio=1, targets={}) => measurement(r,pref,targets,ratio,units);
assert.deepEqual(calculate(row), {quantity:3,unit:'handfuls'});
assert.deepEqual(calculate(row,'volume',2), {quantity:4,unit:'cup'});
assert.deepEqual(calculate(row,'mass',2,{mass:'kg'}), {quantity:0.48,unit:'kg'});
assert.deepEqual(calculate(row,'volume',0.5,{volume:'ml'}), {quantity:250,unit:'ml'});
assert.deepEqual(calculate(row,'volume',1,{volume:'tsp'}), {quantity:100,unit:'tsp'});
const massOnly = {...row,volume_quantity:null};
assert.deepEqual(calculate(massOnly,'volume',2), {quantity:480,unit:'g'});
assert.deepEqual(calculate(row,'mass',1,{mass:'ml'}), {quantity:240,unit:'g'});
const optionalOnly = {...row,mass_quantity:null,volume_quantity:null};
assert.deepEqual(calculate(optionalOnly,'volume',0.5), {quantity:1.5,unit:'handfuls'});
assert.deepEqual(calculate({...row,mass_quantity:28.349523125},'mass',1,{mass:'oz'}), {quantity:1,unit:'oz'});
for(let i=0;i<50;i++) calculate(row,'volume',8/3,{volume:'ml'});
assert.deepEqual(calculate(row), {quantity:3,unit:'handfuls'}); // No mutation or cumulative rounding.
assert.equal(calculate(row,'mass',Infinity),null);
assert.equal(calculate(row,'mass',0),null);
assert.equal(calculate({},'mass'),null);
console.log('Measurement checks passed: scaling, alternates, same-type conversions, fallbacks, immutable originals, and invalid results.');

assert.deepEqual(calculate(row, 'original', 2), {quantity:6,unit:'handfuls'});
assert.deepEqual(calculate(massOnly, 'volume'), {quantity:240,unit:'g'});
assert.deepEqual(calculate({...row,optional_quantity:null}, 'original'), {quantity:240,unit:'g'});
assert.deepEqual(calculate(optionalOnly, 'mass', 2), {quantity:6,unit:'handfuls'});
console.log('Global measurement modes passed: default amounts, missing-mode fallbacks, and scaling.');

for (const [quantity, expected] of [[0.125,'⅛'],[0.25,'¼'],[1/3,'⅓'],[0.375,'⅜'],[0.5,'½'],[0.625,'⅝'],[2/3,'⅔'],[0.75,'¾'],[0.875,'⅞'],[1.5,'1½'],[2,'2'],[0.333,'⅓'],[0.667,'⅔'],[0.3,'0.3'],[0.49,'0.49'],[0.001,'0.001']]) {
 for (const unit of ['', 'cloves', 'cup', 'tbsp', 'tsp', 'lb']) {
  assert.equal(formatQuantity(quantity, unit), expected);
 }
}
for (const unit of ['g','kg','ml','l']) assert.equal(formatQuantity(1.5, unit), '1.5');
assert.equal(formatQuantity(0.5), '0.5'); // Non-ingredient numbers keep decimals.
assert.equal(formatQuantity(calculate(optionalOnly, 'original', 0.5).quantity, ''), '1½');
console.log('Quantity formatting passed: common fractions, mixed numbers, scaling, decimal fallbacks, metric units, and small quantities.');
