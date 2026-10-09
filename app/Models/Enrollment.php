<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    // lưu vết sinh viên nào đăng ký khóa học nào
    protected $fillable = ['student_id', 'course_id'];

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
