<?php

return [
    'enabled'         => env('ELASTICSEARCH_ENABLED', true),
    'host'            => env('ZSLAB_ES_HOST', 'elasticsearch'),
    'port'            => (int) env('ZSLAB_ES_PORT', 9200),
    'scheme'          => env('ZSLAB_ES_SCHEME', 'http'),
    'analyzer_preset' => env('ELASTICSEARCH_ANALYZER_PRESET', 'nori_ngram'),
];
