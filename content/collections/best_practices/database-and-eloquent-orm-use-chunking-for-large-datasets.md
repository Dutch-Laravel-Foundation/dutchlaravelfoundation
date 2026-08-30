---
id: 95cd5e77-6d7a-55d8-bb0d-e7726f30515d
blueprint: best_practices
title: 'Use Chunking for Large Datasets'
summary: 'Loading thousands of records into memory at once can cause memory exhaustion and slow response times. Laravel provides several chunking and lazy collection strategies — chunk(), chunkById(), cursor(), lazy(), and lazyById() — each suited to...'
chapters:
  - title: Introduction
    anchor: introduction
  - title: Why
    anchor: why
  - title: 'Suitable For'
    anchor: suitable-for
  - title: 'Less Suitable'
    anchor: less-suitable
  - title: Examples
    anchor: examples
  - title: 'More Info'
    anchor: more-info
best_practice_categories:
  - database-and-eloquent-orm
category_slug: database-and-eloquent-orm
category_title: 'Database & Eloquent ORM'
source_path: database-and-eloquent-orm/use-chunking-for-large-datasets.md
source_sha: c0967a7f4ce3d79bcd2f427ac0c16eb1b20adff8
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c0967a7f4ce3d79bcd2f427ac0c16eb1b20adff8/database-and-eloquent-orm/use-chunking-for-large-datasets.md'
boost_skill_path: null
related_files: []
---
<a name="introduction"></a>
## Introduction

Loading thousands of records into memory at once can cause memory exhaustion and slow response times. Laravel provides several chunking and lazy collection strategies — `chunk()`, `chunkById()`, `cursor()`, `lazy()`, and `lazyById()` — each suited to different scenarios depending on whether you need relationships, are modifying records, or prioritize memory efficiency.

<a name="why"></a>
## Why

- **Prevents memory exhaustion**: Processing records in smaller batches keeps memory usage predictable
- **Safe during mutations**: `chunkById()` and `lazyById()` use `id > last_id` instead of `OFFSET`, preventing skipped or duplicated records when modifying data during iteration
- **Memory-efficient reads**: `cursor()` holds only one model in memory at a time via a PHP generator
- **Relationship support**: `lazy()` supports eager loading while still chunking, unlike `cursor()`

<a name="suitable-for"></a>
## Suitable For

- Batch processing large datasets (imports, exports, notifications)
- Scheduled commands processing many records
- Reports or data transformations on large tables
- Any operation iterating over more than a few hundred records

<a name="less-suitable"></a>
## Less Suitable

- Small datasets where loading all records at once is fine
- Queries that return a limited number of records by design

<a name="examples"></a>
## Examples

### Basic Chunking

```php
// Bad: loads everything into memory
$users = User::all();
foreach ($users as $user) {
    $user->notify(new WeeklyDigest);
}

// Good: processes in batches of 200
User::where('subscribed', true)->chunk(200, function ($users) {
    foreach ($users as $user) {
        $user->notify(new WeeklyDigest);
    }
});
```

### Use `chunkById()` When Modifying Records

Standard `chunk()` uses `OFFSET` which shifts when rows change. `chunkById()` uses `id > last_id`, which is safe against mutation:

```php
User::where('active', false)->chunkById(200, function ($users) {
    $users->each->delete();
});
```

### Choosing Between `cursor()` and `lazy()`

- `cursor()` — one model in memory at a time, but cannot eager-load relationships (N+1 risk)
- `lazy()` — chunked pagination returning a flat `LazyCollection`, supports eager loading

```php
// Good: attribute-only work, maximum memory efficiency
foreach (User::where('active', true)->cursor() as $user) {
    ProcessUser::dispatch($user->id);
}

// Good: when you need relationships
foreach (User::with('roles')->lazy() as $user) {
    echo $user->roles->count();
}
```

### Use `lazyById()` When Updating During Iteration

`lazy()` uses offset pagination — updating records during iteration can skip or double-process. `lazyById()` uses `id > last_id`, safe against mutation:

```php
User::where('needs_update', true)->lazyById()->each(function ($user) {
    $user->update(['needs_update' => false]);
});
```

<a name="more-info"></a>
## More Info

- [Laravel Chunking Results Documentation](https://laravel.com/docs/eloquent#chunking-results)
- [Laravel Lazy Collections Documentation](https://laravel.com/docs/eloquent#lazy-collection)
- [Prevent N+1 Queries](./prevent-n-plus-one-queries.md) — for eager loading and query optimization
- [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
