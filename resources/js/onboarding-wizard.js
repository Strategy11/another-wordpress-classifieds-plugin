/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/@wordpress/dom-ready/build-module/index.js":
/*!*****************************************************************!*\
  !*** ./node_modules/@wordpress/dom-ready/build-module/index.js ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ domReady)
/* harmony export */ });
/**
 * @typedef {() => void} Callback
 *
 * TODO: Remove this typedef and inline `() => void` type.
 *
 * This typedef is used so that a descriptive type is provided in our
 * automatically generated documentation.
 *
 * An in-line type `() => void` would be preferable, but the generated
 * documentation is `null` in that case.
 *
 * @see https://github.com/WordPress/gutenberg/issues/18045
 */

/**
 * Specify a function to execute when the DOM is fully loaded.
 *
 * @param {Callback} callback A function to execute after the DOM is ready.
 *
 * @example
 * ```js
 * import domReady from '@wordpress/dom-ready';
 *
 * domReady( function() {
 * 	//do something after DOM loads.
 * } );
 * ```
 *
 * @return {void}
 */
function domReady(callback) {
  if (typeof document === 'undefined') {
    return;
  }
  if (document.readyState === 'complete' ||
  // DOMContentLoaded + Images/Styles/etc loaded, so we call directly.
  document.readyState === 'interactive' // DOMContentLoaded fires at this point, so we call directly.
  ) {
    return void callback();
  }

  // DOMContentLoaded has not fired yet, delay callback until then.
  document.addEventListener('DOMContentLoaded', callback);
}
//# sourceMappingURL=index.js.map

/***/ }),

/***/ "./resources/js/onboarding-wizard/elements/createPageElements.js":
/*!***********************************************************************!*\
  !*** ./resources/js/onboarding-wizard/elements/createPageElements.js ***!
  \***********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createPageElements: () => (/* binding */ createPageElements)
/* harmony export */ });
/**
 * Creates a page elements manager.
 *
 * @param {Object} [initialElements={}] An object containing initial DOM elements.
 * @throws {Error} Throws an error if the `initialElements` is not an object.
 * @return {Object} An object with methods to get and add elements.
 */
function createPageElements(initialElements = {}) {
  if (typeof initialElements !== 'object' || initialElements === null) {
    throw new Error('createPageElements: initialElements must be a non-null object');
  }
  let elements = initialElements;

  /**
   * Retrieve the initialized essential DOM elements.
   *
   * @return {Object} The initialized elements object.
   */
  function getElements() {
    return elements;
  }

  /**
   * Add new elements to the elements object.
   *
   * @param {Object} newElements An object containing new elements to be added.
   * @throws {Error} Throws an error if the `newElements` is not a non-null object.
   * @return {void} Updates the elements object by merging the new elements into it.
   */
  function addElements(newElements) {
    if (typeof newElements !== 'object' || newElements === null) {
      throw new Error('addElements: newElements must be a non-null object');
    }
    elements = {
      ...elements,
      ...newElements
    };
  }
  return {
    getElements,
    addElements
  };
}

/***/ }),

/***/ "./resources/js/onboarding-wizard/elements/elements.js":
/*!*************************************************************!*\
  !*** ./resources/js/onboarding-wizard/elements/elements.js ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addElements: () => (/* binding */ addElements),
/* harmony export */   getElements: () => (/* binding */ getElements)
/* harmony export */ });
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../shared */ "./resources/js/onboarding-wizard/shared/index.js");
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! . */ "./resources/js/onboarding-wizard/elements/index.js");
/**
 * Internal dependencies
 */


const {
  getElements,
  addElements
} = (0,___WEBPACK_IMPORTED_MODULE_1__.createPageElements)({
  onboardingWizardPage: document.getElementById(`${_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX}-wizard-page`),
  container: document.getElementById(`${_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX}-container`),
  rootline: document.getElementById(`${_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX}-rootline`),
  steps: document.querySelectorAll(`.${_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX}-step`),
  skipStepButtons: document.querySelectorAll(`.${_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX}-skip-step`),
  consentTrackingButton: document.getElementById(`${_shared__WEBPACK_IMPORTED_MODULE_0__.PREFIX}-consent-tracking`),
  collapsible: document.querySelector('.awpcp-collapsible')
});

