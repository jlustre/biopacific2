<div id="partGEmployeeSignatureModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-2xl rounded-lg bg-white p-5 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="partGEmployeeSignatureModalTitle">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h3 id="partGEmployeeSignatureModalTitle" class="text-lg font-bold text-slate-900">Sign Competency Assessment</h3>
                <p class="mt-1 text-sm text-slate-600">Draw your signature below or upload a signature image, then confirm your acknowledgement.</p>
                <p id="partGEmployeeSignatureEmployeeName" class="mt-2 hidden text-sm font-semibold text-slate-900"></p>
            </div>
            <button type="button" id="partGEmployeeSignatureModalClose" class="text-2xl leading-none text-slate-500 hover:text-slate-800" aria-label="Close">&times;</button>
        </div>

        <div class="space-y-4">
            <div>
                <div class="mb-1 flex items-center justify-between gap-2">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-700">Signature panel</label>
                    <button
                        type="button"
                        id="partGEmployeeSignatureClear"
                        class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50"
                    >Clear</button>
                </div>
                <canvas id="partGEmployeeSignatureCanvas" width="640" height="280" class="h-[280px] w-full rounded-md border border-slate-300 bg-white touch-none"></canvas>
                <div id="partGEmployeeSignatureScaleControls" class="mt-2 hidden flex-wrap items-center gap-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-600">Signature size</span>
                    <button type="button" id="partGEmployeeSignatureScaleDown" class="rounded-md border border-slate-300 bg-white px-2.5 py-1 text-sm font-bold text-slate-700 hover:bg-slate-50" aria-label="Make signature smaller">−</button>
                    <input type="range" id="partGEmployeeSignatureScaleRange" min="25" max="100" value="65" class="min-w-[140px] flex-1 accent-sky-600">
                    <button type="button" id="partGEmployeeSignatureScaleUp" class="rounded-md border border-slate-300 bg-white px-2.5 py-1 text-sm font-bold text-slate-700 hover:bg-slate-50" aria-label="Make signature larger">+</button>
                </div>
                <p class="mt-1 text-[11px] text-slate-500">Draw your signature or upload an image below. Use Clear to start over.</p>
            </div>

            <div>
                <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-700">Or choose a signature file</p>
                <div class="flex flex-wrap items-center gap-2">
                    <label
                        for="partGEmployeeSignatureUpload"
                        class="inline-flex cursor-pointer items-center rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 shadow-sm hover:bg-slate-50"
                    >Choose file…</label>
                    <input
                        type="file"
                        id="partGEmployeeSignatureUpload"
                        accept=".png,.jpg,.jpeg,.webp"
                        class="hidden"
                    >
                    <span id="partGEmployeeSignatureSelectedFile" class="hidden text-xs font-medium text-emerald-700"></span>
                </div>
                <p class="mt-1 text-[11px] text-slate-500">Use the file browser to find your signature image, select one PNG/JPG/WEBP file, and it will appear in the panel above.</p>
            </div>

            <p id="partGEmployeeSignatureError" class="hidden rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800"></p>
        </div>

        <div class="mt-5 flex justify-end gap-2">
            <button type="button" id="partGEmployeeSignatureCancel" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Cancel</button>
            <button type="button" id="partGEmployeeSignatureConfirm" class="inline-flex min-w-[12rem] items-center justify-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-black disabled:cursor-not-allowed disabled:opacity-70">
                <span data-signature-confirm-label>Confirm Signature &amp; Acknowledge</span>
                <span data-signature-confirm-loading class="hidden inline-flex items-center gap-2">
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

