<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines (Tiếng Việt)
    |--------------------------------------------------------------------------
    */

    'accepted' => ':Attribute phải được chấp nhận.',
    'accepted_if' => ':Attribute phải được chấp nhận khi :other là :value.',
    'active_url' => ':Attribute không phải là đường dẫn URL hợp lệ.',
    'after' => ':Attribute phải là ngày sau ngày :date.',
    'after_or_equal' => ':Attribute phải là ngày sau hoặc bằng :date.',
    'alpha' => ':Attribute chỉ được chứa chữ cái.',
    'alpha_dash' => ':Attribute chỉ được chứa chữ cái, chữ số, dấu gạch ngang và gạch dưới.',
    'alpha_num' => ':Attribute chỉ được chứa chữ cái và chữ số.',
    'array' => ':Attribute phải là một danh sách mảng.',
    'before' => ':Attribute phải là ngày trước ngày :date.',
    'before_or_equal' => ':Attribute phải là ngày trước hoặc bằng :date.',
    'between' => [
        'array' => ':Attribute phải có từ :min đến :max phần tử.',
        'file' => 'Dung lượng :attribute phải từ :min đến :max kilobyte.',
        'numeric' => ':Attribute phải nằm trong khoảng :min đến :max.',
        'string' => ':Attribute phải có từ :min đến :max ký tự.',
    ],
    'boolean' => ':Attribute phải là đúng hoặc sai.',
    'confirmed' => ':Attribute xác nhận không khớp.',
    'current_password' => 'Mật khẩu hiện tại không chính xác.',
    'date' => ':Attribute không đúng định dạng ngày tháng.',
    'date_equals' => ':Attribute phải là ngày bằng với :date.',
    'date_format' => ':Attribute không khớp với định dạng :format.',
    'declined' => ':Attribute phải bị từ chối.',
    'different' => ':Attribute và :other phải khác nhau.',
    'digits' => ':Attribute phải có :digits chữ số.',
    'digits_between' => ':Attribute phải có từ :min đến :max chữ số.',
    'email' => ':Attribute không đúng định dạng.',
    'ends_with' => ':Attribute phải kết thúc bằng một trong các giá trị sau: :values.',
    'exists' => ':Attribute được chọn không tồn tại.',
    'file' => ':Attribute phải là một tệp tin.',
    'filled' => ':Attribute không được để trống.',
    'image' => ':Attribute phải là hình ảnh hợp lệ.',
    'in' => ':Attribute được chọn không hợp lệ.',
    'in_array' => ':Attribute phải tồn tại trong :other.',
    'integer' => ':Attribute phải là số nguyên.',
    'ip' => ':Attribute phải là một địa chỉ IP hợp lệ.',
    'json' => ':Attribute phải là một chuỗi JSON hợp lệ.',
    'lowercase' => ':Attribute phải là chữ viết thường.',
    'max' => [
        'array' => ':Attribute không được vượt quá :max phần tử.',
        'file' => 'Dung lượng :attribute không được vượt quá :max kilobyte.',
        'numeric' => ':Attribute không được lớn hơn :max.',
        'string' => ':Attribute không được vượt quá :max ký tự.',
    ],
    'mimes' => ':Attribute phải là tệp thuộc định dạng: :values.',
    'mimetypes' => ':Attribute phải là tệp có kiểu MIME: :values.',
    'min' => [
        'array' => ':Attribute phải có tối thiểu :min phần tử.',
        'file' => 'Dung lượng :attribute phải có tối thiểu :min kilobyte.',
        'numeric' => ':Attribute phải tối thiểu là :min.',
        'string' => ':Attribute phải có tối thiểu :min ký tự.',
    ],
    'numeric' => ':Attribute phải là một chữ số.',
    'password' => [
        'letters' => ':Attribute phải chứa ít nhất một chữ cái.',
        'mixed' => ':Attribute phải chứa cả chữ hoa và chữ thường.',
        'numbers' => ':Attribute phải chứa ít nhất một chữ số.',
        'symbols' => ':Attribute phải chứa ít nhất một ký tự đặc biệt.',
        'uncompromised' => ':Attribute đã từng xuất hiện trong vụ rò rỉ dữ liệu. Vui lòng chọn mật khẩu khác.',
    ],
    'present' => ':Attribute phải có mặt trong dữ liệu gửi lên.',
    'prohibited' => ':Attribute bị cấm không được gửi lên.',
    'regex' => 'Định dạng của :attribute không hợp lệ.',
    'required' => ':Attribute không được để trống.',
    'same' => ':Attribute và :other phải trùng khớp với nhau.',
    'size' => [
        'array' => ':Attribute phải chứa chính xác :size phần tử.',
        'file' => 'Dung lượng :attribute phải chính xác là :size kilobyte.',
        'numeric' => ':Attribute phải bằng :size.',
        'string' => ':Attribute phải chứa đúng :size ký tự.',
    ],
    'string' => ':Attribute phải là chuỗi ký tự hợp lệ.',
    'unique' => ':Attribute đã được sử dụng.',
    'uploaded' => 'Tải lên :attribute thất bại.',
    'uppercase' => ':Attribute phải viết hoa.',
    'url' => ':Attribute không đúng định dạng URL.',

    /*
    |--------------------------------------------------------------------------
    | Tùy biến thông báo cụ thể cho từng trường
    |--------------------------------------------------------------------------
    */
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
            'confirmed' => 'Mật khẩu xác nhận không khớp.',
        ],
        'current_password' => [
            'required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password' => 'Mật khẩu hiện tại không chính xác.',
        ],
        'ends_at' => [
            'after_or_equal' => 'Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.',
            'date' => 'Thời gian kết thúc không đúng định dạng ngày tháng.',
        ],
        'starts_at' => [
            'date' => 'Thời gian bắt đầu không đúng định dạng ngày tháng.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tên các thuộc tính trường (Attribute names) thân thiện
    |--------------------------------------------------------------------------
    */
    'attributes' => [
        'name' => 'Họ và tên',
        'email' => 'Email',
        'password' => 'Mật khẩu',
        'password_confirmation' => 'Mật khẩu xác nhận',
        'current_password' => 'Mật khẩu hiện tại',
        'title' => 'Tiêu đề',
        'description' => 'Mô tả',
        'price' => 'Học phí',
        'thumbnail' => 'Ảnh bìa',
        'thumbnail_file' => 'Tệp ảnh bìa',
        'avatar' => 'Ảnh đại diện',
        'content' => 'Nội dung',
        'content_type' => 'Loại nội dung',
        'order_number' => 'Số thứ tự',
        'duration_minutes' => 'Thời gian làm bài',
        'passing_score' => 'Điểm đạt',
        'starts_at' => 'thời gian bắt đầu',
        'ends_at' => 'thời gian kết thúc',
        'rating' => 'Đánh giá sao',
        'comment' => 'Bình luận',
        'amount' => 'Số tiền',
        'reason' => 'Lý do báo cáo',
        'details' => 'Chi tiết',
    ],

];
