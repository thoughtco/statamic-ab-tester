/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/ABExperimentWizard.vue?vue&type=script&lang=js&":
/*!*****************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/ABExperimentWizard.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _HasWizardSteps_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./HasWizardSteps.js */ "./resources/js/components/HasWizardSteps.js");
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  mixins: [_HasWizardSteps_js__WEBPACK_IMPORTED_MODULE_0__["default"]],
  props: {
    route: {
      type: String
    }
  },
  data: function data() {
    return {
      currentStep: 0,
      steps: [__('Naming'), __('Variants'), __('Goal')],
      experiment: {
        title: null,
        handle: null,
        variants: [],
        goal_type: null,
        goal_destination: null
      }
    };
  },
  computed: {
    canSubmit: function canSubmit() {
      if (this.experiment.email) {
        return isEmail(this.experiment.email);
      }

      return true;
    }
  },
  methods: {
    canGoToStep: function canGoToStep(step) {
      if (step >= 1) {
        return Boolean(this.experiment.title && this.experiment.handle);
      }

      return true;
    },
    submit: function submit() {
      var _this = this;

      this.$axios.post(this.route, this.experiment).then(function (response) {
        window.location = response.data.redirect;
      })["catch"](function (error) {
        _this.$toast.error(error.response.data.message);
      });
    }
  },
  watch: {
    'experiment.title': function experimentTitle(val) {
      this.experiment.handle = this.$slugify(val, '_');
    }
  },
  mounted: function mounted() {
    this.$keys.bindGlobal(['command+return'], this.next);
    this.$keys.bindGlobal(['command+delete'], this.previous);
  }
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/ABExperimentWizard.vue?vue&type=template&id=194d51d8&":
/*!*********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/ABExperimentWizard.vue?vue&type=template&id=194d51d8& ***!
  \*********************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { staticClass: "max-w-xl mx-auto rounded shadow bg-white" },
    [
      _c("div", { staticClass: "max-w-lg mx-auto pt-6 relative" }, [
        _c(
          "div",
          { staticClass: "wizard-steps" },
          _vm._l(_vm.steps, function(step, index) {
            return _c(
              "a",
              {
                staticClass: "step",
                class: { complete: _vm.currentStep >= index },
                on: {
                  click: function($event) {
                    return _vm.goToStep(index)
                  }
                }
              },
              [
                _c("div", { staticClass: "ball" }, [_vm._v(_vm._s(index + 1))]),
                _vm._v(" "),
                _c("div", { staticClass: "label" }, [_vm._v(_vm._s(step))])
              ]
            )
          }),
          0
        )
      ]),
      _vm._v(" "),
      _vm.currentStep === 0
        ? _c("div", [
            _c(
              "div",
              { staticClass: "max-w-md mx-auto px-2 py-6 text-center" },
              [
                _c("h1", { staticClass: "mb-3" }, [
                  _vm._v(_vm._s(_vm.__("Create a New Experiment")))
                ]),
                _vm._v(" "),
                _c("p", {
                  staticClass: "text-grey",
                  domProps: {
                    textContent: _vm._s(
                      _vm.__(
                        "Experiments allow you to run variations on parts of your website and track the visitors' experiences, to make data-driven decisions on changes."
                      )
                    )
                  }
                })
              ]
            ),
            _vm._v(" "),
            _c("div", { staticClass: "max-w-md mx-auto px-2 pb-7" }, [
              _c(
                "label",
                {
                  staticClass: "font-bold text-base mb-sm",
                  attrs: { for: "name" }
                },
                [_vm._v(_vm._s(_vm.__("Title")))]
              ),
              _vm._v(" "),
              _c("input", {
                directives: [
                  {
                    name: "model",
                    rawName: "v-model",
                    value: _vm.experiment.title,
                    expression: "experiment.title"
                  }
                ],
                staticClass: "input-text",
                attrs: { type: "text", autofocus: "", tabindex: "1" },
                domProps: { value: _vm.experiment.title },
                on: {
                  input: function($event) {
                    if ($event.target.composing) {
                      return
                    }
                    _vm.$set(_vm.experiment, "title", $event.target.value)
                  }
                }
              })
            ]),
            _vm._v(" "),
            _c("div", { staticClass: "max-w-md mx-auto px-2 pb-7" }, [
              _c(
                "label",
                {
                  staticClass: "font-bold text-base mb-sm",
                  attrs: { for: "name" }
                },
                [_vm._v(_vm._s(_vm.__("Handle")))]
              ),
              _vm._v(" "),
              _c("input", {
                directives: [
                  {
                    name: "model",
                    rawName: "v-model",
                    value: _vm.experiment.handle,
                    expression: "experiment.handle"
                  }
                ],
                staticClass: "input-text",
                attrs: { type: "text", tabindex: "2" },
                domProps: { value: _vm.experiment.handle },
                on: {
                  input: function($event) {
                    if ($event.target.composing) {
                      return
                    }
                    _vm.$set(_vm.experiment, "handle", $event.target.value)
                  }
                }
              })
            ])
          ])
        : _vm._e(),
      _vm._v(" "),
      _vm.currentStep === 1
        ? _c("div", [
            _c(
              "div",
              { staticClass: "max-w-md mx-auto px-2 py-6 text-center" },
              [
                _c("h1", { staticClass: "mb-3" }, [
                  _vm._v(_vm._s(_vm.__("Variants")))
                ]),
                _vm._v(" "),
                _c("p", {
                  staticClass: "text-grey",
                  domProps: {
                    textContent: _vm._s(
                      _vm.__(
                        "Variants are the names of each of the experiment variations you wish to create."
                      )
                    )
                  }
                })
              ]
            ),
            _vm._v(" "),
            _c(
              "div",
              { staticClass: "max-w-md mx-auto px-2 pb-7" },
              [
                _c(
                  "label",
                  {
                    staticClass: "font-bold text-base mb-sm",
                    attrs: { for: "name" }
                  },
                  [_vm._v(_vm._s(_vm.__("Variants")))]
                ),
                _vm._v(" "),
                _c("list-fieldtype", {
                  staticClass: "list-reset",
                  model: {
                    value: _vm.experiment.variants,
                    callback: function($$v) {
                      _vm.$set(_vm.experiment, "variants", $$v)
                    },
                    expression: "experiment.variants"
                  }
                })
              ],
              1
            )
          ])
        : _vm._e(),
      _vm._v(" "),
      _vm.currentStep === 2
        ? _c("div", [
            _c(
              "div",
              { staticClass: "max-w-md mx-auto px-2 py-6 text-center" },
              [
                _c("h1", { staticClass: "mb-3" }, [
                  _vm._v(_vm._s(_vm.__("Goal")))
                ]),
                _vm._v(" "),
                _c("p", {
                  staticClass: "text-grey",
                  domProps: {
                    textContent: _vm._s(
                      _vm.__(
                        "A goal is the 'thing' that defines whether an experiment was successful."
                      )
                    )
                  }
                })
              ]
            ),
            _vm._v(" "),
            _c(
              "div",
              { staticClass: "max-w-md mx-auto px-2 pb-7" },
              [
                _c(
                  "label",
                  {
                    staticClass: "font-bold text-base mb-sm",
                    attrs: { for: "name" }
                  },
                  [_vm._v(_vm._s(_vm.__("Goal Type")))]
                ),
                _vm._v(" "),
                _c("select-input", {
                  attrs: {
                    options: [{ value: "redirect", label: "Redirect" }]
                  },
                  model: {
                    value: _vm.experiment.goal_type,
                    callback: function($$v) {
                      _vm.$set(_vm.experiment, "goal_type", $$v)
                    },
                    expression: "experiment.goal_type"
                  }
                })
              ],
              1
            ),
            _vm._v(" "),
            _c("div", { staticClass: "max-w-md mx-auto px-2 pb-7" }, [
              _c(
                "label",
                {
                  staticClass: "font-bold text-base mb-sm",
                  attrs: { for: "name" }
                },
                [_vm._v(_vm._s(_vm.__("Goal Destination")))]
              ),
              _vm._v(" "),
              _c("input", {
                directives: [
                  {
                    name: "model",
                    rawName: "v-model",
                    value: _vm.experiment.goal_destination,
                    expression: "experiment.goal_destination"
                  }
                ],
                staticClass: "input-text",
                attrs: { type: "text", autofocus: "", tabindex: "1" },
                domProps: { value: _vm.experiment.goal_destination },
                on: {
                  input: function($event) {
                    if ($event.target.composing) {
                      return
                    }
                    _vm.$set(
                      _vm.experiment,
                      "goal_destination",
                      $event.target.value
                    )
                  }
                }
              })
            ])
          ])
        : _vm._e(),
      _vm._v(" "),
      _c("div", { staticClass: "border-t p-2" }, [
        _c(
          "div",
          { staticClass: "max-w-md mx-auto flex items-center justify-center" },
          [
            !_vm.onFirstStep
              ? _c(
                  "button",
                  {
                    staticClass: "btn mx-2 w-32",
                    attrs: { tabindex: "3" },
                    on: { click: _vm.previous }
                  },
                  [
                    _vm._v(
                      "\n                ← " +
                        _vm._s(_vm.__("Previous")) +
                        "\n            "
                    )
                  ]
                )
              : _vm._e(),
            _vm._v(" "),
            !_vm.onLastStep
              ? _c(
                  "button",
                  {
                    staticClass: "btn mx-2 w-32",
                    attrs: { tabindex: "4", disabled: !_vm.canContinue },
                    on: { click: _vm.next }
                  },
                  [
                    _vm._v(
                      "\n                " +
                        _vm._s(_vm.__("Next")) +
                        " →\n            "
                    )
                  ]
                )
              : _vm._e(),
            _vm._v(" "),
            _vm.onLastStep
              ? _c(
                  "button",
                  {
                    staticClass: "btn-primary mx-3",
                    attrs: { tabindex: "4", disabled: !_vm.canSubmit },
                    on: { click: _vm.submit }
                  },
                  [
                    _vm._v(
                      "\n                " +
                        _vm._s(_vm.__("Create Experiment")) +
                        "\n            "
                    )
                  ]
                )
              : _vm._e()
          ]
        )
      ])
    ]
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js":
/*!********************************************************************!*\
  !*** ./node_modules/vue-loader/lib/runtime/componentNormalizer.js ***!
  \********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return normalizeComponent; });
/* globals __VUE_SSR_CONTEXT__ */

