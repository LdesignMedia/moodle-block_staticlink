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
class block_staticlink extends block_base {

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
        return array('all' => true);
    }

    public function specialization(): void {
        if (isset($this->config->title)) {
            $this->title = $this->title = format_string($this->config->title, true, ['context' => $this->context]);
        } else {
            $this->title = get_string('newstaticlinkblock', 'block_staticlink');
        }
    }

    /**
     * Allow multiple instances
     * @return boolean
     */
    public function instance_allow_multiple() {
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
        if ($this->content_is_trusted()) {
            // Fancy html allowed only on course, category and system blocks.
            $filteropt->noclean = true;
        }
        $this->content = new stdClass;
        $this->content->footer = '';
        if (isset($this->config->text)) {
            // Rewrite url.
            $this->config->text = file_rewrite_pluginfile_urls($this->config->text, 'pluginfile.php', $this->context->id, 'block_staticlink', 'content', null);
            $this->content->text = get_string('staticlink', 'block_staticlink', $this->config->text);
        } else {
            $this->content->text = '';
        }

        unset($filteropt); // Memory footprint.

        return $this->content;
    }

    /**
     * Return an object containing all the block content to be returned by external functions.
     *
     * @param core_renderer $output the rendered used for output
     *
     * @return stdClass      object containing the block title, central content, footer and linked files (if any).
     */
    public function get_content_for_external($output): stdClass {
        $bc = new stdClass;
        $bc->title = null;
        $bc->content = '';
        $bc->contenformat = FORMAT_MOODLE;
        $bc->footer = '';

        if (!$this->hide_header()) {
            $bc->title = $this->title;
        }

        if (isset($this->config->text)) {
            $filteropt = new stdClass;
            if ($this->content_is_trusted()) {
                // Fancy html allowed only on course, category and system blocks.
                $filteropt->noclean = true;
            }

            $format = FORMAT_HTML;
            // Check to see if the format has been properly set on the config.
            if (isset($this->config->format)) {
                $format = $this->config->format;
            }
            [$bc->content, $bc->contentformat] = \core_external\util::format_text(
                $this->config->text,
                $format,
                $this->context,
                'block_staticlink',
                'content',
                null,
                $filteropt
            );
        }
        return $bc;
    }


    /**
     * Serialize and store config data
     */
    public function instance_config_save($data, $nolongerused = false): void {

        $config = clone($data);

        parent::instance_config_save($config, $nolongerused);
    }

    /**
     * Delete everything related to this instance if you have
     * been using persistent storage other than the configdata field.
     *
     * @return bool
     */
    public function instance_delete(): bool {
        return true;
    }

    /**
     * Copy any block-specific data when copying to a new block instance.
     * @param int $fromid the id number of the block instance to copy from
     * @return boolean
     */
    public function instance_copy($fromid): bool {
        return true;
    }

    /**
     * @return bool
     */
    private function content_is_trusted(): bool {
        global $SCRIPT;

        if (!$context = context::instance_by_id($this->instance->parentcontextid, IGNORE_MISSING)) {
            return false;
        }
        // Find out if this block is on the profile page.
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                // This is exception - page is completely private, nobody else may see content there.
                // That is why we allow JS here.
                return true;
            } else {
                // No JS on public personal pages, it would be a big security issue.
                return false;
            }
        }

        return true;
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

    /**
     * Return the plugin config settings for external functions.
     *
     * @return stdClass the configs for both the block instance and plugin
     */
    public function get_config_for_external(): stdClass {

        // Return all settings for all users since it is safe (no private keys, etc..).
        $instanceconfigs = !empty($this->config) ? $this->config : new stdClass();

        return (object) [
            'instance' => $instanceconfigs,
        ];
    }
}
