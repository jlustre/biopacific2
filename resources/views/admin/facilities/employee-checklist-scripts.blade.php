<script>
    // Set current user info for modal population (must be before any modal logic)
    @if(auth()->check())
        window.currentUserId = {{ auth()->user()->id }};
        window.currentUserName = @json(auth()->user()->name);
    @endif

    function openVerifyModal(itemName, empId, viewOnly = false) {
        document.getElementById('verifyDocName').value = itemName;
        document.getElementById('verifyEmpId').value = empId;
        var link = Array.from(document.querySelectorAll('.verify-link, .view-link')).find(
            l => l.getAttribute('data-item-name') === itemName && l.getAttribute('data-emp-id') == empId
        );
        var row = link ? link.closest('tr') : null;
        var docTypeId = row ? row.getAttribute('data-doc-type-id') : '';
        document.getElementById('verifyForm').setAttribute('data-doc-type-id', docTypeId);
        document.getElementById('verifyOnFile').checked = true;
        var verifiedDt = link && link.getAttribute('data-verified-dt') ? link.getAttribute('data-verified-dt') : '';
        var expDt = link && link.getAttribute('data-exp-dt') ? link.getAttribute('data-exp-dt') : '';
        var comments = link && link.getAttribute('data-comments') ? link.getAttribute('data-comments') : '';
        var verifiedBy = link && link.getAttribute('data-verified-by') ? link.getAttribute('data-verified-by') : '';
        var expDtNotRequired = link && link.getAttribute('data-exp-dt-not-required') == '1';
        document.getElementById('verifyVerifiedDt').value = verifiedDt || (function(){
            var today = new Date();
            var yyyy = today.getFullYear();
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var dd = String(today.getDate()).padStart(2, '0');
            return yyyy + '-' + mm + '-' + dd;
        })();
        document.getElementById('verifyExpDt').value = expDt;
        document.getElementById('verifyComments').value = comments;
        if (window.currentUserName && !verifiedBy) {
            document.getElementById('verifyVerifiedBy').value = window.currentUserName;
        } else if (verifiedBy) {
            document.getElementById('verifyVerifiedBy').value = verifiedBy;
        }
        if (window.currentUserId && !verifiedBy) {
            document.getElementById('verifyVerifiedById').value = window.currentUserId;
        } else if (verifiedBy) {
            document.getElementById('verifyVerifiedById').value = verifiedBy;
        }
        document.getElementById('expDtNotRequired').checked = expDtNotRequired;
        document.getElementById('verifyExpDt').disabled = expDtNotRequired;
        var modal = document.getElementById('verifyModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeVerifyModal() {
        var modal = document.getElementById('verifyModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function bindChecklistLinks() {
        document.querySelectorAll('.view-link').forEach(function (el) {
            el.onclick = function (e) {
                e.preventDefault();
                openVerifyModal(this.getAttribute('data-item-name'), this.getAttribute('data-emp-id'), true);
            };
        });
        document.querySelectorAll('.verify-link').forEach(function (el) {
            el.onclick = function (e) {
                e.preventDefault();
                openVerifyModal(this.getAttribute('data-item-name'), this.getAttribute('data-emp-id'));
            };
        });
        document.querySelectorAll('.unverify-link').forEach(function (el) {
            el.onclick = function (e) {
                e.preventDefault();
                var empId = this.getAttribute('data-emp-id');
                var docName = this.getAttribute('data-item-name');
                var docTypeId = parseInt(this.closest('tr').getAttribute('data-doc-type-id') || '1', 10);
                var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                var payload = {
                    doc_name: docName,
                    doc_type_id: docTypeId,
                    revoke: 1
                };
                fetch(`/admin/employees/${empId}/checklist/unverify`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(async response => {
                    let data;
                    try {
                        data = await response.json();
                    } catch (err) {
                        return;
                    }
                    if (data.success && data.data && data.data.item) {
                        var row = this.closest('tr');
                        updateChecklistRow(row, data.data.item, docName, empId);
                    }
                })
                .catch(async err => {
                    // Optionally show error
                });
            };
        });
    }

    function updateChecklistRow(row, item, docName, empId) {
        if (!row || row.children.length < 5) return;
        var checkbox = row.querySelector('input[type="checkbox"]');
        if (checkbox) checkbox.checked = !!item.on_file;
        row.children[2].textContent = ((item.on_file || item.verified_dt) && (!item.verified_dt || item.verified_dt === '')) ? 'N/A' : (item.verified_dt || '');
        row.children[3].textContent = ((item.on_file || item.verified_dt) && (!item.exp_dt || item.exp_dt === '')) ? 'N/A' : (item.exp_dt || '');
        var verifiedByCell = row.children[row.children.length - 1];
        while (verifiedByCell.firstChild) { verifiedByCell.removeChild(verifiedByCell.firstChild); }
        verifiedByCell.appendChild(document.createTextNode(item.verified_by_name || item.verified_by || ''));
        var actionCell = row.children[1];
        var checkboxElem = actionCell.querySelector('input[type="checkbox"]');
        var expDtNotRequiredAttr = '';
        if (typeof item.exp_dt_not_required !== 'undefined') {
            expDtNotRequiredAttr = ` data-exp-dt-not-required="${item.exp_dt_not_required ? 1 : 0}"`;
        }
        var itemNameAttr = docName.replace(/"/g, '&quot;');
        var actionLinks = '';
        if (item.verified_by || item.verified_by_name) {
            actionLinks =
                `<a href=\"#\" class=\"text-red-600 underline ml-3 mr-2 unverify-link\" data-item-name=\"${itemNameAttr}\" data-emp-id=\"${empId}\"${expDtNotRequiredAttr}>Revoke</a>` +
                `<span>|</span>` +
                `<a href=\"#\" class=\"text-teal-600 underline ml-2 view-link\" data-item-name=\"${itemNameAttr}\" data-emp-id=\"${empId}\"${expDtNotRequiredAttr}>View</a>`;
        } else {
            actionLinks =
                `<a href=\"#\" class=\"text-teal-600 underline ml-2 verify-link\" data-item-name=\"${itemNameAttr}\" data-emp-id=\"${empId}\"` +
                ` data-on-file=\"${item.on_file ? 1 : 0}\"` +
                ` data-verified-dt=\"${item.verified_dt || ''}\"` +
                ` data-exp-dt=\"${item.exp_dt || ''}\"` +
                ` data-comments=\"${item.comments || ''}\"` +
                ` data-verified-by=\"${item.verified_by || ''}\"` +
                ` data-exp-dt-not-required=\"${item.exp_dt_not_required ? 1 : 0}\"` +
                `>Verify</a>`;
        }
        // Remove all action links after the checkbox (if any), or all children except the checkbox
        if (checkboxElem) {
            while (checkboxElem.nextSibling) {
                checkboxElem.parentNode.removeChild(checkboxElem.nextSibling);
            }
            var tempDiv = document.createElement('span');
            tempDiv.innerHTML = actionLinks;
            Array.from(tempDiv.childNodes).forEach(function(node) {
                actionCell.appendChild(node);
            });
        } else {
            // Remove all children (action links) from actionCell
            while (actionCell.firstChild) {
                actionCell.removeChild(actionCell.firstChild);
            }
            var tempDiv = document.createElement('span');
            tempDiv.innerHTML = actionLinks;
            Array.from(tempDiv.childNodes).forEach(function(node) {
                actionCell.appendChild(node);
            });
        }
        bindChecklistLinks();
    }

    function handleChecklistFormSubmit(e) {
        e.preventDefault();
        var empId = document.getElementById('verifyEmpId').value;
        var docName = document.getElementById('verifyDocName').value;
        var docTypeId = parseInt(document.getElementById('verifyForm').getAttribute('data-doc-type-id') || '1', 10);
        var onFile = !!document.getElementById('verifyOnFile').checked;
        var verifiedDt = document.getElementById('verifyVerifiedDt').value;
        var row = Array.from(document.querySelectorAll('.verify-link, .view-link')).find(
            l => l.getAttribute('data-item-name') === docName && l.getAttribute('data-emp-id') == empId
        )?.closest('tr');
        var expDtNotRequired = document.getElementById('expDtNotRequired').checked;
        if (!expDtNotRequired && row && row.querySelector('.verify-link') && row.querySelector('.verify-link').hasAttribute('data-exp-dt-not-required')) {
            var vattr = row.querySelector('.verify-link').getAttribute('data-exp-dt-not-required');
            expDtNotRequired = (vattr === '1' || vattr === 1 || vattr === true || vattr === 'true');
        }
        document.getElementById('expDtNotRequired').checked = expDtNotRequired;
        document.getElementById('verifyExpDt').disabled = expDtNotRequired;
        var expDtRaw = document.getElementById('verifyExpDt').value;
        var expDt = expDtNotRequired ? null : (expDtRaw === '' ? null : expDtRaw);
        var comments = document.getElementById('verifyComments').value;
        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var verifiedById = document.getElementById('verifyVerifiedById').value;
        var payload = {
            doc_name: docName,
            doc_type_id: docTypeId,
            on_file: onFile,
            verified_dt: verifiedDt,
            exp_dt: expDt,
            comments: comments,
            verified_by: verifiedById,
            exp_dt_not_required: expDtNotRequired ? 1 : 0
        };
        fetch(`/admin/employees/${empId}/checklist/verify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(async response => {
            let data;
            try {
                data = await response.json();
            } catch (err) {
                closeVerifyModal();
                return;
            }
            if (data.success && data.data && data.data.item) {
                var row = Array.from(document.querySelectorAll('.verify-link, .view-link')).find(l => l.getAttribute('data-item-name') === docName && l.getAttribute('data-emp-id') == empId)?.closest('tr');
                updateChecklistRow(row, data.data.item, docName, empId);
                closeVerifyModal();
            } else {
                closeVerifyModal();
            }
        })
        .catch(async err => {
            closeVerifyModal();
        });
    }

    function updateChecklistRow(row, item, docName, empId) {
        if (!row || row.children.length < 5) return;
        // Checkbox
        var checkbox = row.children[1].querySelector('input[type="checkbox"]');
        if (checkbox) {
            if (item.on_file === true || item.on_file === 1 || item.on_file === '1') {
                checkbox.setAttribute('checked', 'checked');
            } else {
                checkbox.removeAttribute('checked');
            }
        }
        // Verification Date
        row.children[2].textContent = ((item.on_file || item.verified_dt) && (!item.verified_dt || item.verified_dt === '')) ? 'N/A' : (item.verified_dt || '');
        // Expiration Date
        row.children[3].textContent = ((item.on_file || item.verified_dt) && (!item.exp_dt || item.exp_dt === '')) ? 'N/A' : (item.exp_dt || '');
        // Verified By (always plain text, always last cell)
        var verifiedByCell = row.children[row.children.length - 1];
        while (verifiedByCell.firstChild) { verifiedByCell.removeChild(verifiedByCell.firstChild); }
        verifiedByCell.appendChild(document.createTextNode(item.verified_by_name || item.verified_by || ''));
        // Action links
        var actionCell = row.children[1];
        var checkboxElem = actionCell.querySelector('input[type="checkbox"]');
        var expDtNotRequiredAttr = '';
        if (typeof item.exp_dt_not_required !== 'undefined') {
            expDtNotRequiredAttr = ` data-exp-dt-not-required="${item.exp_dt_not_required ? 1 : 0}"`;
        }
        var itemNameAttr = docName.replace(/"/g, '&quot;');
        var actionLinks = '';
        if (item.verified_by || item.verified_by_name) {
            actionLinks =
                `<a href="#" class="text-red-600 underline ml-3 mr-2 unverify-link" data-item-name="${itemNameAttr}" data-emp-id="${empId}"${expDtNotRequiredAttr}>Revoke</a>` +
                `<span>|</span>` +
                `<a href="#" class="text-teal-600 underline ml-2 view-link" data-item-name="${itemNameAttr}" data-emp-id="${empId}"${expDtNotRequiredAttr}>View</a>`;
        } else {
            actionLinks =
                `<a href="#" class="text-teal-600 underline ml-2 verify-link" data-item-name="${itemNameAttr}" data-emp-id="${empId}"` +
                ` data-on-file="${item.on_file ? 1 : 0}"` +
                ` data-verified-dt="${item.verified_dt || ''}"` +
                ` data-exp-dt="${item.exp_dt || ''}"` +
                ` data-comments="${item.comments || ''}"` +
                ` data-verified-by="${item.verified_by || ''}"` +
                ` data-exp-dt-not-required="${item.exp_dt_not_required ? 1 : 0}"` +
                `>Verify</a>`;
        }
        if (checkboxElem) {
            while (checkboxElem.nextSibling) {
                checkboxElem.parentNode.removeChild(checkboxElem.nextSibling);
            }
            var tempDiv = document.createElement('span');
            tempDiv.innerHTML = actionLinks;
            Array.from(tempDiv.childNodes).forEach(function(node) {
                actionCell.appendChild(node);
            });
        }
        // Re-bind events for new links
        bindChecklistLinks();
    }

    function handleChecklistFormSubmit(e) {
        e.preventDefault();
        var empId = document.getElementById('verifyEmpId').value;
        var docName = document.getElementById('verifyDocName').value;
        var docTypeId = parseInt(document.getElementById('verifyForm').getAttribute('data-doc-type-id') || '1', 10);
        var onFile = !!document.getElementById('verifyOnFile').checked;
        var verifiedDt = document.getElementById('verifyVerifiedDt').value;
        var row = Array.from(document.querySelectorAll('.verify-link, .view-link')).find(
            l => l.getAttribute('data-item-name') === docName && l.getAttribute('data-emp-id') == empId
        )?.closest('tr');
        var expDtNotRequired = document.getElementById('expDtNotRequired').checked;
        if (!expDtNotRequired && row && row.querySelector('.verify-link') && row.querySelector('.verify-link').hasAttribute('data-exp-dt-not-required')) {
            var vattr = row.querySelector('.verify-link').getAttribute('data-exp-dt-not-required');
            expDtNotRequired = (vattr === '1' || vattr === 1 || vattr === true || vattr === 'true');
        }
        document.getElementById('expDtNotRequired').checked = expDtNotRequired;
        document.getElementById('verifyExpDt').disabled = expDtNotRequired;
        var expDtRaw = document.getElementById('verifyExpDt').value;
        var expDt = expDtNotRequired ? null : (expDtRaw === '' ? null : expDtRaw);
        var comments = document.getElementById('verifyComments').value;
        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var payload = {
            doc_name: docName,
            doc_type_id: docTypeId,
            on_file: onFile,
            verified_dt: verifiedDt,
            exp_dt: expDt,
            comments: comments,
            exp_dt_not_required: expDtNotRequired
        };
        fetch(`/admin/employees/${empId}/checklist/verify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(async response => {
            let data;
            try {
                data = await response.json();
            } catch (err) {
                return;
            }
            if (data.success && data.data && data.data.item) {
                var row = Array.from(document.querySelectorAll('.verify-link, .view-link')).find(l => l.getAttribute('data-item-name') === docName && l.getAttribute('data-emp-id') == empId)?.closest('tr');
                updateChecklistRow(row, data.data.item, docName, empId);
                closeVerifyModal();
            }
        })
        .catch(async err => {
            // Optionally handle error
        });
    }

    document.querySelectorAll('.tab-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.tab-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            const target = this.getAttribute('href');
            document.querySelector(target).classList.remove('hidden');
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Expiration Date Not Required checkbox logic
        var expDtNotRequired = document.getElementById('expDtNotRequired');
        var expDtInput = document.getElementById('verifyExpDt');
        if (expDtNotRequired && expDtInput) {
            expDtNotRequired.addEventListener('change', function() {
                if (this.checked) {
                    expDtInput.value = '';
                    expDtInput.disabled = true;
                } else {
                    expDtInput.disabled = false;
                }
            });
        }
        bindChecklistLinks();
        var verifyForm = document.getElementById('verifyForm');
        if (verifyForm) {
            verifyForm.addEventListener('submit', handleChecklistFormSubmit);
        }
    });
</script>