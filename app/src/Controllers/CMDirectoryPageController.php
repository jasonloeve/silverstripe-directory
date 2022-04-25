<?php

namespace CS\Directory\Controllers;

use CS\Directory\PageController;
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\DataList;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\Form;
use SilverStripe\ErrorPage\ErrorPage;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\Controller;
use CS\Directory\Models\CMDirectoryCategory;

class CMDirectoryPageController extends PageController
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
            ->create(CMDirectoryController::class, $this->Directory(), $this->dataRecord, null)
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
            ->create(CMDirectoryController::class, $this->Directory(), $this->dataRecord, null);
        $controller->setRequest($this->request);
        return $controller->DirectorySearchForm();
    }

    /*
     * -------------------------------------------------------------------------
     *  Template methods
     * -------------------------------------------------------------------------
     */

    public function DirectoryAction()
    {
        $action = $this->request->param('Action') ?: 'index';
        return $action;
    }

    /*
 * -------------------------------------------------------------------------
 *  Helper methods
 * -------------------------------------------------------------------------
 */

}
