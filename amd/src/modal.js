export const init = async() => {

    const assessmentTypeSelector = document.getElementById('assessmenttype');
    const feedbackDuedatePicker = document.getElementById('feedbackduedate');
    const hidingCheckbox = document.getElementById('hidden');
    const previousFeedbackDuedate = document.getElementById('previousfeedbackduedate');

    if (assessmentTypeSelector) {
        assessmentTypeSelector.addEventListener('change', async(e) => {
            const assessmentType = e.target.value;
            // TODO: Use assess_type::ASSESS_TYPE_DUMMY and assess_type::ASSESS_TYPE_SUMMATIVE.
            const assessTypeDummy = 2;

            if (parseInt(assessmentType, 10) === assessTypeDummy) {
                hidingCheckbox.checked = true; // Dummy assessments are hidden from the student report.
                hidingCheckbox.disabled = true; // Changing hiding state is disabled.
            } else {
                hidingCheckbox.checked = false; // Other assessments are shown to students by default.
                hidingCheckbox.disabled = false; // Changing hiding state is enabled.
            }
        });
    }

    if (feedbackDuedatePicker) {
        feedbackDuedatePicker.addEventListener('change', function() {
            const reason = document.getElementById('js-reason');
            const reasonField = document.getElementById('reason');
            if (feedbackDuedatePicker.value === previousFeedbackDuedate.value || !feedbackDuedatePicker.value) {
                reason.classList.add('d-none'); // Hide the input field.
                reasonField.required = false;
            } else {
                reason.classList.remove('d-none'); // Show an input field.
                reasonField.required = true; // Make it a required field.
            }
        });
    }
};