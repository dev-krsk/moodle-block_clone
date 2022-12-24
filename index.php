<?php
/**
 * @package   local_block_clone
 * @copyright 2022, Yuriy Yurinskiy <yuriyyurinskiy@yandex.ru>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/form.php');
require_once(__DIR__ . '/lib.php');

$systemcontext = $context = context_system::instance();

$title = get_string('pluginname', 'local_block_clone');
$pageheading = format_string($SITE->fullname, true, array('context' => $systemcontext));
$url = new moodle_url('/local/block_clone/index.php');

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($title);
$PAGE->set_heading($pageheading);

/** Проверяем авторизован ли пользователь */
require_login();

/** Проверяем права пользователя */
if (!is_siteadmin()) {
    header('Location: ' . $CFG->wwwroot);
    die();
}

echo $OUTPUT->header();

$mform = new local_block_clone_form_categories();

if ($data = $mform->get_data()) {
    local_block_clone_create_block($data->categoryid, $data->blockid, $data->weight, $data->subcategories ?? false);

    redirect($url, 'Операция выполнена', 30);
}

$mform->display();

echo $OUTPUT->footer();