<?php
return [
    'secret' => getenv('JWT_SECRET'),
    'algorithm' => 'HS256',
    'expiration' => (int)getenv('JWT_EXPIRATION') ?: 3600,
];
