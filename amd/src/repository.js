import {call as ajax} from 'core/ajax';

export const updateSummativeState = (
    itemid,
    summativestate,
) => ajax([{
    methodname: 'report_feedback_tracker_save_summative_state',
    args: {
        itemid: itemid,
        summativestate: summativestate
    },
}])[0];

export const updateHidingState = (
    itemid,
    hidingstate,
) => ajax([{
    methodname: 'report_feedback_tracker_save_hiding_state',
    args: {
        itemid: itemid,
        hidingstate: hidingstate
    },
}])[0];

export const updateFeedbackDuedate = (
    itemid,
    duedate,
    duedatereason,
) => ajax([{
    methodname: 'report_feedback_tracker_save_feedback_duedate',
    args: {
        itemid: itemid,
        duedate: duedate,
        duedatereason: duedatereason
    },
}])[0];

export const deleteFeedbackDuedate = (
    itemid,
) => ajax([{
    methodname: 'report_feedback_tracker_delete_feedback_duedate',
    args: {
        itemid: itemid
    },
}])[0];

export const updateGeneralFeedback = (
    itemid,
    generalfeedback,
    gfurl,
    gfdate,
) => ajax([{
    methodname: 'report_feedback_tracker_update_general_feedback',
    args: {
        itemid: itemid,
        generalfeedback: generalfeedback,
        gfurl: gfurl,
        gfdate: gfdate
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