// IMPORTANT: Do NOT use ES2015 features in this file (except for modules).
// This module is a runtime utility for cleaner component module output and will
// be included in the final webpack user bundle.

function normalizeComponent (
  scriptExports,
  render,
  staticRenderFns,
  functionalTemplate,
  injectStyles,
  scopeId,
  moduleIdentifier, /* server only */
  shadowMode /* vue-cli only */
) {
  // Vue.extend constructor export interop
  var options = typeof scriptExports === 'function'
    ? scriptExports.options
    : scriptExports

  // render functions
  if (render) {
    options.render = render
    options.staticRenderFns = staticRenderFns
    options._compiled = true
  }

  // functional template
  if (functionalTemplate) {
    options.functional = true
  }

  // scopedId
  if (scopeId) {
    options._scopeId = 'data-v-' + scopeId
  }

  var hook
  if (moduleIdentifier) { // server build
    hook = function (context) {
      // 2.3 injection
      context =
        context || // cached call
        (this.$vnode && this.$vnode.ssrContext) || // stateful
        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional
      // 2.2 with runInNewContext: true
      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
        context = __VUE_SSR_CONTEXT__
      }
      // inject component styles
      if (injectStyles) {
        injectStyles.call(this, context)
      }
      // register component module identifier for async chunk inferrence
      if (context && context._registeredComponents) {
        context._registeredComponents.add(moduleIdentifier)
      }
    }
    // used by ssr in case component is cached and beforeCreate
    // never gets called
    options._ssrRegister = hook
  } else if (injectStyles) {
    hook = shadowMode
      ? function () { injectStyles.call(this, this.$root.$options.shadowRoot) }
      : injectStyles
  }

  if (hook) {
    if (options.functional) {
      // for template-only hot-reload because in that case the render fn doesn't
      // go through the normalizer
      options._injectStyles = hook
      // register for functional component in vue file
      var originalRender = options.render
      options.render = function renderWithStyleInjection (h, context) {
        hook.call(context)
        return originalRender(h, context)
      }
    } else {
      // inject component registration as beforeCreate hook
      var existing = options.beforeCreate
      options.beforeCreate = existing
        ? [].concat(existing, hook)
        : [hook]
    }
  }

  return {
    exports: scriptExports,
    options: options
  }
}


