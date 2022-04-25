<?php

namespace CS\Directory\Pages;

use CS\Directory\Page;
use CS\Directory\PageController;
use CS\Directory\Pages\CMDirectoryCategoryPage;
use CS\Directory\Models\CMDirectory;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Control\HTTPRequest;
use CS\Directory\CMDirectoryController;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\View\ArrayData;

class CMDirectoryPage extends Page
{
    private static $table_name = "CMDirectoryPage";
    private static $description = 'Pages for directory listings';

    private static $allowed_children = array(CMDirectoryCategoryPage::class);

    private static $has_one = array(
        'Directory' => CMDirectory::class,
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $directories = CMDirectory::get()->map('ID', 'Name');
        $directoryName = Injector::inst()->create(CMDirectory::class)->i18n_singular_name();
        $fields->addFieldToTab(
            'Root.Main',
            DropdownField::create(
                'DirectoryID',
                $directoryName,
                $directories
            )
        );

        return $fields;
    }
}
