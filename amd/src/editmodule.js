import Modal from 'core/modal';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import {getAssessmentTypes} from "./repository";
import {get_string as getString} from 'core/str';

export const init = async() => {
    document.addEventListener('click', async e => {
        if (e.target.closest('.js-edit-tracker-data')) {

            // SHAME.
            // Get data-module-data json.
            // Ideally this would be call to php function, but we grab it from dom.
            const moduleDataString = e.target.dataset.moduleData;
            const moduleData = JSON.parse(moduleDataString);

            const title = `${await getString('edit', 'report_feedback_tracker')} ${moduleData.name}`;
            const locked = moduleData.locked === "1";

            // Get assessment type options with the current selection.
            const selection = +moduleData.assesstype; // Convert to int.
            const assesstypes = JSON.parse(await getAssessmentTypes(selection));

            // If type is either dummy or summative set 'hide from student report' option.
            const assessTypeDummy = 2; // This is assess_type::ASSESS_TYPE_DUMMY.
            const hiddendisabled = (selection === assessTypeDummy) || +locked;

            const today = new Date();
            const formattedtoday = today.toISOString().split('T')[0];

            // Show a modal to edit.
            const modal = await Modal.create({
                title: title,
                removeOnClose: true,
                body: Templates.render('report_feedback_tracker/course/modedit_modal', {
                    assesstype: moduleData.assesstype,
                    assesstypes: assesstypes,
                    assesstypelabel: moduleData.assesstypelabel,
                    contact: moduleData.contact,
                    courseid: moduleData.courseid,
                    formattedduedate: moduleData.formattedduedate,
                    formattedreleaseddate: moduleData.formattedreleaseddate,
                    feedbackduedatereason: moduleData.feedbackduedatereason,
                    generalfeedback: moduleData.generalfeedback,
                    gradeitemid: moduleData.gradeitemid,
                    hidden: moduleData.hidden === "1",
                    hiddendisabled: hiddendisabled,
                    locked: locked,
                    method: moduleData.method,
                    name: moduleData.name,
                    partid: moduleData.partid,
                    sesskey: M.cfg.sesskey,
                    today: formattedtoday,
                }),
            });

            await modal.show();

            modal.getRoot().on(ModalEvents.cancel, () => {
                modal.destroy();
            });

            modal.getRoot().on(ModalEvents.hidden, function() {
                modal.destroy();
            });
        }
    });

};