/***/ }),

/***/ "./resources/js/ab.js":
/*!****************************!*\
  !*** ./resources/js/ab.js ***!
  \****************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _components_ABExperimentWizard__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/ABExperimentWizard */ "./resources/js/components/ABExperimentWizard.vue");
/* harmony import */ var _components_ABExperimentResults__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/ABExperimentResults */ "./resources/js/components/ABExperimentResults.js");


Statamic.$components.register('ab-experiment-wizard', _components_ABExperimentWizard__WEBPACK_IMPORTED_MODULE_0__["default"]);
Statamic.$components.register('ab-experiment-results', _components_ABExperimentResults__WEBPACK_IMPORTED_MODULE_1__["default"]);

/***/ }),

/***/ "./resources/js/components/ABExperimentResults.js":
/*!********************************************************!*\
  !*** ./resources/js/components/ABExperimentResults.js ***!
  \********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ({
  props: ['initial', 'refreshUrl'],
  data: function data() {
    return {
      results: this.initial
    };
  },
  methods: {
    refresh: function refresh() {
      var _this = this;

      this.$axios.get(this.refreshUrl).then(function (_ref) {
        var data = _ref.data;
        _this.results = data.results;
        console.log('refreshed');
        setTimeout(_this.refresh, 1000);
      });
    }
  },
  mounted: function mounted() {
    setTimeout(this.refresh, 1000);
  },
  render: function render() {
    return this.$scopedSlots["default"]({
      results: this.results
    });
  }
});

/***/ }),

