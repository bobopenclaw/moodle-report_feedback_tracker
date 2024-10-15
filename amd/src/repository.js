import {call as ajax} from 'core/ajax';

export const updateAssessmentType = (
    itemid,
    partid,
    assessmenttype,
) => ajax([{
    methodname: 'report_feedback_tracker_save_assessment_type',
    args: {
        itemid: itemid,
        partid: partid,
        assessmenttype: assessmenttype
    },
}])[0];

export const updateCohortState = (
    itemid,
    partid,
    cohortstate,
) => ajax([{
    methodname: 'report_feedback_tracker_save_cohort_state',
    args: {
        itemid: itemid,
        partid: partid,
        cohortstate: cohortstate
    },
}])[0];

export const updateHidingState = (
    itemid,
    partid,
    hidingstate,
) => ajax([{
    methodname: 'report_feedback_tracker_save_hiding_state',
    args: {
        itemid: itemid,
        partid: partid,
        hidingstate: hidingstate
    },
}])[0];

export const updateFeedbackDuedate = (
    itemid,
    partid,
    duedate,
    duedatereason,
) => ajax([{
    methodname: 'report_feedback_tracker_save_feedback_duedate',
    args: {
        itemid: itemid,
        partid: partid,
        duedate: duedate,
        duedatereason: duedatereason
    },
}])[0];

export const deleteFeedbackDuedate = (
    itemid,
    partid,
) => ajax([{
    methodname: 'report_feedback_tracker_delete_feedback_duedate',
    args: {
        itemid: itemid,
        partid: partid
    },
}])[0];

export const updateGeneralFeedback = (
    itemid,
    partid,
    generalfeedback,
    gfurl,
) => ajax([{
    methodname: 'report_feedback_tracker_update_general_feedback',
    args: {
        itemid: itemid,
        partid: partid,
        generalfeedback: generalfeedback,
        gfurl: gfurl
    },
}])[0];

export const renderStudentFeedback = (
    studentid,
    courseid,
) => ajax([{
    methodname: 'report_feedback_tracker_render_student_feedback',
    args: {
        studentid: studentid,
        courseid: courseid
    },
}])[0];
