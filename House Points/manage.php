<?php
// manage house point categories
if (isActionAccessible($guid, $connection2,"/modules/House Points/manage.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    
    $page->breadcrumbs->add(__('Manage points'));

    $modpath =  "./modules/".$_SESSION[$guid]["module"];
    include $modpath."/moduleFunctions.php";
    include $modpath."/manage_function.php";
   
    ?>
    <script>
        var modpath = '<?php echo $modpath ?>';
    </script>
    <?php
    
    $man = new man($guid, $connection2);
    $man->modpath = $modpath;
    
    $man->mainform();
}
