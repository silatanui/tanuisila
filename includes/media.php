<?php
/**
 * Shared media placeholder markup for missing / broken images.
 */
if (!function_exists('media_placeholder')) {
  function media_placeholder(string $label = '', string $class = ''): string
  {
    $letter = '';
    $trim = trim($label);
    if ($trim !== '') {
      if (function_exists('mb_substr')) {
        $letter = mb_strtoupper(mb_substr($trim, 0, 1, 'UTF-8'), 'UTF-8');
      } else {
        $letter = strtoupper(substr($trim, 0, 1));
      }
    }

    $classes = trim('media-ph ' . $class);
    $inner = $letter !== ''
      ? '<span class="media-ph__letter">' . htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') . '</span>'
      : '<span class="media-ph__icon" aria-hidden="true"></span>';

    return '<span class="' . htmlspecialchars($classes, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true">'
      . '<span class="media-ph__bg"></span>'
      . '<span class="media-ph__pattern"></span>'
      . $inner
      . '</span>';
  }
}
