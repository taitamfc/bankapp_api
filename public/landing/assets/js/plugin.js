/* ==============
 ========= Js Documentation =========

 Template Name: Mosto
 Version: 4.3
 Description: Software and App Landing Pages HTML Template
 Author: Pixelaxis
 Author URI: https://themeforest.net/user/pixelaxis

    =========================

     01. Select Language
     ---------------------------
     02. counter one
     ---------------------------
     03. video popup
     ---------------------------
     04. wow init
     ---------------------------
     
    =========================
============== */

(function ($) {
  "use strict";

  jQuery(document).ready(function () {
    // Select Language
    $(".language-select").niceSelect();

    // counter one
    if (document.querySelector(".counter") !== null) {
      $(".counter").counterUp({
        delay: 10,
        time: 1000,
      });
    }

    // video popup
    if (document.querySelector(".video__popup") !== null) {
      $(".video__popup").magnificPopup({
        disableOn: 768,
        type: "iframe",
        mainClass: "mfp-fade",
        removalDelay: 160,
        preloader: false,
        fixedContentPos: false,
      });
    }

    // wow init
    new WOW().init();
  });
})(jQuery);
