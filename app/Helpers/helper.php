<?php

if (! function_exists('encryptOpenSSL')) {
    /**
     * Encrypts a given value using OpenSSL, with an initialization vector (IV).
     * The encrypted value is URL-safe.
     *
     * @param  string  $plainValue  The value to encrypt
     * @return string The encrypted value, with the IV prepended and URL-safe encoded
     */
    function encryptOpenSSL(string $plainValue): string
    {
        $method = config('openssl.method'); // You can use AES-128, AES-192, etc.
        $key = config('openssl.key');
        // Generate an initialization vector (IV) for AES
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
        // Encrypt the data
        $encrypted = openssl_encrypt($plainValue, $method, $key, 0, $iv);
        // Combine the IV and encrypted string (needed for decryption)
        $combined = $iv.$encrypted;

        // Use URL-safe base64 encoding
        return base64url_encode($combined);
    }
}

if (! function_exists('decryptOpenSSL')) {
    /**
     * Decrypts a given value using OpenSSL, with an initialization vector (IV).
     * The input value is expected to be URL-safe encoded.
     *
     * @param  string  $encryptedWithIvValue  The value to decrypt, with the IV prepended and URL-safe encoded
     * @return string|false The decrypted value, or `false` on failure
     */
    function decryptOpenSSL(string $encryptedWithIvValue): false|string
    {
        $method = config('openssl.method'); // You can use AES-128, AES-192, etc.
        $key = config('openssl.key');
        // Decode the URL-safe base64 encoded string
        $decodedData = base64url_decode($encryptedWithIvValue);
        $iv = substr($decodedData, 0, openssl_cipher_iv_length($method));
        $encryptedData = substr($decodedData, openssl_cipher_iv_length($method));

        return openssl_decrypt($encryptedData, $method, $key, 0, $iv);
    }
}

if (! function_exists('base64url_encode')) {
    /**
     * Encodes data using URL-safe base64 encoding.
     *
     * @param  string  $data  The data to encode
     * @return string The URL-safe base64 encoded data
     */
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (! function_exists('base64url_decode')) {
    /**
     * Decodes data using URL-safe base64 encoding.
     *
     * @param  string  $data  The URL-safe base64 encoded data
     * @return string The decoded data
     */
    function base64url_decode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

function format_number(int $number): string
{
    if ($number < 1000) {
        return (string) Number::format($number, 0);
    }

    if ($number < 1000000) {
        return Number::format($number / 1000, 2).'k';
    }

    return Number::format($number / 1000000, 2).'M';
};
