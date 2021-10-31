<?php

return [
    'enable_encryption' => true,
    'encrypt_method' => 'aes-128-ecb',
    'encrypt_key' => env('APP_KEY', null)
];