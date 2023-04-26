SWIFT_BaseClass = function() {
	var Events, Log, Properties,
	__slice = [].slice,
	__indexOf = [].indexOf || function(item) {
		for (var i = 0, l = this.length; i < l; i++) {
			if (i in this && this[i] === item) return i;
		}
		return -1;
	},
	__readyCallbacks = [],
	__hasProp = {}.hasOwnProperty,
	__extends = function(child, parent) {
		for (var key in parent) {
			if (__hasProp.call(parent, key)) child[key] = parent[key];
		}
		function ctor() {
			this.constructor = child;
		}
		ctor.prototype = parent.prototype;
		child.prototype = new ctor();
		child._super = parent.prototype;
		return child;
	},
	__extendPrototype = function(child, parent) {
		for (var key in parent.prototype) {
			child.prototype[key] = parent.prototype[key];
		}
		return child;
	},
	__bind = function(fn, me) {
		return function(){
			return fn.apply(me, arguments);
		};
	},
	__wrap = function(func, superFunc) {

		function K() {}

		var newFunc = function() {
			var ret, sup = this._super;
			this._super = superFunc || K;
			ret = func.apply(this, arguments);
			this._super = sup;
			return ret;
		};

		newFunc.base = func;
		return newFunc;
	};

	var _Base = function() {
		var initFunction = this.init || function() {};
		initFunction.apply(this, arguments);
	};

	_Base.prototype.init = function() { };

	_Base.fn = _Base.prototype;
	_Base.fn.parent = _Base;
	_Base._super = _Base.__proto__;

	// Adding class properties
	_Base._static = function(obj) {
		var __static = obj.__static;
		for(var i in obj) {
			_Base[i] = obj[i];
		}

		// support for the '__static' hook
		if (__static) __static(_Base)
	};
	_Base.fn._static = _Base._static;

	// Inherit and extend
	_Base.extend = function(obj) {
		var Class = __extends(function() {}, this);

		Class.prototype = new this;
		__extends(Class.prototype, Class.fn);
		Class.fn = Class.prototype;
		Class.prototype.parent = this;

		for(var i in obj) {
			if (typeof obj[i] === 'function') {
				Class.prototype[i] = __wrap(obj[i], Class.prototype[i]);
			} else {
				Class.prototype[i] = obj[i];
				Class[i] = obj[i];
			}
		}

		return Class;
	};
	_Base.fn.extend = _Base.extend;

	_Base.include = function(obj) {
		for(var i in obj) {
			if (typeof _Base.fn[i] === 'undefined') {
				_Base.fn[i] = obj[i];
			}
		}
	};
	_Base.fn.include = _Base.include;

	// Adding a proxy function
	_Base.proxy = function(func) {
		var self = this;
		return(function() {
			return func.apply(self, arguments);
		});
	};

	// Add the function on instances too
	_Base.fn.proxy = _Base.proxy;
	_Base.create = function(instances, statics) {
		var result;

		if (instances) {
			result = this.extend(instances);
		} else {
			result = this;
		}

		if (statics) {
			result._static(statics);
		}

		if (typeof result.unbind === 'function') {
			result.unbind();
		}

		var finalResult = new result;

		if (typeof finalResult.ready === 'function') {
			__readyCallbacks.push(finalResult.proxy(finalResult.ready));
		}

		if (typeof finalResult.constructor === 'function') {
			finalResult.constructor.apply(this, []);
		}

		return finalResult;
	};


	// Classes

	/*
	 * Events
	 */
	Events = {
		bind: function(ev, callback) {
			var calls, evs, name, _i, _len;
			evs = ev.split(' ');
			calls = this.hasOwnProperty('_callbacks') && this._callbacks || (this._callbacks = {});
			for (_i = 0, _len = evs.length; _i < _len; _i++) {
				name = evs[_i];
				calls[name] || (calls[name] = []);
				calls[name].push(callback);
			}
			return this;
		},
		one: function(ev, callback) {
			return this.bind(ev, function() {
				this.unbind(ev, arguments.callee);
				return callback.apply(this, arguments);
			});
		},
		trigger: function() {
			var args, callback, ev, list, _i, _len, _ref;
			args = 1 <= arguments.length ? __slice.call(arguments, 0) : [];
			ev = args.shift();
			list = this.hasOwnProperty('_callbacks') && ((_ref = this._callbacks) != null ? _ref[ev] : void 0);
			if (!list) {
				return;
			}
			for (_i = 0, _len = list.length; _i < _len; _i++) {
				callback = list[_i];
				if (callback.apply(this, args) === false) {
					break;
				}
			}
			return true;
		},
		unbind: function(ev, callback) {
			var cb, i, list, _i, _len, _ref;
			if (!ev) {
				this._callbacks = {};
				return this;
			}
			list = (_ref = this._callbacks) != null ? _ref[ev] : void 0;
			if (!list) {
				return this;
			}
			if (!callback) {
				delete this._callbacks[ev];
				return this;
			}
			for (i = _i = 0, _len = list.length; _i < _len; i = ++_i) {
				cb = list[i];
				if (!(cb === callback)) {
					continue;
				}
				list = list.slice();
				list.splice(i, 1);
				this._callbacks[ev] = list;
				break;
			}
			return this;
		}
	};

	_Base.include(Events);

	/*
	 * Log
	 */
	Log = {
		trace: true,
		logPrefix: '(SWIFT)',
		log: function() {
			var args;
			args = 1 <= arguments.length ? __slice.call(arguments, 0) : [];
			if (!this.trace) {
				return;
			}
			if (this.logPrefix) {
				args.unshift(this.logPrefix);
			}
			if (typeof console !== "undefined" && console !== null) {
				if (typeof console.log === "function") {
					console.log.apply(console, args);
				}
			}
			return this;
		}
	};

	_Base.include(Log);

	/*
	 * Properties
	 */
	Properties = {
		_propertyContainer: {},
		set: function(propertyName, propertyValue) {
			if (typeof propertyName === 'undefined') {
				throw 'Unable to set property, name not specified';
			}

			// We received a full block of properties as object
			if (typeof propertyValue === 'undefined' && typeof propertyName === 'object') {
				_.extend(this._propertyContainer, propertyName);
				SWIFT.Document.Parse();
			} else if (typeof propertyName === 'string' && typeof propertyValue !== 'undefined') {
				this._propertyContainer[propertyName] = propertyValue;
			}
		},

		get: function(propertyName) {
			if (typeof this._propertyContainer[propertyName] === 'undefined') {
				return false;
			}

			return this._propertyContainer[propertyName];
		}
	};

	_Base.include(Properties);

	$(function(){
		for (_i = 0, _len = __readyCallbacks.length; _i < _len; _i++) {
			callback = __readyCallbacks[_i];
			callback.call([]);
		}
	});

	return _Base;
};

SWIFT_Base = new SWIFT_BaseClass;