/***/ }),

/***/ "./resources/js/onboarding-wizard/elements/index.js":
/*!**********************************************************!*\
  !*** ./resources/js/onboarding-wizard/elements/index.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addElements: () => (/* reexport safe */ _elements__WEBPACK_IMPORTED_MODULE_1__.addElements),
/* harmony export */   createPageElements: () => (/* reexport safe */ _createPageElements__WEBPACK_IMPORTED_MODULE_0__.createPageElements),
/* harmony export */   getElements: () => (/* reexport safe */ _elements__WEBPACK_IMPORTED_MODULE_1__.getElements)
/* harmony export */ });
/* harmony import */ var _createPageElements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./createPageElements */ "./resources/js/onboarding-wizard/elements/createPageElements.js");
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./elements */ "./resources/js/onboarding-wizard/elements/elements.js");



/***/ }),

/***/ "./resources/js/onboarding-wizard/events/collapsibleListener.js":
/*!**********************************************************************!*\
  !*** ./resources/js/onboarding-wizard/events/collapsibleListener.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./resources/js/onboarding-wizard/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../shared */ "./resources/js/onboarding-wizard/shared/index.js");
/**
 * Internal dependencies
 */



/**
 * Manages event handling for the "Skip" step button.
 *
 * @return {void}
 */
function addCollapsibleEvents() {
  const {
    collapsible
  } = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)();
  collapsible.addEventListener('click', onCollapsibleClick);
}

/**
 * Handles the click event on the collapsible section.
 *
 * @private
 * @param {Event} event The event object
 * @return {void}
 */
const onCollapsibleClick = event => {
  event.preventDefault();
  const collapsible = event.currentTarget;
  collapsible.classList.toggle(_shared__WEBPACK_IMPORTED_MODULE_1__.OPEN_CLASS);
  const content = collapsible.nextElementSibling;
  content.classList.toggle(_shared__WEBPACK_IMPORTED_MODULE_1__.HIDDEN_CLASS);
  content.classList.toggle('awpcp-fadein-down');
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addCollapsibleEvents);

/***/ }),

/***/ "./resources/js/onboarding-wizard/events/consentTrackingButtonListener.js":
/*!********************************************************************************!*\
  !*** ./resources/js/onboarding-wizard/events/consentTrackingButtonListener.js ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./resources/js/onboarding-wizard/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../shared */ "./resources/js/onboarding-wizard/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../utils */ "./resources/js/onboarding-wizard/utils/index.js");
/**
 * Internal dependencies
 */




/**
 * Manages event handling for the "Allow & Continue" button in the "Never miss an important update" step.
 *
 * @return {void}
 */
function addConsentTrackingButtonEvents() {
  const {
    consentTrackingButton
  } = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)();
  consentTrackingButton.addEventListener('click', onConsentTrackingButtonClick);
}

/**
 * Handles the click event on the "Allow & Continue" button in the "Never miss an important update" step.
 *
 * @private
 * @param {Event} event The event object
 * @return {void}
 */
const onConsentTrackingButtonClick = async event => {
  event.preventDefault();
  const formData = new FormData();
  formData.append('action', 'awpcp_onboarding_consent_tracking');
  formData.append('nonce', _shared__WEBPACK_IMPORTED_MODULE_1__.NONCE);
  let data;
  try {
    // eslint-disable-next-line no-undef
    const response = await fetch(ajaxurl, {
      method: 'POST',
      body: formData
    });
    data = await response.json();
  } catch (error) {
    // eslint-disable-next-line no-console
    console.error('Error:', error);
  }
  if (!data.success) {
    // eslint-disable-next-line no-console
    console.error(data || 'Request failed');
    return;
  }
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.navigateToNextStep)(data);
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addConsentTrackingButtonEvents);

/***/ }),

/***/ "./resources/js/onboarding-wizard/events/index.js":
/*!********************************************************!*\
  !*** ./resources/js/onboarding-wizard/events/index.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addEventListeners: () => (/* binding */ addEventListeners)
