# Polymorph

Polymorph is a PHP micro-framework for creating Polymer applications that are both browser- and bot-friendly.

It
* uses [Silex](http://silex.sensiolabs.org/) for server-side code (routing, configuration, security, base templates, ...)
* uses [Polymer](https://www.polymer-project.org/) for client-side code

## Principles

* Routes (and their Silex handlers) are defined in a central config
* UI intelligence, app logic, and static content is defined in custom HTML elements
* A basic SEO-friendly page template is defined in Silex
    * title
    * header
    * footer
    * canvas
    * content
        * static content: from a view template
        * dynamic content: from a handler query, converted to bot-friendly RDFa
* Silex can return the page template without surrounding layout markup
    * used by client code that loads a view dynamically (e.g. via iron-ajax or importHref)
    * ?partials=true
    * response contains only meta data, import-links and content partials
    * client code transitions to new view and updates nav, title, partials, etc. while keeping the page layout
* Client code does not know about routes (unless an element has element-level sub-routes)
    * changed routes trigger a server call and the view gets refreshed (planned: views and partials can be flagged as static)

## Development

For working on the framework code itself,
you need to add a self-symlink in the `bower_components` directory,
so that relative element imports work:
 
    cd /path/to/repo
    bower link
    bower link polymorph
