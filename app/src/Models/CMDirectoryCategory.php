<?php

namespace CS\Directory\Models;

use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;
use CS\Directory\Models\CMDirectory;
use CS\Directory\Models\CMDirectoryEntry;
use SilverStripe\ORM\Hierarchy\Hierarchy;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\NumericField;
use SilverStripe\View\Parsers\URLSegmentFilter;
use SilverStripe\Security\Permission;
use SilverStripe\Control\Controller;
use SilverStripe\CMS\Controllers\ModelAsController;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\PermissionProvider;


/**
 * Directory category
 */
class CMDirectoryCategory extends DataObject implements PermissionProvider
{

    private static $table_name = 'CMDirectoryCategory';

    private static $db = array(
        'Name' => 'Varchar(255)',
        "URLSegment" => "Varchar(255)",
        'Sort' => 'Int',
    );

    private static $has_many = array(
        'Children' => CMDirectoryCategory::class,
    );

    private static $has_one = array(
        'Directory' => CMDirectory::class,
        'Parent' => CMDirectoryCategory::class,
    );

    private static $belongs_many_many = array(
        'Entries' => CMDirectoryEntry::class,
    );

    private static $extensions = array(
        Hierarchy::class,
    );

    private static $casting = array(
        'CategoryName' => 'Text',
    );

    private static $default_sort = 'Sort';

    /*
     * -------------------------------------------------------------------------
     *  Management methods
     * -------------------------------------------------------------------------
     */