/* harmony export */ });
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../utils */ "./resources/js/onboarding-wizard/utils/index.js");
/* harmony import */ var _collapsibleListener__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./collapsibleListener */ "./resources/js/onboarding-wizard/events/collapsibleListener.js");
/* harmony import */ var _consentTrackingButtonListener__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./consentTrackingButtonListener */ "./resources/js/onboarding-wizard/events/consentTrackingButtonListener.js");
/* harmony import */ var _skipStepButtonListener__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./skipStepButtonListener */ "./resources/js/onboarding-wizard/events/skipStepButtonListener.js");
/**
 * Internal dependencies
 */





/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
function addEventListeners() {
  (0,_skipStepButtonListener__WEBPACK_IMPORTED_MODULE_3__["default"])();
  (0,_collapsibleListener__WEBPACK_IMPORTED_MODULE_1__["default"])();
  (0,_consentTrackingButtonListener__WEBPACK_IMPORTED_MODULE_2__["default"])();
}

/**
 * Responds to browser navigation events (back/forward) by updating the UI to match the step indicated in the URL or history state.
 *
 * @param {PopStateEvent} event The event object associated with the navigation action.
 * @return {void}
 */
window.addEventListener('popstate', event => {
  const stepName = event.state?.step || (0,_utils__WEBPACK_IMPORTED_MODULE_0__.getQueryParam)('step');
  // Navigate to the specified step without adding to browser history
  (0,_utils__WEBPACK_IMPORTED_MODULE_0__.navigateToStep)(stepName, 'replaceState');
});

/***/ }),

/***/ "./resources/js/onboarding-wizard/events/skipStepButtonListener.js":
/*!*************************************************************************!*\
  !*** ./resources/js/onboarding-wizard/events/skipStepButtonListener.js ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./resources/js/onboarding-wizard/elements/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../utils */ "./resources/js/onboarding-wizard/utils/index.js");
/**
 * Internal dependencies
 */



/**
 * Manages event handling for the "Skip" step button.
 *
 * @return {void}
 */
function addSkipStepButtonEvents() {
  const {
    skipStepButtons
  } = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)();
  skipStepButtons.forEach(skipButton => {
    skipButton.addEventListener('click', _utils__WEBPACK_IMPORTED_MODULE_1__.navigateToNextStep);
  });
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (addSkipStepButtonEvents);

/***/ }),

/***/ "./resources/js/onboarding-wizard/initializeOnboardingWizard.js":
/*!**********************************************************************!*\
  !*** ./resources/js/onboarding-wizard/initializeOnboardingWizard.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ui */ "./resources/js/onboarding-wizard/ui/index.js");
/* harmony import */ var _events__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./events */ "./resources/js/onboarding-wizard/events/index.js");
/**
 * Internal dependencies
 */



/**
 * Initializes Onboarding Wizard.
 *
 * @return {void}
 */
function initializeOnboardingWizard() {
  (0,_ui__WEBPACK_IMPORTED_MODULE_0__.setupInitialView)();
  (0,_events__WEBPACK_IMPORTED_MODULE_1__.addEventListeners)();
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (initializeOnboardingWizard);

/***/ }),

/***/ "./resources/js/onboarding-wizard/shared/constants.js":
/*!************************************************************!*\
  !*** ./resources/js/onboarding-wizard/shared/constants.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CURRENT_CLASS: () => (/* binding */ CURRENT_CLASS),
/* harmony export */   HIDDEN_CLASS: () => (/* binding */ HIDDEN_CLASS),
/* harmony export */   HIDE_JS_CLASS: () => (/* binding */ HIDE_JS_CLASS),
/* harmony export */   INITIAL_STEP: () => (/* binding */ INITIAL_STEP),
/* harmony export */   NONCE: () => (/* binding */ NONCE),
/* harmony export */   OPEN_CLASS: () => (/* binding */ OPEN_CLASS),
/* harmony export */   PREFIX: () => (/* binding */ PREFIX),
/* harmony export */   STEPS: () => (/* binding */ STEPS)
/* harmony export */ });
const {
  INITIAL_STEP,
  NONCE
} = window.awpcpOnboardingWizardVars;
const PREFIX = 'awpcp-onboarding';
const HIDDEN_CLASS = 'hidden';
const HIDE_JS_CLASS = 'awpcp-hide-js';
const CURRENT_CLASS = 'awpcp-current';
const OPEN_CLASS = 'awpcp-open';
const STEPS = {
  INITIAL: INITIAL_STEP,
  SUCCESS: 'success'
};

