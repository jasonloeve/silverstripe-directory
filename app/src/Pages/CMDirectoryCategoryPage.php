<?php

namespace CS\Directory\Pages;

use CS\Directory\Page;
use CS\Directory\PageController;
use CS\Directory\CMDirectoryPage;
use CS\Directory\Models\CMDirectoryCategory;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use CS\Directory\CMDirectoryController;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\View\ArrayData;

class CMDirectoryCategoryPage extends Page
{
	private static $table_name = "CMDirectoryCategoryPage";

    /**
     * @config
     * @var array
     */
    private static $allowed_children = 'none';

    /**
     * @config
     * @var string
     */
    private static $default_parent = CMDirectoryPage::class;

    /**
     * @config
     * @var boolean
     */
    private static $can_be_root = false;

    private static $has_one = array(
        'Category' => CMDirectoryCategory::class,
    );

    protected $directory;

    /**
     * Find directory
     * @return CMDirectory
     */
    public function Directory()
    {
        if ($this->directory) {
            return $this->directory;
        }
        if ($this->CategoryID) {
            $this->directory = $this->Category()->Directory();
        } else {
            $parent = $this->Parent();
            if (!empty($parent->DirectoryID)) {
                $this->directory = $parent->Directory();
            }
        }
        return $this->directory;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('CategoryID');

        $directory = $this->Directory();
        $directoryId = ($directory) ? (int) $directory->ID : null;

        if ($directoryId) {
            $categoriesField = TreeDropdownField::create('CategoryID', _t(CMDirectoryEntry::class . '.SelectCategory', 'Select category'), CMDirectoryCategory::class, 'ID', 'Name');
            $categoriesField->setFilterFunction(function ($cat) use ($directoryId) {
                // Compare the category directory to the current directory
                if (!empty($cat->DirectoryID) && !empty($directoryId)) {
                    return intval($cat->DirectoryID) === intval($directoryId);
                }
                return false;
            });
            $fields->addFieldToTab('Root.Main', $categoriesField, 'Content');
        }

        return $fields;
    }

}

class CMDirectoryCategoryPage_Controller extends PageController
{

    /**
     * Allowed actions
     * @var array
     */
    private static $allowed_actions = array(
        'search',
        'DirectorySearchForm',
    );

    private static $url_handlers = array(
        'search//$Category/$ChildCategory' => 'search',
    );

    /*
     * -------------------------------------------------------------------------
     *  General action methods
     * -------------------------------------------------------------------------
     */

    public function search(HTTPRequest $request)
    {
        $response = Injector::inst()
            ->create(CMDirectoryController::class, $this->Directory(), $this->dataRecord, $this->Category())
            ->index($request);
        if ($response instanceof HTTPResponse) {
            return $response;
        }
        return $this->customise(ArrayData::create(array(
            'SearchResults' => $response,
        )));
    }

    /*
     * -------------------------------------------------------------------------
     *  Form methods
     * -------------------------------------------------------------------------
     */

    /**
     * Search form
     */
    public function DirectorySearchForm()
    {
        $controller = Injector::inst()
            ->create(CMDirectoryController::class, $this->Directory(), $this->dataRecord, $this->Category());
        $controller->setRequest($this->request);
        return $controller->DirectorySearchForm();
    }

    /*
     * -------------------------------------------------------------------------
     *  Template methods
     * -------------------------------------------------------------------------
     */

    /*
 * -------------------------------------------------------------------------
 *  Helper methods
 * -------------------------------------------------------------------------
 */

}
