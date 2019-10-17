/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import BootstrapVue from 'bootstrap-vue';

window.Vue.use(BootstrapVue);

window.moment = require('moment');
window.moment.locale('fr');

import VueTimers from 'vue-timers'

Vue.use(VueTimers);

import IdleVue from 'idle-vue';
const eventsHub = new Vue();
Vue.use(IdleVue, {
    eventEmitter: eventsHub,
    idleTime: 300000
});

import {library} from '@fortawesome/fontawesome-svg-core'
import {faCircle, faDotCircle, faExclamationTriangle} from '@fortawesome/free-solid-svg-icons'
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'

library.add(faCircle);
library.add(faDotCircle);
library.add(faExclamationTriangle);

Vue.component('font-awesome-icon', FontAwesomeIcon)


/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const files = require.context('./', true, /\.vue$/i);
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

//Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

