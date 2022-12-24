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

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/blocklib.php');

/**
 * This class is form course categories
 *
 * @copyright 2022, YuriyYurinskiy <yuriyyurinskiy@yandex.ru>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_block_clone_form_categories extends moodleform {
    /**
     * @see lib/moodleform#definition()
     */
    public function definition() {
        $mform = $this->_form;

        $categoryoptions = array();
        $categoryoptions[0] = get_string('top');
        $categoryoptions += core_course_category::make_categories_list('moodle/category:manage');

        $mform->addElement('select', 'categoryid', get_string('categories'), $categoryoptions);
        $mform->setDefault('categoryid', $this->_customdata['categoryid']);

        $mform->addElement('checkbox', 'subcategories', get_string('subcategories'));
        $mform->setDefault('subcategories', $this->_customdata['subcategories']);

        $blockoptions = $this->get_blocks();

        $mform->addElement('select', 'blockid', get_string('block'), $blockoptions);
        $mform->setDefault('blockid', $this->_customdata['blockid']);

        $weightoptions = $this->get_weight();

        $mform->addElement('select', 'weight', get_string('weight', 'block'), $weightoptions);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', 'Добавить блок');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    protected function get_blocks() {
        $regions = array('side-post');

        $page = new moodle_page();
        $page->set_context(context_course::instance(1));
        $page->set_pagetype('course-view-*');
        $page->set_subpage('');
        $page->set_url(new moodle_url('/'));

        $blockmanager = new block_manager($page);
        $blockmanager->add_regions($regions, false);
        $blockmanager->set_default_region($regions[0]);
        $blockmanager->load_blocks();

        $blocks = $blockmanager->get_addable_blocks();

        return array_column(array_map(function ($item) {
            return [
                'id' => $item->name,
                'name' => $item->title,
            ];
        }, $blocks), 'name', 'id');
    }

    protected function get_weight() {
        $blockweight = 5;
        $weightoptions = array();
        if ($blockweight < -block_manager::MAX_WEIGHT) {
            $weightoptions[$blockweight] = $blockweight;
        }
        for ($i = -block_manager::MAX_WEIGHT; $i <= block_manager::MAX_WEIGHT; $i++) {
            $weightoptions[$i] = $i;
        }
        if ($blockweight > block_manager::MAX_WEIGHT) {
            $weightoptions[$blockweight] = $blockweight;
        }
        $first = reset($weightoptions);
        $weightoptions[$first] = get_string('bracketfirst', 'block', $first);
        $last = end($weightoptions);
        $weightoptions[$last] = get_string('bracketlast', 'block', $last);

        return $weightoptions;
    }
}
