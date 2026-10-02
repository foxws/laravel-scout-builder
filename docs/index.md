---
title: Introduction
metadata:
  role: Search
  group: search
  eyebrow: "Laravel Scout · Query Builder · API Filters"
  desc: "Build safe Laravel Scout search queries straight from API requests."
  requires: "PHP ^8.4"
  laravel: "12.x / 13.x"
  licence: MIT
---

# Introduction

[Laravel Scout](https://laravel.com/docs/scout) is Laravel's search package.
This package adds a query builder on top of it, so you can turn HTTP request
parameters into safe search queries.

It's modeled closely on
[spatie/laravel-query-builder](https://github.com/spatie/laravel-query-builder)
and uses the same `AllowedFilter` / `AllowedSort` style. If you already know
that package, this one will feel familiar — it's just built for Scout
instead of Eloquent.

With it, API clients can filter, sort, include relationships, and paginate
search results, all through query string parameters, without you writing
that logic by hand for every endpoint.

> **Credits** — This package is a close adaptation of
> spatie/laravel-query-builder. All credit for the original architecture,
> patterns, and API design belongs to [Spatie](https://spatie.be). If this
> package is useful to you, please consider
> [supporting Spatie](https://spatie.be/open-source/support-us).

Continue to [Installation](./installation.md) to get started.
