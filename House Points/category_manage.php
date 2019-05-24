<?php
// manage house point categories
if (isActionAccessible($guid, $connection2,"/modules/House Points/category_manage.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {

    $page->breadcrumbs->add(__('Categories'));
    
    $modpath =  "./modules/".$_SESSION[$guid]["module"];
    include $modpath."/moduleFunctions.php";
    include $modpath."/category_function.php";
    
    $cat = new cat($guid, $connection2);
    $cat->modpath = $modpath;
    
    $cat->mainform();
}
