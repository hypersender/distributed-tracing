<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Header Name
    |--------------------------------------------------------------------------
    |
    | The HTTP header used to identify a request across services.
    | Set on both the incoming request and the outgoing response.
    |
    */
    'header_name' => env('DISTRIBUTED_TRACING_HEADER', 's-request-id'),

];
