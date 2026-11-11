<?php

require_once __DIR__ . '/../classes/FitClass.php';
require_once __DIR__ . '/Product.php';

function fit_get_measurements_ctr($userId)
{
    return (new FitClass())->getMeasurements($userId);
}

function fit_validate_measurements_post(array $post)
{
    $errors = [];
    $values = [];
    $filled = 0;

    foreach (FitClass::BODY_RANGES as $dimension => $range) {
        $parsed = tgh_parse_measurement($post[$dimension] ?? '', $range[0], $range[1]);
        if (!$parsed['valid']) {
            $errors[] = ucfirst($dimension) . ' must be a number between ' . $range[0] . ' and ' . $range[1] . ' cm.';
        } else {
            $values[$dimension] = $parsed['value'];
            if (!$parsed['empty']) {
                $filled++;
            }
        }
    }

    if (!$errors && $filled === 0) {
        $errors[] = 'Enter at least one measurement.';
    }

    return ['errors' => $errors, 'values' => $values];
}

function fit_save_measurements_ctr($userId, array $post)
{
    $checked = fit_validate_measurements_post($post);
    if ($checked['errors']) {
        return ['ok' => false, 'errors' => $checked['errors']];
    }
    if (!(new FitClass())->saveMeasurements($userId, $checked['values'])) {
        return ['ok' => false, 'errors' => ['Your measurements could not be saved. Please try again.']];
    }
    return ['ok' => true];
}

function fit_delete_measurements_ctr($userId)
{
    if (!(new FitClass())->deleteMeasurements($userId)) {
        return ['ok' => false, 'errors' => ['Your measurements could not be deleted. Please try again.']];
    }
    return ['ok' => true];
}

function fit_get_matches_ctr($userId, $garmentType)
{
    $saved = fit_get_measurements_ctr($userId);
    if (!$saved) {
        return ['has_measurements' => false, 'matches' => [], 'unavailable_types' => []];
    }

    $body = [
        'chest' => $saved['chest_cm'],
        'waist' => $saved['waist_cm'],
        'hips' => $saved['hips_cm'],
        'inseam' => $saved['inseam_cm']
    ];

    $type = array_key_exists((string)$garmentType, FitClass::TYPE_LABELS) ? $garmentType : null;

    $unavailable = [];
    foreach (FitClass::TYPE_LABELS as $key => $label) {
        $missing = FitClass::missingFor($key, $body);
        if ($missing) {
            $unavailable[$key] = ['label' => $label, 'missing' => $missing];
        }
    }

    if ($type !== null && isset($unavailable[$type])) {
        return ['has_measurements' => true, 'matches' => [], 'unavailable_types' => $unavailable];
    }

    return [
        'has_measurements' => true,
        'matches' => (new FitClass())->findMatches($userId, $body, $type),
        'unavailable_types' => $unavailable
    ];
}
