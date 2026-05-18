<div data-competency-section="test-competency" class="mb-6 p-4 border rounded bg-white">
    <h3 class="font-bold mb-2">Test Competency Section</h3>
    <div class="space-y-2">
        <div>
            <label class="block font-semibold">Skill 1:</label>
            <label><input type="radio" name="skill1" value="E"> E</label>
            <label><input type="radio" name="skill1" value="S"> S</label>
            <label><input type="radio" name="skill1" value="U"> U</label>
            <label><input type="radio" name="skill1" value="N"> N</label>
        </div>
        <div>
            <label class="block font-semibold">Skill 2:</label>
            <label><input type="radio" name="skill2" value="E"> E</label>
            <label><input type="radio" name="skill2" value="S"> S</label>
            <label><input type="radio" name="skill2" value="U"> U</label>
            <label><input type="radio" name="skill2" value="N"> N</label>
        </div>
        <div>
            <label class="block font-semibold">Skill 3:</label>
            <label><input type="radio" name="skill3" value="E"> E</label>
            <label><input type="radio" name="skill3" value="S"> S</label>
            <label><input type="radio" name="skill3" value="U"> U</label>
            <label><input type="radio" name="skill3" value="N"> N</label>
        </div>
    </div>
</div>
<!-- Summary fields -->
<div class="mb-4">
    <label>Total Score: <input id="testTotalScore" type="text" readonly class="border px-2 py-1 w-16"></label>
    <label class="ml-4">Average: <input id="testAverageScore" type="text" readonly class="border px-2 py-1 w-16"></label>
</div>
<!-- Save as Draft button and message -->
<div class="mb-4">
    <button id="testSaveDraftBtn" type="button" class="bg-blue-600 text-white px-4 py-2 rounded">Save as Draft</button>
    <span id="testSubmitAssessmentMessage" class="ml-4 text-sm"></span>
</div>

<script>
(function() {
    const sectionId = 'test-competency';
    const root = document.querySelector('[data-competency-section="' + sectionId + '"]');
    const totalScoreField = document.getElementById('testTotalScore');
    const averageScoreField = document.getElementById('testAverageScore');
    const radios = root.querySelectorAll('input[type="radio"]');
    const skillNames = ['skill1', 'skill2', 'skill3'];

    function getScore(val) {
        if (val === 'E') return 3;
        if (val === 'S') return 2;
        if (val === 'U') return 1;
        return 0;
    }

    function updateSummary() {
        let total = 0, rated = 0;
        skillNames.forEach(name => {
            const checked = root.querySelector('input[name="' + name + '"]:checked');
            if (checked && checked.value !== 'N') {
                total += getScore(checked.value);
                rated++;
            }
        });
        totalScoreField.value = rated > 0 ? total : '';
        averageScoreField.value = rated > 0 ? (total / rated).toFixed(2) : '';
    }

    function saveSelections() {
        const selections = {};
        skillNames.forEach(name => {
            const checked = root.querySelector('input[name="' + name + '"]:checked');
            if (checked) selections[name] = checked.value;
        });
        localStorage.setItem('testCompetencySelections', JSON.stringify(selections));
    }

    function restoreSelections() {
        let selections = {};
        try {
            selections = JSON.parse(localStorage.getItem('testCompetencySelections')) || {};
        } catch {}
        skillNames.forEach(name => {
            if (selections[name]) {
                const radio = root.querySelector('input[name="' + name + '"][value="' + selections[name] + '"]');
                if (radio) radio.checked = true;
            }
        });
    }

    function bind() {
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                updateSummary();
                saveSelections();
            });
        });
    }

    // On load
    restoreSelections();
    updateSummary();
    bind();

    // Save as Draft button
    const saveBtn = document.getElementById('testSaveDraftBtn');
    const msg = document.getElementById('testSubmitAssessmentMessage');
    if (saveBtn && msg) {
        saveBtn.addEventListener('click', function() {
            msg.textContent = '';
            saveBtn.disabled = true;
            setTimeout(function() {
                msg.textContent = 'Draft saved!';
                msg.className = 'ml-4 text-green-700';
                saveBtn.disabled = false;
            }, 600);
        });
    }
})();
