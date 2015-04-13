# xhprof-graph
XHProf + Neo4J

Parses `*.xhprof` files and stores to Neo4J for reporting, tracking and analytics. It's in a minimally-functioning state right now.

## Requirements
1. [Composer](https://getcomposer.org/)

## Setup
1. `git submodule init && git submodule update`
2. `composer install`



View on a per-run basis (per-pageview).
![Runs](gh/runs.png)

Most expensive classes (average across all runs).
![Classes](gh/classes.png)
