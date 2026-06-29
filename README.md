# Dutch Laravel Foundation website

## Installation

...

## LLM / Agent integration

This site exposes content to LLMs and agent frameworks:

- `robots.txt` — AI bot rules and Cloudflare Content Signals
- `/llms.txt` and `/llms-full.txt` — index and full dump of curated content
- Markdown negotiation — append `.md` to any content URL, or send `Accept: text/markdown`

The llms.txt caches are invalidated automatically on Statamic `EntrySaved` / `EntryDeleted` events.
