!function(){"use strict";var e=window.wp.element,t=window.wp.i18n;function n(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,a=new Array(t);n<t;n++)a[n]=e[n];return a}function a(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){var n=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=n){var a,o,c=[],l=!0,r=!1;try{for(n=n.call(e);!(l=(a=n.next()).done)&&(c.push(a.value),!t||c.length!==t);l=!0);}catch(e){r=!0,o=e}finally{try{l||null==n.return||n.return()}finally{if(r)throw o}}return c}}(e,t)||function(e,t){if(e){if("string"==typeof e)return n(e,t);var a=Object.prototype.toString.call(e).slice(8,-1);return"Object"===a&&e.constructor&&(a=e.constructor.name),"Map"===a||"Set"===a?Array.from(e):"Arguments"===a||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(a)?n(e,t):void 0}}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}var o=function(){var n=a((0,e.useState)(!1),2),o=n[0],c=n[1];return(0,e.createElement)("div",{className:"ct-theme-required"},(0,e.createElement)("h2",null,(0,e.createElement)("span",null,(0,e.createElement)("svg",{viewBox:"0 0 24 24"},(0,e.createElement)("path",{d:"M12,23.6c-1.4,0-2.6-1-2.8-2.3L8.9,20h6.2l-0.3,1.3C14.6,22.6,13.4,23.6,12,23.6z M24,17.8H0l3.1-2c0.5-0.3,0.9-0.7,1.1-1.3c0.5-1,0.5-2.2,0.5-3.2V7.6c0-4.1,3.2-7.3,7.3-7.3s7.3,3.2,7.3,7.3v3.6c0,1.1,0.1,2.3,0.5,3.2c0.3,0.5,0.6,1,1.1,1.3L24,17.8zM6.1,15.6h11.8c0,0-0.1-0.1-0.1-0.2c-0.7-1.3-0.7-2.9-0.7-4.2V7.6c0-2.8-2.2-5.1-5.1-5.1c-2.8,0-5.1,2.2-5.1,5.1v3.6c0,1.3-0.1,2.9-0.7,4.2C6.1,15.5,6.1,15.6,6.1,15.6z"}))),(0,t.__)("Action Required - Install Blocksy Theme","blocksy-companion")),(0,e.createElement)("p",null,(0,t.__)("Blocksy Companion is the complementary plugin to Blocksy theme. It adds a bunch of great features to the theme and acts as an unlocker for the Blocksy Pro package.","blocksy-companion")),(0,e.createElement)("p",null,(0,t.__)("In order to take full advantage of all features it has to offer - please install and activate the Blocksy theme also.","blocksy-companion")),(0,e.createElement)("button",{className:"button button-primary",onClick:function(e){e.preventDefault(),c(!0),ctDashboardLocalizations.themeIsInstalled?location=ctDashboardLocalizations.activate:wp.updates.ajax("install-theme",{success:function(){setTimeout((function(){location=ctDashboardLocalizations.activate}))},error:function(){setTimeout((function(){location=ctDashboardLocalizations.activate}))},slug:"blocksy"})}},o?(0,t.__)("Loading...","blocksy-companion"):(0,t.__)("Install and activate the Blocksy theme","blocksy-companion")))},c=function(){var n=a((0,e.useState)(!1),2);n[0],n[1];return(0,e.createElement)("div",{className:"ct-theme-required"},(0,e.createElement)("h2",null,(0,e.createElement)("span",null,(0,e.createElement)("svg",{viewBox:"0 0 24 24"},(0,e.createElement)("path",{d:"M12,23.6c-1.4,0-2.6-1-2.8-2.3L8.9,20h6.2l-0.3,1.3C14.6,22.6,13.4,23.6,12,23.6z M24,17.8H0l3.1-2c0.5-0.3,0.9-0.7,1.1-1.3c0.5-1,0.5-2.2,0.5-3.2V7.6c0-4.1,3.2-7.3,7.3-7.3s7.3,3.2,7.3,7.3v3.6c0,1.1,0.1,2.3,0.5,3.2c0.3,0.5,0.6,1,1.1,1.3L24,17.8zM6.1,15.6h11.8c0,0-0.1-0.1-0.1-0.2c-0.7-1.3-0.7-2.9-0.7-4.2V7.6c0-2.8-2.2-5.1-5.1-5.1c-2.8,0-5.1,2.2-5.1,5.1v3.6c0,1.3-0.1,2.9-0.7,4.2C6.1,15.5,6.1,15.6,6.1,15.6z"}))),(0,t.__)("Action Required - Blocksy Theme and Companion version mismatch","blocksy-companion")),(0,e.createElement)("p",null,(0,t.__)("We detected that you are using an outdated version of Blocksy Theme. Please update it to the latest version.","blocksy-companion")),(0,e.createElement)("p",null,(0,t.__)("In order to take full advantage of all features it has to offer - please install and activate the latest versions of both Blocksy theme and Blocksy Companion plugin.","blocksy-companion")),(0,e.createElement)("button",{className:"button button-primary",onClick:function(e){e.preventDefault(),location=ctDashboardLocalizations.run_updates}},(0,t.__)("Update Now","blocksy-companion")))},l=function(){return ctDashboardLocalizations.theme_version_mismatch?(0,e.createElement)(c,null):(0,e.createElement)(o,null)};document.addEventListener("DOMContentLoaded",(function(){document.getElementById("ct-dashboard")&&(0,e.render)((0,e.createElement)(l,null),document.getElementById("ct-dashboard"))}))}();