<?php

namespace CS\Directory;

use CS\Directory\Models\CMDirectory;
use SilverStripe\Admin\ModelAdmin;

/**
 * Management of directory
 */
class CMDirectoryAdmin extends ModelAdmin
{

    private static $url_segment = 'directory';

    /**
     * @todo Allow entries to be linked/unlinked to directories, to be true many_many.
     * Currently it only allows deletes, so operates more like has_many
     * @var array
     */
    private static $managed_models = array(CMDirectory::class);

    private static $menu_title = 'Directories';

    /**@todo - Make menu icon
    private static $menu_icon = "";
     */

    /**
     * @todo use nested list view, like pages, and add tree view later
     * @return Form
     */

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        //$categoriesField = $form->Fields()->dataFieldByName('Categories');

        return $form;
    }

}