/***/ }),

/***/ "./resources/js/onboarding-wizard/shared/index.js":
/*!********************************************************!*\
  !*** ./resources/js/onboarding-wizard/shared/index.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CURRENT_CLASS: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.CURRENT_CLASS),
/* harmony export */   HIDDEN_CLASS: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.HIDDEN_CLASS),
/* harmony export */   HIDE_JS_CLASS: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.HIDE_JS_CLASS),
/* harmony export */   INITIAL_STEP: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.INITIAL_STEP),
/* harmony export */   NONCE: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.NONCE),
/* harmony export */   OPEN_CLASS: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.OPEN_CLASS),
/* harmony export */   PREFIX: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.PREFIX),
/* harmony export */   STEPS: () => (/* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_0__.STEPS)
/* harmony export */ });
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./constants */ "./resources/js/onboarding-wizard/shared/constants.js");


/***/ }),

/***/ "./resources/js/onboarding-wizard/ui/index.js":
/*!****************************************************!*\
  !*** ./resources/js/onboarding-wizard/ui/index.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   setupInitialView: () => (/* reexport safe */ _setupInitialView__WEBPACK_IMPORTED_MODULE_0__["default"]),
/* harmony export */   updateRootline: () => (/* reexport safe */ _rootline__WEBPACK_IMPORTED_MODULE_1__.updateRootline)
/* harmony export */ });
/* harmony import */ var _setupInitialView__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setupInitialView */ "./resources/js/onboarding-wizard/ui/setupInitialView.js");
/* harmony import */ var _rootline__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./rootline */ "./resources/js/onboarding-wizard/ui/rootline.js");



/***/ }),

/***/ "./resources/js/onboarding-wizard/ui/rootline.js":
/*!*******************************************************!*\
  !*** ./resources/js/onboarding-wizard/ui/rootline.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   updateRootline: () => (/* binding */ updateRootline)
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./resources/js/onboarding-wizard/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../shared */ "./resources/js/onboarding-wizard/shared/index.js");
/**
 * Internal dependencies
 */


const COMPLETED_STEP_CLASS = 'awpcp-completed-step';

/**
 * Updates the rootline to reflect the current and completed steps.
 *
 * - Applies COMPLETED_STEP_CLASS to steps before the current one.
 * - Applies CURRENT_CLASS to the current step, unless it is the success step.
 *
 * @param {string} currentStep The current step in the process.
 * @return {void}
 */
function updateRootline(currentStep) {
  const {
    rootline
  } = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)();
  const currentItem = rootline.querySelector(`.awpcp-onboarding-rootline-item[data-step="${currentStep}"]`);
  rootline.querySelectorAll('.awpcp-onboarding-rootline-item').forEach(item => {
    item.classList.remove(COMPLETED_STEP_CLASS);
    item.classList.remove(_shared__WEBPACK_IMPORTED_MODULE_1__.CURRENT_CLASS);
  });
  let prevItem = currentItem.previousElementSibling;
  if (prevItem) {
    while (prevItem) {
      prevItem.classList.add(COMPLETED_STEP_CLASS);
      prevItem = prevItem.previousElementSibling; // move to the previous sibling
    }
  }
  if (currentStep === _shared__WEBPACK_IMPORTED_MODULE_1__.STEPS.SUCCESS) {
    currentItem.classList.add(COMPLETED_STEP_CLASS);
  } else {
    currentItem.classList.add(_shared__WEBPACK_IMPORTED_MODULE_1__.CURRENT_CLASS);
  }
}

/***/ }),

/***/ "./resources/js/onboarding-wizard/ui/setupInitialView.js":
/*!***************************************************************!*\
  !*** ./resources/js/onboarding-wizard/ui/setupInitialView.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ setupInitialView)
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./resources/js/onboarding-wizard/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../shared */ "./resources/js/onboarding-wizard/shared/index.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../utils */ "./resources/js/onboarding-wizard/utils/index.js");
/**
 * Internal dependencies
 */




