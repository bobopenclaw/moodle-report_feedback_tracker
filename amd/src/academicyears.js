export const init = () => {
    selectCoursesByAcademicYear();

    // Click the leftmost (=current) year.
    const elements = document.querySelectorAll('[data-action="select-ay"]');
    if (elements.length > 0) {
        // Click the leftmost element (the first one in the NodeList)
        elements[0].click();
    }
};

/**
 * Filter the student table.
 */
export function selectCoursesByAcademicYear() {
    window.console.log('academicyears.js initialised');

    document.addEventListener('click', async e => {
        if (e.target.closest('[data-action="select-ay"]')) {
            const target = e.target;
            const filterAcademicYear = target.getAttribute('data-value');

            // Loop through each element and enable it (again).
            document.querySelectorAll('[data-action="select-ay"]').forEach(element => {
                element.classList.remove('disabled');
            });
            // Disable the button for the current selection.
            target.classList.add('disabled');
            filterCoursesByAcademicYear(filterAcademicYear);
        }
    });
}

/**
 * Filter the coruses by Academic Year.
 * @param {string} filterAcademicYear
 */
function filterCoursesByAcademicYear(filterAcademicYear) {
    window.console.log("filtering by: " + filterAcademicYear);
    const dataArea = document.getElementById('courses_area');
    const courses = dataArea.getElementsByClassName('course_row');

    const filterCourses = () => {
        Array.from(courses).forEach(course => {
            course.style.display = course.getAttribute('data-ay') === filterAcademicYear ? '' : 'none';
        });
    };

    filterCourses();
}