SWIFT.Library.Template = SWIFT.Base.extend({
	templateCache: {},

	AddToCache: function(cacheContainer) {
		_.each(cacheContainer, function(templateData, templateName) {
			SWIFT.Template.AddTemplate(templateName, templateData);
		});
	},

	AddTemplate: function(templateName, templateData) {
		this.templateCache[templateName] = templateData;
	},

	Get: function(templateName) {
		if (typeof this.templateCache[templateName] === 'undefined') {
			return '';
		}

		return this.templateCache[templateName];
	},

	get: function(templateName) {
		return this.Get(templateName);
	}
});