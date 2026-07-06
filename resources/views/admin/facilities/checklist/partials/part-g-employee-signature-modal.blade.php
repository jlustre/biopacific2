<div id="partGEmployeeSignatureModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-lg bg-white p-5 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="partGEmployeeSignatureModalTitle">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h3 id="partGEmployeeSignatureModalTitle" class="text-lg font-bold text-slate-900">Sign Competency Assessment</h3>
                <p class="mt-1 text-sm text-slate-600">Draw your signature below or upload a signature image, then confirm your acknowledgement.</p>
                @if(!empty($partGEmployeeFullName))
                <p class="mt-2 text-sm font-semibold text-slate-900">Employee: {{ $partGEmployeeFullName }}</p>
                @endif
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
                <input type="file" id="partGEmployeeSignatureUpload" name="employee_signature_upload" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm text-slate-700">
            </div>

            <p id="partGEmployeeSignatureError" class="hidden rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800"></p>
        </div>

        <div class="mt-5 flex justify-end gap-2">
            <button type="button" id="partGEmployeeSignatureCancel" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Cancel</button>
            <button type="button" id="partGEmployeeSignatureConfirm" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">Confirm Signature &amp; Acknowledge</button>
        </div>
    </div>
</div>

<input type="hidden" name="employee_signature_data" id="partGEmployeeSignatureData" value="">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('partGEmployeeSignatureModal');
        var openBtn = document.getElementById('partGOpenEmployeeSignatureModal');
        var form = document.getElementById('partGCompetencyWorkflowForm');
        var canvas = document.getElementById('partGEmployeeSignatureCanvas');
        var hiddenData = document.getElementById('partGEmployeeSignatureData');
        var uploadInput = document.getElementById('partGEmployeeSignatureUpload');
        var errorBox = document.getElementById('partGEmployeeSignatureError');
        if (!modal || !openBtn || !form || !canvas || !hiddenData) return;

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
            hiddenData.value = '';
        }

        function openModal() {
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

        ['partGEmployeeSignatureModalClose', 'partGEmployeeSignatureCancel'].forEach(function(id) {
            var button = document.getElementById(id);
            if (button) button.addEventListener('click', closeModal);
        });

        var clearBtn = document.getElementById('partGEmployeeSignatureClear');
        if (clearBtn) clearBtn.addEventListener('click', clearCanvas);

        if (uploadInput) {
            uploadInput.addEventListener('change', function() {
                if (uploadInput.files && uploadInput.files.length > 0) {
                    clearCanvas();
                }
            });
        }

        var confirmBtn = document.getElementById('partGEmployeeSignatureConfirm');
        if (confirmBtn) confirmBtn.addEventListener('click', function() {
            var hasUpload = uploadInput && uploadInput.files && uploadInput.files.length > 0;
            if (!hasDrawing && !hasUpload) {
                setError('Draw your signature or upload a signature image before continuing.');
                return;
            }

            if (hasDrawing) {
                hiddenData.value = canvas.toDataURL('image/png');
            } else {
                hiddenData.value = '';
            }

            var actionInput = document.getElementById('partGWorkflowAction');
            if (actionInput) actionInput.value = 'acknowledge';

            form.submit();
        });

        window.addEventListener('resize', resizeCanvas);
    });
</script>
