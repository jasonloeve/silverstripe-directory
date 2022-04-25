<?php

namespace CS\Directory\Models;

use Exception;
use SilverStripe\Forms\ListBoxField;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;
use CS\Directory\Pages\CMDirectoryPage;
use SilverStripe\Forms\TextField;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Security\Permission;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\PermissionProvider;
use Symbiote\GridFieldExtensions\GridFieldAddNewMultiClass;

class CMDirectory extends DataObject implements PermissionProvider
{

    private static $table_name = 'CMDirectory';

    private static $db = array(
        'Name' => 'Varchar(255)',
        'EntryTypes' => 'Text',
    );

    private static $has_many = array(
        'Categories' => CMDirectoryCategory::class,
    );

    private static $many_many = array(
        'Entries' => CMDirectoryEntry::class,
    );

    private static $belongs_to = array(
        'Page' => CMDirectoryPage::class,
    );

    /**
     * @config
     * @var array
     */
    private static $entry_types = array(
        CMDirectoryPersonEntry::class,
        CMDirectoryBusinessEntry::class,
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab('Root.Main', TextField::create('Name', _t(CMDirectory::class . '.Name', 'Name')));

        /*
         * Entry type options
         */
        $entryClasses = ClassInfo::subClassesFor(CMDirectoryEntry::class);
        array_shift($entryClasses);
        $entryTypes = array();
        foreach ($entryClasses as $className) {
            try {
                $inst = Injector::inst()->create($className);
                $entryTypes[$className] = $inst->i18n_singular_name();
            } catch (Exception $ex) {
                error_log("$className could not be created. " . $ex->getMessage());
            }
        }

        $fields->addFieldToTab('Root.Main', ListBoxField::create(
            'EntryTypes',
            _t(CMDirectory::class . '.EntryTypes', 'Entry types'),
            $entryTypes,
            '',
            null,
            true// multiple
        ));

        /**
         * Categories
         */
        $categoriesGrid = $fields->dataFieldByName('Categories');

        if ($categoriesGrid) {
            // Remove relation link autocompleter
            $autoCompleter = $categoriesGrid->getConfig()->getComponentByType(GridFieldAddExistingAutocompleter::class);
            $categoriesGrid->getConfig()->removeComponent($autoCompleter);

            // Replace delete action and specify actual deleting, not just removing
            $delete = $categoriesGrid->getConfig()->getComponentByType(GridFieldDeleteAction::class);
            $categoriesGrid->getConfig()->removeComponent($delete);
            $categoriesGrid->getConfig()->addComponent(new GridFieldDeleteAction(false));

            // Sorting
            if (class_exists(GridFieldOrderableRows::class)) {
                $categoriesGrid->getConfig()->addComponent(new GridFieldOrderableRows('Sort'));
            } elseif (class_exists('GridFieldSortableRows')) {
                $categoriesGrid->getConfig()->addComponent(new GridFieldSortableRows('Sort'));
            } else {
                $fields->addFieldToTab('Root.Main', NumericField::create('Sort', 'Sort Order')
                        ->setDescription(_t(CMDirectoryCategory::class . '.SortDescription', 'Enter a whole number to use for sorting (low numbers come first)'))
                );
            }
        }

        /**
         * Entries
         * @todo Allow both unlinking and deleting
         */
        $fields->removeByName('Entries');
        // Only display entries after saving Directory, so we have the entry types
        if ($this->exists()) {
            // Modify Entries GridField config
            $entryGridConf = GridFieldConfig_RelationEditor::create();
            $entryGridConf->removeComponentsByType(GridFieldAddNewButton::class)->addComponent(Injector::inst()->get(GridFieldAddNewMultiClass::class));

            $selectedEntryTypes = explode(',', $this->EntryTypes);
            // If only one entry type, set as default
            $defaultEntryType = (is_array($selectedEntryTypes) && count($selectedEntryTypes) === 1) ? current($selectedEntryTypes) : null;

            $entryGridConf->getComponentByType(GridFieldAddNewMultiClass::class)->setClasses($selectedEntryTypes, $defaultEntryType);
            $entriesPlural = CMDirectoryEntry::create()->i18n_plural_name();

            // Remove relation link autocompleter
            $autoCompleter = $entryGridConf->getComponentByType(GridFieldAddExistingAutocompleter::class);
            $entryGridConf->removeComponent($autoCompleter);

            // Replace delete action and specify actual deleting, not just removing
            $delete = $entryGridConf->getComponentByType(GridFieldDeleteAction::class);
            $entryGridConf->removeComponent($delete);
            $entryGridConf->addComponent(new GridFieldDeleteAction(false));

            $fields->addFieldToTab("Root.$entriesPlural", GridField::create('Entries', $entriesPlural, $this->Entries(), $entryGridConf));

        }

        return $fields;
    }

    /*
    public function Link() {
    return $this->Directory()->Link('browse/'.$this->ID);
    }
     *
     */

    public function canView($member = null)
    {
        return true;
    }

    public function canEdit($member = null)
    {
        return Permission::check('CMDIRECTORY_EDIT');
    }

    public function canDelete($member = null)
    {
        return Permission::check('CMDIRECTORY_DELETE');
    }

    public function canCreate($member = null, $context = [])
    {
        return Permission::check('CMDIRECTORY_CREATE');
    }

    public function providePermissions()
    {
        return array(
            'CMDIRECTORY_CREATE' => array(
                'name' => _t(
                    'CMDirectory.CreatePermissionLabel',
                    'Create a directory'
                ),
                'category' => _t(
                    'CMDirectory.PermissionCategory',
                    'Directories'
                ),
            ),
            'CMDIRECTORY_EDIT' => array(
                'name' => _t(
                    'CMDirectory.EditPermissionLabel',
                    'Edit a directory'
                ),
                'category' => _t(
                    'CMDirectory.PermissionCategory',
                    'Directories'
                ),
            ),
            'CMDIRECTORY_DELETE' => array(
                'name' => _t(
                    'CMDirectory.DeletePermissionLabel',
                    'Delete a directory and all its categories'
                ),
                'category' => _t(
                    'CMDirectory.PermissionCategory',
                    'Directories'
                ),
            ),
        );
    }
}
