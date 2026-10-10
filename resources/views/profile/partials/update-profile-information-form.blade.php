<section x-data="{
    showEmailModal: {{ $errors->changeEmail->isNotEmpty() || session('email_modal_open') || session('status') === 'reset-link-sent' ? 'true' : 'false' }},
    showPassword: false,
    newEmail: '{{ old('email', '') }}',
    newEmailError: '',
    isSendingReset: false,
    resetSentMessage: '{{ session('reset_message', '') }}',
    resetErrorMessage: '',

    checkNewEmail() {
        const val = (this.newEmail || '').trim().toLowerCase();
        if (!val || !val.includes('@') || !val.includes('.')) {
            this.newEmailError = '';
            return;
        }
        if (val === '{{ strtolower($user->email) }}') {
            this.newEmailError = 'Vui lòng nhập địa chỉ email khác với email hiện tại.';
            return;
        }
        fetch('{{ route('check-email') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: val })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exists) {
                this.newEmailError = 'Email đã được sử dụng';
            } else {
                this.newEmailError = '';
            }
        })
        .catch(() => {});
    },

    sendResetLink() {
        if (this.isSendingReset) return;
        this.isSendingReset = true;
        this.resetSentMessage = '';
        this.resetErrorMessage = '';

        fetch('{{ route('profile.forgot-password') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            this.isSendingReset = false;
            if (data.success) {
                this.resetSentMessage = data.message || 'Đã gửi liên kết đặt lại mật khẩu về email gốc ({{ $user->email }}). Vui lòng kiểm tra hộp thư!';
            } else {
                this.resetErrorMessage = data.message || 'Không thể gửi email đặt lại mật khẩu.';
            }
        })
        .catch(() => {
            this.isSendingReset = false;
            this.resetErrorMessage = 'Có lỗi xảy ra khi gửi email. Vui lòng thử lại sau.';
        });
    }
}">
    <header>
        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            
            Thông tin cá nhân & Ảnh đại diện
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Cập nhật ảnh đại diện, tên hiển thị và địa chỉ email tài khoản của bạn.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <style>
            .avatar-cropper-box .cropper-view-box,
            .avatar-cropper-box .cropper-face {
                border-radius: 50% !important;
            }
            .avatar-cropper-box .cropper-view-box {
                outline: 2px solid #ec4899 !important;
            }
        </style>

        <!-- Avatar Upload & Crop Section -->
        <div x-data="{ 
            previewUrl: '{{ $user->avatar_url ?? '' }}',
            rawImageSrc: '{{ $user->avatar_url ?? '' }}',
            hasAvatar: {{ $user->avatar ? 'true' : 'false' }},
            removeAvatar: false,
            isCropped: false,
            showCropModal: false,
            cropperInstance: null,
            userName: '{{ addslashes($user->name) }}',
            userEmail: '{{ addslashes($user->email) }}',
            userRoleLabel: '{{ $user->role === 'admin' ? 'Quản trị viên' : ($user->role === 'teacher' ? 'Giảng viên' : 'Học viên') }}',

            fileChosen(event) {
                const file = event.target.files[0];
                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    alert('Vui lòng chọn một tệp hình ảnh hợp lệ (PNG, JPG, JPEG, WEBP).');
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.rawImageSrc = e.target.result;
                    this.startCropping();
                };
                reader.readAsDataURL(file);
            },

            startCropping() {
                if (!this.rawImageSrc) return;
                this.showCropModal = true;
                this.$nextTick(() => {
                    const img = document.getElementById('avatar-crop-target-image');
                    if (!img) return;

                    const initCropper = () => {
                        if (typeof Cropper === 'undefined') {
                            console.error('Cropper.js chưa sẵn sàng');
                            return;
                        }

                        if (this.cropperInstance) {
                            this.cropperInstance.destroy();
                            this.cropperInstance = null;
                        }

                        this.cropperInstance = new Cropper(img, {
                            aspectRatio: 1,
                            viewMode: 0,
                            dragMode: 'move',
                            autoCropArea: 0.9,
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: false,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                            checkCrossOrigin: true,
                        });
                    };

                    img.onload = () => initCropper();
                    img.onerror = () => {
                        console.error('Lỗi khi tải ảnh avatar:', this.rawImageSrc);
                        alert('Không thể tải hình ảnh này để cắt. Vui lòng thử tải ảnh khác từ máy tính.');
                        this.cancelCropModal();
                    };

                    if (this.rawImageSrc.startsWith('http://') || this.rawImageSrc.startsWith('https://')) {
                        img.crossOrigin = 'anonymous';
                    } else {
                        img.removeAttribute('crossorigin');
                    }

                    if (img.src === this.rawImageSrc && img.complete && img.naturalWidth > 0) {
                        initCropper();
                    } else {
                        img.src = this.rawImageSrc;
                        if (img.complete && img.naturalWidth > 0) {
                            initCropper();
                        }
                    }
                });
            },

            applyCroppedImage() {
                if (!this.cropperInstance) return;

                try {
                    const canvas = this.cropperInstance.getCroppedCanvas({
                        width: 500,
                        height: 500,
                        fillColor: '#171717',
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });

                    if (!canvas) return;

                    const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.92);
                    this.previewUrl = croppedDataUrl;
                    this.removeAvatar = false;
                    this.isCropped = true;

                    // Gán base64 vào hidden input dự phòng
                    const croppedInput = document.getElementById('avatar_cropped_data');
                    if (croppedInput) {
                        croppedInput.value = croppedDataUrl;
                    }

                    // Gán vào input file bằng DataTransfer
                    canvas.toBlob((blob) => {
                        if (blob) {
                            const file = new File([blob], 'avatar-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                            const fileInput = document.getElementById('avatar-file-input');
                            if (fileInput) {
                                const dt = new DataTransfer();
                                dt.items.add(file);
                                fileInput.files = dt.files;
                            }
                        }
                    }, 'image/jpeg', 0.92);

                    this.cancelCropModal();
                } catch (e) {
                    console.error('Lỗi khi áp dụng cắt avatar:', e);
                    alert('Không thể xuất ảnh do giới hạn bảo mật (CORS). Vui lòng tải ảnh trực tiếp từ máy.');
                    this.cancelCropModal();
                }
            },

            cancelCropModal() {
                if (this.cropperInstance) {
                    this.cropperInstance.destroy();
                    this.cropperInstance = null;
                }
                this.showCropModal = false;
            },

            zoom(delta) {
                if (this.cropperInstance) {
                    this.cropperInstance.zoom(delta);
                }
            },

            rotate(degree) {
                if (this.cropperInstance) {
                    this.cropperInstance.rotate(degree);
                }
            },

            resetCrop() {
                if (this.cropperInstance) {
                    this.cropperInstance.reset();
                }
            },

            reCropCurrent() {
                if (this.rawImageSrc) {
                    this.startCropping();
                } else if (this.previewUrl) {
                    this.rawImageSrc = this.previewUrl;
                    this.startCropping();
                }
            },

            triggerFileInput() {
                const fileInput = document.getElementById('avatar-file-input');
                if (fileInput) fileInput.click();
            },

            deleteAvatar() {
                this.removeAvatar = true;
                this.previewUrl = '';
                this.rawImageSrc = '';
                this.isCropped = false;

                const fileInput = document.getElementById('avatar-file-input');
                if (fileInput) fileInput.value = '';

                const croppedInput = document.getElementById('avatar_cropped_data');
                if (croppedInput) croppedInput.value = '';
            }
        }" class="p-6 bg-slate-50/80 border border-slate-200/80 rounded-3xl space-y-5">
            
            <div class="flex items-center justify-between border-b border-slate-200/60 pb-3">
                <div>
                    <label class="block font-bold text-sm text-slate-800">
                        Ảnh đại diện (Avatar)
                    </label>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Hỗ trợ định dạng JPG, PNG, WEBP (tối đa 5MB).
                    </p>
                </div>
                <span class="whitespace-nowrap shrink-0 px-2.5 py-1 rounded-full text-xs font-bold bg-pink-50 text-pink-700 border border-pink-200">
                    Tỉ lệ 1:1
                </span>
            </div>

            <!-- Avatar Upload Controls -->
            <div class="flex flex-col sm:flex-row items-center gap-5">
                <!-- Avatar Preview Thumbnail -->
                <div class="relative shrink-0">
                    <template x-if="previewUrl && !removeAvatar">
                        <img :src="previewUrl" 
                             alt="{{ $user->name }}" 
                             class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg ring-2 ring-pink-500/30" />
                    </template>
                    <template x-if="!previewUrl || removeAvatar">
                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-pink-600 to-rose-600 text-white flex items-center justify-center font-black text-3xl border-4 border-white shadow-lg ring-2 ring-pink-500/20">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </template>
                </div>

                <!-- Avatar Actions -->
                <div class="space-y-2 text-center sm:text-left flex-1">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                        <button type="button" 
                                @click="triggerFileInput()"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white font-bold rounded-xl text-xs transition shadow-sm shadow-pink-500/25">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Chọn ảnh mới</span>
                        </button>

                        <input id="avatar-file-input" 
                               type="file" 
                               name="avatar" 
                               class="hidden" 
                               accept="image/png, image/jpeg, image/jpg, image/webp" 
                               @change="fileChosen($event)" />

                        <template x-if="previewUrl && !removeAvatar">
                            <button type="button" 
                                    @click="reCropCurrent()"
                                    class="inline-flex items-center gap-1 px-3.5 py-2 bg-white border border-slate-300 hover:border-pink-500 text-slate-700 hover:text-pink-600 rounded-xl text-xs font-semibold transition shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                Cắt lại ảnh
                            </button>
                        </template>

                        <template x-if="(hasAvatar && !removeAvatar) || (previewUrl && !hasAvatar)">
                            <button type="button" 
                                    @click="deleteAvatar()" 
                                    class="inline-flex items-center gap-1 px-3 py-2 bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-100 rounded-xl text-xs font-semibold transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Gỡ ảnh
                            </button>
                        </template>
                    </div>

                    <!-- Hidden inputs -->
                    <input type="hidden" name="avatar_cropped_data" id="avatar_cropped_data" value="">
                    <input type="hidden" name="remove_avatar" :value="removeAvatar ? '1' : '0'">

                    <x-input-error class="mt-1" :messages="$errors->get('avatar')" />
                </div>
            </div>

            <!-- Xem trước thanh menu (Topbar) -->
            <div x-show="previewUrl && !removeAvatar" 
                 x-transition
                 class="pt-1">
                <div class="rounded-2xl bg-neutral-950 border border-neutral-800 shadow-lg overflow-hidden">
                    <!-- Top pink accent line matching navbar -->
                    <div class="h-0.5 bg-pink-600"></div>

                    <div class="px-4 py-2.5 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black tracking-tight text-white">Ctrl C+V</span>
                            <span class="text-[10px] text-neutral-400 font-semibold hidden sm:inline">&bull; Xem trước thanh menu</span>
                        </div>

                        <!-- User dropdown button from navbar -->
                        <div class="inline-flex items-center gap-2.5 px-3 py-1.5 border border-white/15 bg-white/5 text-left">
                            <img :src="previewUrl" alt="Avatar" class="w-8 h-8 rounded-full object-cover shrink-0" />
                            <div class="leading-tight text-left">
                                <div class="font-bold text-white text-xs truncate max-w-[140px]" x-text="userName"></div>
                                <div class="text-[10px] font-extrabold uppercase tracking-wider text-pink-500" x-text="userRoleLabel"></div>
                            </div>
                            <span class="text-neutral-500 text-xs">&#9662;</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- AVATAR CROP MODAL POPUP DIALOG (Tỉ lệ 1:1 với Cropper.js)      -->
            <!-- ============================================================= -->
            <div x-show="showCropModal" 
                 x-transition.opacity
                 class="fixed inset-0 z-[99999] bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-6 overflow-y-auto"
                 style="display: none;">
                
                <div @click.away="cancelCropModal()" 
                     class="bg-neutral-950 text-white border border-neutral-800 rounded-3xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col my-auto max-h-[92vh]">
                    
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full bg-pink-500 shadow-sm shadow-pink-500"></span>
                            <div>
                                <h3 class="text-base font-black uppercase tracking-tight text-white">
                                    Cắt & Căn Chỉnh Ảnh Đại Diện
                                </h3>
                                <p class="text-xs text-neutral-400">
                                    Khung cắt khóa cố định tỉ lệ <strong>1:1</strong> chuẩn cho ảnh đại diện hình tròn/vuông
                                </p>
                            </div>
                        </div>

                        <button type="button" 
                                @click="cancelCropModal()"
                                class="text-neutral-400 hover:text-white p-2 rounded-xl hover:bg-white/10 transition text-lg leading-none">
                            &times;
                        </button>
                    </div>

                    <!-- Modal Crop Canvas Area -->
                    <div class="relative bg-neutral-900/90 p-4 flex-1 flex items-center justify-center min-h-[300px] max-h-[50vh] overflow-hidden avatar-cropper-box">
                        <div class="max-w-full max-h-full">
                            <img id="avatar-crop-target-image" 
                                 src="" 
                                 alt="Cắt ảnh avatar" 
                                 class="max-w-full max-h-[45vh] block">
                        </div>
                    </div>

                    <!-- Toolbar Controls -->
                    <div class="px-6 py-3 bg-neutral-900 border-t border-white/5 flex flex-wrap items-center justify-between gap-3 shrink-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" 
                                    @click="zoom(0.1)"
                                    title="Phóng to"
                                    class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold text-white transition flex items-center gap-1">
                                <span>+ Phóng to</span>
                            </button>
                            <button type="button" 
                                    @click="zoom(-0.1)"
                                    title="Thu nhỏ"
                                    class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold text-white transition flex items-center gap-1">
                                <span>- Thu nhỏ</span>
                            </button>
                            <button type="button" 
                                    @click="rotate(-90)"
                                    title="Xoay trái 90 độ"
                                    class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold text-white transition flex items-center gap-1">
                                <span>↺ Xoay trái</span>
                            </button>
                            <button type="button" 
                                    @click="rotate(90)"
                                    title="Xoay phải 90 độ"
                                    class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold text-white transition flex items-center gap-1">
                                <span>↻ Xoay phải</span>
                            </button>
                            <button type="button" 
                                    @click="resetCrop()"
                                    title="Đặt lại vị trí ban đầu"
                                    class="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/15 text-xs font-bold text-neutral-300 transition">
                                <span>⟲ Đặt lại</span>
                            </button>
                        </div>

                        <div class="text-[11px] text-neutral-400 italic">
                            Kéo chuột để căn chỉnh khuôn mặt vào giữa
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 border-t border-white/10 bg-neutral-950 flex items-center justify-between shrink-0">
                        <button type="button" 
                                @click="cancelCropModal()"
                                class="px-5 py-2.5 rounded-xl border border-white/15 text-neutral-300 hover:text-white hover:bg-white/10 text-xs font-bold uppercase tracking-wider transition">
                            Hủy bỏ
                        </button>

                        <button type="button" 
                                @click="applyCroppedImage()"
                                class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-500 hover:to-rose-500 text-white text-xs font-extrabold uppercase tracking-wider shadow-lg shadow-pink-600/30 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Áp dụng cắt avatar & Xem trước</span>
                        </button>
                    </div>

                </div>
            </div>

        </div>

        <!-- Name -->
        <div>
            <label for="name" class="block font-medium text-sm text-slate-700 mb-1">
                Họ và tên <span class="text-rose-500">*</span>
            </label>
            <input id="name" 
                   name="name" 
                   type="text" 
                   class="block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition duration-150" 
                   value="{{ old('name', $user->name) }}" 
                   required 
                   autofocus 
                   autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <!-- Email -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="block font-medium text-sm text-slate-700">
                    Địa chỉ Email <span class="text-xs font-normal text-slate-400">(Mail gốc tài khoản)</span>
                </label>
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                    <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Mail gốc bảo mật</span>
                </span>
            </div>

            <!-- Hidden input để form cập nhật thông tin cá nhân chính vẫn giữ nguyên $user->email -->
            <input type="hidden" name="email" value="{{ $user->email }}">

            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <input type="email" 
                           value="{{ $user->email }}" 
                           disabled 
                           readonly 
                           class="block w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium text-slate-700 select-all cursor-not-allowed" />
                </div>

                <!-- Nút Thay đổi Email -->
                <button type="button" 
                        id="open-change-email-btn"
                        @click="showEmailModal = true"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-neutral-900 hover:bg-neutral-800 active:scale-[0.98] text-white font-bold text-xs rounded-xl shadow-sm transition duration-150 shrink-0 cursor-pointer">
                    <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    <span>Đổi Email</span>
                </button>
            </div>

            @if (session('status') === 'email-updated')
                <div class="mt-2.5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Địa chỉ email đã được thay đổi thành công!</span>
                </div>
            @endif

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2.5 p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800">
                    <p>
                        Địa chỉ email của bạn chưa được xác minh.
                        <button form="send-verification" class="underline font-semibold text-amber-900 hover:text-amber-700 ms-1">
                            Nhấn vào đây để gửi lại email xác minh.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-emerald-600">
                            Một liên kết xác minh mới đã được gửi tới địa chỉ email của bạn.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="flex items-center gap-4 pt-1">
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 bg-pink-600 hover:bg-pink-700 text-white font-semibold text-sm rounded-xl shadow-sm shadow-pink-500/30 transition duration-150 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:ring-offset-2">
                Lưu thay đổi
            </button>

            @if (session('status') === 'profile-updated')
                <div x-data="{ show: true }"
                     x-show="show"
                     x-transition
                     x-init="setTimeout(() => show = false, 3000)"
                     class="flex items-center gap-1.5 text-sm font-semibold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200">
                    Đã cập nhật hồ sơ thành công!
                </div>
            @endif
        </div>
    </form>

    <!-- ============================================================= -->
    <!-- MODAL ĐỔI EMAIL - YÊU CẦU NHẬP MẬT KHẨU / GỬI VỀ MAIL GỐC    -->
    <!-- ============================================================= -->
    <div x-show="showEmailModal" 
         x-transition.opacity
         class="fixed inset-0 z-[99999] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;">
        
        <div @click.away="showEmailModal = false" 
             class="bg-white rounded-3xl w-full max-w-lg shadow-2xl border border-slate-200 overflow-hidden my-auto animate-in fade-in zoom-in-95 duration-150">
            
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-pink-100 border border-pink-200 flex items-center justify-center text-pink-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            Thay đổi địa chỉ Email
                        </h3>
                        <p class="text-xs text-slate-500">
                            Mail gốc hiện tại: <span class="font-semibold text-slate-700">{{ $user->email }}</span>
                        </p>
                    </div>
                </div>

                <button type="button" 
                        @click="showEmailModal = false"
                        class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition text-xl leading-none">
                    &times;
                </button>
            </div>

            <!-- Modal Form -->
            <form method="POST" action="{{ route('profile.email.update') }}" class="p-6 space-y-4">
                @csrf
                @method('patch')

                <!-- Thông báo gửi link reset mật khẩu thành công -->
                <template x-if="resetSentMessage">
                    <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-800 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-bold text-emerald-900">Đã gửi email khôi phục mật khẩu!</p>
                            <p x-text="resetSentMessage" class="mt-0.5"></p>
                            <p class="mt-1 text-[11px] text-emerald-700">
                                Vui lòng kiểm tra hộp thư đến (hoặc hòm thư Spam) của <strong>{{ $user->email }}</strong>, nhấp vào liên kết để đổi lại mật khẩu của bạn.
                            </p>
                        </div>
                    </div>
                </template>

                <template x-if="resetErrorMessage">
                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-700 flex items-start gap-2">
                        <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p x-text="resetErrorMessage"></p>
                    </div>
                </template>

                <!-- Input Email Mới -->
                <div>
                    <label for="modal_new_email" class="block font-bold text-xs uppercase tracking-wider text-slate-700 mb-1.5">
                        Địa chỉ Email mới <span class="text-rose-500">*</span>
                    </label>
                    <input id="modal_new_email"
                           name="email"
                           type="email"
                           x-model="newEmail"
                           @input.debounce.350ms="checkNewEmail()"
                           @blur="checkNewEmail()"
                           required
                           placeholder="nhap-email-moi@example.com"
                           class="block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition duration-150"
                           :class="{ 'border-rose-400 ring-1 ring-rose-200': newEmailError }" />
                    <template x-if="newEmailError">
                        <p class="mt-1.5 text-xs text-rose-500 font-medium" x-text="newEmailError"></p>
                    </template>
                    <x-input-error :messages="$errors->changeEmail->get('email')" class="mt-1.5 text-xs text-rose-500" />
                </div>

                <!-- Input Mật khẩu hiện tại -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="modal_current_password" class="block font-bold text-xs uppercase tracking-wider text-slate-700">
                            Mật khẩu hiện tại <span class="text-rose-500">*</span>
                        </label>

                        <!-- Nút Quên mật khẩu: Gửi mail về mail gốc -->
                        <button type="button"
                                @click="sendResetLink()"
                                :disabled="isSendingReset"
                                class="text-xs font-semibold text-pink-600 hover:text-pink-700 hover:underline disabled:opacity-50 transition flex items-center gap-1 cursor-pointer">
                            <span x-show="!isSendingReset">Quên mật khẩu?</span>
                            <span x-show="isSendingReset" class="inline-flex items-center gap-1 text-slate-500">
                                <svg class="animate-spin h-3 w-3 text-pink-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Đang gửi mail...</span>
                            </span>
                        </button>
                    </div>

                    <div class="relative">
                        <input id="modal_current_password"
                               name="password"
                               :type="showPassword ? 'text' : 'password'"
                               type="password"
                               required
                               placeholder="Nhập mật khẩu hiện tại của bạn"
                               class="block w-full px-3.5 py-2.5 pr-11 rounded-xl border border-slate-300 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition duration-150" />
                        <button type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1">
                            <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 1.274-4.057 5.064-7 9.542-7 1.053 0 2.062.18 3 .512M7.5 7.5l9 9M10.125 10.125a3 3 0 114.25 4.25" />
                            </svg>
                        </button>
                    </div>

                    <p class="mt-1 text-[11px] text-slate-500">
                        Vì lý do bảo mật, bạn cần nhập mật khẩu hiện tại trước khi hoàn tất đổi email. Nếu quên, nhấn <strong>Quên mật khẩu?</strong> ở trên để nhận thư khôi phục về mail gốc.
                    </p>
                    <x-input-error :messages="$errors->changeEmail->get('password')" class="mt-1.5 text-xs text-rose-500" />
                </div>

                <!-- Modal Footer -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button"
                            @click="showEmailModal = false"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold uppercase tracking-wider transition cursor-pointer">
                        Hủy
                    </button>
                    <button type="submit"
                            :disabled="newEmailError !== ''"
                            class="px-5 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 active:scale-[0.98] text-white text-xs font-extrabold uppercase tracking-wider shadow-md shadow-pink-500/25 transition disabled:opacity-50 cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Xác nhận đổi Email</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
