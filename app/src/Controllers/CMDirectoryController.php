<?php

namespace CS\Directory\Controllers;

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

class CMDirectoryController extends Controller
{

    /**
     * @config
     * @var array
     */
    private static $allowed_actions = array(
        'index',
        'search',
    );

    protected $directory;

    protected $page;

    public function __construct($directory, $page, $parentCategory = null)
    {
        parent::__construct();

        $this->directory = $directory;
        $this->parentCategory = $parentCategory;
        $this->page = $page;

    }

    public function dentist($request)
    {
        return 'dentist action';
    }

    public function index(HTTPRequest $request)
    {
        $segment = $request->param('Category');
        $nextSegment = $request->param('ChildCategory');

        /*
         * Delegate according to category segment
         */
        // Action method requested (non-category)
        if ($segment && $this->hasAction($segment)) {
            return $this->handleAction($request, $segment);
        }

        $category = $this->findCategoryOrFail($segment, $this->parentCategory);

        // Child category present
        if ($nextSegment) {
            $response = $this->delegateToNestedController($request, $category);
        }
        // Category requested
        elseif ($category) {
            $response = $this->searchByCategory($category);
        }
        // Category assigned
        elseif ($this->parentCategory) {
            $response = $this->searchByCategory($this->parentCategory);
        }
        // No action
        else {
            $response = $this->searchAll();
        }

        if ($response instanceof DataList) {
            $this->extend('updateSearchResults', $response, $request);
        }
        return $response;

    }

    protected function delegateToNestedController(HTTPRequest $request, $category)
    {
        // Category found
        $request->shiftAllParams();
        $request->shift();

        $response = Injector::inst()->create(CMDirectoryController::class, $this->directory, $this->page, $category)->index($request);
        return $response;
    }

    protected function searchByCategory($category)
    {
        return $category->Entries();
    }

    protected function searchAll()
    {

        return $this->directory->Entries();
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
        $parentID = ($this->parentCategory) ? (int) $this->parentCategory->ID : 0;
        $category = $this->findCategory($this->request->param('Category'), $parentID);
        $categoryID = ($category) ? (int) $category->ID : $parentID; // Default to parent

        $fields = FieldList::create(
            DropdownField::create('category', _t(CMDirectoryPage::class . '.SearchFormCategoryLabel'), $this->categoryOptions(), $categoryID)
        );

        $required = RequiredFields::create();

        // Extension hook
        $this->extend('updateSearchFormFields', $fields, $required);

        $actions = FieldList::create(
            FormAction::create("doSearch")->setTitle(_t(CMDirectoryPage::class . '.SearchFormSubmit', 'Search'))
        );

        $form = Form::create($this, 'DirectorySearchForm', $fields, $actions, $required);

        $action = $this->page->Link();
        if (strpos($action, 'DirectorySearchForm') === false) {
            $action = static::join_links($action, 'DirectorySearchForm');
        }

        $form->setFormAction($action);
        $form->setFormMethod('POST');

        $this->extend('updateSearchForm', $form);

        return $form;
    }

    /**
     * @todo check security?
     * @return string
     */
    public function Link($action = null)
    {
        return $this->request->getURL();
    }
    /**
     * Get array of top level categories
     * @return array
     */
    protected function categoryOptions()
    {
        if (!$this->directory) {
            return array();
        }
        return $this->directory->Categories()->filter(array('ParentID' => 0))->map('ID', 'Name');
    }

    /**
     *
     * @param array $data
     * @param object $form
     * @return SS_HTTPResponse | false - Redirection
     */
    public function doSearch($data, $form)
    {

        $catId = (!empty($data['category'])) ? (int) $data['category'] : 0;
        $category = ($catId) ? CMDirectoryCategory::get()->byID($catId) : null;
        /*
        if(!$category) {
        return $this->redirectBack();
        }
         *
         */

        $url = $category->Link(true);

        return $this->redirect($url);

    }

    /*
     * -------------------------------------------------------------------------
     *  Helper methods
     * -------------------------------------------------------------------------
     */

    protected function findCategory($segment, $parentCategory)
    {
        $parentID = ($parentCategory && !empty($parentCategory->ID))
        ? (int) $parentCategory->ID : 0;
        return CMDirectoryCategory::create()->findBySegment($segment, $this->directory->ID, $parentID);
    }

