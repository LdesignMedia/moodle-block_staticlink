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
 * @category  files
 *
 * @param stdClass $course         course object
 * @param stdClass $birecord_or_cm block instance record
 * @param stdClass $context        context object
 * @param string $filearea         file area
 * @param array $args              extra arguments
 * @param bool $forcedownload      whether or not force download
 * @param array $options           additional options affecting the file serving
 *
 */
function block_staticlink_pluginfile($course, $birecord_or_cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $CFG, $USER;

    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    // If block is in course context, then check if user has capability to access course.
    if ($context->get_course_context(false)) {
        require_course_login($course);
    } else if ($CFG->forcelogin) {
        require_login();
    } else {
        // Get parent context and see if user have proper permission.
        $parentcontext = $context->get_parent_context();
        if ($parentcontext->contextlevel === CONTEXT_COURSECAT) {
            // Check if category is visible and user can view this category.
            if (!core_course_category::get($parentcontext->instanceid, IGNORE_MISSING)) {
                send_file_not_found();
            }
        } else if ($parentcontext->contextlevel === CONTEXT_USER && $parentcontext->instanceid != $USER->id) {
            // The block is in the context of a user, it is only visible to the user who it belongs to.
            send_file_not_found();
        }
        // At this point there is no way to check SYSTEM context, so ignoring it.
    }

    if ($filearea !== 'content') {
        send_file_not_found();
    }
}

/**
 * Perform global search replace such as when migrating site to new URL.
 *
 * @param  $search
 * @param  $replace
 *
 * @return void
 */
function block_staticlink_global_db_replace($search, $replace): void {
    global $DB;

    $instances = $DB->get_recordset('block_instances', ['blockname' => 'staticlink']);
    foreach ($instances as $instance) {
        $config = unserialize_object(base64_decode($instance->configdata));
        if (isset($config->text) && is_string($config->text)) {
            $config->text = str_replace($search, $replace, $config->text);
            $DB->update_record('block_instances', (object) [
                'id' => $instance->id,
                'configdata' => base64_encode(serialize($config)), 'timemodified' => time(),
            ]);
        }
    }
    $instances->close();
}

/**
 * Given an array with a file path, it returns the itemid and the filepath for the defined filearea.
 *
 * @param string $filearea The filearea.
 * @param array $args      The path (the part after the filearea and before the filename).
 *
 * @return array The itemid and the filepath inside the $args path, for the defined filearea.
 */
function block_staticlink_get_path_from_pluginfile(string $filearea, array $args): array {
    // This block never has an itemid (the number represents the revision but it's not stored in database).
    array_shift($args);

    // Get the filepath.
    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    return [
        'itemid' => 0,
        'filepath' => $filepath,
    ];
}
