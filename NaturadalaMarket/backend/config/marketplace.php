<?php

return [
    'inquiry_notification_email' => env('INQUIRY_NOTIFICATION_EMAIL'),
    'registration_enabled' => (bool) env('MARKETPLACE_REGISTRATION_ENABLED', false)
        && is_file(resource_path('views/legal/terms.blade.php'))
        && is_file(resource_path('views/legal/privacy.blade.php'))
        && ! str_starts_with(env('MARKETPLACE_TERMS_VERSION', 'draft-'), 'draft-')
        && ! str_starts_with(env('MARKETPLACE_PRIVACY_VERSION', 'draft-'), 'draft-'),
    'terms_version' => env('MARKETPLACE_TERMS_VERSION', 'draft-2026-10-10'),
    'privacy_version' => env('MARKETPLACE_PRIVACY_VERSION', 'draft-2026-10-10'),
    'legal_review_notice' => (bool) env('LEGAL_REVIEW_NOTICE', true),
];
