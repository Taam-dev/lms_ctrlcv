<?php

return [
    'required' => ':Attribute không được để trống.',
    'email' => ':Attribute không đúng định dạng.',
    'date' => ':Attribute không đúng định dạng ngày tháng.',
    'after_or_equal' => ':Attribute phải là ngày sau hoặc bằng :date.',
    'min' => [
        'string' => ':Attribute phải có tối thiểu :min ký tự.',
    ],

    'custom' => [
        'email' => [
            'unique' => 'Email đã được sử dụng',
            'required' => 'Vui lòng nhập email.',
            'email' => 'Địa chỉ email không đúng định dạng.',
        ],
        'password' => [
            'required' => 'Vui lòng nhập mật khẩu.',
            'min' => [
                'string' => 'Mật khẩu phải có tối thiểu :min ký tự.',
            ],
        ],
        'ends_at' => [
            'after_or_equal' => 'Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.',
            'date' => 'Thời gian kết thúc không đúng định dạng ngày tháng.',
        ],
        'starts_at' => [
            'date' => 'Thời gian bắt đầu không đúng định dạng ngày tháng.',
        ],
    ],

    'attributes' => [
        'email' => 'Email',
        'password' => 'Mật khẩu',
        'starts_at' => 'thời gian bắt đầu',
        'ends_at' => 'thời gian kết thúc',
    ],
];
