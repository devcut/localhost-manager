require('../scss/app.scss');

import Vue from 'vue';

let projects = $('.list-project').data('projects');

new Vue({
    el: '#app',
    delimiters: ['${', '}'],
    props: {
        name: String,
        framework: String
    },
    data: {
        projects: projects,
        search: ""
    },
    computed: {
        filteredProjects: function () {
            return this.projects.filter((projects) => {
                return projects.name.toLowerCase().match(this.search.toLowerCase());
            });
        }
    }
});