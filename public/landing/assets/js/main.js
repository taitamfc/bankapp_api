/* ==============
 ========= Js Documentation =========

 Template Name: Mosto
 Version: 4.3
 Description: Software and App Landing Pages HTML Template
 Author: Pixelaxis
 Author URI: https://themeforest.net/user/pixelaxis

    =========================

     01. data background
     ---------------------------
     02. Animated Hamburger Icon
     ---------------------------
     03. Position Navbar On Scroll
     ---------------------------
     04. window resize
     ---------------------------
 
    =========================
============== */

(function ($) {
  "use strict";

  jQuery(document).ready(function () {
    // data background
    $("[data-background]").each(function () {
      $(this).css(
        "background-image",
        "url(" + $(this).attr("data-background") + ")"
      );
    });

    // Animated Hamburger Icon
    $(".navbar-toggler").on("click", function () {
      $(this).toggleClass("toggle-active");
      $("header").toggleClass("header-active");
    });

    // Position Navbar On Scroll
    $(window).on("scroll", function () {
      var scroll = $(window).scrollTop();
      if ((scroll > 30) | (scroll == 30)) {
        $("header").addClass(
          "wow animate__animated animate__fadeInDown header-fixed"
        );
      } else {
        $("header").removeClass(
          "wow animate__animated animate__fadeInDown header-fixed"
        );
      }
    });

    // window resize
    $(window).resize(function () {
      if ($(".navbar-collapse").hasClass("show")) {
        $(".navbar-collapse").removeClass("show");
        $(".navbar-toggler").removeClass("toggle-active");
        $("header").removeClass("header-active");
      }
    });

    // Scroll Bottom To Top
    var ScrollTop = $(".scrollToTop");
    $(window).on("scroll", function () {
      if ($(this).scrollTop() < 500) {
        ScrollTop.removeClass("active");
      } else {
        ScrollTop.addClass("active");
      }
    });

    $(".scrollToTop").on("click", function () {
      $("html, body").animate(
        {
          scrollTop: 0,
        },
        500
      );
      return false;
    });
  });
})(jQuery);
