<?php
return [
    'app' => [
        'name' => 'Jira Clone',
    ],

    'models' => [
        [
            'name'      => 'Project',
            'nav_icon'  => 'heroicon-o-folder',
            'nav_group' => 'Projects',
            'fields' => [
                ['name' => 'name',  'type' => 'string'],
                ['name' => 'key',   'type' => 'string', 'length' => 10],
                ['name' => 'icon',  'type' => 'string', 'default' => '📋'],
                ['name' => 'type',  'type' => 'enum', 'options' => ['scrum','kanban'], 'default' => 'scrum'],
            ],
            'has_many' => ['Sprint', 'Issue'],
        ],

        [
            'name'      => 'Sprint',
            'nav_icon'  => 'heroicon-o-play',
            'nav_group' => 'Projects',
            'fields' => [
                ['name' => 'project',    'type' => 'belongs_to'],
                ['name' => 'name',       'type' => 'string'],
                ['name' => 'status',     'type' => 'enum', 'options' => ['planning','active','completed'], 'default' => 'planning'],
                ['name' => 'start_date', 'type' => 'date', 'nullable' => true],
                ['name' => 'end_date',   'type' => 'date', 'nullable' => true],
            ],
            'has_many' => ['Issue'],
        ],

        [
            'name'      => 'Issue',
            'nav_icon'  => 'heroicon-o-bug-ant',
            'nav_group' => 'Issues',
            'fields' => [
                ['name' => 'project',      'type' => 'belongs_to'],
                ['name' => 'sprint',       'type' => 'belongs_to', 'nullable' => true],
                ['name' => 'key',          'type' => 'string'],
                ['name' => 'title',        'type' => 'string'],
                ['name' => 'description',  'type' => 'text', 'nullable' => true],
                ['name' => 'type',         'type' => 'enum', 'options' => ['story','bug','task','epic'], 'default' => 'task'],
                ['name' => 'status',       'type' => 'enum', 'options' => ['todo','in_progress','in_review','done'], 'default' => 'todo'],
                ['name' => 'priority',     'type' => 'enum', 'options' => ['lowest','low','medium','high','highest'], 'default' => 'medium'],
                ['name' => 'assignee',     'type' => 'string', 'nullable' => true],
                ['name' => 'story_points', 'type' => 'integer', 'nullable' => true],
                ['name' => 'due_date',     'type' => 'date', 'nullable' => true],
            ],
        ],
    ],

    'stats' => [
        ['label' => 'Total Issues',  'query' => 'Issue::count()',                              'icon' => 'heroicon-o-clipboard-document-list', 'color' => 'primary'],
        ['label' => 'In Progress',   'query' => "Issue::where('status','in_progress')->count()",'icon' => 'heroicon-o-arrow-path',              'color' => 'warning'],
        ['label' => 'Done',          'query' => "Issue::where('status','done')->count()",       'icon' => 'heroicon-o-check-circle',             'color' => 'success'],
        ['label' => 'Bugs',          'query' => "Issue::where('type','bug')->count()",          'icon' => 'heroicon-o-bug-ant',                  'color' => 'danger'],
    ],
];
