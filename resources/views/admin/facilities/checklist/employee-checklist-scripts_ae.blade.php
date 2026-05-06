<script>
    // Set current user info for modal population (must be before any modal logic)
    @if(auth()->check())
        window.currentUserId = {{ auth()->user()->id }};
        window.currentUserName = @json(auth()->user()->name);
    @endif
    
    // Ensure the modal form is wired up to the handler
    document.addEventListener('DOMContentLoaded', function() {
        var verifyForm = document.getElementById('verifyForm');
        if (verifyForm) {
            verifyForm.addEventListener('submit', handleChecklistFormSubmit);
        }
    });

    // Modal close function for PART A-E
    function closeVerifyModalAE() {
        var modal = document.getElementById('verifyModal');
        var subitemValidationMsg = document.getElementById('subitemValidationMsg');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        if (subitemValidationMsg) {
            subitemValidationMsg.textContent = '';
            subitemValidationMsg.classList.add('hidden');
        }
    }

    function getChecklistRowLevel(row) {
        if (!row) {
            return 0;
        }

        return parseInt(row.getAttribute('data-item-level') || '0', 10);
    }

    function getIncompleteChecklistSubitems(row) {
        if (!row || (!row.closest('#partE') && !row.closest('#partG'))) {
            return [];
        }

        var currentLevel = getChecklistRowLevel(row);
        var incompleteSubitems = [];
        var nextRow = row.nextElementSibling;

        while (nextRow) {
            if (!nextRow.hasAttribute('data-item-level')) {
                nextRow = nextRow.nextElementSibling;
                continue;
            }

            var nextLevel = getChecklistRowLevel(nextRow);
            if (nextLevel <= currentLevel) {
                break;
            }

            if (nextRow.getAttribute('data-item-disabled') !== '1') {
                var checkbox = nextRow.querySelector('input[type="checkbox"]');
                if (!checkbox || !checkbox.checked) {
                    incompleteSubitems.push(nextRow.getAttribute('data-item-label') || nextRow.getAttribute('data-item-name') || 'Unnamed sub-item');
                }
            }

            nextRow = nextRow.nextElementSibling;
        }

        return incompleteSubitems;
    }

    // Opens the PART A-E modal and populates its fields for the selected item/employee
    function findChecklistLink(itemName, empId, itemId = null, checklistKey = null) {
        return Array.from(document.querySelectorAll('.verify-link, .view-link')).find((link) => {
            if (String(link.getAttribute('data-emp-id')) !== String(empId)) {
                return false;
            }

            if (itemId && String(link.getAttribute('data-item-id')) === String(itemId)) {
                return true;
            }

            if (checklistKey && link.getAttribute('data-checklist-key') === checklistKey) {
                return true;
            }

            return link.getAttribute('data-item-name') === itemName;
        });
    }

    function openVerifyModalAE(itemName, empId, itemId = null, checklistKey = null, viewOnly = false) {
        // Get modal and field elements
        var modal = document.getElementById('verifyModal');
        var empIdField = document.getElementById('verifyEmpId');
        var checklistItemIdField = document.getElementById('verifyChecklistItemId');
        var docNameField = document.getElementById('verifyDocName');
        var docTypeIdField = document.getElementById('verifyDocTypeId');
        var onFileField = document.getElementById('verifyOnFile');
        var verifiedDtField = document.getElementById('verifyVerifiedDt');
        var expDtField = document.getElementById('verifyExpDt');
        var commentsField = document.getElementById('verifyComments');
        var verifiedByField = document.getElementById('verifyVerifiedBy');
        var verifiedByIdField = document.getElementById('verifyVerifiedById');
        var expDtRequiredMsg = document.getElementById('expDtRequiredMsg');
        var subitemValidationMsg = document.getElementById('subitemValidationMsg');

        if (subitemValidationMsg) {
            subitemValidationMsg.textContent = '';
            subitemValidationMsg.classList.add('hidden');
        }

        // Find isExpiring for this item (assumes a global JS object window.checklistItemsByName)
        var isExpiring = 0;
        if (window.checklistItemsByName && window.checklistItemsByName[itemName]) {
            isExpiring = window.checklistItemsByName[itemName].isExpiring ? 1 : 0;
        }
        // Show/hide Expiration Date field and required message
        if (expDtField && expDtRequiredMsg) {
            if (isExpiring) {
                expDtField.disabled = false;
                expDtField.parentElement.classList.remove('hidden');
                expDtField.required = true;
                expDtRequiredMsg.classList.add('hidden');
            } else {
                expDtField.value = '';
                expDtField.disabled = true;
                expDtField.required = false;
                expDtField.parentElement.classList.add('hidden');
                expDtRequiredMsg.classList.add('hidden');
            }
        }
        
        // Find the row for this itemName/empId to get any existing data
        var link = findChecklistLink(itemName, empId, itemId, checklistKey);
        var row = link ? link.closest('tr') : null;
        
        // Set hidden fields for employee and document
        if (empIdField) empIdField.value = empId;
        if (checklistItemIdField) checklistItemIdField.value = itemId || (link ? (link.getAttribute('data-item-id') || '') : '');
        if (docNameField) docNameField.value = itemName;
        if (docTypeIdField) docTypeIdField.value = row ? (row.getAttribute('data-doc-type-id') || '') : '';
        
        // Always default verification date to today unless in viewOnly mode
        if (verifiedDtField && !viewOnly) {
            verifiedDtField.value = (new Date()).toISOString().slice(0, 10);
        }
        // Populate fields from data attributes if available
        if (link) {
            if (onFileField) onFileField.value = link.getAttribute('data-on-file') || '1';
            if (verifiedDtField && link.hasAttribute('data-verified-dt') && link.getAttribute('data-verified-dt')) {
                verifiedDtField.value = link.getAttribute('data-verified-dt');
            }
            if (expDtField && link.hasAttribute('data-exp-dt')) expDtField.value = link.getAttribute('data-exp-dt') || '';
            if (commentsField && link.hasAttribute('data-comments')) commentsField.value = link.getAttribute('data-comments') || '';
            if (verifiedByField && link.hasAttribute('data-verified-by')) verifiedByField.value = link.getAttribute('data-verified-by') || '';
            if (verifiedByIdField && link.hasAttribute('data-verified-by')) verifiedByIdField.value = link.getAttribute('data-verified-by') || '';
        }
        
        // Only default to the current user when editing a new confirmation.
        if (!viewOnly) {
            if (window.currentUserName && verifiedByField) verifiedByField.value = window.currentUserName;
            if (window.currentUserId && verifiedByIdField) verifiedByIdField.value = window.currentUserId;
        }
        
        // Set fields to readonly if viewOnly is true
        var editBtn = document.getElementById('editBtn');
        var saveBtn = document.getElementById('saveBtn');
        if (viewOnly) {
            if (onFileField) onFileField.disabled = true;
            if (verifiedDtField) verifiedDtField.readOnly = true;
            if (expDtField) expDtField.readOnly = true;
            if (commentsField) commentsField.readOnly = true;
            if (editBtn) editBtn.classList.remove('hidden');
            if (saveBtn) saveBtn.classList.add('hidden');
        } else {
            if (onFileField) onFileField.disabled = false;
            if (verifiedDtField) verifiedDtField.readOnly = false;
            if (expDtField) expDtField.readOnly = false;
            if (commentsField) commentsField.readOnly = false;
            if (editBtn) editBtn.classList.add('hidden');
            if (saveBtn) saveBtn.classList.remove('hidden');
        }
        // Store initial values for change detection
        if (!viewOnly) {
            window._verifyModalInitial = {
                comments: commentsField ? commentsField.value : '',
                verifiedDt: verifiedDtField ? verifiedDtField.value : ''
            };
        }
        // Show the modal
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }
    
    // Edit button logic for modal (global, always attaches)
    document.addEventListener('DOMContentLoaded', function() {
        var editBtn = document.getElementById('editBtn');
        var saveBtn = document.getElementById('saveBtn');
        if (editBtn && saveBtn) {
            editBtn.addEventListener('click', function() {
                // Always re-query modal fields in case modal was re-rendered
                var commentsField = document.getElementById('verifyComments');
                var verifiedDtField = document.getElementById('verifyVerifiedDt');
                var expDtField = document.getElementById('verifyExpDt');
                if (commentsField) commentsField.readOnly = false;
                if (verifiedDtField) verifiedDtField.readOnly = false;
                if (expDtField) expDtField.readOnly = false;
                editBtn.classList.add('hidden');
                saveBtn.classList.remove('hidden');
                // Store initial values for change detection
                window._verifyModalInitial = {
                    comments: commentsField ? commentsField.value : '',
                    verifiedDt: verifiedDtField ? verifiedDtField.value : '',
                    expDt: expDtField ? expDtField.value : ''
                };
            });
            // Change detection to show Save only if changed
            function checkForChanges() {
                var commentsField = document.getElementById('verifyComments');
                var verifiedDtField = document.getElementById('verifyVerifiedDt');
                var expDtField = document.getElementById('verifyExpDt');
                var changed = false;
                if (window._verifyModalInitial) {
                    if (
                        (commentsField && commentsField.value !== window._verifyModalInitial.comments) ||
                        (verifiedDtField && verifiedDtField.value !== window._verifyModalInitial.verifiedDt) ||
                        (expDtField && expDtField.value !== window._verifyModalInitial.expDt)
                    ) {
                        changed = true;
                    }
                }
                if (changed) {
                    saveBtn.classList.remove('hidden');
                } else {
                    saveBtn.classList.add('hidden');
                }
            }
            // Attach listeners for change detection
            document.addEventListener('input', function(e) {
                if (['verifyComments','verifyVerifiedDt','verifyExpDt'].includes(e.target.id)) {
                    checkForChanges();
                }
            });
        }
    });
 
    // This block initializes the tab navigation for the employee checklist UI.
    // It sets up click handlers for each tab, manages the active tab's styling,
    // shows/hides the correct content, and remembers the last active tab in localStorage.
    document.addEventListener('DOMContentLoaded', function () {
        // Get all tab link elements
        const tabLinks = document.querySelectorAll('#employeeFileTabs .tab-link');
        // Use the rendered tab panes so stale localStorage values cannot blank the checklist.
        const tabContents = document.querySelectorAll('#employeeFileTabs ~ div.tab-content, #partA, #partB, #partC, #partD, #partE, #partF, #partG');
        const validTabIds = Array.from(tabLinks).map(link => link.getAttribute('data-tab'));

        // Function to activate a tab by its ID
        function setActiveTab(tabId) {
            const resolvedTabId = validTabIds.includes(tabId) ? tabId : 'partA';
            const activeTabId = resolvedTabId;

            // Loop through all tab links and update their classes for active/inactive state
            tabLinks.forEach(link => {
                // Remove all possible state classes first
                link.classList.remove('text-white', 'bg-teal-600', 'border-teal-600', 'bg-white');
                // If this link matches the active tab, add active classes
                if (link.getAttribute('data-tab') === activeTabId) {
                    link.classList.add('text-white', 'bg-teal-600', 'border-teal-600');
                } else {
                    // Otherwise, make it look inactive
                    link.classList.add('bg-white');
                }
            });
            // Hide all tab content sections
            tabContents.forEach(tc => {
                if (tc) tc.classList.add('hidden');
            });
            // Show the selected tab's content
            const activeContent = document.getElementById(activeTabId);
            if (activeContent) activeContent.classList.remove('hidden');
            // Remember the selected tab in localStorage for persistence
            localStorage.setItem('employeeChecklistActiveTab', activeTabId);
        }
        
        // Add click event listeners to each tab link to activate the correct tab
        tabLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                setActiveTab(this.getAttribute('data-tab'));
            });
        });
        // On page load, restore the last active tab from localStorage, or default to partA
        const lastTab = localStorage.getItem('employeeChecklistActiveTab') || 'partA';
        setActiveTab(lastTab);
        // Always bind PART A-E links on load
        bindChecklistLinks();
    });

    function bindChecklistLinksAE() {
        // For each 'Verify' link in PART A-E, bind click to open the modal in edit mode
        document.querySelectorAll('.verify-link[data-item-name]').forEach(function(link) {
            link.onclick = function(e) {
                e.preventDefault();
                var itemName = this.getAttribute('data-item-name');
                var empId = this.getAttribute('data-emp-id');
                var itemId = this.getAttribute('data-item-id');
                var checklistKey = this.getAttribute('data-checklist-key');
                openVerifyModalAE(itemName, empId, itemId, checklistKey, false); // false = not view-only
            };
        });
        // For each 'View' link in PART A-E, bind click to open the modal in view-only mode
        document.querySelectorAll('.view-link[data-item-name]').forEach(function(link) {
            link.onclick = function(e) {
                e.preventDefault();
                var itemName = this.getAttribute('data-item-name');
                var empId = this.getAttribute('data-emp-id');
                var itemId = this.getAttribute('data-item-id');
                var checklistKey = this.getAttribute('data-checklist-key');
                openVerifyModalAE(itemName, empId, itemId, checklistKey, true); // true = view-only
            };
        });
        document.querySelectorAll('.unverify-link[data-item-name]').forEach(function(link) {
            link.onclick = function(e) {
                e.preventDefault();
                var itemName = this.getAttribute('data-item-name');
                var itemId = this.getAttribute('data-item-id');
                var empId = this.getAttribute('data-emp-id');
                var row = this.closest('tr');
                var itemLabel = this.getAttribute('data-item-label') || itemName;
                if (!window.confirm(`Warning: this will unconfirm "${itemLabel}". Continue?`)) {
                    return;
                }
                var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch(`/admin/employees/${empId}/checklist/unverify`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ doc_name: itemName, checklist_item_id: itemId || null })
                })
                .then(async response => {
                    let data;
                    let rawText = await response.text();
                    try {
                        data = JSON.parse(rawText);
                    } catch (err) {
                        alert('Confirmation failed.');
                        return;
                    }
                    if (data.success && data.data && data.data.item) {
                        updateChecklistRow(row, data.data.item, itemName, empId);
                    } else {
                        alert('Confirmation failed.');
                    }
                })
                .catch(err => {
                    alert('Confirmation failed.');
                });
            };
        });
    }

    // Handles the form submission for checklist verification modal
    function handleChecklistFormSubmit(e) {
        e.preventDefault();
        var empId = document.getElementById('verifyEmpId').value;
        var checklistItemId = document.getElementById('verifyChecklistItemId').value;
        var docName = document.getElementById('verifyDocName').value;
        var docTypeId = parseInt(document.getElementById('verifyDocTypeId').value || document.getElementById('verifyForm').getAttribute('data-doc-type-id') || '1', 10);
        var onFile = true; // Always true when verified
        var verifiedDt = document.getElementById('verifyVerifiedDt').value;
        var row = findChecklistLink(docName, empId, checklistItemId)?.closest('tr');
        var expDtField = document.getElementById('verifyExpDt');
        var expDtRequiredMsg = document.getElementById('expDtRequiredMsg');
        var subitemValidationMsg = document.getElementById('subitemValidationMsg');
        var isExpiring = 0;
        if (window.checklistItemsByName && window.checklistItemsByName[docName]) {
            isExpiring = window.checklistItemsByName[docName].isExpiring ? 1 : 0;
        }
        if (subitemValidationMsg) {
            subitemValidationMsg.textContent = '';
            subitemValidationMsg.classList.add('hidden');
        }
        var incompleteSubitems = getIncompleteChecklistSubitems(row);
        if (incompleteSubitems.length > 0) {
            if (subitemValidationMsg) {
                subitemValidationMsg.textContent = 'Confirm all sub-items first: ' + incompleteSubitems.join(', ');
                subitemValidationMsg.classList.remove('hidden');
            }
            return;
        }
        // If item is expiring, require Expiration Date
        if (isExpiring && (!expDtField.value || expDtField.value === '')) {
            expDtRequiredMsg.classList.remove('hidden');
            expDtField.focus();
            return;
        } else {
            expDtRequiredMsg.classList.add('hidden');
        }
        document.getElementById('verifyExpDt').disabled = true;
        var expDtRaw = document.getElementById('verifyExpDt').value;
        var expDt = expDtRaw === '' ? null : expDtRaw;
        var comments = document.getElementById('verifyComments').value;
        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var verifiedById = document.getElementById('verifyVerifiedById').value;
        var payload = {
            employee_num: empId,
            checklist_item_id: checklistItemId || null,
            doc_name: docName,
            doc_type_id: docTypeId,
            on_file: onFile,
            verified_dt: verifiedDt,
            exp_dt: expDt,
            comments: comments,
            verified_by: verifiedById,
        };
        console.log('Checklist AE Save payload:', payload);
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
            let rawText = await response.text();
            try {
                data = JSON.parse(rawText);
            } catch (err) {
                console.error('Checklist save: JSON parse error', rawText);
                alert('Save failed.');
                    closeVerifyModalAE();
                return;
            }
            if (data.success && data.data && data.data.item) {
                var row = findChecklistLink(docName, empId, checklistItemId, data.data.checklist_key)?.closest('tr');
                updateChecklistRow(row, data.data.item, docName, empId, checklistItemId, data.data.checklist_key);
                    closeVerifyModalAE();
            } else {
                alert('Save failed.');
                    closeVerifyModalAE();
            }
        })
        .catch(async err => {
            console.error('Checklist save: AJAX error', err);
            alert('Save failed.');
                closeVerifyModalAE();
        });
    }

    // Updates the PART A-E checklist table row after verification save
    function updateChecklistRow(row, item, docName, empId, itemId = null, checklistKey = null) {
        if (!row || row.children.length < 5) return;
        // Update the On File checkbox in the row
        var checkbox = row.children[1].querySelector('input[type="checkbox"]');
        if (checkbox) {
            if (item.on_file === true || item.on_file === 1 || item.on_file === '1') {
                checkbox.setAttribute('checked', 'checked');
            } else {
                checkbox.removeAttribute('checked');
            }
        }
        // Update the Verification Date cell
        row.children[2].textContent = ((item.on_file || item.verified_dt) && (!item.verified_dt || item.verified_dt === '')) ? 'N/A' : (item.verified_dt || '');
        // Update the Expiration Date cell
        row.children[3].textContent = ((item.on_file || item.verified_dt) && (!item.exp_dt || item.exp_dt === '')) ? 'N/A' : (item.exp_dt || '');
        // Update the Verified By cell (always last cell)
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
        var itemLabel = row.getAttribute('data-item-label') || docName;
        var itemLabelAttr = itemLabel.replace(/"/g, '&quot;');
        var itemIdAttr = itemId ? ` data-item-id="${String(itemId).replace(/"/g, '&quot;')}"` : '';
        var checklistKeyAttr = checklistKey ? ` data-checklist-key="${String(checklistKey).replace(/"/g, '&quot;')}"` : '';
        var verifyLabel = row.closest('#partE') ? 'Confirm' : 'Verify';
        var actionLinks = '';
        if (item.verified_by || item.verified_by_name) {
            actionLinks =
            `<a href="#" class="text-red-600 underline ml-3 mr-2 unverify-link" title="Click to unconfirm Item" data-item-name="${itemNameAttr}" data-item-label="${itemLabelAttr}"${itemIdAttr}${checklistKeyAttr} data-emp-id="${empId}"${expDtNotRequiredAttr}>Confirmed</a>` +
                `<span>|</span>` +
            `<a href="#" class="text-teal-600 underline ml-2 view-link" title="View Confirmation Details" data-item-name="${itemNameAttr}" data-item-label="${itemLabelAttr}"${itemIdAttr}${checklistKeyAttr} data-emp-id="${empId}"` +
                ` data-on-file="${item.on_file ? 1 : 0}"` +
                ` data-verified-dt="${item.verified_dt || ''}"` +
                ` data-exp-dt="${item.exp_dt || ''}"` +
                ` data-comments="${item.comments || ''}"` +
                ` data-verified-by="${item.verified_by_name || item.verified_by || ''}"${expDtNotRequiredAttr}>View</a>`;
        } else {
            // If not verified, show Verify link with tooltip and all relevant data attributes
            actionLinks =
                `<a href="#" class="text-teal-600 underline ml-3 verify-link" title="Click to confirm Item" data-item-name="${itemNameAttr}" data-item-label="${itemLabelAttr}"${itemIdAttr}${checklistKeyAttr} data-emp-id="${empId}" title="Verify Item"` +
                ` data-on-file="${item.on_file ? 1 : 0}"` +
                ` data-verified-dt="${item.verified_dt || ''}"` +
                ` data-exp-dt="${item.exp_dt || ''}"` +
                ` data-comments="${item.comments || ''}"` +
                ` data-verified-by="${item.verified_by || ''}"` +
                ` data-exp-dt-not-required="${item.exp_dt_not_required ? 1 : 0}"` +
                `>${verifyLabel}</a>`;
        }
        // Remove any existing action links after the checkbox, then add new ones
        if (checkboxElem) {
            while (checkboxElem.nextSibling) {
                checkboxElem.parentNode.removeChild(checkboxElem.nextSibling);
            }
            var tempDiv = document.createElement('span');
            tempDiv.innerHTML = actionLinks;
            if (tempDiv && actionCell) {
                Array.from(tempDiv.childNodes).forEach(function(node) {
                    actionCell.appendChild(node);
                });
            }
        }
        bindChecklistLinks();
    }

    // Legacy/fallback tab navigation logic for checklist tabs (may be redundant with Alpine.js or other tab logic)
    document.querySelectorAll('.tab-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            // Remove 'active' class from all tab links
            document.querySelectorAll('.tab-link').forEach(l => l.classList.remove('active'));
            // Add 'active' class to the clicked tab
            this.classList.add('active');
            // Hide all tab content sections
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            // Show the selected tab's content
            const target = this.getAttribute('href');
            document.querySelector(target).classList.remove('hidden');
        });
    });

    // Alias for legacy compatibility: allow code expecting bindChecklistLinks() to work
    function bindChecklistLinks() {
        bindChecklistLinksAE();
    }

    // Edit button logic for modal (global, always attaches)
    document.addEventListener('DOMContentLoaded', function() {
        var editBtn = document.getElementById('editBtn');
        var saveBtn = document.getElementById('saveBtn');
        if (editBtn && saveBtn) {
        editBtn.addEventListener('click', function() {
        // Always re-query modal fields in case modal was re-rendered
        var commentsField = document.getElementById('verifyComments');
        var verifiedDtField = document.getElementById('verifyVerifiedDt');
        var expDtField = document.getElementById('verifyExpDt');
        if (commentsField) commentsField.readOnly = false;
        if (verifiedDtField) verifiedDtField.readOnly = false;
        if (expDtField) expDtField.readOnly = false;
        editBtn.classList.add('hidden');
        saveBtn.classList.remove('hidden');
        // Store initial values for change detection
        window._verifyModalInitial = {
        comments: commentsField ? commentsField.value : '',
        verifiedDt: verifiedDtField ? verifiedDtField.value : '',
        expDt: expDtField ? expDtField.value : ''
        };
        });
        // Change detection to show Save only if changed
        function checkForChanges() {
        var commentsField = document.getElementById('verifyComments');
        var verifiedDtField = document.getElementById('verifyVerifiedDt');
        var expDtField = document.getElementById('verifyExpDt');
        var changed = false;
        if (window._verifyModalInitial) {
        if (
        (commentsField && commentsField.value !== window._verifyModalInitial.comments) ||
        (verifiedDtField && verifiedDtField.value !== window._verifyModalInitial.verifiedDt) ||
        (expDtField && expDtField.value !== window._verifyModalInitial.expDt)
        ) {
        changed = true;
        }
        }
        if (changed) {
        saveBtn.classList.remove('hidden');
        } else {
        saveBtn.classList.add('hidden');
        }
        }
        // Attach listeners for change detection
        document.addEventListener('input', function(e) {
        if (['verifyComments','verifyVerifiedDt','verifyExpDt'].includes(e.target.id)) {
        checkForChanges();
        }
        });
        }
    });    
</script>