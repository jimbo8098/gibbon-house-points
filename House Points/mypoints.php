<?php
// manage house point categories
if (isActionAccessible($guid, $connection2,"/modules/House Points/mypoints.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    
    $page->breadcrumbs->add(__('My points'));

    $modpath =  "./modules/".$_SESSION[$guid]["module"];
    include $modpath."/function.php";
    include $modpath."/mypoints_function.php";
   
    ?>
    <script>
        var modpath = '<?php echo $modpath ?>';
        var studentID = '<?php echo $_SESSION[$guid]['gibbonPersonID'] ?>';
    </script>
    <?php
    
    $mypt = new mypt($guid, $connection2);
    $mypt->modpath = $modpath;
    
    $mypt->mainform();
}
