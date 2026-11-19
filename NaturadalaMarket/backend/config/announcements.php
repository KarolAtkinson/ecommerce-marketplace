<?php

/*
 * Text holder for the announcement bar slideshow.
 *
 * Add, edit or remove entries here. The bar sorts them A-Z by "text"
 * automatically, so the order in this file does not matter.
 * "url" and "label" are optional; both are needed to show a link.
 * "url" accepts a path, a full URL or a mailto: address.
 */
return [
    'interval_seconds' => 20,

    'messages' => [
        ['text' => 'Masz pytania?', 'label' => 'Napisz do nas', 'url' => 'mailto:kontakt@naturadala.pl'],
        ['text' => 'Naturalne wsparcie. Dobra współpraca.'],
        ['text' => 'Polski produkt. Prosty skład.'],
        ['text' => 'Poznaj koncentrat kolagenowy Naturadała', 'label' => 'Zobacz ofertę', 'url' => '/products'],
        ['text' => 'Zapytaj o ofertę hurtową', 'label' => 'Skontaktuj się', 'url' => '/#kontakt'],
    ],
];
