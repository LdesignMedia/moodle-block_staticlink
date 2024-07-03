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
 * @copyright 21/05/2024 Mfreak.nl | LdesignMedia.nl - Luuk Verhoeven
 * @author    Nihaal Shaikh
 */
class block_staticlink_edit_form extends block_edit_form {

    /**
     * Specific fields for block_staticlink
     * @param object $mform the form being built.
     */
    protected function specific_definition($mform): void {

        // Fields for editing Static Link block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_staticlink'));
        $mform->setType('config_title', PARAM_TEXT);

        $mform->addElement('text', 'config_text', get_string('configcontent', 'block_staticlink'));
        $mform->setType('config_text', PARAM_TEXT);
    }

    /**
     * Load in existing data as form defaults. Usually new entry defaults are stored directly in
     * form definition (new entry form); this function is used to load in data where values
     * already exist and data is being edited (edit entry form).
     *
     * @param $defaults
     */
    public function set_data($defaults): void {
        $canedit = $this->block->user_can_edit();
        $config = $this->block->config;

        foreach (['title', 'text'] as $field) {
            if (!$canedit && !empty($config->$field)) {
                ${$field} = $config->$field;
                $defaults->{"config_$field"} = format_string(${$field}, true, $this->page->context);
                unset($config->$field);
            }
        }

        parent::set_data($defaults);

        if (!isset($config)) {
            $this->block->config = new stdClass();
        }

        if (isset($title) || isset($text)) {
            $config->title = $title ?? $config->title;
            $config->text = $text ?? $config->text;
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
