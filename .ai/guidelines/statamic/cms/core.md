## Statamic

- This site uses Statamic. Content lives in flat files under `content/` (collections, globals, navigation, trees). Users live in `users/`.
- Blueprints and fieldsets in `resources/blueprints` and `resources/fieldsets` define the content schema. Check them before you change authored fields.
- Use `php please` for Statamic commands. Add `--help` to inspect a command.
- Extend Statamic with tags, modifiers and fieldtypes (`php please make:...`), not by editing vendor code.
- The Control Panel (`/cp`) is built with Vue 3. The public site is React/Inertia.
- Docs: https://statamic.dev/llms.txt