/**
 * Initializes the onboarding wizard's UI, sets up the initial step based on certain conditions,
 * and applies necessary UI enhancements for a smoother user experience.
 *
 * @return {void}
 */
function setupInitialView() {
  navigateToInitialStep();
  fadeInPageElements();
}

/**
 * Determines the initial step in the onboarding process and navigates to it, considering the installation
 * status of Formidable Pro and specific query parameters.
 *
 * @private
 * @return {void}
 */
function navigateToInitialStep() {
  const initialStepName = determineInitialStep();
  clearOnboardingQueryParams();
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.navigateToStep)(initialStepName, 'replaceState');
}

/**
 * Determines the initial step based on the current state, such as whether Formidable Pro is installed
 * and the presence of specific query parameters. Also handles the removal of unnecessary steps.
 *
 * @private
 * @return {string} The name of the initial step to navigate to.
 */
function determineInitialStep() {
  return (0,_utils__WEBPACK_IMPORTED_MODULE_2__.getQueryParam)('step') || _shared__WEBPACK_IMPORTED_MODULE_1__.STEPS.INITIAL;
}

/**
 * Clears specific query parameters related to the onboarding process.
 *
 * @private
 * @return {void}
 */
function clearOnboardingQueryParams() {
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.removeQueryParam)('key');
  (0,_utils__WEBPACK_IMPORTED_MODULE_2__.removeQueryParam)('success');
}

/**
 * Smoothly fades in the background and container elements of the page for a more pleasant user experience.
 *
 * @private
 * @return {void}
 */
function fadeInPageElements() {
  const {
    onboardingWizardPage,
    container
  } = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)();
  onboardingWizardPage.classList.remove(_shared__WEBPACK_IMPORTED_MODULE_1__.HIDE_JS_CLASS);
  container.classList.toggle('awpcp-fadein-up');
}

/***/ }),

/***/ "./resources/js/onboarding-wizard/utils/index.js":
/*!*******************************************************!*\
  !*** ./resources/js/onboarding-wizard/utils/index.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getQueryParam: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_0__.getQueryParam),
/* harmony export */   hasQueryParam: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_0__.hasQueryParam),
/* harmony export */   hide: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_1__.hide),
/* harmony export */   hideElements: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_1__.hideElements),
/* harmony export */   isVisible: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_1__.isVisible),
/* harmony export */   navigateToNextStep: () => (/* reexport safe */ _navigateToStep__WEBPACK_IMPORTED_MODULE_2__.navigateToNextStep),
/* harmony export */   navigateToPrevStep: () => (/* reexport safe */ _navigateToStep__WEBPACK_IMPORTED_MODULE_2__.navigateToPrevStep),
/* harmony export */   navigateToStep: () => (/* reexport safe */ _navigateToStep__WEBPACK_IMPORTED_MODULE_2__.navigateToStep),
/* harmony export */   removeQueryParam: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_0__.removeQueryParam),
/* harmony export */   setQueryParam: () => (/* reexport safe */ _url__WEBPACK_IMPORTED_MODULE_0__.setQueryParam),
/* harmony export */   show: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_1__.show),
/* harmony export */   showElements: () => (/* reexport safe */ _visibility__WEBPACK_IMPORTED_MODULE_1__.showElements)
/* harmony export */ });
/* harmony import */ var _url__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./url */ "./resources/js/onboarding-wizard/utils/url.js");
/* harmony import */ var _visibility__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./visibility */ "./resources/js/onboarding-wizard/utils/visibility.js");
/* harmony import */ var _navigateToStep__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./navigateToStep */ "./resources/js/onboarding-wizard/utils/navigateToStep.js");




/***/ }),

/***/ "./resources/js/onboarding-wizard/utils/navigateToStep.js":
/*!****************************************************************!*\
  !*** ./resources/js/onboarding-wizard/utils/navigateToStep.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   navigateToNextStep: () => (/* binding */ navigateToNextStep),
