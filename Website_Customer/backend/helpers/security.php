<?php

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_verify($token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function normalize_phone($phone): string
{
    return preg_replace('/[\s.\-]/', '', (string)$phone);
}

function validate_phone($phone): bool
{
    return (bool)preg_match('/^(0|\+84)\d{9,10}$/', normalize_phone($phone));
}
