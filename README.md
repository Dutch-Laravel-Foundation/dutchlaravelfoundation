# Dutch Laravel Foundation website

## Installation

...

## LLM / Agent integration

This site exposes content to LLMs and agent frameworks:

- `robots.txt` — AI bot rules and Cloudflare Content Signals
- `/llms.txt` and `/llms-full.txt` — index and full dump of curated content
- `/.well-known/mcp.json` — MCP server discovery card
- Markdown negotiation — append `.md` to any content URL, or send `Accept: text/markdown`
- MCP server — `/mcp` (rate limited to 60 req/min per IP)

After adding or editing insights/knowledge/events/internships/cases content, rebuild the search index used by the `search_content` MCP tool:

```
php please search:update --all
```

The llms.txt caches are invalidated automatically on Statamic `EntrySaved` / `EntryDeleted` events.