<div id="partGReviewerSignatureModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-2xl rounded-lg bg-white p-5 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="partGReviewerSignatureModalTitle">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h3 id="partGReviewerSignatureModalTitle" class="text-lg font-bold text-slate-900">Sign &amp; Complete Assessment</h3>
                <p class="mt-1 text-sm text-slate-600">Draw your signature below or upload a signature image, then complete this competency assessment.</p>
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
                <canvas id="partGReviewerSignatureCanvas" width="640" height="280" class="h-[280px] w-full rounded-md border border-slate-300 bg-white touch-none"></canvas>
                <div id="partGReviewerSignatureScaleControls" class="mt-2 hidden flex-wrap items-center gap-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-600">Signature size</span>
                    <button type="button" id="partGReviewerSignatureScaleDown" class="rounded-md border border-slate-300 bg-white px-2.5 py-1 text-sm font-bold text-slate-700 hover:bg-slate-50" aria-label="Make signature smaller">−</button>
                    <input type="range" id="partGReviewerSignatureScaleRange" min="25" max="100" value="65" class="min-w-[140px] flex-1 accent-sky-600">
                    <button type="button" id="partGReviewerSignatureScaleUp" class="rounded-md border border-slate-300 bg-white px-2.5 py-1 text-sm font-bold text-slate-700 hover:bg-slate-50" aria-label="Make signature larger">+</button>
                </div>
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
                        accept=".png,.jpg,.jpeg,.webp"
                        class="hidden"
                    >
                    <span id="partGReviewerSignatureSelectedFile" class="hidden text-xs font-medium text-emerald-700"></span>
                </div>
                <p class="mt-1 text-[11px] text-slate-500">Use the file browser to find your signature image, select one PNG/JPG/WEBP file, and it will appear in the panel above.</p>
            </div>

            <p id="partGReviewerSignatureError" class="hidden rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800"></p>
        </div>

        <div class="mt-5 flex justify-end gap-2">
            <button type="button" id="partGReviewerSignatureCancel" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Cancel</button>
            <button type="button" id="partGReviewerSignatureConfirm" class="inline-flex min-w-[12rem] items-center justify-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-black disabled:cursor-not-allowed disabled:opacity-70">
                <span data-signature-confirm-label>Confirm Signature &amp; Complete</span>
                <span data-signature-confirm-loading class="hidden inline-flex items-center gap-2">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var activeFormKey = null;

        function setSignatureConfirmLoading(confirmBtn, relatedButtons, loading) {
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
            }

            (relatedButtons || []).forEach(function(button) {
                if (button) {
                    button.disabled = Boolean(loading);
                }
            });
        }

        function formForKey(key) {
            return document.querySelector('[data-partg-workflow-form="' + key + '"]');
        }

        function actionInputForForm(form) {
            if (!form) return null;
            var key = form.getAttribute('data-partg-workflow-form');
            return document.getElementById('partGWorkflowAction-' + key);
        }

        function hiddenFieldForForm(form, fieldIdPrefix) {
            if (!form) return null;
            var key = form.getAttribute('data-partg-workflow-form');
            return document.getElementById(fieldIdPrefix + '-' + key);
        }

        function removeDynamicUpload(form, fieldName) {
            if (!form) return;
            form.querySelectorAll('input[data-partg-dynamic-upload="' + fieldName + '"]').forEach(function(node) {
                node.remove();
            });
        }

        function attachUploadToForm(form, sourceInput, fieldName) {
            removeDynamicUpload(form, fieldName);
            if (!form || !sourceInput || !sourceInput.files || sourceInput.files.length === 0) {
                return;
            }

            var clone = sourceInput.cloneNode(true);
            clone.name = fieldName;
            clone.setAttribute('data-partg-dynamic-upload', fieldName);
            clone.classList.add('hidden');
            form.appendChild(clone);
        }

        document.querySelectorAll('[data-partg-workflow-form]').forEach(function(form) {
            var actionInput = actionInputForForm(form);

            form.addEventListener('click', function(event) {
                var button = event.target.closest('[data-partg-action]');
                if (!button || !form.contains(button)) {
                    return;
                }

                if (actionInput) {
                    actionInput.value = button.getAttribute('data-partg-action') || '';
                }
            });

            form.addEventListener('submit', function(event) {
                var submitter = event.submitter;
                if (submitter && submitter.getAttribute('data-partg-action') && actionInput) {
                    actionInput.value = submitter.getAttribute('data-partg-action') || '';
                }
            });
        });

        document.addEventListener('click', function(event) {
            var employeeOpen = event.target.closest('.partg-open-employee-signature-modal');
            if (employeeOpen) {
                activeFormKey = employeeOpen.getAttribute('data-partg-form');
                if (window.partGEmployeeSignature && typeof window.partGEmployeeSignature.open === 'function') {
                    window.partGEmployeeSignature.open(activeFormKey);
                }
                return;
            }

            var reviewerOpen = event.target.closest('.partg-open-reviewer-signature-modal');
            if (reviewerOpen) {
                activeFormKey = reviewerOpen.getAttribute('data-partg-form');
                if (window.partGReviewerSignature && typeof window.partGReviewerSignature.open === 'function') {
                    window.partGReviewerSignature.open(activeFormKey);
                }
            }
        });

        function initSignatureModal(config) {
            var modal = document.getElementById(config.modalId);
            var canvas = document.getElementById(config.canvasId);
            var uploadInput = document.getElementById(config.uploadId);
            var uploadFileName = config.uploadFileNameId ? document.getElementById(config.uploadFileNameId) : null;
            var scaleControls = config.scaleControlsId ? document.getElementById(config.scaleControlsId) : null;
            var scaleRange = config.scaleRangeId ? document.getElementById(config.scaleRangeId) : null;
            var scaleDownBtn = config.scaleDownId ? document.getElementById(config.scaleDownId) : null;
            var scaleUpBtn = config.scaleUpId ? document.getElementById(config.scaleUpId) : null;
            var errorBox = document.getElementById(config.errorId);
            var confirmBtn = document.getElementById(config.confirmId);
            var cancelBtn = config.cancelId ? document.getElementById(config.cancelId) : null;
            var closeBtn = config.closeId ? document.getElementById(config.closeId) : null;
            if (!modal || !canvas || !confirmBtn) {
                return null;
            }

            var ctx = canvas.getContext('2d');
            var strokeLayer = document.createElement('canvas');
            var strokeCtx = strokeLayer.getContext('2d');
            var drawing = false;
            var hasDrawing = false;
            var uploadedImg = null;
            var imageRect = null;
            var interaction = null;
            var interactionStart = null;
            var HANDLE_SIZE = 10;
            var HANDLE_HIT = 16;
            var HANDLE_OFFSET = 5;
            var MIN_IMAGE_SIZE = 32;
            var PANEL_PADDING = 6;
            var INITIAL_UPLOAD_SCALE = 0.65;

            function canvasSize() {
                return {
                    width: canvas.clientWidth,
                    height: canvas.clientHeight || 280
                };
            }

            function setSelectedFileName(name) {
                if (!uploadFileName) {
                    return;
                }

                if (!name) {
                    uploadFileName.textContent = '';
                    uploadFileName.classList.add('hidden');

                    return;
                }

                uploadFileName.textContent = 'Selected: ' + name;
                uploadFileName.classList.remove('hidden');
            }

            function toggleScaleControls(show) {
                if (!scaleControls) {
                    return;
                }

                scaleControls.classList.toggle('hidden', !show);
                scaleControls.classList.toggle('flex', show);
            }

            function maxFitSize(img) {
                var size = canvasSize();
                var maxWidth = size.width - (PANEL_PADDING * 2);
                var maxHeight = size.height - (PANEL_PADDING * 2);
                var scale = Math.min(maxWidth / img.width, maxHeight / img.height);

                return {
                    width: img.width * scale,
                    height: img.height * scale,
                };
            }

            function currentScalePercent() {
                if (!uploadedImg || !imageRect) {
                    return INITIAL_UPLOAD_SCALE * 100;
                }

                var fit = maxFitSize(uploadedImg);
                if (fit.width <= 0 || fit.height <= 0) {
                    return 65;
                }

                return Math.round(Math.min(100, Math.max(25, (imageRect.w / fit.width) * 100)));
            }

            function syncScaleRange() {
                if (!scaleRange) {
                    return;
                }

                scaleRange.value = String(currentScalePercent());
            }

            function applySignatureScale(percent) {
                if (!uploadedImg) {
                    return;
                }

                var size = canvasSize();
                var fit = maxFitSize(uploadedImg);
                var factor = Math.min(100, Math.max(25, percent)) / 100;
                var width = fit.width * factor;
                var height = fit.height * factor;

                imageRect = clampImageRect({
                    x: (size.width - width) / 2,
                    y: (size.height - height) / 2,
                    w: width,
                    h: height,
                });

                syncScaleRange();
                redraw(true);
            }

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

            function syncStrokeLayerSize() {
                var size = canvasSize();
                strokeLayer.width = size.width;
                strokeLayer.height = size.height;
                strokeCtx.lineWidth = 2;
                strokeCtx.lineCap = 'round';
                strokeCtx.strokeStyle = '#111827';
            }

            function resizeCanvas() {
                var ratio = window.devicePixelRatio || 1;
                var size = canvasSize();
                canvas.width = size.width * ratio;
                canvas.height = size.height * ratio;
                ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.strokeStyle = '#111827';
                syncStrokeLayerSize();
            }

            function resetSignatureState() {
                hasDrawing = false;
                uploadedImg = null;
                imageRect = null;
                interaction = null;
                interactionStart = null;
            }

            function clearCanvas() {
                var size = canvasSize();
                ctx.clearRect(0, 0, size.width, size.height);
                strokeCtx.clearRect(0, 0, size.width, size.height);
                resetSignatureState();
                toggleScaleControls(false);
                setSelectedFileName('');
            }

            function hitHandle(pos, rect) {
                var handles = getHandlePositions(rect);
                var keys = ['nw', 'ne', 'sw', 'se'];
                for (var i = 0; i < keys.length; i++) {
                    var key = keys[i];
                    var handle = handles[key];
                    if (Math.abs(pos.x - handle.x) <= HANDLE_HIT && Math.abs(pos.y - handle.y) <= HANDLE_HIT) {
                        return key;
                    }
                }
                return null;
            }

            function pointInRect(pos, rect) {
                return pos.x >= rect.x && pos.x <= rect.x + rect.w && pos.y >= rect.y && pos.y <= rect.y + rect.h;
            }

            function clampImageRect(rect) {
                var size = canvasSize();
                var maxWidth = size.width - (PANEL_PADDING * 2);
                var maxHeight = size.height - (PANEL_PADDING * 2);
                rect.w = Math.max(MIN_IMAGE_SIZE, Math.min(rect.w, maxWidth));
                rect.h = Math.max(MIN_IMAGE_SIZE, Math.min(rect.h, maxHeight));
                rect.x = Math.max(PANEL_PADDING, Math.min(rect.x, size.width - rect.w - PANEL_PADDING));
                rect.y = Math.max(PANEL_PADDING, Math.min(rect.y, size.height - rect.h - PANEL_PADDING));
                return rect;
            }

            function getHandlePositions(rect) {
                return {
                    nw: { x: rect.x - HANDLE_OFFSET, y: rect.y - HANDLE_OFFSET },
                    ne: { x: rect.x + rect.w + HANDLE_OFFSET, y: rect.y - HANDLE_OFFSET },
                    sw: { x: rect.x - HANDLE_OFFSET, y: rect.y + rect.h + HANDLE_OFFSET },
                    se: { x: rect.x + rect.w + HANDLE_OFFSET, y: rect.y + rect.h + HANDLE_OFFSET },
                };
            }

            function redraw(showHandles) {
                var size = canvasSize();
                ctx.clearRect(0, 0, size.width, size.height);
                ctx.drawImage(strokeLayer, 0, 0, size.width, size.height);

                if (!uploadedImg || !imageRect) {
                    return;
                }

                ctx.drawImage(uploadedImg, imageRect.x, imageRect.y, imageRect.w, imageRect.h);

                if (!showHandles) {
                    return;
                }

                ctx.save();
                ctx.strokeStyle = '#0284c7';
                ctx.lineWidth = 1;
                ctx.strokeRect(imageRect.x, imageRect.y, imageRect.w, imageRect.h);

                var handles = getHandlePositions(imageRect);
                Object.keys(handles).forEach(function(key) {
                    var handle = handles[key];
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(handle.x - (HANDLE_SIZE / 2), handle.y - (HANDLE_SIZE / 2), HANDLE_SIZE, HANDLE_SIZE);
                    ctx.strokeRect(handle.x - (HANDLE_SIZE / 2), handle.y - (HANDLE_SIZE / 2), HANDLE_SIZE, HANDLE_SIZE);
                });
                ctx.restore();
            }

            function exportSignatureDataUrl() {
                redraw(false);
                return canvas.toDataURL('image/png');
            }

            function initialImageRect(img) {
                var size = canvasSize();
                var fit = maxFitSize(img);
                var width = fit.width * INITIAL_UPLOAD_SCALE;
                var height = fit.height * INITIAL_UPLOAD_SCALE;

                return clampImageRect({
                    x: (size.width - width) / 2,
                    y: (size.height - height) / 2,
                    w: width,
                    h: height,
                });
            }

            function drawUploadedImage(file) {
                setError('');
                var reader = new FileReader();
                reader.onload = function(event) {
                    var img = new Image();
                    img.onload = function() {
                        strokeCtx.clearRect(0, 0, strokeLayer.width, strokeLayer.height);
                        uploadedImg = img;
                        imageRect = initialImageRect(img);
                        hasDrawing = true;
                        toggleScaleControls(true);
                        syncScaleRange();
                        redraw(true);
                        setSelectedFileName(file.name);
                    };
                    img.onerror = function() {
                        setError('Unable to load the selected image. Please try another file.');
                        if (uploadInput) uploadInput.value = '';
                        setSelectedFileName('');
                    };
                    img.src = event.target.result;
                };
                reader.onerror = function() {
                    setError('Unable to read the selected image. Please try another file.');
                    if (uploadInput) uploadInput.value = '';
                    setSelectedFileName('');
                };
                reader.readAsDataURL(file);
            }

            function openModal(formKey) {
                activeFormKey = formKey;
                setSignatureConfirmLoading(confirmBtn, [cancelBtn, closeBtn], false);
                setError('');
                clearCanvas();
                var hidden = hiddenFieldForForm(formForKey(formKey), config.hiddenIdPrefix);
                if (hidden) hidden.value = '';
                if (uploadInput) uploadInput.value = '';
                setSelectedFileName('');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                resizeCanvas();
                redraw(false);
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

            function resizeImageRect(handle, pos) {
                if (!imageRect || !interactionStart || !uploadedImg) {
                    return;
                }

                var startRect = interactionStart.rect;
                var aspect = uploadedImg.width / uploadedImg.height;
                var next = { x: startRect.x, y: startRect.y, w: startRect.w, h: startRect.h };

                if (handle === 'se') {
                    next.w = Math.max(MIN_IMAGE_SIZE, pos.x - startRect.x);
                    next.h = next.w / aspect;
                } else if (handle === 'sw') {
                    next.w = Math.max(MIN_IMAGE_SIZE, (startRect.x + startRect.w) - pos.x);
                    next.h = next.w / aspect;
                    next.x = (startRect.x + startRect.w) - next.w;
                } else if (handle === 'ne') {
                    next.w = Math.max(MIN_IMAGE_SIZE, pos.x - startRect.x);
                    next.h = next.w / aspect;
                    next.y = (startRect.y + startRect.h) - next.h;
                } else if (handle === 'nw') {
                    next.w = Math.max(MIN_IMAGE_SIZE, (startRect.x + startRect.w) - pos.x);
                    next.h = next.w / aspect;
                    next.x = (startRect.x + startRect.w) - next.w;
                    next.y = (startRect.y + startRect.h) - next.h;
                }

                imageRect = clampImageRect(next);
                syncScaleRange();
                redraw(true);
            }

            canvas.addEventListener('pointerdown', function(event) {
                var pos = pointerPosition(event);

                if (uploadedImg && imageRect) {
                    var handle = hitHandle(pos, imageRect);
                    if (handle) {
                        interaction = 'resize-' + handle;
                        interactionStart = { rect: Object.assign({}, imageRect), pos: pos };
                        canvas.setPointerCapture(event.pointerId);
                        event.preventDefault();
                        return;
                    }

                    if (pointInRect(pos, imageRect)) {
                        interaction = 'move';
                        interactionStart = { rect: Object.assign({}, imageRect), pos: pos };
                        canvas.setPointerCapture(event.pointerId);
                        event.preventDefault();
                        return;
                    }
                }

                if (uploadedImg) {
                    return;
                }

                drawing = true;
                hasDrawing = true;
                canvas.setPointerCapture(event.pointerId);
                strokeCtx.beginPath();
                strokeCtx.moveTo(pos.x, pos.y);
            });

            canvas.addEventListener('pointermove', function(event) {
                var pos = pointerPosition(event);

                if (interaction && interaction.indexOf('resize-') === 0) {
                    resizeImageRect(interaction.replace('resize-', ''), pos);
                    return;
                }

                if (interaction === 'move' && interactionStart) {
                    var dx = pos.x - interactionStart.pos.x;
                    var dy = pos.y - interactionStart.pos.y;
                    imageRect = clampImageRect({
                        x: interactionStart.rect.x + dx,
                        y: interactionStart.rect.y + dy,
                        w: interactionStart.rect.w,
                        h: interactionStart.rect.h
                    });
                    redraw(true);
                    return;
                }

                if (!drawing || uploadedImg) {
                    return;
                }

                strokeCtx.lineTo(pos.x, pos.y);
                strokeCtx.stroke();
                redraw(false);
            });

            function stopInteraction(event) {
                drawing = false;
                interaction = null;
                interactionStart = null;
                if (event && canvas.hasPointerCapture(event.pointerId)) {
                    canvas.releasePointerCapture(event.pointerId);
                }
            }

            canvas.addEventListener('pointerup', stopInteraction);
            canvas.addEventListener('pointerleave', stopInteraction);

            [config.closeId, config.cancelId].forEach(function(id) {
                var button = document.getElementById(id);
                if (button) button.addEventListener('click', closeModal);
            });

            var clearBtn = document.getElementById(config.clearId);
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    setError('');
                    clearCanvas();
                    if (uploadInput) uploadInput.value = '';
                    redraw(false);
                });
            }

            if (uploadInput) {
                uploadInput.addEventListener('change', function() {
                    if (!uploadInput.files || uploadInput.files.length === 0) {
                        setSelectedFileName('');

                        return;
                    }

                    var file = uploadInput.files[0];
                    if (!/^image\/(png|jpe?g|webp)$/i.test(file.type || '') && !/\.(png|jpe?g|webp)$/i.test(file.name || '')) {
                        setError('Please choose a PNG, JPG, or WEBP image.');
                        uploadInput.value = '';
                        setSelectedFileName('');

                        return;
                    }

                    drawUploadedImage(file);
                });
            }

            if (scaleRange) {
                scaleRange.addEventListener('input', function() {
                    applySignatureScale(parseInt(scaleRange.value, 10) || 65);
                });
            }

            if (scaleDownBtn) {
                scaleDownBtn.addEventListener('click', function() {
                    applySignatureScale(currentScalePercent() - 5);
                });
            }

            if (scaleUpBtn) {
                scaleUpBtn.addEventListener('click', function() {
                    applySignatureScale(currentScalePercent() + 5);
                });
            }

            confirmBtn.addEventListener('click', function() {
                var form = formForKey(activeFormKey);
                var hidden = hiddenFieldForForm(form, config.hiddenIdPrefix);
                var actionInput = actionInputForForm(form);

                if (!hasDrawing) {
                    setError('Draw your signature or upload a signature image before continuing.');
                    return;
                }

                if (!form || !hidden || !actionInput) {
                    setError('Unable to locate the active assessment form.');
                    return;
                }

                hidden.value = exportSignatureDataUrl();
                removeDynamicUpload(form, config.uploadFieldName);
                actionInput.value = config.submitAction;
                setSignatureConfirmLoading(confirmBtn, [cancelBtn, closeBtn], true);
                form.submit();
            });

            window.addEventListener('resize', function() {
                if (modal.classList.contains('hidden')) {
                    return;
                }

                var previous = exportSignatureDataUrl();
                resizeCanvas();

                if (!previous || previous.indexOf('data:image/png;base64,') !== 0) {
                    redraw(Boolean(uploadedImg));
                    return;
                }

                if (uploadedImg && imageRect) {
                    redraw(true);
                    return;
                }

                var img = new Image();
                img.onload = function() {
                    strokeCtx.clearRect(0, 0, strokeLayer.width, strokeLayer.height);
                    var size = canvasSize();
                    strokeCtx.drawImage(img, 0, 0, size.width, size.height);
                    hasDrawing = true;
                    redraw(false);
                };
                img.src = previous;
            });

            return { open: openModal, close: closeModal };
        }

        window.partGEmployeeSignature = initSignatureModal({
            modalId: 'partGEmployeeSignatureModal',
            canvasId: 'partGEmployeeSignatureCanvas',
            uploadId: 'partGEmployeeSignatureUpload',
            uploadFileNameId: 'partGEmployeeSignatureSelectedFile',
            scaleControlsId: 'partGEmployeeSignatureScaleControls',
            scaleRangeId: 'partGEmployeeSignatureScaleRange',
            scaleDownId: 'partGEmployeeSignatureScaleDown',
            scaleUpId: 'partGEmployeeSignatureScaleUp',
            errorId: 'partGEmployeeSignatureError',
            confirmId: 'partGEmployeeSignatureConfirm',
            closeId: 'partGEmployeeSignatureModalClose',
            cancelId: 'partGEmployeeSignatureCancel',
            clearId: 'partGEmployeeSignatureClear',
            hiddenIdPrefix: 'partGEmployeeSignatureData',
            uploadFieldName: 'employee_signature_upload',
            submitAction: 'acknowledge'
        });

        window.partGReviewerSignature = initSignatureModal({
            modalId: 'partGReviewerSignatureModal',
            canvasId: 'partGReviewerSignatureCanvas',
            uploadId: 'partGReviewerSignatureUpload',
            uploadFileNameId: 'partGReviewerSignatureSelectedFile',
            scaleControlsId: 'partGReviewerSignatureScaleControls',
            scaleRangeId: 'partGReviewerSignatureScaleRange',
            scaleDownId: 'partGReviewerSignatureScaleDown',
            scaleUpId: 'partGReviewerSignatureScaleUp',
            errorId: 'partGReviewerSignatureError',
            confirmId: 'partGReviewerSignatureConfirm',
            closeId: 'partGReviewerSignatureModalClose',
            cancelId: 'partGReviewerSignatureCancel',
            clearId: 'partGReviewerSignatureClear',
            hiddenIdPrefix: 'partGReviewerSignatureData',
            uploadFieldName: 'reviewer_signature_upload',
            submitAction: 'approve'
        });

        function markReviewerFormsChanged() {
            document.querySelectorAll('.partg-reviewer-approval-actions').forEach(function(container) {
                var approveBtn = container.querySelector('.partg-open-reviewer-signature-modal');
                var resubmitBtn = container.querySelector('.partg-resubmit-for-employee-btn');
                if (!approveBtn || !resubmitBtn) return;
                approveBtn.classList.add('hidden');
                resubmitBtn.classList.remove('hidden');
            });
        }

        document.addEventListener('partg-summary-updated', markReviewerFormsChanged);

        document.addEventListener('livewire:init', function() {
            if (window.Livewire && typeof window.Livewire.on === 'function') {
                window.Livewire.on('partg-summary-updated', markReviewerFormsChanged);
            }
        });
    });
</script>
