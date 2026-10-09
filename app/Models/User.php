<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // khai báo các trường cho phép điền dữ liệu, nhớ thêm role vào ko là lỗi mass assignment
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // thêm role vô đây để tí nữa phân quyền admin/teacher/student
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // 1 giảng viên thì tạo đc nhiều khóa học
    public function courses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    // 1 sinh viên thì đăng ký học đc nhiều khóa
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    // giảng viên tạo các bài quiz
    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'teacher_id');
    }

    // học viên làm các bài quiz
    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class, 'student_id');
    }

    // các bài giảng học viên đã hoàn thành
    public function lessonCompletions()
    {
        return $this->hasMany(LessonCompletion::class, 'student_id');
    }

    /**
     * Lấy đường dẫn URL của avatar nếu tồn tại trong storage.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (empty($this->avatar)) {
            return null;
        }

        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
            return $this->avatar;
        }

        if (Storage::disk('public')->exists($this->avatar) || file_exists(public_path('storage/'.$this->avatar))) {
            return asset('storage/'.$this->avatar);
        }

        return null;
    }

    /**
     * Kiểm tra người dùng có quyền Giảng viên (Teacher / GV)
     */
    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    /**
     * Alias kiểm tra quyền Giảng viên để tương thích ngược
     */
    public function isInstructor(): bool
    {
        return $this->isTeacher();
    }

    /**
     * Kiểm tra người dùng có quyền Quản trị viên (Admin)
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Kiểm tra người dùng có phải là Học viên (Student)
     */
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }
}
