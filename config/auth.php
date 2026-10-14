<?php

return [
    'jwt_secret' => env('JWT_SECRET'),
    'jwt_ttl' => (int) env('JWT_TTL', 480),
];
