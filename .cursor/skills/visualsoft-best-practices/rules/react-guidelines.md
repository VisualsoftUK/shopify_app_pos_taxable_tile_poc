# React Guidelines

Visualsoft-specific React conventions. Apply these **in addition** to `rules/js-guidelines.md` when working in React files. When there is a conflict with Boost's `inertia-react-development` skill, these take precedence.

## Components

- Abstract logical UI blocks into standalone components — avoid large chunks of inline JSX within a single file.
- Destructure props in the component signature.
- Use `<></>` shorthand for fragments unless a `key` attribute is required.
- Always use self-closing tags for components with no children: `<MyComponent />`.
- No inline styles — avoid `style={{}}`. Use CSS classes and external stylesheets.
- Handle edge cases and unhappy paths with early returns to keep the core render logic unindented and clear.

## Skeletons

- Every component that fetches or defers data must have a matching loading skeleton.
- When modifying a component's layout, update its skeleton simultaneously to prevent layout shift.

## Naming

- Prefix boolean variables and props with `is`, `has`, or `should` — e.g. `isLoading`, `hasError`, `shouldRedirect`.
- Name internal event handlers `handle[Event]` — e.g. `handleClick`, `handleSubmit`.
- Name event handler props `on[Event]` — e.g. `onClick`, `onSubmit`.

## Performance

- Wrap expensive calculations in `useMemo`.
- Wrap stable callbacks passed as props in `useCallback` to prevent unnecessary re-renders.

## Imports

Organise imports in this order:
1. React
2. External libraries (Polaris, Inertia, etc.)
3. Internal components
4. Styles / assets

## Polaris (Shopify apps)

- Prefer `@shopify/polaris` components over raw HTML elements (`div`, `button`, etc.).
- Always import `Modal` from `@shopify/app-bridge-react`, not from the core Polaris library.
