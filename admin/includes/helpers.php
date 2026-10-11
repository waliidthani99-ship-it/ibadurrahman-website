<?php
/**
 * Shared UI helpers for the admin panel.
 */

if (!function_exists('status_badge')) {
    function status_badge($status) {
        $map = [
            "Pending"  => ["bg-amber-100",  "text-amber-700",  "fa-hourglass-half"],
            "Approved" => ["bg-emerald-100","text-emerald-700","fa-circle-check"],
            "Rejected" => ["bg-rose-100",   "text-rose-700",   "fa-circle-xmark"],
        ];
        $c = isset($map[$status]) ? $map[$status] : ["bg-gray-100", "text-gray-600", "fa-circle"];
        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold '
            . $c[0] . ' ' . $c[1] . '"><i class="fas ' . $c[2] . '"></i>' . sanitize($status) . '</span>';
    }
}

if (!function_exists('status_dot')) {
    function status_dot($status) {
        $map = ["Pending" => "bg-amber-500", "Approved" => "bg-emerald-500", "Rejected" => "bg-rose-500"];
        $c = isset($map[$status]) ? $map[$status] : "bg-gray-400";
        return '<span class="inline-block w-2 h-2 rounded-full ' . $c . '"></span>';
    }
}

if (!function_exists('name_initials')) {
    function name_initials($name) {
        $name = trim((string)$name);
        if ($name === "") return "?";
        $parts = preg_split('/\s+/', $name);
        $out = "";
        foreach ($parts as $p) {
            if ($p === "") continue;
            $out .= mb_substr($p, 0, 1);
            if (mb_strlen($out) >= 2) break;
        }
        return mb_strtoupper($out);
    }
}

if (!function_exists('avatar_color')) {
    function avatar_color($seed) {
        $palette = [
            "bg-emerald-500", "bg-teal-500", "bg-sky-500", "bg-indigo-500",
            "bg-violet-500", "bg-fuchsia-500", "bg-rose-500", "bg-amber-500",
        ];
        $i = abs((int)$seed) % count($palette);
        return $palette[$i];
    }
}

if (!function_exists('initial_avatar')) {
    function initial_avatar($name, $seed = 0, $size = "w-10 h-10") {
        return '<span class="inline-flex items-center justify-center ' . $size . ' rounded-full text-white font-semibold text-sm '
            . avatar_color($seed) . ' shrink-0">' . sanitize(name_initials($name)) . '</span>';
    }
}

if (!function_exists('admin_empty_state')) {
    function admin_empty_state($title, $message, $icon = "fa-inbox") {
        echo '<div class="flex flex-col items-center justify-center text-center py-14 px-6">'
            . '<div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-4">'
            . '<i class="fas ' . $icon . '"></i></div>'
            . '<p class="font-semibold text-gray-700">' . sanitize($title) . '</p>'
            . '<p class="text-sm text-gray-500 mt-1 max-w-sm">' . sanitize($message) . '</p>'
            . '</div>';
    }
}
