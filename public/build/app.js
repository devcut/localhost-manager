(window.webpackJsonp=window.webpackJsonp||[]).push([["app"],{ldto:function(t,n,r){},ng4s:function(t,n,r){"use strict";r.r(n),function(t){r("OG14"),r("f3/d"),r("SRfc"),r("0l/t");var n=r("oCYn");r("ldto");var e=t(".list-project"),i=e.data("projects");e.length>0&&new n.a({el:"#app",delimiters:["${","}"],props:{name:String,framework:String,git:String,favicon:String,modification:String},data:{projects:i,search:""},computed:{filteredProjects:function(){var t=this;return this.projects.filter((function(n){return n.name.toLowerCase().match(t.search.toLowerCase())}))}}})}.call(this,r("EVdn"))}},[["ng4s","runtime",0,1]]]);