<?php

declare(strict_types=1);

return [

    'validation' => [
        'playlists' => [
            'categories' => [
                'required' => 'Please add at least one category.',
                'invalid_format' => 'Categories must be a list.',
                'min' => 'Please add at least one category.',
                'item_required' => 'Category name cannot be empty.',
                'item_string' => 'Category must be text.',
                'item_max' => 'Category name is too long (max 100 characters).',
                'duplicate' => 'Duplicate categories are not allowed.',
            ],
        ],
    ],

    'pages' => [
        'playlists' => [
            'title' => 'Collect Educational Playlists from YouTube',
            'sub_title' => 'Enter categories and click Start - The system will automatically collect playlists using AI',
            'search_started' => 'Playlist search started successfully!',
            'fields' => [
                'search-box' => [
                    'title' => 'Enter Categories',
                    'placeholder' => 'Write category name then press Enter',
                ],
                'search-titles' => 'Search Titles',
                'buttons' => [
                    'start' => 'Start',
                    'stop' => 'Stop',
                ],
            ],

            'results' => [
                'title' => 'Discovered Playlists',
                'stats' => 'Found :count playlists in :cat_count categories',
            ],

            'empty_state' => [
                'title' => 'No Playlists Found',
                'message' => 'We couldn\'t find any playlists for the selected categories. Try different search terms.',
            ],

            'card' => [
                'lessons_label' => 'lesson',
                'views_label' => 'views',
            ],
        ],
    ],
];
