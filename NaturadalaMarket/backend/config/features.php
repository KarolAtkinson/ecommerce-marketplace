<?php

return [
    // While true, the retailer workspace, retailer application and partner prices are closed to everyone except administrators.
    'retailer_maintenance' => env('RETAILER_MAINTENANCE', true),

    'subscriber_discount_percent' => (int) env('SUBSCRIBER_DISCOUNT_PERCENT', 10),
];
