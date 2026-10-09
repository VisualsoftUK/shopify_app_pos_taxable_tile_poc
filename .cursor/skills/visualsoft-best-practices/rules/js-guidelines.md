# JavaScript Guidelines

Visualsoft-specific JavaScript conventions. These extend any Boost JS/frontend skills — when there is a conflict, these take precedence.

## Variables & Scope

- Never use `var`. Use `const` by default; use `let` only when reassignment is strictly necessary.
- Use `camelCase` for all variables and function names.
- Use full words for identifiers — `index` not `i`, `response` not `res`.
- Variable names must clearly describe their intent and the data they hold.
- Use `{ name }` object shorthand instead of `{ name: name }`.

## Strings

- Use single quotes `'` for strings. Use double quotes or backticks only when the content requires them.
- Use template literals `` `${value}` `` instead of `+` concatenation.

## Conditionals & Control Flow

- Always use `===` for comparisons — never `==`.
- Replace deep `else-if` chains with `switch` statements, object lookups, or early returns.
- Avoid nested ternary operators. If logic is complex, use separate variables or helper functions.

## Async

- Use `async`/`await` with `try`/`catch`. Avoid `.then()`/`.catch()` chains.
- Run independent async operations in parallel with `Promise.all()`.
- Ensure `catch` blocks provide descriptive, context-rich error messages.

## Objects & Safety

- Use `?.` optional chaining to safely access deeply nested properties.
- Use `??` (nullish coalescing) for `null`/`undefined` fallbacks — not `||`, which incorrectly overrides `0` and `false`.
- Use modern ES6 features (destructuring, spread, etc.) unless they reduce readability.

## File Structure

- Keep `/components` exclusive to `.jsx` files.
- Move pure logic to `/utils` or `/hooks`.
- Name React component files with `PascalCase` (e.g. `OrderCard.jsx`).
- Name utility/logic files with `camelCase` (e.g. `apiHelpers.js`).

## Documentation

Every function and custom hook must have a JSDoc block with `@param` (including types and descriptions) and `@returns`. Add an `@example` block for complex logic.

```js
/**
 * Formats a price value for display.
 *
 * @param {number} amount - The raw price in pence.
 * @param {string} currency - ISO 4217 currency code.
 * @returns {string} Formatted price string (e.g. "£12.99").
 * @example
 * formatPrice(1299, 'GBP') // "£12.99"
 */
function formatPrice(amount, currency) { ... }
```
