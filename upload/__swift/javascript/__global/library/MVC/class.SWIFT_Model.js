SWIFT.Library.Model = Backbone.Model.extend({
	methodUrl: function(method) {
		if (typeof this.path !== 'undefined') {
			if (typeof this.attributes.id !== 'undefined') {
		   		return SWIFT.get('basename') + this.path + this.attributes.id;
			} else {
		   		return SWIFT.get('basename') + this.path;
			}
		}

    	return false;
	},

	sync: function(method, model, options) {
		if (model.methodUrl && model.methodUrl(method.toLowerCase())) {
			options = options || {};
			options.url = model.methodUrl(method.toLowerCase());
		}

		Backbone.sync(method, model, options);
	}
});