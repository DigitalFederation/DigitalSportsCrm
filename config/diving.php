<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Certification systems
    |--------------------------------------------------------------------------
    |
    | The diving certification agencies this installation recognizes (e.g.
    | PADI, SSI, CMAS, SDI_TDI, DDI, GUE). This is deployment-specific data —
    | the platform takes no position on which agencies exist — so it is empty
    | by default. Each installation sets its own via the comma-separated
    | DIVING_CERTIFICATION_SYSTEMS environment variable.
    |
    */

    'certification_systems' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('DIVING_CERTIFICATION_SYSTEMS', ''))
    ))),

];
