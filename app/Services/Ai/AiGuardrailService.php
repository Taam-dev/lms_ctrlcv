<?php

namespace App\Services\Ai;

use Illuminate\Support\Str;

class AiGuardrailService
{
    public const REFUSAL_MESSAGE = 'Nội dung bạn hỏi không thuộc phạm vi của trang web Ctrl C+V LMS. Mình chỉ hỗ trợ giải đáp thông tin về các khóa học, giảng viên, lộ trình học tập và các tính năng trên hệ thống thôi nhé!';

    /**
     * Kiểm tra xem câu hỏi của người dùng có nằm ngoài phạm vi trang web LMS hay không.
     */
    public function isOffTopic(string $query): bool
    {
        $trimmed = trim($query);
        if ($trimmed === '') {
            return false;
        }

        $lower = mb_strtolower($trimmed, 'UTF-8');
        $ascii = Str::ascii($lower);

        // 1. Nếu là các câu chào hỏi, cảm ơn hoặc giới thiệu cơ bản -> Cho phép
        if ($this->isGreetingOrCourtesy($lower, $ascii)) {
            return false;
        }

        // 2. Nếu có chứa từ khóa liên quan trực tiếp đến khóa học / hệ thống LMS -> Cho phép
        if ($this->isLmsRelevant($lower, $ascii)) {
            return false;
        }

        // 3. Kiểm tra các mẫu chủ đề cấm / ngoài phạm vi rõ ràng (Off-topic patterns)
        if ($this->matchesExplicitOffTopic($lower, $ascii)) {
            return true;
        }

        // 4. Nếu người dùng yêu cầu giải toán, viết văn, làm thơ, kể chuyện cổ tích -> Từ chối
        if ($this->isCreativeWritingOrMathSolving($lower, $ascii)) {
            return true;
        }

        // 5. Nếu câu hỏi quá chung chung hoặc là câu đố ngẫu nhiên không liên quan đến học tập / công nghệ
        if ($this->isGeneralTriviaOrGossip($lower, $ascii)) {
            return true;
        }

        return false;
    }