    /**
     * Get editing form fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('DirectoryID');
        $fields->removeByName('ParentID');
        $fields->removeByName('Sort');
        // Category-Entry relations managed via CMSDirectoryEntry, to help avoid duplicate entries
        $fields->removeByName('Entries');

        $subcategoriesGrid = $fields->dataFieldByName('Children');
        if ($subcategoriesGrid) {
            // Remove relation link autocompleter
            $autoCompleter = $subcategoriesGrid->getConfig()->getComponentByType(GridFieldAddExistingAutocompleter::class);
            $subcategoriesGrid->getConfig()->removeComponent($autoCompleter);

            // Replace delete action and specify actual deleting, not just removing
            $delete = $subcategoriesGrid->getConfig()->getComponentByType(GridFieldDeleteAction::class);
            $subcategoriesGrid->getConfig()->removeComponent($delete);
            $subcategoriesGrid->getConfig()->addComponent(new GridFieldDeleteAction(false));

            // Sorting
            if (class_exists(GridFieldOrderableRows::class)) {
                $subcategoriesGrid->getConfig()->addComponent(new GridFieldOrderableRows('Sort'));
            } elseif (class_exists('GridFieldSortableRows')) {
                $subcategoriesGrid->getConfig()->addComponent(new GridFieldSortableRows('Sort'));
            } else {
                $fields->addFieldToTab('Root.Main', NumericField::create('Sort', 'Sort Order')
                        ->setDescription(_t(CMDirectoryCategory::class . '.SortDescription', 'Enter a whole number to use for sorting (low numbers come first)'))
                );
            }
        }

        return $fields;
    }

    /**
     * Validates/generates URLSegment for cateegory when saving
     */
    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->DirectoryID) {
            $this->DirectoryID = $this->findDirectoryID();
        }
        // If there is no URLSegment set, generate one from Title
        $defaultPrefix = _t(DirectoryCategory::class . '.UrlSegmentDefault', 'category');
        $defaultSegment = $this->generateURLSegment($defaultPrefix);
        if ((!$this->URLSegment || $this->URLSegment == $defaultSegment) && $this->Title) {
            $this->URLSegment = $this->generateURLSegment($this->Title);
        } else if ($this->isChanged('URLSegment', 2)) {
            $filter = URLSegmentFilter::create();
            $this->URLSegment = $filter->filter($this->URLSegment);
            // If after sanitising there is no URLSegment, give it a reasonable default
            if (!$this->URLSegment) {
                $this->URLSegment = "$defaultPrefix-$this->ID";
            }

        }

        // Ensure that this object has a non-conflicting URLSegment value.
        $count = 2;
        while (!$this->validURLSegment()) {
            $this->URLSegment = preg_replace('/-[0-9]+$/', "", $this->URLSegment) . '-' . $count;
            $count++;
        }
    }

    /**
     * Deletes all subcategories when deleting this category
     */
    protected function onBeforeDelete()
    {
        parent::onBeforeDelete();

        foreach ($this->Children() as $cat) {
            $cat->delete();
        }
    }

    /*
     * -------------------------------------------------------------------------
     *  Permissions
     * -------------------------------------------------------------------------
     */

    /**
     *
     * @param Member $member
     * @return boolean
     */
    public function canView($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        return true;
    }

    /**
     *
     * @param Member $member
     * @return boolean
     */
    public function canEdit($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        return Permission::check('CMDIRECTORYCATEGORY_EDIT');
    }

    /**
     *
     * @param Member $member
     * @return boolean
     */
    public function canDelete($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        return Permission::check('CMDIRECTORYCATEGORY_DELETE');
    }

    /**
     *
     * @param Member $member
     * @return boolean
     */
    public function canCreate($member = null, $context = [])
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        return Permission::check('CMDIRECTORYCATEGORY_CREATE');
    }

    /**
     *
     * @see DataObject::providePermissions()
     * @return type
     */
    public function providePermissions()
    {
        return array(
            'CMDIRECTORYCATEGORY_CREATE' => array(
                'name' => _t(
                    'CMDirectoryCategory.CreatePermissionLabel',
                    'Create a category'
                ),
                'category' => _t(
                    'CMDirectory.PermissionCategory',
                    'Directories'
                ),
            ),
            'CMDIRECTORYCATEGORY_EDIT' => array(
                'name' => _t(
                    'CMDirectoryCategory.EditPermissionLabel',
                    'Edit a category'
                ),
                'category' => _t(
                    'CMDirectory.PermissionCategory',
                    'Directories'
                ),
            ),
            'CMDIRECTORYCATEGORY_DELETE' => array(
                'name' => _t(
                    'CMDirectoryCategory.DeletePermissionLabel',
                    'Delete a category and all its subcategories'
                ),
                'category' => _t(
                    'CMDirectory.PermissionCategory',
                    'Directories'
                ),
            ),
        );
    }

    /*
     * -------------------------------------------------------------------------
     *  Search methods
     * -------------------------------------------------------------------------
     */

    /**
     *
     * @param string $segment
     */
    public function findBySegment($segment, $directoryID, $parentID)
    {
        $filter = array(
            'DirectoryID' => (int) $directoryID,
            'URLSegment' => rawurlencode($segment),
            'ParentID' => (int) $parentID,
        );

        return static::get()->filter($filter)->first();
    }

    /*
     * -------------------------------------------------------------------------
     *  Template methods
     * -------------------------------------------------------------------------
     */

    public function NestedPath()
    {
        $parts = array(
            $this->URLSegment,
        );
        $parent = $this->Parent();
        // Get rest of link from parent
        if ($parent && $parent->exists()) {
            $parts[] = $parent->NestedPath();
        }
        return call_user_func_array(array(Controller::class, 'join_links'), array_reverse($parts));
    }

    public function Link($search = false)
    {
        $path = $this->NestedPath();

        $action = ($search) ? 'search' : 'browse';
        // Add page link
        $pageLink = '';
        $directoryPage = $this->findDirectoryPage();
        if ($directoryPage && $directoryPage->exists()) {
            $pageLink = $directoryPage->Link();
        }

        // Put the link together
        return Controller::join_links($pageLink, $action, $path);

    }
    /*
     * -------------------------------------------------------------------------
     *  Helper methods
     * -------------------------------------------------------------------------
     */

    /**
     * Finds the directory ID by traversing the category parents
     * @return object
     */
    protected function findDirectoryPage()
    {
        $directory = $this->findDirectory();
        return ($directory) ? $directory->Page() : null;
    }

    /**
     * Finds the directory ID by traversing the category parents
     * @return object
     */
    protected function findDirectory()
    {
        $directoryID = $this->findDirectoryID();
        return CMDirectory::get()->byID($directoryID);
    }

    /**
     * Finds the directory ID. Traverses the category parents if necessary
     * @return int
     */
    protected function findDirectoryID()
    {
        if ($this->DirectoryID) {
            return $this->DirectoryID;
        }
        $parent = $this->Parent();
        while ($parent->exists()) {
            if (!empty($parent->DirectoryID)) {
                return $parent->DirectoryID;
            }
            $parent = $parent->Parent();
        }
        return 0;
    }

    /**
     * Returns true if this object has a URLSegment value that does not conflict with any other objects. This method
     * checks for:
     *  - A page with the same URLSegment that has a conflict
     *  - Conflicts with actions on the parent page
     *  - A conflict caused by a root page having the same URLSegment as a class name
     *
     * @return bool
     */
    public function validURLSegment()
    {
        $directory = $this->Directory();

        if ($directory && $page = $directory->Page()) {
            if ($controller = ModelAsController::controller_for($page)) {
                if ($controller instanceof Controller && $controller->hasAction($this->URLSegment)) {
                    return false;
                }

            }
        }

        // Filters by url, id, and parent
        $filter = array('"CMDirectoryCategory"."URLSegment"' => $this->URLSegment);
        if ($this->ID) {
            $filter['"CMDirectoryCategory"."ID" <> ?'] = $this->ID;
        }

        $filter['"CMDirectoryCategory"."ParentID"'] = $this->ParentID ? $this->ParentID : 0;

        // Check existence
        $existingCategory = DataObject::get_one(CMDirectoryCategory::class, $filter);
        if ($existingCategory) {
            return false;
        }

        return !($existingCategory);
    }

    /**
     * Generate a URL segment based on the name
     * @return string url segment
     */
    protected function generateURLSegment($title)
    {
        $defaultPrefix = _t(CMDirectoryCategory::class . '.UrlSegmentDefault', 'category');
        $filter = URLSegmentFilter::create();
        $t = $filter->filter($title);
        // Fallback to generic page name if path is empty (= no valid, convertable characters)
        if (!$t || $t == '-' || $t == '-1') {
            $t = "$defaultPrefix-$this->ID";
        }

        return $t;
    }
}
