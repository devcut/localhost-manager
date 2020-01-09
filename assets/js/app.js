require('../scss/app.scss');

import Vue from 'vue';

let listProjects = $('.list-project');
let projects = listProjects.data('projects');

if (listProjects.length > 0) {
    new Vue({
        el: '#app',
        delimiters: ['${', '}'],
        props: {
            name: String,
            framework: String,
            git: String,
            favicon: String
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
}