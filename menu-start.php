<?php
$DB = new classes\Database();
$DB->query("SELECT tbl_tUserMenu.UserId,tbl_tMenu.* FROM tbl_tUserMenu LEFT JOIN tbl_tMenu ON tbl_tUserMenu.MenuCode=tbl_tMenu.MenuCode WHERE tbl_tUserMenu.UserId=? ORDER BY MenuLevel, Ordinal");
$DB->execute([$empid]);
$menus = $DB->resultset();

// GET NAME
$DB->query("SELECT * FROM lib_CEGusers WHERE empid = ?");
$DB->execute([$empid]);
$res = $DB->getrow();
if(!$res){ header('location: 404.html?You-are-not-authorized'); }
$username = $_SESSION['empname'];
$position = $_SESSION['positn'];
$dept = $_SESSION['department'];
?>

<link rel="stylesheet" type="text/css" href="main/css/menu.css">
<div class="page-wrapper chiller-theme toggled">
  <a id="show-sidebar" class="btn btn-sm btn-dark" href="#">
  <i class="fas fa-bars"></i>
  </a>
  <nav id="sidebar" class="sidebar-wrapper">
  <div class="sidebar-content">
    <div class="sidebar-brand">
    <a href="index.php"> ENGINEERING PORTAL</a>
    <div id="close-sidebar">
    <i class="fas fa-times"></i>
    </div>
  </div>

  <div class="sidebar-header">
    <div class="user-pic">
    <?php
    // if( file_exists($_SERVER['DOCUMENT_ROOT']."/Photo/".$empid.".jpg") ){
    // echo '<img class="img-responsive img-rounded" src="../../Photo/'.$empid.'.jpg" width: "160px"; height: "150px"; alt="User picture">';
    // }else{
    // if( file_exists($_SERVER['DOCUMENT_ROOT']."/Photo/".$empid.".png") ){
    // echo '<img class="img-responsive img-rounded" src="../../Photo/'.$empid.'.png" width: "160px"; height: "150px"; alt="User picture">';
    // }else{
    // echo '<img class="img-responsive img-rounded" src="main/img/user.jpg" alt="User picture">';
    // }
    // }
    ?>

    </div>
  <div class="user-info">
    <span class="user-name"><?php echo ucwords(strtolower($username)); ?></span>
    <span class="user-role"><?php echo $position; ?></span>
    <span class="user-status">
    <i class="fa fa-circle"></i>
    <span><?php echo $empid; ?></span>
    </span>
  </div>
</div>

<div class="sidebar-menu">
<ul>
<?php

$items = array();
$i=0;

foreach ($menus as $key => $menu) {
  if($menu->MenuCode == $menu->ParentCode || strlen($menu->MenuCode) == strlen($menu->ParentCode)){
    $items[$menu->MenuCode]=[$menu->MenuName];

  } else {
    if( empty( $items[$menu->ParentCode][0] ) ){
      $find = (string) substr($menu->ParentCode, 0, 4);
      $items[$find][$menu->ParentCode]['last'][$menu->MenuCode] = ["url"=>$menu->ExecutePage,"name"=>$menu->MenuName];
    } else {
      $items[$menu->ParentCode][$menu->MenuCode] = ["url"=>$menu->ExecutePage,"name"=>$menu->MenuName];
    }
  }
}

foreach ($items as $key => $value) {
  // Array Range
  echo '<li class="sidebar-dropdown">
  <a href="#">
  <i class="fa fa-folder"></i>
  <span>'.$value[0] . '</span>
  </a>
  <div class="sidebar-submenu">
  <ul>
  ';
  foreach ($value as $k => $val) {
    if( is_array($value[$k]) ){
      if(empty($value[$k]['last'])){
      echo '<li>
      <a href="'.$value[$k]['url'].'">'.$value[$k]['name'].'</a>
      </li>';
      } else {
        echo '<li>
        <a href="#">'.$value[$k]['name'].'</a>
        <ul>';
        
        foreach ($value[$k]['last'] as $i => $v) {
          echo '<li><a href="'.$v['url'].'">'.$v['name'].'</a></li>';
        }
        echo '
        </ul>
        </li>';
      }
    }
  }
  echo '

  </ul>
  </div>
  </li>';
}
?>

</ul>
</div>
<!-- sidebar-menu  -->
</div>
<!-- sidebar-content  -->

</nav>
<!-- sidebar-wrapper  -->
<main class="page-content">