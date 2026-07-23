<div id="partGReviewerSignatureModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-2xl rounded-lg bg-white p-5 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="partGReviewerSignatureModalTitle">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h3 id="partGReviewerSignatureModalTitle" class="text-lg font-bold text-slate-900">Sign &amp; Approve Assessment</h3>
                <p class="mt-1 text-sm text-slate-600">Draw your signature below or upload a signature image, then approve this competency assessment as completed.</p>
            </div>
            <button type="button" id="partGReviewerSignatureModalClose" class="text-2xl leading-none text-slate-500 hover:text-slate-800" aria-label="Close">&times;</button>
        </div>

        <div class="space-y-4">
            <div>
                <div class="mb-1 flex items-center justify-between gap-2">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-700">Signature panel</label>
                    <button
                        type="button"
                        id="partGReviewerSignatureClear"
                        class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50"
                    >Clear</button>
                </div>
                <canvas id="partGReviewerSignatureCanvas" width="640" height="220" class="h-[220px] w-full rounded-md border border-slate-300 bg-white touch-none"></canvas>
                <p class="mt-1 text-[11px] text-slate-500">Draw your signature or upload an image below. Use Clear to start over.</p>
            </div>

            <div>
                <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-700">Or choose a signature file</p>
                <div class="flex flex-wrap items-center gap-2">
                    <label
                        for="partGReviewerSignatureUpload"
                        class="inline-flex cursor-pointer items-center rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 shadow-sm hover:bg-slate-50"
                    >Choose file…</label>
                    <input
                        type="file"
                        id="partGReviewerSignatureUpload"
                        name="reviewer_signature_upload"
                        accept=".png,.jpg,.jpeg,.webp"
                        class="hidden"
                    >
                    <span id="partGReviewerSignatureUploadName" class="hidden text-xs font-medium text-emerald-700"></span>
                </div>
                <p class="mt-1 text-[11px] text-slate-500">Use the file browser to find your signature image, select one PNG/JPG/WEBP file, and it will appear in the panel above.</p>
            </div>

            <p id="partGReviewerSignatureError" class="hidden rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800"></p>
        </div>

        <div class="mt-5 flex justify-end gap-2">
            <button type="button" id="partGReviewerSignatureCancel" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Cancel</button>
            <button type="button" id="partGReviewerSignatureConfirm" class="inline-flex min-w-[12rem] items-center justify-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-black disabled:cursor-not-allowed disabled:opacity-70">
                <span data-signature-confirm-label>Confirm Signature &amp; Approve</span>
                <span data-signature-confirm-loading class="hidden items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Signing…
                </span>
            </button>
        </div>
    </div>
</div>

