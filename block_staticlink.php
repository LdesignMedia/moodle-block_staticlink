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
class block_staticlink extends block_base {

    /**
     * Init the block.
     */
    public function init(): void {
        $this->title = get_string('pluginname', 'block_staticlink');
    }

    /**
     * Which page types this block may appear on.
     *
     * The information returned here is processed by the
     * {@link blocks_name_allowed_in_format()} function. Look there if you need
     * to know exactly how this works.
     *
     * Default case: everything except mod and tag.
     *
     * @return array page-type prefix => true/false.
     */
    public function applicable_formats(): array {
        return ['all' => true];
    }

    /**
     * This function is called on your subclass right after an instance is loaded
     * Use this function to act on instance data just after it's loaded and before anything else is done
     * For instance: if your block will have different title's depending on location (site, course, blog, etc)
     */
    public function specialization(): void {
        if (isset($this->config->title)) {
            $this->title = $this->title = format_string($this->config->title, true, ['context' => $this->context]);
        } else {
            $this->title = get_string('newstaticlinkblock', 'block_staticlink');
        }
    }

    /**
     * Allow multiple instances
     *
     * @return bool
     */
    public function instance_allow_multiple(): bool {
        return true;
    }

    /**
     * Parent class version of this function simply returns NULL
     * Get the content
     *
     * @return object
     */
    public function get_content(): object {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');
        if ($this->content !== null) {
            return $this->content;
        }

        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;
        $this->content = new stdClass;
        $this->content->footer = '';
        if (isset($this->config->text)) {
            // Rewrite url.
            $this->config->text = file_rewrite_pluginfile_urls(
                $this->config->text,
                'pluginfile.php',
                $this->context->id,
                'block_staticlink',
                'content',
                null
            );
            $this->content->text = get_string('staticlink', 'block_staticlink', $this->config->text);
        } else {
            $this->content->text = '';
        }

        unset($filteropt); // Memory footprint.

        return $this->content;
    }

    /**
     * The block should only be dockable when the title of the block is not empty
     * and when parent allows docking.
     *
     * @return bool
     */
    public function instance_can_be_docked(): bool {
        return !empty($this->config->title) && parent::instance_can_be_docked();
    }

}
