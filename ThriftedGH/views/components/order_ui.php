<?php
if (!function_exists('tg_short_ref')) {
    function tg_short_ref($ref) { return strtoupper(substr((string)$ref, 0, 12)); }
    function tg_when($value) { $t = strtotime((string)$value); return $t ? date('j M Y, g:i A', $t) : ''; }
    function tg_img($basePath, $path) { return tgh_e($basePath . tgh_catalogue_image($path)); }
    function tg_stage($items) {
        $rank = ['pending' => 0, 'processing' => 1, 'shipped' => 2, 'delivered' => 3];
        if (!$items) return 0;
        $min = 3;
        foreach ($items as $i) $min = min($min, $rank[$i['fulfilment_status']] ?? 0);
        return $min;
    }
    function tg_status($o, $items = null) {
        if (!empty($o['payment_review'])) return ['Needs review', 'review'];
        $s = $o['payment_status'] ?? '';
        if ($s === 'paid') {
            if ($items && tg_stage($items) === 3) return ['Delivered', 'paid'];
            return ['Paid', 'paid'];
        }
        if ($s === 'pending') return ['Awaiting payment', 'pending'];
        return ['Not completed', 'failed'];
    }
    function tg_fulfilment_label($status) {
        return ['pending' => 'Waiting for seller', 'processing' => 'Being prepared', 'shipped' => 'On the way', 'delivered' => 'Delivered'][$status] ?? ucfirst((string)$status);
    }
    function tg_timeline($labels, $done) {
        echo '<ol class="tg-timeline">';
        foreach ($labels as $i => $label) {
            $n = $i + 1;
            $class = $n <= $done ? 'is-done' : ($n === $done + 1 ? 'is-current' : '');
            echo '<li class="' . $class . '">' . tgh_e($label) . '</li>';
        }
        echo '</ol>';
    }
}
