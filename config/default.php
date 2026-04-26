<?php
return [
    'Altcha' => [
        /*
         * HMAC key for signing challenges. Falls back to Security.salt
         * automatically. Only set this if you want a separate key.
         */
        'hmacKey' => null,

        /*
         * Maximum number the client must brute-force to solve the challenge.
         * Higher = more computational work for the client (and bots).
         */
        'maxNumber' => 100000,

        /*
         * Length of the random salt string.
         */
        'saltLength' => 12,

        /*
         * Hash algorithm. Currently only SHA-256 is supported.
         */
        'algorithm' => 'SHA-256',

        /*
         * URL for the altcha-widget web component JS.
         * Change this if you want to self-host the script.
         */
        'jsUrl' => 'https://cdn.jsdelivr.net/npm/altcha@latest/dist/altcha.js',
    ],
];
