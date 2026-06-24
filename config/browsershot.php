<?php

return [
    /*
     * Path to the Chrome executable for Browsershot
     * Leave null to auto-detect, or specify path for production
     */
    'chrome_path' => env('BROWSERSHOT_CHROME_PATH', null),

    /*
     * Node binary path
     */
    'node_path' => env('BROWSERSHOT_NODE_PATH', null),

    /*
     * NPM binary path
     */
    'npm_path' => env('BROWSERSHOT_NPM_PATH', null),
];
