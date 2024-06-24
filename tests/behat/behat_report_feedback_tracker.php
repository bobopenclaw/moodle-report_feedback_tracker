<?php
/**
 * Steps definitions related to editing mode.
 *
 * @package    report_feedback_tracker
 * @copyright  2024 UCL <m.opitz@ucl.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;

class behat_report_feedback_tracker extends behat_base {

    /**
     * @When I select :option from the :dropdown dropdown
     * @param string $option
     * @param string $dropdown
     */
    public function iSelectFromTheDropdown($option, $dropdown) {
        $this->getSession()->getPage()->selectFieldOption($dropdown, $option);
    }


}