    /**
     * Nhận diện lời chào, cảm ơn, hỏi danh tính trợ lý
     */
    protected function isGreetingOrCourtesy(string $lower, string $ascii): bool
    {
        $patterns = [
            '/^(xin chào|chào|chao|hello|hi|hey|alo|halo)/i',
            '/^(bạn là ai|ban la ai|who are you|bạn tên gì|gioi thieu|giới thiệu)/i',
            '/^(cảm ơn|cam on|thanks|thank you|cảm ơn bạn|tạm biệt|tam biet|bye)/i',
            '/^(giúp tôi|giup toi|trợ giúp|tro giup|help)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $lower)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Nhận diện các câu hỏi gắn liền với ngữ cảnh LMS (khóa học, tiến độ, bài giảng, giảng viên...)
     */
    protected function isLmsRelevant(string $lower, string $ascii): bool
    {
        $lmsKeywords = [
            'khoa hoc', 'khóa học', 'khoa', 'khóa', 'mon hoc', 'môn học',
            'bai giang', 'bài giảng', 'bai hoc', 'bài học', 'giao trinh', 'giáo trình',
            'lo trinh', 'lộ trình', 'tu van', 'tư vấn', 'hoc phi', 'học phí',
            'hoc gi', 'học gì', 'nen hoc', 'nên học', 'nguoi moi', 'người mới',
            'giang vien', 'giảng viên', 'thay', 'thầy', 'co', 'cô', 'teacher',
            'danh gia', 'đánh giá', 'review', 'rating', 'sao', 'star',
            'hoc vien', 'học viên', 'dang ky', 'đăng ký', 'enroll', 'mua khoa', 'mua khóa',
            'mien phi', 'miễn phí', 'free', 'tien do', 'tiến độ', 'hoan thanh', 'hoàn thành',
            'quiz', 'trac nghiem', 'trắc nghiệm', 'bai thi', 'bài thi', 'kiem tra', 'kiểm tra', 'diem', 'điểm',
            'vi tien', 'ví tiền', 'nap tien', 'nạp tiền', 'vietqr', 'balance', 'so du', 'số dư',
            'tai khoan', 'tài khoản', 'mat khau', 'mật khẩu', 'dang nhap', 'đăng nhập', 'profile', 'ho so', 'hồ sơ',
            'ctrl c+v', 'ctrlcv', 'lms', 'he thong', 'hệ thống', 'trang web', 'website',
            'chung chi', 'chứng chỉ', 'bao cao', 'báo cáo', 'report',
        ];

        foreach ($lmsKeywords as $kw) {
            if (str_contains($lower, $kw) || str_contains($ascii, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Nhận diện các chủ đề ngoài lề: Nấu ăn, thời tiết, chính trị, thể thao, showbiz, y tế, bói toán
     */
    protected function matchesExplicitOffTopic(string $lower, string $ascii): bool
    {
        $offTopicKeywords = [
            // Nấu ăn / Ẩm thực
            'nau an', 'nấu ăn', 'cong thuc nau', 'công thức nấu', 'mon an', 'món ăn',
            'nau pho', 'nấu phở', 'cach lam banh', 'cách làm bánh', 'nguyen lieu', 'nguyên liệu',
            'luoc ga', 'luộc gà', 'chien xao', 'chiên xào', 'nem ran', 'nem rán',

            // Thời tiết
            'thoi tiet', 'thời tiết', 'du bao thoi tiet', 'dự báo thời tiết',
            'hom nay mua khong', 'hôm nay mưa không', 'nhiet do', 'nhiệt độ',

            // Chính trị / Chiến tranh
            'tong thong', 'tổng thống', 'chinh tri', 'chính trị', 'bau cu', 'bầu cử',
            'chien tranh', 'chiến tranh', 'quan su', 'quân sự', 'nga ukraine', 'putin', 'biden', 'trump',

            // Thể thao / Showbiz / Phim ảnh
            'bong da', 'bóng đá', 'ngoai hang anh', 'ngoại hạng anh', 'champions league',
            'world cup', 'ronaldo', 'messi', 'ca si', 'ca sĩ', 'dien vien', 'diễn viên',
            'showbiz', 'scandal', 'rap chieu phim', 'rạp chiếu phim',

            // Y tế / Khám chữa bệnh
            'kham benh', 'khám bệnh', 'chua benh', 'chữa bệnh', 'trieu chung', 'triệu chứng',
            'uong thuoc', 'uống thuốc', 'bac si', 'bác sĩ', 'benh vien', 'bệnh viện',

            // Bói toán / Tử vi
            'tu vi', 'tử vi', 'boi toan', 'bói toán', 'xem boi', 'xem bói',
            'cung hoang dao', 'cung hoàng đạo', 'phong thuy', 'phong thủy',

            // Trò chơi / Game lậu
            'lien quan', 'liên quân', 'toc chien', 'tốc chiến', 'free fire', 'pubg',
            'hack game', 'tai game', 'tải game',
        ];

        foreach ($offTopicKeywords as $kw) {
            if (str_contains($lower, $kw) || str_contains($ascii, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Nhận diện yêu cầu làm thơ, viết tiểu thuyết, giải bài tập toán / lý / hóa phổ thông
     */
    protected function isCreativeWritingOrMathSolving(string $lower, string $ascii): bool
    {
        $patterns = [
            '/(viet|viết|lam|làm|sang tac|sáng tác)\s*(bai tho|bài thơ|tho|thơ)/i',
            '/(viet|viết)\s*(van|văn|doan van|đoạn văn|tieu thuyet|tiểu thuyết|truyen|truyện|kich ban|kịch bản)/i',
            '/(ke chuyen|kể chuyện)\s*(co tich|cổ tích|ma|hai|hài)/i',
            '/(giai|giải)\s*(phuong trinh|phương trình|he phuong trinh|hệ phương trình|toan|toán|tich phan|tích phân|dao ham|đạo hàm)/i',
            '/(tinh|tính)\s*(can bac hai|căn bậc hai|tich phan|tích phân|dao ham|đạo hàm)\s*cua/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $lower)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Nhận diện các câu hỏi đố lịch sử, địa lý, câu đố mẹo ngẫu nhiên không liên quan đến học tập
     */
    protected function isGeneralTriviaOrGossip(string $lower, string $ascii): bool
    {
        $triviaTriggers = [
            'thu do cua', 'thủ đô của',
            'ai tim ra chau my', 'ai tìm ra châu mỹ',
            'dan so the gioi', 'dân số thế giới',
            'dinh fansipan cao', 'đỉnh fansipan cao',
            'song dai nhat the gioi', 'sông dài nhất thế giới',
        ];

        foreach ($triviaTriggers as $trig) {
            if (str_contains($lower, $trig) || str_contains($ascii, $trig)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Trả về thông báo từ chối chuẩn mực theo quy định
     */
    public function getRefusalResponse(): array
    {
        return [
            'success' => true,
            'message' => self::REFUSAL_MESSAGE,
            'source' => 'guardrail',
            'intent' => 'refusal',
            'relevant_courses' => [],
            'suggested_chips' => [
                'Tất cả khóa học',
                'Lộ trình học',
                'Khóa học của tôi',
            ],
        ];
    }
}
