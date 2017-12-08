# Relax Comment Filters

[![Build Status](https://travis-ci.org/wearerequired/relax-comment-filters.svg?branch=master)](https://travis-ci.org/wearerequired/relax-comment-filters)

Forces comments to go through the more liberal post HTML filters, rather than the restrictive comment filters.

## Description

With this plugin users can for example post images into comments which don't get stripped by the default KSES filters. This is done by using `wp_filter_post_kses()` instead of `wp_filter_kses()`.
