<?php
/**
 * A Tag is a generic way to group nodes.
 * Tags point to associated nodes (think: git tags) and represent arbitrary
 * groupings.
 * @example
 * (n:Tag {name: 'Benchmark-before refactor'})
 * (n:Tag {name: '@todo-fix these!'})
 * etc...
 * 
 * I'm a graph DB n00b, but I'm thinking allowing different labels will help 
 * with search performance later. In other words, specific types of tags 
 * should extend this class and apply their own label. I'm not sure if this is
 * the best idea or not:
 * (n:TagBenchmark {name: 'Before refactor'})
 * (n:TagTodo {name: 'Whatever', priority:'High'})
 */
class Tag {
    
}