/* harmony export */   navigateToPrevStep: () => (/* binding */ navigateToPrevStep),
/* harmony export */   navigateToStep: () => (/* binding */ navigateToStep)
/* harmony export */ });
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../elements */ "./resources/js/onboarding-wizard/elements/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../shared */ "./resources/js/onboarding-wizard/shared/index.js");
/* harmony import */ var _ui__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../ui */ "./resources/js/onboarding-wizard/ui/index.js");
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! . */ "./resources/js/onboarding-wizard/utils/index.js");
/**
 * Internal dependencies
 */





/**
 * Navigates to the given step in the onboarding sequence.
 * Optionally updates the browser's history state to include the current step.
 *
 * @param {string} stepName                   The name of the step to navigate to.
 * @param {string} [updateMethod='pushState'] Specifies the method to update the browser's history and URL. Accepts 'pushState' or 'replaceState'. If omitted, defaults to 'pushState'.
 * @return {void}
 */
const navigateToStep = (stepName, updateMethod = 'pushState') => {
  // Find the target step element
  const targetStep = document.querySelector(`.${_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX}-step[data-step-name="${stepName}"]`);
  if (!targetStep) {
    return;
  }

  // Find and hide the current step element
  const currentStep = document.querySelector(`.${_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX}-step.${_shared__WEBPACK_IMPORTED_MODULE_1__.CURRENT_CLASS}`);
  if (currentStep) {
    currentStep.classList.remove(_shared__WEBPACK_IMPORTED_MODULE_1__.CURRENT_CLASS);
    (0,___WEBPACK_IMPORTED_MODULE_3__.hide)(currentStep);
    currentStep.classList.remove('awpcp-fadein-up');
  }

  // Display the target step element
  targetStep.classList.add(_shared__WEBPACK_IMPORTED_MODULE_1__.CURRENT_CLASS);
  (0,___WEBPACK_IMPORTED_MODULE_3__.show)(targetStep);
  targetStep.classList.add('awpcp-fadein-up');

  // Update the onboarding wizard's current step attribute
  const {
    onboardingWizardPage
  } = (0,_elements__WEBPACK_IMPORTED_MODULE_0__.getElements)();
  onboardingWizardPage.setAttribute('data-current-step', stepName);

  // Update the URL query parameter, with control over history update method
  (0,___WEBPACK_IMPORTED_MODULE_3__.setQueryParam)('step', stepName, updateMethod);
  (0,_ui__WEBPACK_IMPORTED_MODULE_2__.updateRootline)(stepName);
};

/**
 * Navigates to the next step in the sequence.
 *
 * The function assumes steps are sequentially ordered in the DOM.
 *
 * @return {void}
 */
const navigateToNextStep = () => {
  const currentStep = document.querySelector(`.${_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX}-step.${_shared__WEBPACK_IMPORTED_MODULE_1__.CURRENT_CLASS}`);
  const nextStep = currentStep?.nextElementSibling;
  if (!nextStep) {
    return;
  }
  navigateToStep(nextStep.dataset.stepName);
};

/**
 * Navigates to the previous step in the sequence.
 *
 * The function assumes steps are sequentially ordered in the DOM.
 *
 * @return {void}
 */
const navigateToPrevStep = () => {
  const currentStep = document.querySelector(`.${_shared__WEBPACK_IMPORTED_MODULE_1__.PREFIX}-step.${_shared__WEBPACK_IMPORTED_MODULE_1__.CURRENT_CLASS}`);
  const prevStep = currentStep?.previousElementSibling;
  if (!prevStep) {
    return;
  }
  navigateToStep(prevStep.dataset.stepName);
};

/***/ }),

/***/ "./resources/js/onboarding-wizard/utils/url.js":
/*!*****************************************************!*\
  !*** ./resources/js/onboarding-wizard/utils/url.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getQueryParam: () => (/* binding */ getQueryParam),
/* harmony export */   hasQueryParam: () => (/* binding */ hasQueryParam),
/* harmony export */   removeQueryParam: () => (/* binding */ removeQueryParam),
/* harmony export */   setQueryParam: () => (/* binding */ setQueryParam)
/* harmony export */ });
/**
 * Initializes URL and URLSearchParams objects from the current window's location
 */
const url = new URL(window.location.href);
const urlParams = url.searchParams;

