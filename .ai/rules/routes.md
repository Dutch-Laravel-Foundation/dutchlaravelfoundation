---
paths:
  - 'routes/**'
---

# Routes

## Public cache and Statamic routing
Public React routes use CachePublicResponse, inertia, and statamic.web. Statamic static cache is deliberately removed from statamic.web in AppServiceProvider so it cannot bypass the public response-cache policy. Preserve control-panel routing and SSR exclusion when changing public routing. Use the existing invalidation listener for authored content changes.
