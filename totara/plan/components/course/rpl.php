<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010, 2011 Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Alastair Munro <alastair.munro@totaralms.com>
 * @author Aaron Barnes <aaron.barnes@totaralms.com>
 * @package totara
 * @subpackage plan
 */

require_once('../../../../config.php');
require_once($CFG->dirroot . '/local/plan/lib.php');
require_once($CFG->dirroot . '/local/plan/components/course/rpl_form.php');

$id = required_param('id', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

require_login();
$plan = new development_plan($id);

//Permissions check
$systemcontext = get_system_context();
if(!has_capability('local/plan:accessanyplan', $systemcontext) && ($plan->get_setting('view') < DP_PERMISSION_ALLOW)) {
        print_error('error:nopermissions', 'local_plan');
}

$userid = $plan->userid;
$componentname = 'course';
$component = $plan->get_component($componentname);

if($component->get_setting('setcompletionstatus') != DP_PERMISSION_ALLOW) {
    error(get_string('error:coursecompletionpermission', 'local_plan'));
}

// Check completion is enabled for course
$course = new object();
$course->id = $courseid;
$info = new completion_info($course);

if (!$info->is_enabled()) {
    print_error('completionnotenabled', 'completion', $component->get_url());
}

// Check course RPLs are enabled
if (!$CFG->enablecourserpl) {
    print_error('error:courserplsaredisabled', 'completion', $component->get_url());
}

if($rpl = get_record('course_completions', 'userid', $userid, 'course', $courseid)){
    $rpltext = stripslashes($rpl->rpl);
    $rplid = $rpl->id;
} else {
    $rpltext = '';
    $rplid = 0;
}

$mform = new totara_course_rpl_form(null, compact('id','rplid','rpltext','courseid','userid'));

$returnurl = $component->get_url();

if ($mform->is_cancelled()) {
    redirect($returnurl);
}
if ($fromform = $mform->get_data()) {
    if(empty($fromform->submitbutton)) {
        totara_set_notification(get_string('error:unknownbuttonclicked', 'local_plan'), $returnurl);
    }

    $rpl = $fromform->rpl;

    // Get completion object
    $params = array(
        'userid'    => $fromform->userid,
        'course'    => $fromform->courseid,
        'id'        => isset($fromform->rplid) ? $fromform->rplid : null
    );

    // Completion
    // Load course completion
    $completion = new completion_completion($params);

    /// Complete user
    if (strlen($rpl)) {
        $completion->rpl = addslashes($rpl);
        $completion->mark_complete();
        $alert_detail = new object();
        $alert_detail->itemname = get_field('course', 'fullname', 'id', $completion->course);
        $alert_detail->text = get_string('completedviarpl', 'local_plan', $completion->rpl);
        $component->send_component_complete_alert($alert_detail);

        add_to_log(SITEID, 'plan', 'completed course', "component.php?id={$plan->id}&amp;c=course", "{$alert_detail->itemname} RPL set (ID:{$completion->course})");

        // If no RPL, uncomplete user, and let aggregation do its thing
    } else {
        $completion->delete();
    }

    totara_set_notification(
        get_string('rplupdated', 'local_plan'),
        $returnurl,
        array('style'=>'notifysuccess')
    );
}


$fullname = $plan->name;
$pagetitle = format_string(get_string('learningplan','local_plan').': '.$fullname);
$navlinks = array();
dp_get_plan_base_navlinks($navlinks, $plan->userid);
$navlinks[] = array('name' => $fullname, 'link'=> $plan->get_display_url(), 'type'=>'title');
$navlinks[] = array('name' => get_string($component->component, 'local_plan'), 'link' => '', 'type' => 'title');

$navigation = build_navigation($navlinks);

///
/// Display page
///
print_header_simple($pagetitle, '', $navigation, '', null, true, '');

// Plan menu
echo dp_display_plans_menu($plan->userid,$plan->id,$plan->role);

// Plan page content
print_container_start(false, '', 'dp-plan-content');

print $plan->display_plan_message_box();

print_heading($fullname);
print $plan->display_tabs($componentname);

$mform->display();

print_container_end();
print_footer();

?>