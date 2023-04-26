window.SWIFT = SWIFT_Base.create({
	Library: {},
	Controllers: {},
	Views: {},
	Models: {},
	Layouts: {},
	Regions: {},
	Collections: {},

	Base: {},

	// Variables
	isSetup: false,

	constructor: function() {
	},

	ready: function() {
		this._super();

		if (this.isSetup == false) {
			// alert('Error: SWIFT.Setup not executed!');
		}

		// Run the Router Setup
		this.Router.Setup();

		// Trigger the ready event
		this.trigger('ready');
	},

	LoadCoreLibraries: function() {
		if (typeof this.Template === 'undefined') {
			// Load the template engine
			this.Template = this.Library.Template.create();
		}
	},

	Setup: function(routerPath, coreProperties) {
		// Load the document manager
		this.Document = this.Library.Document.create();

		// Load the language manager
		this.Language = this.Library.Language.create();

		// Load the router
		this.Router = this.Library.Router.create({
			'routerPath': routerPath
		});

		// Load the template engine
		if (typeof this.Template === 'undefined') {
			this.Template = this.Library.Template.create();
		}

		this.isSetup = true;

		if (typeof coreProperties === 'object') {
			this.set(coreProperties);
		}

		// Load the browser detection lib
		this.Browser = this.Library.Browser.create();

		// Trigger the setup event
		this.trigger('setup');
	},

	Controller: function(appName, controllerName, ControllerObject) {
		if (typeof this.Controllers[appName] === 'undefined') {
			this.Controllers[appName] = {};
		}

		if (typeof this.Controllers[appName][controllerName] === 'undefined') {
			this.Controllers[appName][controllerName] = new Array();
		}

		this.Controllers[appName][controllerName].push(ControllerObject);
	}
});

// Namespace SWIFT_Base
SWIFT.Base = new SWIFT_BaseClass;

// Remove all globals
SWIFT_Base = ''; delete SWIFT_Base;
SWIFT_BaseClass = ''; delete SWIFT_BaseClass;