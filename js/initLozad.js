jQuery(document).ready(function(){  
/* replace retina src images if use class image2x */
  var image2x = function () {
      //console.log('images2x');
      var pixelRatio = !!window.devicePixelRatio ? window.devicePixelRatio : 1;

      //var pixelRatio = 2; //hidden for retina

      // verify the existence of the file
      var use_if_file_exists = false;

      if (pixelRatio > 1) {
          if (use_if_file_exists) {
              var http = new XMLHttpRequest();
          }
          __handleBgImageTo2xBg(use_if_file_exists);

          var els = jQuery("img.image2x").get();

          var len = els.length;

          for (var i = 0; i < len; i++) {
              var src = els[i].src;
              var data_src = $(els[i]).data('src');
              var data_srcset = $(els[i]).data('srcset');
              var srcset = $(els[i]).attr('srcset');
              var source = $(els[i]).siblings('source').get();

              if (data_src == undefined) {
                  data_src = '';
              }
              if (data_srcset == undefined) {
                  data_srcset = '';
              }
              if (srcset == undefined) {
                  srcset = '';
              }

              src = __replaceImgTo2Img(src);
              data_src = __replaceImgTo2Img(data_src);
              data_srcset = __replaceImgTo2Img(data_srcset);
              srcset = __replaceImgTo2Img(srcset);
              reolaceSrcset(source);
              if (use_if_file_exists) {
                  if (UrlExists(src)) {
                      $(els[i]).attr({
                          'src': src,
                          'data-src': data_src,
                          'data-srcset': data_srcset,
                          'srcset': srcset
                      });
                  }
              } else {

                  $(els[i]).attr({
                      'src': src,
                      'data-src': data_src,
                      'data-srcset': data_srcset,
                      'srcset': srcset
                  });
              }

          }
      }

      function __handleBgImageTo2xBg(useIfFileExists) {
          jQuery('.image2x[data-background-image]').each(function (i, el) {
              var jQueryEl = $(el);
              var data_background_image = $(els[i]).data('background-image');

              if (useIfFileExists) {
                  // Example: jQueryEl.css('backgroundImage') = url("/images/upload/bkg-cover/bgHeadLand@2x.jpg")
                  if (UrlExists(__replaceImgTo2Img(data_background_image.substring(5, (data_background_image.length - 2))))) {
                      __replaceBgImageTo2xBg(jQueryEl);
                  }
              } else {
                  __replaceBgImageTo2xBg(jQueryEl);
              }
              return true;
          });
      }

      function __replaceBgImageTo2xBg(jQueryEl) {
          jQueryEl.attr('data-background-image', __replaceImgTo2Img(jQueryEl.data('background-image')));
      }

      function __replaceImgTo2Img(currentString) {
          if (currentString.match('@2x')) {
              return currentString;
          } else {
              return currentString.replace('.png', '@2x.png')
                  .replace('.jpg', '@2x.jpg')
                  .replace('.webp', '@2x.webp');
          }
      }

      function reolaceSrcset(els) {
          var len = els.length;
          for (var i = 0; i < len; i++) {
              var src = els[i].srcset;
              els[i].srcset = __replaceImgTo2Img(src);
          }
      }

      function UrlExists(url) {
          http.open('HEAD', url, false);
          http.send();
          return http.status != 404;
      }

  };

  /*--------------------------------*/
  /* START init lazyload */
  if (typeof lozad == 'function') {
      if (initLazyload == undefined) {
          var initLazyload = function () {
              lozad('.lazyload', {
                  loaded: function (el) {
                      el.classList.remove('lazyload')
                  }
              }).observe();

              image2x();

              jQuery(window).load(function () {
                  jQuery(document).trigger('scroll', {
                      detail: 'Display on trigger...'
                  });
              });
          };
      };

      initLazyload();

  } else {
      image2x();
  };
	
});