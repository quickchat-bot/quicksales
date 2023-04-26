SWIFT.Library.Router = SWIFT.Base.extend({
	'routerPath': '',
	'logPrefix': '(ROUTER)',
	'routerArguments': [],

	Navigate: function() {

	},

	PreNavigation: function(routerPath) {

	},

	PostNavigation: function(routerURL) {
		var swiftBaseName = SWIFT.get('basename');
		var baseNameStrip = routerURL.substr(0, swiftBaseName.length);

		var parsedURL = false;
		if (routerURL.substr(0, 1) == '/')
		{
			parsedURL = routerURL;
		} else {
			// Is basename same?
			if (baseNameStrip.toLowerCase() == swiftBaseName.toLowerCase()) {
				parsedURL = routerURL.substr(swiftBaseName.length);
			}
		}

		if (parsedURL !== false) {
			this.ExecuteControllers(parsedURL);
		}

	},

	ExecuteControllers: function(routerPath) {
		var routerChunks = routerPath.split('/');

		if (routerChunks.length < 3) {
			return false;
		}

		this.routerPath = routerPath;

		var routerArguments = [];
		var chunkIndex = 0;
		_.each(routerChunks, function(chunkName) {
			if (chunkName != '') {
				if (chunkIndex == 0) {
					appName = chunkName.toLowerCase();
				} else if (chunkIndex == 1) {
					controllerName = chunkName.toLowerCase();
				} else if (chunkIndex == 2) {
					actionName = chunkName;
				} else {
					routerArguments[routerArguments.length] = chunkName;
				}

				chunkIndex++;
			}
		});

		this.routerArguments = routerArguments;

		if (typeof SWIFT.Controllers[appName] === 'undefined') {
			return false;
		}

		if (typeof SWIFT.Controllers[appName][controllerName] === 'undefined') {
			return false;
		}

		for (i = 0; i < SWIFT.Controllers[appName][controllerName].length; i++) {
			if (typeof SWIFT.Controllers[appName][controllerName][i][actionName] === 'function') {
				SWIFT.Controllers[appName][controllerName][i][actionName].apply(SWIFT.Controllers[appName][controllerName][i], routerArguments);
			}
		}
	},

	Setup: function() {
		this.PostNavigation(this.routerPath);
	}
});