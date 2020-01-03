/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');

import Vue from 'vue';

new Vue({
    el: '#app',
    delimiters: ['${', '}'],
    data: {
        projects: $('.list-project').data('project'),
        search: ""
    },
    computed: {
        filteredProjects: function () {
            return this.projects.filter((projects) => {
                return projects.toLowerCase().match(this.search.toLowerCase());
            });
        }
    }
});