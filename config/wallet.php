<?php

return [
    'currency' => 'VNĐ',

    // Cấu hình mã VietQR ngân hàng thực tế
    'vietqr' => [
        'bank_id' => env('VIETQR_BANK_ID', 'MB'),
        'bank_name' => env('VIETQR_BANK_NAME', 'Ngân hàng Quân Đội (MBBank)'),
        'account_no' => env('VIETQR_ACCOUNT_NO', '0929089332'),
        'account_name' => env('VIETQR_ACCOUNT_NAME', 'LE CHI TAM'),
        'template' => env('VIETQR_TEMPLATE', 'compact2'),
        'qr_image' => 'images/vietqr-mb-lechitam.png',
    ],

    // Các mốc nạp nhanh gợi ý
    'quick_amounts' => [
        50000,
        100000,
        200000,
        500000,
        1000000,
    ],
];