/***/ "./resources/js/components/ABExperimentWizard.vue":
/*!********************************************************!*\
  !*** ./resources/js/components/ABExperimentWizard.vue ***!
  \********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _ABExperimentWizard_vue_vue_type_template_id_194d51d8___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ABExperimentWizard.vue?vue&type=template&id=194d51d8& */ "./resources/js/components/ABExperimentWizard.vue?vue&type=template&id=194d51d8&");
/* harmony import */ var _ABExperimentWizard_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ABExperimentWizard.vue?vue&type=script&lang=js& */ "./resources/js/components/ABExperimentWizard.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _ABExperimentWizard_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _ABExperimentWizard_vue_vue_type_template_id_194d51d8___WEBPACK_IMPORTED_MODULE_0__["render"],
  _ABExperimentWizard_vue_vue_type_template_id_194d51d8___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/ABExperimentWizard.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/ABExperimentWizard.vue?vue&type=script&lang=js&":
/*!*********************************************************************************!*\
  !*** ./resources/js/components/ABExperimentWizard.vue?vue&type=script&lang=js& ***!
  \*********************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ABExperimentWizard_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./ABExperimentWizard.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/ABExperimentWizard.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ABExperimentWizard_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/ABExperimentWizard.vue?vue&type=template&id=194d51d8&":
/*!***************************************************************************************!*\
  !*** ./resources/js/components/ABExperimentWizard.vue?vue&type=template&id=194d51d8& ***!
  \***************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ABExperimentWizard_vue_vue_type_template_id_194d51d8___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./ABExperimentWizard.vue?vue&type=template&id=194d51d8& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/ABExperimentWizard.vue?vue&type=template&id=194d51d8&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ABExperimentWizard_vue_vue_type_template_id_194d51d8___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ABExperimentWizard_vue_vue_type_template_id_194d51d8___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/HasWizardSteps.js":
/*!***************************************************!*\
  !*** ./resources/js/components/HasWizardSteps.js ***!
  \***************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ({
  data: function data() {
    return {
      currentStep: 0
    };
  },
  computed: {
    onFirstStep: function onFirstStep() {
      return this.currentStep === 0;
    },
    onLastStep: function onLastStep() {
      return this.currentStep === this.steps.length - 1;
    },
    canContinue: function canContinue() {
      return this.canGoToStep(this.currentStep + 1);
    }
  },
  methods: {
    goToStep: function goToStep(n) {
      if (this.canGoToStep(n)) {
        this.currentStep = n;
      }
    },
    next: function next() {
      if (!this.onLastStep) {
        this.goToStep(this.currentStep + 1);
      }
    },
    previous: function previous() {
      if (!this.onFirstStep) {
        this.goToStep(this.currentStep - 1);
      }
    }
  }
});

/***/ }),

/***/ 0:
/*!**********************************!*\
  !*** multi ./resources/js/ab.js ***!
  \**********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/aryeh/Code/Packages/statamic-a-b/resources/js/ab.js */"./resources/js/ab.js");


/***/ })

/******/ });