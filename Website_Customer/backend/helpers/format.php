<?php

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money($value)
{
    return number_format((int)$value, 0, ',', '.') . 'đ';
}

function duration($hours)
{
    $totalMinutes = (int)round($hours * 60);
    $hoursPart = intdiv($totalMinutes, 60);
    $minutesPart = $totalMinutes % 60;

    return $hoursPart . ' giờ' . ($minutesPart ? ' ' . $minutesPart . ' phút' : '');
}

function old($key, $default = '')
{
    return isset($_POST[$key]) && is_scalar($_POST[$key]) ? e($_POST[$key]) : e($default);
}
