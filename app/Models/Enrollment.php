<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    // lưu vết sinh viên nào đăng ký khóa học nào, kèm số tiền đã chi trả khi mua
    protected $fillable = ['student_id', 'course_id', 'price_paid'];

    protected $casts = [
        'price_paid' => 'integer',
    ];

    // ai là người đăng ký
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // đăng ký khóa học nào
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
