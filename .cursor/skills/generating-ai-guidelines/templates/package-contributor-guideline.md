# Template: Package Contributor Guideline

File: `.ai/guidelines/{area}.blade.php`

Same format as the app guideline, but scoped to contributors working on the package itself rather than consumers of it.

```blade
## State Machine

- States and transitions are defined in `src/StateMachine/`.
- Add new states by extending `AbstractState`; register them in `StateMachineServiceProvider`.
- All transition logic must be covered by a Feature test in `tests/StateMachine/`.

### Skills
- Activate `state-machine-development` when adding or modifying states or transitions.
```
