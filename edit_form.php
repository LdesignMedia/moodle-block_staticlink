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
 * Form for editing Static Link block instances.
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   block_staticlink
 * @copyright 29/12/2021 Mfreak.nl | LdesignMedia.nl - Luuk Verhoeven
 * @author    Nihaal Shaikh
 */
class block_staticlink_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $CFG;

        // Fields for editing Static Link block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_staticlink'));
        $mform->setType('config_title', PARAM_TEXT);

        $mform->addElement('text', 'config_text', get_string('configcontent', 'block_staticlink'));
        $mform->setType('config_text', PARAM_TEXT);
    }

    function set_data($defaults) {
        if (!$this->block->user_can_edit() && !empty($this->block->config->title)) {
            // If a title has been set but the user cannot edit it format it nicely.
            $title = $this->block->config->title;
            $defaults->config_title = format_string($title, true, $this->page->context);
            // Remove the title from the config so that parent::set_data doesn't set it.
            unset($this->block->config->title);
        }

        if (!$this->block->user_can_edit() && !empty($this->block->config->text)) {
            // If a title has been set but the user cannot edit it format it nicely.
            $title = $this->block->config->text;
            $defaults->config_text = format_string($title, true, $this->page->context);
            // Remove the text from the config so that parent::set_data doesn't set it.
            unset($this->block->config->text);
        }
        parent::set_data($defaults);
        // Restore $text.
        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }
        if (isset($title) || isset($content)) {
            // Reset the preserved title or content.
            $this->block->config->title = $title;
            $this->block->config->text = $content;
        }
    }

    /**
     * Display the configuration form when block is being added to the page
     *
     * @return bool
     */
    public static function display_form_when_adding(): bool {
        return true;
    }

}
