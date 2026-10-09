@props(['course' => null])

@php
    $currentBanner = $course && !empty($course->thumbnail) ? $course->banner_url : '';
    $currentUrl = $course && (str_starts_with($course->thumbnail ?? '', 'http://') || str_starts_with($course->thumbnail ?? '', 'https://')) ? $course->thumbnail : '';
    $initialHasBanner = !empty($currentBanner);
@endphp

<div x-data="{
    tab: '{{ $currentUrl ? 'url' : 'upload' }}',
    previewUrl: '{{ $currentBanner }}',
    rawImageSrc: '{{ $currentBanner }}',
    customUrl: '{{ $currentUrl }}',
    hasBanner: {{ $initialHasBanner ? 'true' : 'false' }},
    isCropped: false,
    showCropModal: false,
    cropperInstance: null,
    courseTitle: '',
    instructorName: '{{ auth()->user()->name ?? 'Giảng viên' }}',

    init() {
        // Lấy tên khóa học hiện tại từ input #title nếu có
        const titleInput = document.getElementById('title');
        if (titleInput) {
            this.courseTitle = titleInput.value.trim() || '{{ $course->title ?? '' }}';
            titleInput.addEventListener('input', (e) => {
                this.courseTitle = e.target.value.trim();
            });
        } else {
            this.courseTitle = '{{ $course->title ?? '' }}';
        }
    },

    onFileSelected(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Chỉ chấp nhận file ảnh
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
            const img = document.getElementById('banner-crop-target-image');
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
                    aspectRatio: 16 / 9,
                    viewMode: 0, // Cho phép thu nhỏ tự do (viewMode 0) để thấy trọn vẹn toàn bộ ảnh vuông / dọc không bị cắt ép
                    dragMode: 'move',
                    autoCropArea: 0.95,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                    checkCrossOrigin: true,
                    ready: () => {
                        // Tự động căn trọn vẹn vào khung nếu ảnh không phải tỉ lệ 16:9 (ảnh vuông, ảnh dọc)
                        const imageData = this.cropperInstance.getImageData();
                        const cropBoxData = this.cropperInstance.getCropBoxData();
                        if (imageData && cropBoxData) {
                            const imgRatio = imageData.naturalWidth / imageData.naturalHeight;
                            const targetRatio = 16 / 9;
                            // Nếu tỉ lệ ảnh hẹp hơn nhiều so với 16:9 thì căn vừa vặn
                            if (imgRatio < targetRatio * 0.9) {
                                this.fitCropBox();
                            }
                        }
                    }
                });
            };

            img.onload = () => initCropper();
            img.onerror = () => {
                console.error('Lỗi khi tải ảnh vào cropper:', this.rawImageSrc);
                alert('Không thể tải hình ảnh này để cắt. Vui lòng thử tải ảnh từ máy tính hoặc dùng ảnh khác.');
                this.cancelCropModal();
            };

            // Hỗ trợ CORS nếu là liên kết http/https
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
            // Lấy canvas đã crop chất lượng cao chuẩn 16:9 (1280x720) với nền tối thanh lịch
            const canvas = this.cropperInstance.getCroppedCanvas({
                width: 1280,
                height: 720,
                fillColor: '#171717', // Điền nền tối chuẩn nếu người dùng thu nhỏ ảnh vào giữa khung
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            if (!canvas) {
                alert('Không thể xuất ảnh đã cắt. Vui lòng thử lại!');
                return;
            }

            const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.92);
            this.previewUrl = croppedDataUrl;
            this.hasBanner = true;
            this.isCropped = true;

            // Gán base64 vào input hidden dự phòng
            const croppedInput = document.getElementById('thumbnail_cropped_data');
            if (croppedInput) {
                croppedInput.value = croppedDataUrl;
            }

            // Tạo File object và đưa vào input file bằng DataTransfer
            canvas.toBlob((blob) => {
                if (blob) {
                    const file = new File([blob], 'course-banner-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                    const fileInput = document.getElementById('thumbnail_file');
                    if (fileInput) {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        fileInput.files = dt.files;
                    }
                }
            }, 'image/jpeg', 0.92);

            this.cancelCropModal();
        } catch (error) {
            console.error('Lỗi khi áp dụng cắt ảnh:', error);
            alert('Không thể xuất ảnh do nguồn ảnh bị giới hạn bảo mật (CORS). Vui lòng lưu ảnh về máy và tải lên trực tiếp để cắt.');
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

    fitCropBox() {
        if (!this.cropperInstance) return;
        const cropBoxData = this.cropperInstance.getCropBoxData();
        const imageData = this.cropperInstance.getImageData();
        if (!cropBoxData || !imageData) return;

        // Tỉ lệ scale để toàn bộ ảnh nằm trọn trong cropbox
        const scaleX = cropBoxData.width / imageData.naturalWidth;
        const scaleY = cropBoxData.height / imageData.naturalHeight;
        const scale = Math.min(scaleX, scaleY);

        const newWidth = imageData.naturalWidth * scale;
        const newHeight = imageData.naturalHeight * scale;
        const left = cropBoxData.left + (cropBoxData.width - newWidth) / 2;
        const top = cropBoxData.top + (cropBoxData.height - newHeight) / 2;

        this.cropperInstance.setCanvasData({
            left: left,
            top: top,
            width: newWidth,
            height: newHeight,
        });
    },

    fillCropBox() {
        if (!this.cropperInstance) return;
        const cropBoxData = this.cropperInstance.getCropBoxData();
        const imageData = this.cropperInstance.getImageData();
        if (!cropBoxData || !imageData) return;

        // Tỉ lệ scale để ảnh phủ kín toàn bộ cropbox
        const scaleX = cropBoxData.width / imageData.naturalWidth;
        const scaleY = cropBoxData.height / imageData.naturalHeight;
        const scale = Math.max(scaleX, scaleY);

        const newWidth = imageData.naturalWidth * scale;
        const newHeight = imageData.naturalHeight * scale;
        const left = cropBoxData.left + (cropBoxData.width - newWidth) / 2;
        const top = cropBoxData.top + (cropBoxData.height - newHeight) / 2;

        this.cropperInstance.setCanvasData({
            left: left,
            top: top,
            width: newWidth,
            height: newHeight,
        });
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
        const fileInput = document.getElementById('thumbnail_file');
        if (fileInput) {
            fileInput.click();
        }
    },

    removeCurrentBanner() {
        this.previewUrl = '';
        this.rawImageSrc = '';
        this.hasBanner = false;
        this.isCropped = false;
        this.customUrl = '';

        const fileInput = document.getElementById('thumbnail_file');
        if (fileInput) fileInput.value = '';

        const croppedInput = document.getElementById('thumbnail_cropped_data');
        if (croppedInput) croppedInput.value = '';

        const removeInput = document.getElementById('remove_thumbnail');
        if (removeInput) removeInput.value = '1';
    },

    applyCustomUrl() {
        if (this.customUrl && this.customUrl.trim() !== '') {
            this.previewUrl = this.customUrl.trim();
            this.rawImageSrc = this.customUrl.trim();
            this.hasBanner = true;
            this.isCropped = false;
        }
    }
}" class="space-y-4">

    <!-- Header Section -->
    <div class="flex items-center justify-between border-b border-pink-100 pb-3">
        <div>
            <label class="block text-xs font-black uppercase tracking-wider text-neutral-800">
                Ảnh Banner Khóa học
            </label>
            <p class="text-xs text-neutral-500 mt-0.5">
                Tải ảnh bìa từ máy tính và cắt ảnh theo tỉ lệ 16:9 chuẩn.
            </p>
        </div>
        <span class="whitespace-nowrap shrink-0 px-2.5 py-1 rounded-full text-xs font-bold bg-pink-50 text-pink-700 border border-pink-200">
            Tỉ lệ 16:9
        </span>
    </div>

    <!-- Hidden inputs for file submission -->
    <input type="file" 
           name="thumbnail_file" 
           id="thumbnail_file" 
           accept="image/png, image/jpeg, image/jpg, image/webp"
           @change="onFileSelected($event)"
           class="hidden">

    <input type="hidden" 
           name="thumbnail_cropped_data" 
           id="thumbnail_cropped_data" 
           value="">

    <input type="hidden" 
           name="remove_thumbnail" 
           id="remove_thumbnail" 
           value="0">

    <!-- Mode Selector Tabs: Upload vs URL -->
    <div class="flex items-center gap-2 p-1 bg-neutral-100 rounded-xl border border-neutral-200 max-w-sm">
        <button type="button" 
                @click="tab = 'upload'"
                :class="tab === 'upload' ? 'bg-neutral-950 text-white shadow-xs' : 'text-neutral-600 hover:text-neutral-900'"
                class="flex-1 py-1.5 px-3 rounded-lg text-center font-bold text-xs transition uppercase tracking-wider">
            Tải ảnh & Cắt ảnh
        </button>
        <button type="button" 
                @click="tab = 'url'"
                :class="tab === 'url' ? 'bg-neutral-950 text-white shadow-xs' : 'text-neutral-600 hover:text-neutral-900'"
                class="flex-1 py-1.5 px-3 rounded-lg text-center font-bold text-xs transition uppercase tracking-wider">
            Đường dẫn URL
        </button>
    </div>

    <!-- TAB 1: UPLOAD & CROP -->
    <div x-show="tab === 'upload'" class="space-y-4">
        <!-- Empty / Upload Dropzone when no banner is chosen -->
        <div x-show="!hasBanner" 
             @click="triggerFileInput()"
             class="border-2 border-dashed border-pink-200 hover:border-pink-500 bg-pink-50/30 hover:bg-pink-50/70 rounded-2xl p-8 text-center cursor-pointer transition-all duration-200 group">
            <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-white border border-pink-200 flex items-center justify-center text-pink-600 group-hover:scale-110 shadow-sm transition-transform">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-sm font-bold text-neutral-800 mb-1">
                Nhấn vào đây để tải ảnh bìa lên từ máy tính
            </div>
            <p class="text-xs text-neutral-500 max-w-md mx-auto">
                Hỗ trợ định dạng PNG, JPG, JPEG, WEBP.
            </p>
            <div class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                <span>Chọn tệp ảnh</span>
                <span class="text-pink-200">&rarr;</span>
            </div>
        </div>

        <!-- OUTSIDE LIVE PREVIEW WIDGET: Hiển thị giao diện thực tế bên ngoài -->
        <div x-show="hasBanner" class="space-y-3">
            <div class="p-5 rounded-3xl bg-neutral-950 text-white border border-neutral-800 shadow-2xl relative overflow-hidden">
                <!-- Background ambient glow -->
                <div class="absolute -top-24 -right-24 w-72 h-72 bg-pink-600/15 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Preview Header -->
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-white/10 relative z-10">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span>
                        <h4 class="text-xs font-black uppercase tracking-wider text-white">
                            Xem trước thẻ khóa học
                        </h4>
                    </div>
                    <span class="text-[11px] text-pink-400 font-bold bg-pink-950 px-2.5 py-0.5 rounded-full border border-pink-800 whitespace-nowrap shrink-0">
                        Tỉ lệ 16:9
                    </span>
                </div>

                <!-- Two-column: Left is Authentic Miniature Course Card, Right is Info & Action Controls -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center relative z-10">
                    
                    <!-- LEFT: Authentic Mini Course Card Mockup -->
                    <div class="md:col-span-7 flex justify-center">
                        <div class="w-full max-w-sm bg-white rounded-2xl overflow-hidden border border-pink-200 shadow-xl text-neutral-900 group">
                            <!-- Banner Container (16:9) -->
                            <div class="relative aspect-video w-full overflow-hidden bg-neutral-900">
                                <img :src="previewUrl" 
                                     alt="Banner xem trước" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute inset-0 bg-gradient-to-t from-neutral-950/80 via-transparent to-black/20"></div>

                                <!-- Overlay badges mimicking actual website card -->
                                <div class="absolute top-2.5 left-2.5 right-2.5 flex items-center justify-between pointer-events-none">
                                    <span class="px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider bg-neutral-950/90 text-pink-400 border border-pink-500/30">
                                        Khóa học
                                    </span>
                                    <span class="text-[10px] font-black px-1.5 py-0.5 bg-pink-600 text-white rounded-xs">
                                        #01
                                    </span>
                                </div>

                                <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-center justify-between text-white text-[11px] font-bold pointer-events-none">
                                    <span class="text-neutral-200 text-[10px]">10 bài giảng</span>
                                    <span class="text-neutral-300 text-[10px]">{{ date('d/m/Y') }}</span>
                                </div>
                            </div>

                            <!-- Card Body Mockup -->
                            <div class="p-4 space-y-2.5">
                                <h5 class="font-extrabold text-sm text-neutral-900 line-clamp-1 group-hover:text-pink-600 transition-colors"
                                    x-text="courseTitle || 'Tên khóa học của bạn...'">
                                </h5>

                                <div class="flex items-center justify-between pt-2 border-t border-neutral-100 text-[11px]">
                                    <div class="flex items-center gap-1.5 text-neutral-600">
                                        <div class="w-5 h-5 rounded-full bg-pink-600 text-white font-bold flex items-center justify-center text-[10px]">
                                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <span class="font-semibold truncate max-w-[120px]" x-text="instructorName"></span>
                                    </div>
                                    <span class="text-pink-600 font-bold">Xem chi tiết &rarr;</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: Action Controls -->
                    <div class="md:col-span-5 space-y-2 text-left">

                        <!-- Action Controls -->
                        <div class="pt-2 flex flex-col gap-2">
                            <button type="button" 
                                    @click="reCropCurrent()"
                                    class="w-full inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-500 hover:to-rose-500 text-white text-xs font-bold uppercase tracking-wider shadow-md shadow-pink-600/30 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Cắt lại ảnh này
                            </button>

                            <button type="button" 
                                    @click="triggerFileInput()"
                                    class="w-full inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white text-xs font-semibold border border-white/10 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Chọn ảnh khác từ máy
                            </button>

                            <button type="button" 
                                    @click="removeCurrentBanner()"
                                    class="w-full inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 text-xs font-semibold border border-rose-500/20 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Gỡ ảnh này
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: EXTERNAL URL -->
    <div x-show="tab === 'url'" class="space-y-4">
        <div class="space-y-2">
            <div class="flex gap-2">
                <input type="url" 
                       name="thumbnail_url" 
                       x-model="customUrl"
                       placeholder="https://example.com/banner.jpg"
                       class="block w-full rounded-xl border-neutral-200 px-3 py-2 text-sm focus:border-pink-500 focus:ring-1 focus:ring-pink-500">
                <button type="button" 
                        @click="applyCustomUrl()"
                        class="px-5 py-2 bg-neutral-950 hover:bg-pink-600 text-white text-xs font-bold uppercase tracking-wider shrink-0 rounded-xl transition">
                    Áp dụng
                </button>
            </div>
            <p class="text-[11px] text-neutral-400">
                Dán trực tiếp URL ảnh từ Unsplash, Imgur hoặc trang web ảnh ngoài.
            </p>
        </div>

        <!-- OUTSIDE LIVE PREVIEW FOR URL TAB IF LOADED -->
        <div x-show="hasBanner" class="p-4 rounded-2xl bg-neutral-950 text-white border border-neutral-800">
            <div class="text-xs font-bold text-neutral-300 mb-3 flex items-center justify-between">
                <span>Xem trước ảnh từ đường dẫn URL:</span>
                <div class="flex items-center gap-3">
                    <button type="button" @click="reCropCurrent()" class="text-pink-400 hover:underline text-[11px] font-bold">Cắt ảnh này</button>
                    <button type="button" @click="removeCurrentBanner()" class="text-rose-400 hover:underline text-[11px]">Gỡ ảnh</button>
                </div>
            </div>
            <div class="aspect-video w-full max-w-md mx-auto rounded-xl overflow-hidden bg-neutral-900 border border-neutral-700">
                <img :src="previewUrl" alt="URL preview" class="w-full h-full object-cover">
            </div>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- CROP MODAL POPUP DIALOG (Tỉ lệ chuẩn 16:9 với Cropper.js)      -->
    <!-- ============================================================= -->
    <div x-show="showCropModal" 
         x-transition.opacity
         class="fixed inset-0 z-[99999] bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-6 overflow-y-auto"
         style="display: none;">
        
        <div @click.away="cancelCropModal()" 
             class="bg-neutral-950 text-white border border-neutral-800 rounded-3xl w-full max-w-4xl shadow-2xl overflow-hidden flex flex-col my-auto max-h-[92vh]">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-pink-500 shadow-sm shadow-pink-500"></span>
                    <div>
                        <h3 class="text-base font-black uppercase tracking-tight text-white">
                            Cắt & Căn Chỉnh Banner Khóa Học
                        </h3>
                        <p class="text-xs text-neutral-400">
                            Khung cắt được khóa cố định tỉ lệ <strong>16:9</strong> chuẩn nhất cho khóa học
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
            <div class="relative bg-neutral-950 p-4 flex-1 flex items-center justify-center min-h-[340px] max-h-[58vh] overflow-hidden w-full">
                <div class="w-full h-full flex items-center justify-center max-h-[54vh]">
                    <img id="banner-crop-target-image" 
                         src="" 
                         alt="Cắt ảnh banner" 
                         class="max-w-full max-h-[52vh] block">
                </div>
            </div>

            <!-- Toolbar Controls -->
            <div class="px-6 py-3 bg-neutral-900 border-t border-white/5 flex flex-wrap items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <button type="button" 
                            @click="fitCropBox()"
                            title="Căn vừa toàn bộ ảnh vào khung 16:9 (dành cho ảnh vuông hoặc dọc)"
                            class="px-3 py-1.5 rounded-lg bg-pink-600/30 hover:bg-pink-600/50 text-xs font-bold text-pink-300 border border-pink-500/30 transition flex items-center gap-1">
                        <span>⤢ Căn vừa (Fit)</span>
                    </button>
                    <button type="button" 
                            @click="fillCropBox()"
                            title="Phóng to lấp đầy khung 16:9"
                            class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold text-white transition flex items-center gap-1">
                        <span>⬛ Lấp đầy (Fill)</span>
                    </button>
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
                    Dùng chuột kéo thả hoặc con lăn để căn góc đẹp nhất
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
                    <span>Áp dụng cắt ảnh & Xem trước</span>
                </button>
            </div>

        </div>
    </div>

</div>