/**
 * Gets the value of a specified query parameter from the current URL.
 *
 * @param {string} paramName The name of the query parameter to retrieve.
 * @return {string|null} The value associated with the specified query parameter name, or null if not found.
 */
const getQueryParam = paramName => urlParams.get(paramName);

/**
 * Removes a query parameter from the current URL and returns the updated URL string.
 *
 * @param {string} paramName The name of the query parameter to remove.
 * @return {string} The updated URL string.
 */
const removeQueryParam = paramName => {
  urlParams.delete(paramName);
  url.search = urlParams.toString();
  return url.toString();
};

/**
 * Sets the value of a query parameter in the current URL and optionally updates the browser's history state.
 *
 * @param {string} paramName                  The name of the query parameter to set.
 * @param {string} paramValue                 The value to set for the query parameter.
 * @param {string} [updateMethod='pushState'] The method to use for updating the history state. Accepts 'pushState' or 'replaceState'.
 * @return {string} The updated URL string.
 */
const setQueryParam = (paramName, paramValue, updateMethod = 'pushState') => {
  urlParams.set(paramName, paramValue);
  url.search = urlParams.toString();
  if (['pushState', 'replaceState'].includes(updateMethod)) {
    const state = {
      [paramName]: paramValue
    };
    window.history[updateMethod](state, '', url);
  }
  return url.toString();
};

/**
 * Checks if a query parameter exists in the current URL.
 *
 * @param {string} paramName The name of the query parameter to check.
 * @return {boolean} True if the query parameter exists, otherwise false.
 */
const hasQueryParam = paramName => urlParams.has(paramName);

/***/ }),

/***/ "./resources/js/onboarding-wizard/utils/visibility.js":
/*!************************************************************!*\
  !*** ./resources/js/onboarding-wizard/utils/visibility.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   hide: () => (/* binding */ hide),
/* harmony export */   hideElements: () => (/* binding */ hideElements),
/* harmony export */   isVisible: () => (/* binding */ isVisible),
/* harmony export */   show: () => (/* binding */ show),
/* harmony export */   showElements: () => (/* binding */ showElements)
/* harmony export */ });
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../shared */ "./resources/js/onboarding-wizard/shared/index.js");
/**
 * Internal dependencies
 */


/**
 * Shows specified elements by removing the hidden class.
 *
 * @param {Array<Element>} elements An array of elements to show.
 * @return {void}
 */
const showElements = elements => Array.from(elements)?.forEach(element => show(element));

/**
 * Hides specified elements by adding the hidden class.
 *
 * @param {Array<Element>} elements An array of elements to hide.
 * @return {void}
 */
const hideElements = elements => Array.from(elements)?.forEach(element => hide(element));

/**
 * Removes the hidden class to show the element.
 *
 * @param {Element} element The element to show.
 * @return {void}
 */
const show = element => element?.classList.remove(_shared__WEBPACK_IMPORTED_MODULE_0__.HIDDEN_CLASS);

/**
 * Adds the hidden class to hide the element.
 *
 * @param {Element} element The element to hide.
 * @return {void}
 */
const hide = element => element?.classList.add(_shared__WEBPACK_IMPORTED_MODULE_0__.HIDDEN_CLASS);

/**
 * Checks if an element is visible.
 *
 * @param {HTMLElement} element The HTML element to check for visibility.
 * @return {boolean} Returns true if the element is visible, otherwise false.
 */
const isVisible = element => {
  const styles = window.getComputedStyle(element);
  return styles.getPropertyValue('display') !== 'none';
};

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
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!*************************************************!*\
  !*** ./resources/js/onboarding-wizard/index.js ***!
  \*************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/dom-ready */ "./node_modules/@wordpress/dom-ready/build-module/index.js");
/* harmony import */ var _initializeOnboardingWizard__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./initializeOnboardingWizard */ "./resources/js/onboarding-wizard/initializeOnboardingWizard.js");
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */

(0,_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_1__["default"])(() => {
  (0,_initializeOnboardingWizard__WEBPACK_IMPORTED_MODULE_0__["default"])();
});
})();

/******/ })()
;
//# sourceMappingURL=onboarding-wizard.js.map