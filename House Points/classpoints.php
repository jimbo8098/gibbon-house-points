<?php
// manage house point categories
if (isActionAccessible($guid, $connection2,"/modules/House Points/classpoints.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    
    $page->breadcrumbs->add(__('View points class'));

    $modpath =  "./modules/".$_SESSION[$guid]["module"];
    include $modpath."/moduleFunctions.php";
    include $modpath."/classpoints_function.php";
   
    ?>
    <script>
        var modpath = '<?php echo $modpath ?>';
    </script>
    <?php
    
    $cls = new cls($guid, $connection2);
    $cls->modpath = $modpath;
    
    $cls->mainform();
}
