/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/app/flickitygallery.js":
/*!************************************!*\
  !*** ./src/app/flickitygallery.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
// https://github.com/metafizzy/flickity
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (function ($) {
  var $flickitygallery = $('.carousel.flickitygallery');

  if ($flickitygallery.length) {
    $flickitygallery.each(function () {
      var $this = $(this);
      var $view = $this.prev().eq(0);
      var firstimg = $this.find('.carousel-cell').on('click', function () {
        var url = $(this).data('view');
        console.log(url);
        console.log($view);
        $view.css('background-image', 'url("' + url + '")');
      }).first().data('view');
      $view.css('background-image', 'url("' + firstimg + '")');
      $this.flickity({
        autoPlay: false,
        prevNextButtons: false,
        wrapAround: true,
        pageDots: false,
        lazyLoad: 6
      });
    });
  }
});

/***/ }),

/***/ "./src/app/main.js":
/*!*************************!*\
  !*** ./src/app/main.js ***!
  \*************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _flickitygallery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./flickitygallery */ "./src/app/flickitygallery.js");
// import animate from './animate'
// import paralax from './paralax'
// import googlephotos from './googlephotos'


(function ($) {
  // animate($)
  // paralax($)
  // googlephotos($)
  (0,_flickitygallery__WEBPACK_IMPORTED_MODULE_0__["default"])($);
})(jQuery);

/***/ }),

/***/ "./src/scss/main.scss":
/*!****************************!*\
  !*** ./src/scss/main.scss ***!
  \****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!************************************************************************************************************************!*\
  !*** ./node_modules/webpack-polyfill-injector/src/loader.js?{"modules":["./src/app/main.js","./src/scss/main.scss"]}! ***!
  \************************************************************************************************************************/
// Webpack Polyfill Injector
function main() {
    __webpack_require__(/*! ./src/app/main.js */ "./src/app/main.js");
    __webpack_require__(/*! ./src/scss/main.scss */ "./src/scss/main.scss");
}
if (function() {
    return /* Array.from */ !("from"in Array&&function(){try{return Array.from({length:-Infinity}),"a"===Array.from(new self.Set(["a"]))[0]&&"a"===Array.from(new self.Map([["a","one"]]))[0][0]}catch(r){return!1}}()
) ||
        /* Array.prototype.fill */ !("fill"in Array.prototype
) ||
        /* Array.prototype.find */ !("find"in Array.prototype
) ||
        /* Array.prototype.findIndex */ !("findIndex"in Array.prototype
) ||
        /* Array.prototype.includes */ !("includes"in Array.prototype
) ||
        /* Object.assign */ !("assign"in Object
) ||
        /* Object.entries */ !("entries"in Object
) ||
        /* Object.values */ !("values"in Object
) ||
        /* Promise */ !("Promise"in self
) ||
        /* String.prototype.startsWith */ !("startsWith"in String.prototype
) ||
        /* fetch */ !("fetch"in self&&"Request"in self&&function(){try{return"signal"in new Request("")}catch(e){return!1}}()
);
}.call(window)) {
    var js = document.createElement('script');
    js.src = "/wp-content/themes/astra-child-cialadamapolyfills.js";
    js.onload = main;
    js.onerror = function onError(message) {
        console.error('Could not load the polyfills: ' + message);
    };
    document.head.appendChild(js);
} else {
    main();
}
})();

/******/ })()
;