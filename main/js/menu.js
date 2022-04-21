jQuery(function ($) {

    $(".sidebar-dropdown > a").click(function() {
  $(".sidebar-submenu").slideUp(200);
  if (
    $(this)
      .parent()
      .hasClass("active")
  ) {
    $(".sidebar-dropdown").removeClass("active");
    $(this)
      .parent()
      .removeClass("active");
  } else {
    $(".sidebar-dropdown").removeClass("active");
    $(this)
      .next(".sidebar-submenu")
      .slideDown(200);
    $(this)
      .parent()
      .addClass("active");
  }
});

$("#close-sidebar").click(function() {
  $(".page-wrapper").removeClass("toggled");
});
$("#show-sidebar").click(function() {
  $(".page-wrapper").addClass("toggled");
});

$('[data-toggle="popover"]').popover();

$('.popover-dismiss').popover({
  trigger: 'focus'
});
   

$("li.sidebar-dropdown div.sidebar-submenu > ul li").each(function(){
  $address = $(this).children('a').attr('href');
  if($address == document.location.pathname.match(/[^\/]+$/)[0] ){
     $(this).parent().parent().parent().parent().parent().addClass('active');
     $(this).parent().parent().parent().parent().prop('style','display:block');
    console.log( $(this).parent().parent().parent().parent().parent());
    return;
  }
});

});