    protected function findCategoryOrFail($segment, $parentCategory)
    {
        $category = $this->findCategory($segment, $parentCategory);

        return ($category) ? $category : $this->handleError(404,
            sprintf(
                _t(CMDirectoryController::class . '.CategoryNotFound', 'Category "%s" not found'),
                $segment)
        );

    }

    /*
     * -------------------------------------------------------------------------
     *  Error methods
     * -------------------------------------------------------------------------
     */

    protected function handleError($statusCode, $msg)
    {
        $response = class_exists(ErrorPage::class) ? ErrorPage::response_for($statusCode) : new HTTPResponse($msg, $statusCode);
        // Log server errors
        if (intval($statusCode) >= 500) {
            Injector::inst()->get(LoggerInterface::class)->error($msg);
        }
        return $this->httpError($statusCode, $response ? $response : $msg);
    }

    public function xhandleRequest(HTTPRequest $request, $model)
    {

        $this->pushCurrent();
        $this->urlParams = $request->allParams();
        $this->setRequest($request);
        $this->getResponse();
        $this->setDataModel($model);
        $action = $request->param('Action');
        $nextAction = $request->param('ID');

        $this->extend('onBeforeInit');

        $this->init();

        $this->extend('onAfterInit');

        $response = $this->getResponse();
        if ($response->isFinished()) {
            //$this->popCurrent();
            return $response;
        }

        /*
         * Checks
         */
        if (empty($this->directory) || empty($this->directory->ID)) {
            $errMsg = sprintf(_t(CMDirectoryController::class . '.DirectoryNotSet', 'Directory not set in %s'), get_class($this));
            //$this->popCurrent();
            return $this->handleError(500, $errMsg);
        }

        //$this->pushCurrent();
        //$this->urlParams = $request->allParams();
        //$this->setRequest($request);
        $this->setDataModel($model);

        $category = null;

        if ($action && !$this->hasAction($action)) {
            $parentID = ($this->parentCategory) ? (int) $this->parentCategory->ID : 0;
            $category = $this->find_category_by_segment($action, $parentID);
        }
        // Valid category, so delegate again with category as parent
        if ($category) {
            //$request->shiftAllParams();
            $request->shift();

            exit();
            $response = Injector::inst()->create(CMDirectoryController::class,
                $this->directory, $this->page, $category)->handleRequest($request, $this->model);
        } else {
            Director::set_current_page($this->page);

            try {
                $response = parent::handleRequest($request, $model);

                Director::set_current_page(null);
            } catch (HTTPResponse_Exception $e) {
                //$this->popCurrent();

                Director::set_current_page(null);

                throw $e;
            }

        }

        return $response;
    }

    /**
     * Gets the category path
     * @param SS_HTTPRequest $request
     * @return array
     */
    protected function xprocessPath($request)
    {

        // Extract and process each category segment
        $pathParts = array();
        $i = 1;
        do {
            $parentCategory = null;
            $segment = rawurlencode($request->param('Cat' . $i));
            // Category segment found
            if (!empty($segment)) {
                $segmentInfo = array(
                    'URLSegment' => $segment,
                );

                // Find category
                $parentID = $parentCategory ? $parentCategory->ID : null;
                $category = $this->find_category_by_segment($segment, $this->directory->ID, $parentID);

                // Category found
                if ($category && $category->exists()) {
                    $segmentInfo['ID'] = $category->ID;
                    $segmentInfo['Name'] = $category->Name;
                    $segmentInfo['ParentID'] = $category->ParentID;

                    $parentCategory = $category;
                }
                // Category not found
                else {
                    $errMsg = _t(CMDirectoryCategoryService::class . '.CategoryNotFound', 'Category not found for %s');
                    $errMsg = sprintf($errMsg, $segment);
                    throw new HTTPResponse_Exception(new HTTPResponse($errMsg, 404));
                }
                $pathParts[] = $segmentInfo;
            }
            // No segment, stop processing
            else {
                break;
            }
            ++$i;
        } while ($segment && $i <= 10);

        return $pathParts;
    }
}
