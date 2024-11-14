export const init = async() => {

    const assessmentTypeSelector = document.getElementById('js-assessmenttype');
    const hidingCheckbox = document.getElementById('hidden');

    if (assessmentTypeSelector) {
        assessmentTypeSelector.addEventListener('change', async(e) => {
            const assessmentType = e.target.value;
            // TODO: Use assess_type::ASSESS_TYPE_DUMMY and assess_type::ASSESS_TYPE_SUMMATIVE.
            const assessTypeDummy = 2;

            if (assessmentType * 1 === assessTypeDummy) {
                hidingCheckbox.checked = true; // Dummy assessments are hidden from students.
                hidingCheckbox.disabled = true;
            } else {
                hidingCheckbox.disabled = false;
            }
        });
    }

};