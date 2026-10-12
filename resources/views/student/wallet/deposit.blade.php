<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="text-xs font-extrabold text-pink-600 uppercase tracking-widest block mb-1">Tài chính cá nhân</span>
                <h2 class="font-black text-2xl sm:text-3xl text-slate-900 tracking-tight flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-neutral-900 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </span>
                    Ví Tiền & Nạp Tiền
                </h2>
            </div>

            <!-- Thẻ số dư ví nổi bật ở header -->
            <div class="bg-neutral-900 border border-neutral-800 rounded-2xl px-5 py-3 text-white flex items-center gap-3.5 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-neutral-400">Số dư khả dụng</div>
                    <div class="text-xl font-black text-white tracking-tight">
                        {{ number_format($user->balance ?? 0, 0, ',', '.') }} đ
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Phần Nạp Tiền & Mã VietQR -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Cột trái: Form nạp tiền (Chọn số tiền) -->
                <div class="lg:col-span-5 bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 shadow-xs space-y-6">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-pink-600">Bước 1</span>
                        <h3 class="font-black text-xl text-slate-900 tracking-tight mt-0.5">Khởi tạo giao dịch nạp</h3>
                        <p class="text-xs text-slate-500 mt-1">Chọn hoặc nhập số tiền bạn muốn nạp vào ví tài khoản LMS.</p>
                    </div>

                    <form action="{{ route('wallet.deposit.store') }}" method="POST" id="deposit-form" class="space-y-5">
                        @csrf

                        <!-- Các mức gợi ý nhanh -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Chọn nhanh số tiền</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                @foreach($quickAmounts as $amount)
                                    <button type="button" 
                                            onclick="selectQuickAmount({{ $amount }})" 
                                            class="quick-btn px-3 py-2.5 rounded-xl border border-slate-200 hover:border-neutral-900 bg-white hover:bg-neutral-50 text-slate-800 font-extrabold text-xs transition text-center shadow-2xs">
                                        {{ number_format($amount, 0, ',', '.') }} đ
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Nhập số tiền tùy chỉnh -->
                        <div>
                            <label for="amount" class="block text-xs font-bold text-slate-700 uppercase mb-2">
                                Hoặc nhập số tiền tùy chọn (VNĐ) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="amount" 
                                       id="amount-input" 
                                       min="10000" 
                                       max="100000000" 
                                       step="1000"
                                       value="{{ old('amount', request('amount', 100000)) }}"
                                       required
                                       placeholder="Ví dụ: 200000" 
                                       class="w-full pl-4 pr-16 py-3.5 rounded-2xl border-slate-200 focus:border-neutral-900 focus:ring-neutral-900 font-black text-lg text-slate-900 shadow-2xs" />
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-xs">
                                    VNĐ
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1.5">Tối thiểu: 10.000 đ &bull; Tối đa: 100.000.000 đ</p>
                            @error('amount')
                                <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nút gửi tạo mã QR -->
                        <button type="submit" 
                                class="w-full py-3.5 px-6 rounded-2xl bg-neutral-900 hover:bg-neutral-800 text-white font-black text-sm transition shadow-md shadow-neutral-950/20 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span>Tạo mã QR nạp tiền</span>
                        </button>
                    </form>

                    <!-- Lưu ý bảo mật -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-500 space-y-1.5">
                        <div class="font-bold text-slate-700 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Quy trình thanh toán an toàn
                        </div>
                        <p>Hệ thống sử dụng cổng tạo mã VietQR chuẩn liên ngân hàng Napas247. Mọi giao dịch nạp tiền sẽ được ghi nhận và lưu vết đầy đủ.</p>
                    </div>
                </div>

                <!-- Cột phải: Khung hiển thị VietQR thực tế & Thông tin xác nhận -->
                <div class="lg:col-span-7 bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 shadow-xs space-y-6">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-pink-600">Bước 2</span>
                            @if($qrData)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-black uppercase bg-amber-100 text-amber-800 border border-amber-200">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    Chờ quét thanh toán
                                </span>
                            @endif
                        </div>
                        <h3 class="font-black text-xl text-slate-900 tracking-tight mt-0.5">Quét mã VietQR chuyển khoản</h3>
                        <p class="text-xs text-slate-500 mt-1">Mở ứng dụng ngân hàng bất kỳ (VCB, MB, Techcombank, Momo...) để quét mã bên dưới.</p>
                    </div>

                    @if($qrData)
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                            <!-- Ảnh mã VietQR MBBank chính chủ -->
                            <div class="md:col-span-5 text-center bg-slate-50 p-4 rounded-2xl border border-slate-200">
                                <img src="{{ asset('images/vietqr-mb-lechitam.png') }}" 
                                     alt="VietQR MB Bank - {{ $qrData['account_name'] }}" 
                                     class="w-full max-w-[240px] mx-auto rounded-xl shadow-sm border border-slate-200 bg-white" />
                                <div class="mt-3 flex items-center justify-center gap-1.5 text-xs font-bold text-slate-600">
                                    <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                    <span>Quét bằng app ngân hàng</span>
                                </div>
                            </div>

                            <!-- Thông tin chuyển khoản chi tiết & Nút Copy -->
                            <div class="md:col-span-7 space-y-3">
                                <!-- Ngân hàng -->
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="text-[10px] uppercase font-bold text-slate-400 whitespace-nowrap">Ngân hàng thụ hưởng</div>
                                        <div class="font-bold text-slate-900 text-sm truncate">{{ $qrData['bank_name'] }}</div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-neutral-900 text-white shrink-0 whitespace-nowrap">{{ $qrData['bank_id'] }}</span>
                                </div>

                                <!-- Số tài khoản -->
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="text-[10px] uppercase font-bold text-slate-400 whitespace-nowrap">Số tài khoản</div>
                                        <div class="font-black text-slate-900 text-base tracking-wider whitespace-nowrap">{{ $qrData['account_no'] }}</div>
                                    </div>
                                    <button type="button" 
                                            onclick="copyText('{{ $qrData['account_no'] }}', this)" 
                                            class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 transition shrink-0 whitespace-nowrap">
                                        Sao chép
                                    </button>
                                </div>

                                <!-- Chủ tài khoản -->
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                                    <div class="text-[10px] uppercase font-bold text-slate-400 whitespace-nowrap">Tên người thụ hưởng</div>
                                    <div class="font-black text-slate-900 text-sm tracking-wide uppercase truncate">{{ $qrData['account_name'] }}</div>
                                </div>

                                <!-- Số tiền cần nạp -->
                                <div class="p-3 bg-pink-50/70 rounded-xl border border-pink-200 flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="text-[10px] uppercase font-bold text-pink-700 whitespace-nowrap">Số tiền thanh toán</div>
                                        <div class="font-black text-pink-700 text-lg whitespace-nowrap">{{ number_format($qrData['amount'], 0, ',', '.') }} đ</div>
                                    </div>
                                    <button type="button" 
                                            onclick="copyText('{{ $qrData['amount'] }}', this)" 
                                            class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white hover:bg-pink-100 border border-pink-200 text-pink-700 transition shrink-0 whitespace-nowrap">
                                        Sao chép
                                    </button>
                                </div>

                                <!-- Nội dung chuyển khoản (Bắt buộc giữ nguyên) -->
                                <div class="p-3.5 bg-neutral-900 text-white rounded-xl border border-neutral-800 space-y-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <span class="text-[11px] uppercase font-extrabold tracking-wider text-pink-400 whitespace-nowrap">
                                                Nội dung chuyển khoản
                                            </span>
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30 whitespace-nowrap">
                                                Bắt buộc
                                            </span>
                                        </div>
                                        <button type="button" 
                                                onclick="copyText('{{ $qrData['transfer_content'] }}', this)" 
                                                class="px-2.5 py-1 rounded text-xs font-bold bg-white text-neutral-900 hover:bg-neutral-200 transition shrink-0 whitespace-nowrap">
                                            Sao chép
                                        </button>
                                    </div>
                                    <div class="font-mono text-base font-black text-white tracking-wider break-all select-all bg-white/10 px-3 py-2 rounded-lg border border-white/10">
                                        {{ $qrData['transfer_content'] }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cảnh báo nội dung -->
                        <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200/80 text-xs text-amber-900 flex items-start gap-2.5">
                            <span class="text-amber-600 font-black text-sm shrink-0">⚠️</span>
                            <div>
                                <strong>Lưu ý quan trọng:</strong> Vui lòng điền chính xác nội dung chuyển khoản 
                                <span class="font-mono font-bold bg-amber-200/60 px-1 py-0.5 rounded text-amber-950">{{ $qrData['transfer_content'] }}</span> 
                                để hệ thống tự động xác nhận số tiền vào ví của bạn.
                            </div>
                        </div>

                        <!-- Nút hành động xác nhận đã chuyển khoản -->
                        <div class="pt-2 flex flex-col sm:flex-row items-center gap-3">
                            <form action="{{ route('wallet.transactions.confirm', $activeTx) }}" method="POST" class="w-full sm:flex-1">
                                @csrf
                                <button type="submit" 
                                        class="w-full py-3.5 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm transition shadow-md shadow-emerald-600/25 flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    <span>Xác nhận tôi đã chuyển khoản</span>
                                </button>
                            </form>

                            <form action="{{ route('wallet.transactions.cancel', $activeTx) }}" method="POST" class="w-full sm:w-auto">
                                @csrf
                                <button type="submit" 
                                        onclick="return confirm('Bạn có chắc muốn hủy yêu cầu nạp tiền này để tạo mã mới?')"
                                        class="w-full sm:w-auto py-3.5 px-5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 font-bold text-xs transition">
                                    Hủy yêu cầu này
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Chưa có mã QR nào đang chờ -->
                        <div class="py-12 px-6 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50/50 space-y-3">
                            <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                </svg>
                            </div>
                            <h4 class="font-bold text-slate-900 text-sm">Chưa có mã QR nạp tiền đang hoạt động</h4>
                            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                                Vui lòng chọn số tiền ở cột bên trái và bấm <strong>"Tạo mã QR nạp tiền"</strong> để nhận mã quét VietQR chuẩn tự động.
                            </p>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Bảng Lịch sử giao dịch ví -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-pink-600">Sổ cái tài khoản</span>
                        <h3 class="font-black text-xl text-slate-900 tracking-tight mt-0.5">Lịch sử giao dịch ví</h3>
                    </div>
                </div>

                @if($transactions->isEmpty())
                    <div class="py-10 text-center text-slate-400 text-xs">
                        Bạn chưa có giao dịch nào được ghi nhận.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-center text-xs text-slate-600">
                            <thead class="bg-slate-50 text-[11px] uppercase font-extrabold text-slate-400 border-b border-slate-100">
                                <tr>
                                    <th class="py-3 px-4 text-center">Mã GD</th>
                                    <th class="py-3 px-4 text-center">Loại giao dịch</th>
                                    <th class="py-3 px-4 text-center">Số tiền</th>
                                    <th class="py-3 px-4 text-center">Mô tả</th>
                                    <th class="py-3 px-4 text-center">Thời gian</th>
                                    <th class="py-3 px-4 text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($transactions as $tx)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-3.5 px-4 font-mono font-bold text-slate-900 text-center">
                                            {{ $tx->code }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="font-bold text-slate-800">{{ $tx->type_label }}</span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            @if($tx->status === 'cancelled')
                                                <div class="inline-flex flex-col items-center">
                                                    <span class="text-slate-400 font-bold">0 đ</span>
                                                    <span class="text-[10px] text-slate-400 line-through">({{ number_format($tx->amount, 0, ',', '.') }} đ)</span>
                                                </div>
                                            @elseif($tx->status === 'pending')
                                                <span class="font-bold text-amber-600">
                                                    +{{ number_format($tx->amount, 0, ',', '.') }} đ
                                                </span>
                                            @else
                                                <span class="font-black {{ $tx->type === 'deposit' ? 'text-emerald-600' : 'text-rose-600' }}">
                                                    {{ $tx->formatted_amount }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate mx-auto text-center">
                                            {{ $tx->description }}
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-400 text-[11px] text-center">
                                            {{ $tx->created_at->format('H:i d/m/Y') }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            @if($tx->status === 'completed')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-100 text-emerald-800">
                                                    Thành công
                                                </span>
                                            @elseif($tx->status === 'pending')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800">
                                                    Đang chờ
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase bg-slate-100 text-slate-500">
                                                    {{ $tx->status_label }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-2">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function selectQuickAmount(amount) {
            const input = document.getElementById('amount-input');
            if (input) {
                input.value = amount;
                input.focus();
            }
        }

        function copyText(text, btnElement) {
            navigator.clipboard.writeText(text).then(() => {
                const originalText = btnElement.innerText;
                btnElement.innerText = '✓ Đã sao chép';
                btnElement.classList.add('bg-emerald-600', 'text-white', 'border-emerald-600');
                setTimeout(() => {
                    btnElement.innerText = originalText;
                    btnElement.classList.remove('bg-emerald-600', 'text-white', 'border-emerald-600');
                }, 2000);
            }).catch(err => {
                prompt('Sao chép nội dung bên dưới:', text);
            });
        }
    </script>
    @endpush
</x-app-layout>
