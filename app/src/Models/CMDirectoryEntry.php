<?php

namespace CS\Directory\Models;

use CS\Directory\Models\CMDirectoryCategory;
use CS\Directory\Models\CMDirectory;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\TreeMultiselectField;
use SilverStripe\Security\Security;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\DataObject;

class CMDirectoryEntry extends DataObject
{
    private static $table_name = 'CMDirectoryEntry';

    private static $db = array(
        'Name' => 'Varchar',
    );

    private static $many_many = array(
        'Categories' => CMDirectoryCategory::class,
    );

    private static $belongs_many_many = array(
        'Directories' => CMDirectory::class,
    );

    /**
     * @config
     */
    private static $summary_fields = array(
        'Name',
        'SummaryDirectories',
        'SummaryCategories',
    );

    private static $casting = array(
        'SummaryDirectories' => 'Text',
        'SummaryCategories' => 'Text',
    );

    /**
     * @config
     * @var array
     */
    private static $disabled_fields = array();

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        /**
         * Directories
         * @todo Determine context. If not editing within directory, show directories field.
         */
        //$dirField = $fields->dataFieldByName('Directories');

        $fields->removeByName('Directories');

        /*
         * Categories
         * If not editing within directory, hide categories field. If editing
         * in directory, limit categories to directory's categories.
         */

        $fields->removeByName('Categories');

        // Bit of a hack to get the current directory context.
        $request = Controller::curr()->getRequest();
        $directoryClass = ($request) ? $request->param('ModelClass') : null;

        $queryParams = $this->getSourceQueryParams();
        $directoryId = (isset($queryParams['Foreign.ID'])) ? $queryParams['Foreign.ID'] : null;

        // Entry has been saved and we're editing under directory context
        if ($this->exists() && $directoryClass === CMDirectory::class && is_numeric($directoryId)) {
            $categoryInst = CMDirectoryCategory::create();
            $catPluralName = $categoryInst->i18n_plural_name();
            $categoriesField = TreeMultiselectField::create('Categories', _t(CMDirectoryEntry::class . '.SelectCategories', 'Select categories'), CMDirectoryCategory::class, 'ID', 'Name');
            // Filter the categories, leaving only the categories within this directory
            $categoriesField->setFilterFunction(function ($cat) use ($directoryId) {
                // Compare the category directory to the current directory
                if (!empty($cat->DirectoryID) && !empty($directoryId)) {
                    return intval($cat->DirectoryID) === intval($directoryId);
                }
                return false;
            });
            $fields->addFieldToTab("Root.$catPluralName", $categoriesField);
        }

        return $fields;
    }

    /**
     * Remove fields disabled in config for this class
     */
    protected function removeDisabledFields($fields)
    {
        $disabled = $this->config()->get('disabled_fields');
        if (is_array($disabled) && count($disabled)) {
            foreach ($disabled as $field) {
                $fields->removeByName($field);
            }
        }
    }

    public function fieldLabels($includerelations = true)
    {

        return array_merge((array) $this->translatedLabels(), parent::fieldLabels($includerelations));
    }

    protected function translatedLabels()
    {
        return array(
            'Name' => _t(CMDirectoryEntry::class . '.Name', 'Name'),
            'SummaryDirectories' => CMDirectory::create()->i18n_plural_name(),
            'SummaryCategories' => CMDirectoryCategory::create()->i18n_plural_name(),
        );
    }

    public function getSummaryDirectories()
    {
        $names = (array) $this->Directories()->column('Name');
        return implode(',', $names);
    }

    public function getSummaryCategories()
    {
        $names = (array) $this->Categories()->column('Name');
        return implode(',', $names);
    }

    /*
    public function Link() {
    return $this->Directory()->Link('browse/'.$this->ID);
    }
     *
     */

    protected function locale()
    {
        if (($member = Security::getCurrentUser()) && $member->Locale) {
            return $member->Locale;
        }

        return i18n::get_locale();
    }

    public function forTemplate()
    {
        $tpl = get_class($this);
        return $this->renderWith($tpl);
    }
}
