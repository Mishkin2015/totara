<?php

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.php');
require_once($CFG->dirroot . '/local/plan/lib.php');

$id = required_param('id', PARAM_INT); // plan id
$caid = required_param('itemid', PARAM_INT); // objective assignment id

$plan = new development_plan($id);
$componentname = 'objective';
$component = $plan->get_component($componentname);
$currenturl = $CFG->wwwroot . '/local/plan/components/objective/view.php?id='.$id.'&amp;itemid='.$caid;
$coursesenabled = $plan->get_component('course')->get_setting('enabled');
$coursename = $plan->get_component('course')->get_setting('name');

$fullname = $plan->name;
$pagetitle = format_string(get_string('developmentplan','local_plan').': '.$fullname);
$navlinks = array();
$plan->get_plan_base_navlinks($navlinks);
$navlinks[] = array('name' => $fullname, 'link'=> $CFG->wwwroot . '/local/plan/view.php?id='.$id, 'type'=>'title');
$navlinks[] = array('name' => $component->get_setting('name'), 'link' => $CFG->wwwroot . '/local/plan/components/objective/index.php?id='.$id, 'type' => 'title');
$navlinks[] = array('name' => get_string('viewitem','local_plan'), 'link' => '', 'type' => 'title');

$navigation = build_navigation($navlinks);

print_header_simple($pagetitle, '', $navigation, '', null, true, '');

print $plan->display_plan_message_box();

print_heading($fullname);

print $plan->display_tabs($componentname);

print $component->display_back_to_index_link();

print $component->display_objective_detail($caid);

if($coursesenabled) {
    print '<h3>' . get_string('linkedx', 'local_plan', $coursename) . '</h3>';
    if($linkedcourses =
        $component->get_linked_components($caid, 'course')) {
        print $plan->get_component('course')->display_linked_courses($linkedcourses);
    } else {
        print '<p>' . get_string('nolinkedx', 'local_plan', $coursename). '</p>';
    }
}
print_footer();


?>