<?php

return [
    'debug' => true,
    'services' => [
        'music_searcher' => 'Spotify',
        'music_provider' => 'YouTubeInMp3',
    ],
    'twig' => [
        'globals' => [
            'sitename' => 'XMusic'
        ]
    ]
];
