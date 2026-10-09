<section>
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

                    if (this.cropperInstance) {
                        this.cropperInstance.destroy();
                        this.cropperInstance = null;
                    }

                    img.src = this.rawImageSrc;
                    img.onload = () => {
                        if (typeof Cropper === 'undefined') {
                            console.error('Cropper.js chưa sẵn sàng');
                            return;
                        }
                        this.cropperInstance = new Cropper(img, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.9,
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: false,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                        });
                    };
                });
            },

            applyCroppedImage() {
                if (!this.cropperInstance) return;

                const canvas = this.cropperInstance.getCroppedCanvas({
                    width: 500,
                    height: 500,
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
            <label for="email" class="block font-medium text-sm text-slate-700 mb-1">
                Địa chỉ Email <span class="text-rose-500">*</span>
            </label>
            <input id="email" 
                   name="email" 
                   type="email" 
                   class="block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition duration-150" 
                   value="{{ old('email', $user->email) }}" 
                   required 
                   autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2 p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800">
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
</section>