<input type="hidden" name="reviewer_signature_data" id="partGReviewerSignatureData" value="">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('partGReviewerSignatureModal');
        var openBtn = document.getElementById('partGOpenReviewerSignatureModal');
        var form = document.getElementById('partGCompetencyWorkflowForm');
        var canvas = document.getElementById('partGReviewerSignatureCanvas');
        var hiddenData = document.getElementById('partGReviewerSignatureData');
        var uploadInput = document.getElementById('partGReviewerSignatureUpload');
        var uploadName = document.getElementById('partGReviewerSignatureUploadName');
        var errorBox = document.getElementById('partGReviewerSignatureError');
        var confirmBtn = document.getElementById('partGReviewerSignatureConfirm');
        var cancelBtn = document.getElementById('partGReviewerSignatureCancel');
        var closeBtn = document.getElementById('partGReviewerSignatureModalClose');
        if (!modal || !openBtn || !form || !canvas || !hiddenData) return;

        function setSignatureConfirmLoading(loading) {
            if (!confirmBtn) {
                return;
            }

            var label = confirmBtn.querySelector('[data-signature-confirm-label]');
            var spinner = confirmBtn.querySelector('[data-signature-confirm-loading]');

            confirmBtn.disabled = Boolean(loading);
            if (label) {
                label.classList.toggle('hidden', loading);
            }
            if (spinner) {
                spinner.classList.toggle('hidden', !loading);
                spinner.classList.toggle('inline-flex', loading);
            }

            [cancelBtn, closeBtn].forEach(function(button) {
                if (button) {
                    button.disabled = Boolean(loading);
                }
            });
        }

        var ctx = canvas.getContext('2d');
        var drawing = false;
        var hasDrawing = false;
        var hasUploadedImage = false;

        function setError(message) {
            if (!errorBox) return;
            if (!message) {
                errorBox.textContent = '';
                errorBox.classList.add('hidden');
                return;
            }
            errorBox.textContent = message;
            errorBox.classList.remove('hidden');
        }

        function hideUploadName() {
            if (!uploadName) return;
            uploadName.textContent = '';
            uploadName.classList.add('hidden');
        }

        function showUploadName(fileName) {
            if (!uploadName) return;
            uploadName.textContent = 'Selected: ' + fileName;
            uploadName.classList.remove('hidden');
        }

        function resizeCanvas() {
            var ratio = window.devicePixelRatio || 1;
            var width = canvas.clientWidth;
            var height = canvas.clientHeight || 220;
            canvas.width = width * ratio;
            canvas.height = height * ratio;
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#111827';
        }

        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.clientWidth, canvas.clientHeight || 220);
            hasDrawing = false;
            hasUploadedImage = false;
            hiddenData.value = '';
            hideUploadName();
        }

        function drawUploadedImage(file) {
            setError('');
            var reader = new FileReader();
            reader.onload = function(event) {
                var img = new Image();
                img.onload = function() {
                    clearCanvas();
                    resizeCanvas();

                    var displayWidth = canvas.clientWidth;
                    var displayHeight = canvas.clientHeight || 220;
                    var maxScale = Math.min(displayWidth / img.width, displayHeight / img.height);
                    var scale = maxScale * 0.85;
                    var width = img.width * scale;
                    var height = img.height * scale;
                    var x = (displayWidth - width) / 2;
                    var y = (displayHeight - height) / 2;

                    ctx.drawImage(img, x, y, width, height);
                    hasUploadedImage = true;
                    hasDrawing = true;
                    showUploadName(file.name);
                };
                img.onerror = function() {
                    setError('Unable to load the selected image. Please try another file.');
                    if (uploadInput) uploadInput.value = '';
                    hideUploadName();
                };
                img.src = event.target.result;
            };
            reader.onerror = function() {
                setError('Unable to read the selected image. Please try another file.');
                if (uploadInput) uploadInput.value = '';
                hideUploadName();
            };
            reader.readAsDataURL(file);
        }

        function openModal() {
            setSignatureConfirmLoading(false);
            setError('');
            clearCanvas();
            if (uploadInput) uploadInput.value = '';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            resizeCanvas();
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function pointerPosition(event) {
            var rect = canvas.getBoundingClientRect();
            return {
                x: event.clientX - rect.left,
                y: event.clientY - rect.top
            };
        }

        canvas.addEventListener('pointerdown', function(event) {
            if (hasUploadedImage) {
                clearCanvas();
                resizeCanvas();
                if (uploadInput) uploadInput.value = '';
            }

            drawing = true;
            hasDrawing = true;
            canvas.setPointerCapture(event.pointerId);
            var pos = pointerPosition(event);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        });

        canvas.addEventListener('pointermove', function(event) {
            if (!drawing) return;
            var pos = pointerPosition(event);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        });

        function stopDrawing(event) {
            if (!drawing) return;
            drawing = false;
            if (event && canvas.hasPointerCapture(event.pointerId)) {
                canvas.releasePointerCapture(event.pointerId);
            }
        }

        canvas.addEventListener('pointerup', stopDrawing);
        canvas.addEventListener('pointerleave', stopDrawing);

        openBtn.addEventListener('click', function() {
            openModal();
        });

        ['partGReviewerSignatureModalClose', 'partGReviewerSignatureCancel'].forEach(function(id) {
            var button = document.getElementById(id);
            if (button) button.addEventListener('click', closeModal);
        });

        var clearBtn = document.getElementById('partGReviewerSignatureClear');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                setError('');
                clearCanvas();
                if (uploadInput) uploadInput.value = '';
                resizeCanvas();
            });
        }

        if (uploadInput) {
            uploadInput.addEventListener('change', function() {
                if (!uploadInput.files || uploadInput.files.length === 0) {
                    hideUploadName();
                    return;
                }

                var file = uploadInput.files[0];
                if (!/^image\/(png|jpe?g|webp)$/i.test(file.type || '') && !/\.(png|jpe?g|webp)$/i.test(file.name || '')) {
                    setError('Please choose a PNG, JPG, or WEBP image.');
                    uploadInput.value = '';
                    hideUploadName();
                    return;
                }

                drawUploadedImage(file);
            });
        }

        if (confirmBtn) confirmBtn.addEventListener('click', function() {
            if (!hasDrawing) {
                setError('Draw your signature or upload a signature image before continuing.');
                return;
            }

            hiddenData.value = canvas.toDataURL('image/png');

            var actionInput = document.getElementById('partGWorkflowAction');
            if (actionInput) actionInput.value = 'approve';

            setSignatureConfirmLoading(true);
            form.submit();
        });

        window.addEventListener('resize', resizeCanvas);
    });
</script>
