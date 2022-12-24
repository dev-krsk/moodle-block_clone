<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains the code for the plugin integration.
 *
 * @package   local_block_clone
 * @copyright 2022, Yuriy Yurinskiy <yuriyyurinskiy@yandex.ru>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/blocklib.php');

function local_block_clone_extend_settings_navigation($settingsnav, $context)
{
    global $PAGE;

    if (is_siteadmin()) {
        if ($settingnode = $settingsnav->find('courses', navigation_node::TYPE_SETTING)) {
            $strfoo = get_string('pluginname', 'local_block_clone');
            $url = new moodle_url('/local/block_clone/index.php');
            $foonode = navigation_node::create(
                $strfoo,
                $url,
                navigation_node::TYPE_SETTING,
                'block_clone',
                'block_clone',
                new pix_icon('i/settings', $strfoo)
            );
            if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
                $foonode->make_active();
            }
            $settingnode->add_node($foonode);
        }
    }
}

function local_block_clone_create_block($categoryid, $blockname, $blockweigth = 0, $subcategory = false)
{
    global $DB;
    $courses = $DB->get_records('course', array('category' => $categoryid), 'sortorder');

    foreach ($courses as $course) {
        $context = context_course::instance($course->id);

        $issetBlocks = $DB->get_records('block_instances', array(
            'blockname' => $blockname,
            'parentcontextid' => $context->id,
        ));

        if (\count($issetBlocks) > 0) {
            $message = sprintf('Блок уже существует в курсе "%s"', $course->fullname);

            \core\notification::warning($message);
            continue;
        }

        $regions = array('side-post');

        $page = new moodle_page();
        $page->set_course($course);
        $page->set_context($context);
        $page->set_pagetype('course-view-*');
        $page->set_subpage('');
        $page->set_url(new moodle_url('/'));

        $blockmanager = new block_manager($page);
        $blockmanager->add_regions($regions, false);
        $blockmanager->set_default_region($regions[0]);

        $blockmanager->add_block(
            $blockname,
            $blockmanager->get_default_region(),
            $blockweigth,
            false
        );

        $message = sprintf('Блок добавлен в курс "%s"', $course->fullname);

        \core\notification::success($message);
    }

    if ($subcategory) {
        $categories = $DB->get_records(
            'course_categories',
            array('parent' => $categoryid),
            'sortorder'
        );

        foreach ($categories as $category) {
            local_block_clone_create_block($category->id, $blockname, $blockweigth, $subcategory);
        }
    }
}