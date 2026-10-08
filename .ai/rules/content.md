---
paths:
  - 'app/Content/**'
---

# Content

## Statamic content boundary
Public content is authored in Statamic. Extend the matching family repository and mapper rather than replacing this boundary with Eloquent or hardcoded React content. GraphQL field selections must follow the entry blueprint; changing authored fields also requires checking resources/blueprints and existing content.
