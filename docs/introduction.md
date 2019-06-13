---
title: Introduction
weight: 1
---

Event sourcing is to data what Git is to code. Most applications only have their current state stored in a database. A lot of useful information gets lost: you don't know _how_ the application got to this state.

Event sourcing tries to solve this problem by storing all events that happen in your app. The state of your application is built by listening to those events.

Here's a traditional example to make it more clear. Imagine you're a bank. Your clients have accounts. Storing the balance of the accounts wouldn't be enough, all the transactions should be remembered too. With event sourcing, the balance isn't a standalone database field, but a value calculated from the stored transactions. This is only one of the many benefits event sourcing brings to the table.

This package aims to be the simple and very pragmatic way to get started with event sourcing in Laravel.

Are you a visual learner? Here's a video that explains the high level concepts op the package: [introduction to Laravel Event Projector video](https://www.youtube.com/watch?v=28jmTeN3VYc).

## We have badges!

<section class="article_badges">
    <a href="https://github.com/spatie/laravel-event-projector/releases"><img src="https://img.shields.io/github/release/spatie/laravel-event-projector.svg?style=flat-square" alt="Latest Version"></a>
    <a href="https://github.com/spatie/laravel-event-projector/blob/master/LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License"></a>
    <a href="https://travis-ci.org/spatie/laravel-event-projector"><img src="https://img.shields.io/travis/spatie/laravel-event-projector/master.svg?style=flat-square" alt="Build Status"></a>
    <a href="https://scrutinizer-ci.com/g/spatie/laravel-event-projector"><img src="https://img.shields.io/scrutinizer/g/spatie/laravel-event-projector.svg?style=flat-square" alt="Quality Score"></a>
    <a href="https://packagist.org/packages/spatie/laravel-event-projector"><img src="https://img.shields.io/packagist/dt/spatie/laravel-event-projector.svg?style=flat-square" alt="Total Downloads"></a>
</section>
