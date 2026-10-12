<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    /**
     * Hiển thị trang nạp tiền và lịch sử ví của học viên
     */
    public function deposit(Request $request)
    {
        $user = auth()->user();
        $quickAmounts = config('wallet.quick_amounts', [50000, 100000, 200000, 500000, 1000000]);

        $bankConfig = config('wallet.vietqr', [
            'bank_id' => 'MB',
            'bank_name' => 'Ngân hàng Quân Đội (MBBank)',
            'account_no' => '0929089332',
            'account_name' => 'LE CHI TAM',
            'template' => 'compact2',
            'qr_image' => 'images/vietqr-mb-lechitam.png',
        ]);

        // Kiểm tra xem người dùng đang xem một giao dịch nạp tiền cụ thể hay có giao dịch đang chờ mới nhất
        $activeTx = null;
        if ($request->filled('tx')) {
            $activeTx = WalletTransaction::where('user_id', $user->id)
                ->where('code', $request->query('tx'))
                ->where('type', 'deposit')
                ->first();
        }

        if (! $activeTx) {
            $activeTx = WalletTransaction::where('user_id', $user->id)
                ->where('type', 'deposit')
                ->where('status', 'pending')
                ->latest()
                ->first();
        }

        $qrData = null;
        if ($activeTx && $activeTx->status === 'pending') {
            $transferContent = $activeTx->metadata['transfer_content'] ?? ("LMS {$activeTx->code}");
            $amount = $activeTx->amount;

            $qrUrl = sprintf(
                'https://img.vietqr.io/image/%s-%s-%s.png?amount=%d&addInfo=%s&accountName=%s',
                $bankConfig['bank_id'],
                $bankConfig['account_no'],
                $bankConfig['template'] ?? 'compact2',
                $amount,
                urlencode($transferContent),
                urlencode($bankConfig['account_name'])
            );

            $qrData = [
                'tx' => $activeTx,
                'qr_url' => $qrUrl,
                'qr_image' => $bankConfig['qr_image'] ?? 'images/vietqr-mb-lechitam.png',
                'bank_id' => $bankConfig['bank_id'],
                'bank_name' => $bankConfig['bank_name'],
                'account_no' => $bankConfig['account_no'],
                'account_name' => $bankConfig['account_name'],
                'amount' => $amount,
                'transfer_content' => $transferContent,
            ];
        }

        $transactions = WalletTransaction::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('student.wallet.deposit', compact(
            'user',
            'quickAmounts',
            'bankConfig',
            'activeTx',
            'qrData',
            'transactions'
        ));
    }

    /**
     * Tạo yêu cầu nạp tiền mới và sinh mã VietQR
     */
    public function createDeposit(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'integer', 'min:10000', 'max:100000000'],
        ], [
            'amount.required' => 'Vui lòng nhập hoặc chọn số tiền cần nạp.',
            'amount.integer' => 'Số tiền nạp phải là số nguyên hợp lệ.',
            'amount.min' => 'Số tiền nạp tối thiểu là 10.000 đ.',
            'amount.max' => 'Số tiền nạp tối đa mỗi lần là 100.000.000 đ.',
        ]);

        $user = auth()->user();
        $amount = (int) $request->amount;
        $bankConfig = config('wallet.vietqr');

        // Sinh mã giao dịch ngẫu nhiên không chứa chữ NAP theo yêu cầu
        $code = strtoupper(Str::random(8));
        $transferContent = "LMS {$code}";

        $transaction = WalletTransaction::create([
            'user_id' => $user->id,
            'code' => $code,
            'type' => 'deposit',
            'amount' => $amount,
            'balance_before' => $user->balance ?? 0,
            'balance_after' => $user->balance ?? 0,
            'status' => 'pending',
            'description' => "Nạp tiền ví qua VietQR ({$code})",
            'payment_method' => 'vietqr',
            'metadata' => [
                'bank_id' => $bankConfig['bank_id'] ?? 'MB',
                'bank_name' => $bankConfig['bank_name'] ?? 'Ngân hàng Quân Đội (MBBank)',
                'account_no' => $bankConfig['account_no'] ?? '0929089332',
                'account_name' => $bankConfig['account_name'] ?? 'LE CHI TAM',
                'transfer_content' => $transferContent,
            ],
        ]);

        return redirect()->route('wallet.deposit', ['tx' => $transaction->code])
            ->with('success', 'Đã khởi tạo yêu cầu nạp tiền! Vui lòng quét mã QR bên dưới để thanh toán.');
    }

    /**
     * Xác nhận thanh toán nạp tiền (Cộng tiền vào ví)
     */
    public function confirmDeposit(Request $request, WalletTransaction $transaction)
    {
        $user = auth()->user();

        abort_if($transaction->user_id !== $user->id && ! $user->isAdmin(), 403, 'Bạn không có quyền xác nhận giao dịch này.');

        if ($transaction->status !== 'pending') {
            return redirect()->route('wallet.deposit')
                ->with('info', 'Giao dịch này đã được xử lý trước đó.');
        }

        DB::transaction(function () use ($transaction, $user) {
            $userToCredit = ($transaction->user_id === $user->id) ? $user : $transaction->user;

            $balanceBefore = $userToCredit->balance ?? 0;
            $userToCredit->increment('balance', $transaction->amount);
            $balanceAfter = $userToCredit->fresh()->balance;

            $transaction->update([
                'status' => 'completed',
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
            ]);
        });

        return redirect()->route('wallet.deposit')
            ->with('success', 'Xác nhận nạp tiền thành công! Đã cộng +'.number_format($transaction->amount, 0, ',', '.').' đ vào ví của bạn.');
    }

    /**
     * Hủy yêu cầu nạp tiền đang chờ
     */
    public function cancelDeposit(WalletTransaction $transaction)
    {
        $user = auth()->user();

        abort_if($transaction->user_id !== $user->id && ! $user->isAdmin(), 403, 'Bạn không có quyền hủy giao dịch này.');

        if ($transaction->status === 'pending') {
            $transaction->update(['status' => 'cancelled']);
        }

        return redirect()->route('wallet.deposit')
            ->with('success', 'Đã hủy yêu cầu nạp tiền.');
    }
}
