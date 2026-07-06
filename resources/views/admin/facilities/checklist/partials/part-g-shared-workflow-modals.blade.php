<div id="partGEmployeeSignatureModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-lg bg-white p-5 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="partGEmployeeSignatureModalTitle">
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
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700">Draw signature</label>
                <canvas id="partGEmployeeSignatureCanvas" width="460" height="140" class="w-full rounded-md border border-slate-300 bg-white touch-none"></canvas>
                <button type="button" id="partGEmployeeSignatureClear" class="mt-2 text-xs font-semibold text-slate-600 hover:text-slate-900">Clear drawing</button>
            </div>

            <div>
                <label for="partGEmployeeSignatureUpload" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700">Or upload signature image</label>
                <input type="file" id="partGEmployeeSignatureUpload" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm text-slate-700">
            </div>

            <p id="partGEmployeeSignatureError" class="hidden rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800"></p>
        </div>

        <div class="mt-5 flex justify-end gap-2">
            <button type="button" id="partGEmployeeSignatureCancel" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Cancel</button>
            <button type="button" id="partGEmployeeSignatureConfirm" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">Confirm Signature &amp; Acknowledge</button>
        </div>
    </div>
</div>

<div id="partGReviewerSignatureModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-lg bg-white p-5 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="partGReviewerSignatureModalTitle">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h3 id="partGReviewerSignatureModalTitle" class="text-lg font-bold text-slate-900">Sign &amp; Complete Assessment</h3>
                <p class="mt-1 text-sm text-slate-600">Draw your signature below or upload a signature image, then complete this competency assessment.</p>
            </div>
            <button type="button" id="partGReviewerSignatureModalClose" class="text-2xl leading-none text-slate-500 hover:text-slate-800" aria-label="Close">&times;</button>
        </div>

        <div class="space-y-4">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700">Draw signature</label>
                <canvas id="partGReviewerSignatureCanvas" width="460" height="140" class="w-full rounded-md border border-slate-300 bg-white touch-none"></canvas>
                <button type="button" id="partGReviewerSignatureClear" class="mt-2 text-xs font-semibold text-slate-600 hover:text-slate-900">Clear drawing</button>
            </div>

            <div>
                <label for="partGReviewerSignatureUpload" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700">Or upload signature image</label>
                <input type="file" id="partGReviewerSignatureUpload" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm text-slate-700">
            </div>

            <p id="partGReviewerSignatureError" class="hidden rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800"></p>
        </div>

        <div class="mt-5 flex justify-end gap-2">
            <button type="button" id="partGReviewerSignatureCancel" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Cancel</button>
            <button type="button" id="partGReviewerSignatureConfirm" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">Confirm Signature &amp; Complete</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var activeFormKey = null;

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
            var errorBox = document.getElementById(config.errorId);
            var confirmBtn = document.getElementById(config.confirmId);
            if (!modal || !canvas || !confirmBtn) {
                return null;
            }

            var ctx = canvas.getContext('2d');
            var drawing = false;
            var hasDrawing = false;

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

            function resizeCanvas() {
                var ratio = window.devicePixelRatio || 1;
                var width = canvas.clientWidth;
                var height = canvas.clientHeight || 140;
                canvas.width = width * ratio;
                canvas.height = height * ratio;
                ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.strokeStyle = '#111827';
            }

            function clearCanvas() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                hasDrawing = false;
            }

            function openModal(formKey) {
                activeFormKey = formKey;
                setError('');
                clearCanvas();
                var hidden = hiddenFieldForForm(formForKey(formKey), config.hiddenIdPrefix);
                if (hidden) hidden.value = '';
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

            [config.closeId, config.cancelId].forEach(function(id) {
                var button = document.getElementById(id);
                if (button) button.addEventListener('click', closeModal);
            });

            var clearBtn = document.getElementById(config.clearId);
            if (clearBtn) clearBtn.addEventListener('click', clearCanvas);

            if (uploadInput) {
                uploadInput.addEventListener('change', function() {
                    if (uploadInput.files && uploadInput.files.length > 0) {
                        clearCanvas();
                    }
                });
            }

            confirmBtn.addEventListener('click', function() {
                var form = formForKey(activeFormKey);
                var hidden = hiddenFieldForForm(form, config.hiddenIdPrefix);
                var actionInput = actionInputForForm(form);
                var hasUpload = uploadInput && uploadInput.files && uploadInput.files.length > 0;

                if (!hasDrawing && !hasUpload) {
                    setError('Draw your signature or upload a signature image before continuing.');
                    return;
                }

                if (!form || !hidden || !actionInput) {
                    setError('Unable to locate the active assessment form.');
                    return;
                }

                hidden.value = hasDrawing ? canvas.toDataURL('image/png') : '';
                attachUploadToForm(form, uploadInput, config.uploadFieldName);
                actionInput.value = config.submitAction;
                form.submit();
            });

            window.addEventListener('resize', resizeCanvas);

            return { open: openModal, close: closeModal };
        }

        window.partGEmployeeSignature = initSignatureModal({
            modalId: 'partGEmployeeSignatureModal',
            canvasId: 'partGEmployeeSignatureCanvas',
            uploadId: 'partGEmployeeSignatureUpload',
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
