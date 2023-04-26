var _unifiedSearchID = 0;
var _unifiedSearchPluginContainer = new Array();

function UnifiedSearchGetAndRender(_elementID) {
	if (typeof _unifiedSearchPluginContainer[_elementID] == 'undefined') {
		return false;
	}

	var _unifiedSearchPluginInstance = _unifiedSearchPluginContainer[_elementID];
	_unifiedSearchPluginInstance.GetAndRender();
}

(function($) {

    $.UnifiedSearch = function(element, options) {
		var unifiedSearchOptions = {
			'unifiedSearchID': 0,
			'searchElement': false,
			'searchPath': '',
			'searchElementID': '',
			'searchTimeoutID': false,
			'searchJsonData': []
		}

		var plugin = this;
		plugin.settings = {};

		var $element = $(element), element = element;


		plugin.init = function() {
			plugin.settings = $.extend({}, unifiedSearchOptions, options);

			plugin.settings.searchElementID = $(element).attr('id');
			_unifiedSearchPluginContainer[plugin.settings.searchElementID] = plugin;

			$(document).bind('click', function(e) {
				var clickedElement=$(e.target);

				// We only destroy the drop down if the click was outside it
				if (!clickedElement.parents().is('.unifiedsearchdropdown')) {
					plugin.DestroyAllDropdowns();
				}
			});


			// Add the class info to the element
			$(element).wrap('<div class="unifiedsearchcontainer"></div>');
			$(element).removeClass('unifiedsearchbox').addClass('unifiedsearchbox');
			$(element).val(swiftLanguage['search']);

			$(element).unbind('blur').bind('blur', function(_parentEvent) {
				if ($(element).val() == '') {
					if (plugin.settings.searchTimeoutID !== false) {
						clearTimeout(plugin.settings.searchTimeoutID);
					}
					$(element).val(swiftLanguage['search']);
				}
			});

			$(element).unbind('keyup mouseup').bind('keyup mouseup', function(_parentEvent) {
				if ($(element).val() == swiftLanguage['search']) {
					$(element).val('');
				}

				var _parentKeyCode = _parentEvent.keyCode ? _parentEvent.keyCode : _parentEvent.which ? _parentEvent.which : _parentEvent.charCode;
				// Dont let the events pass through for the following keys
				if (_parentKeyCode == 40 || _parentKeyCode == 38 || _parentKeyCode == 13 || _parentKeyCode == 27) {

					return false;

				// Dont do anything for windows/command, alt, control, shift keys
				} else if (_parentKeyCode == 224 || _parentKeyCode == 18 || _parentKeyCode == 17 || _parentKeyCode == 16) {
					return true;
				}

				// Trigger only if there is no other timeout in queue
				if (plugin.settings.searchTimeoutID === false) {
					plugin.settings.searchTimeoutID = setTimeout('UnifiedSearchGetAndRender("' + plugin.settings.searchElementID + '");', 400);

				// We have timeout in queue, cancel that first and then set a new one
				} else {
					clearTimeout(plugin.settings.searchTimeoutID);
					plugin.settings.searchTimeoutID = setTimeout('UnifiedSearchGetAndRender("' + plugin.settings.searchElementID + '");', 400);
				}

			});
		}

		plugin.GetAndRender = function() {
			_unifiedSearchID++;

			var _textContents = $(element).val();

			var _postData = '_textContents=' + EscapeHTML(_textContents);

			plugin.settings.unifiedSearchID = _unifiedSearchID;

			if (_textContents == '') {
				return;
			}

			$(element).addClass('unifiedsearchboxwait');

			// Get the data via JSON
			$.ajax({type: 'POST', url: plugin.settings.searchPath, dataType: 'json', data: _postData, success: function(_jsonData) {
				plugin.settings.searchJsonData = _jsonData.data;

				// First destroy all current drop downs
				plugin.DestroyAllDropdowns();

				// We need to build the auto complete control now
				plugin.BuildDropdown();
			}});
		}

		plugin.DestroyAllDropdowns = function() {
			$('.unifiedsearchdropdown').remove();
			$(element).parent().removeClass('unifiedsearchcontainermenu');
		}

		plugin.BuildDropdown = function() {
			// Build the dropdown
			var _dropDownParentElement = document.createElement('ul');
			_dropDownParentElement.id = plugin.settings.unifiedSearchID;
			$(_dropDownParentElement).attr('style', 'z-index: 1; left: 0px; top:0px; display: none;').attr('class', 'unifiedsearchdropdown');

			var _hasChildren = false;

			$.each(plugin.settings.searchJsonData, function(_key, _valueContainer) {
				var _rightInfo = '';
				var _finalTitle = _key;
				if (_key.indexOf('::', 0) != -1) {
					_finalTitle = _key.substring(0, _key.indexOf('::', 0));
					_rightInfo = _key.substring(_key.indexOf('::', 0)+2);
				}

				var _dropDownLIElement = document.createElement('li');
				var _dropDownSPANElement = document.createElement('span');
				_dropDownSPANElement.innerHTML = _finalTitle;
				$(_dropDownLIElement).addClass('title');
				$(_dropDownSPANElement).addClass('usdtitle');



				var _dropDownSPANRightElement = document.createElement('span');
				_dropDownSPANRightElement.innerHTML = _rightInfo;
				$(_dropDownSPANRightElement).addClass('usdrightinfo');

				_hasChildren = true;

				_dropDownLIElement.appendChild(_dropDownSPANRightElement);
				_dropDownLIElement.appendChild(_dropDownSPANElement);
				_dropDownParentElement.appendChild(_dropDownLIElement);

				var _lastDropDownLIElement = false;

				$.each(_valueContainer, function(_subKey, _subValueContainer) {
					var _dropDownLIElement = document.createElement('li');
					var _dropDownAElement = document.createElement('a');
					var _dropDownSubSpanElement = document.createElement('span');
					_dropDownAElement.innerHTML = _subValueContainer[0];
					_dropDownSubSpanElement.innerHTML = _subValueContainer[2];
					$(_dropDownSubSpanElement).addClass('usdfloat');

					_lastDropDownLIElement = _dropDownLIElement;

					_hasChildren = true;

					$(_dropDownLIElement).unbind('click').bind('click', function(e) {
						var _dispatchLink = _subValueContainer[1];
						if (_dispatchLink.substr(0, 1) == ':') {
							eval(_dispatchLink.substr(1, _dispatchLink.length));
						} else {
							loadViewportData(_subValueContainer[1]);
						}

						$(element).val(swiftLanguage['search']).blur();
						plugin.DestroyAllDropdowns();
					});

					_dropDownLIElement.appendChild(_dropDownSubSpanElement);
					_dropDownLIElement.appendChild(_dropDownAElement);

					if (_subValueContainer[3] != '') {
						var _dropDownDIVElement = document.createElement('div');
						_dropDownDIVElement.innerHTML = _subValueContainer[3];
						$(_dropDownDIVElement).addClass('usdinfo');
						_dropDownLIElement.appendChild(_dropDownDIVElement);
					}

					$(_dropDownLIElement).hover(function() {
						$('#' + plugin.settings.unifiedSearchID).children('li').removeClass('selected');
						$(this).addClass('selected').children('.usdinfo').slideDown('fast');
					}, function() {
						$(this).removeClass('selected').children('.usdinfo').hide();
					})

					_dropDownParentElement.appendChild(_dropDownLIElement);
				});

				$(_lastDropDownLIElement).addClass('last');
			});

			$('body').append(_dropDownParentElement);
			$(element).removeClass('unifiedsearchboxwait');

			if (_hasChildren == false || !$(_dropDownParentElement).children('li').length) {
				$(_dropDownParentElement).hide();

				return true;
			}

			// Position the drop down
			$(_dropDownParentElement).show().position({
				of: $(element),
				my: 'left top',
				at: 'left bottom',
				offset: '0px 4px',
				collision: 'none none'
			});

			// Add a handler for keyboard
			// Down: 40, Up: 38, Enter: 13, Escape: 27
			$(element).unbind('keydown').bind('keydown', function(_event) {
				var _keyCode = _event.keyCode ? _event.keyCode : _event.which ? _event.which : _event.charCode;

				// Escape: Destroy Dropdown
				if (_keyCode == 27) {
					$(element).val('').blur();
					plugin.DestroyAllDropdowns();

				// Down Arrow: Next Item
				} else if (_keyCode == 40) {
					var _selectedLIElements = $(_dropDownParentElement).children('.selected');

					// Shouldnt have happened..
					if (_selectedLIElements.length > 1) {
						$.each(_selectedLIElements, function() {
							$(this).removeClass('selected').children('.usdinfo').hide();
						});

						if ($(_dropDownParentElement).children('li:first').hasClass('title')) {
		 					$(_dropDownParentElement).children('li:first').next().addClass('selected').children('.usdinfo').slideDown('fast');
						} else {
		 					$(_dropDownParentElement).children('li:first').addClass('selected').children('.usdinfo').slideDown('fast');
						}

					// Move to the next item
					} else if (_selectedLIElements.length == 1) {
						_selectedLIElements.removeClass('selected').children('.usdinfo').hide();

						if (!_selectedLIElements.next().length) {
							if ($(_dropDownParentElement).children('li:first').hasClass('title')) {
								$(_dropDownParentElement).children('li:first').next().addClass('selected').children('.usdinfo').slideDown('fast');
							} else {
								$(_dropDownParentElement).children('li:first').addClass('selected').children('.usdinfo').slideDown('fast');
							}

						} else {
							if (_selectedLIElements.next().hasClass('title')) {
								_selectedLIElements.next().next().addClass('selected').children('.usdinfo').slideDown('fast');
							} else {
								_selectedLIElements.next().addClass('selected').children('.usdinfo').slideDown('fast');
							}
						}

					// No item selected, select first one
					} else if (_selectedLIElements.length == 0) {
						if ($(_dropDownParentElement).children('li:first').hasClass('title')) {
		 					$(_dropDownParentElement).children('li:first').next().addClass('selected').children('.usdinfo').slideDown('fast');
						} else {
		 					$(_dropDownParentElement).children('li:first').addClass('selected').children('.usdinfo').slideDown('fast');
						}

					}

				// Up Arrow: Previous Item
				} else if (_keyCode == 38) {
					var _selectedLIElements = $(_dropDownParentElement).children('.selected');

					// Shouldnt have happened..
					if (_selectedLIElements.length > 1) {
						$.each(_selectedLIElements, function() {
							$(this).removeClass('selected').children('.usdinfo').hide();
						});

						if ($(_dropDownParentElement).children('li:last').hasClass('title')) {
		 					$(_dropDownParentElement).children('li:last').prev().addClass('selected').children('.usdinfo').slideDown('fast');
						} else {
		 					$(_dropDownParentElement).children('li:last').addClass('selected').children('.usdinfo').slideDown('fast');
						}

					// Move to the previous item
					} else if (_selectedLIElements.length == 1) {
						_selectedLIElements.removeClass('selected').children('.usdinfo').hide();

						if (!_selectedLIElements.prev().length) {
							if ($(_dropDownParentElement).children('li:last').hasClass('title')) {
								$(_dropDownParentElement).children('li:last').prev().addClass('selected').children('.usdinfo').slideDown('fast');
							} else {
								$(_dropDownParentElement).children('li:last').addClass('selected').children('.usdinfo').slideDown('fast');
							}

						} else {
							if (_selectedLIElements.prev().hasClass('title')) {
								if (_selectedLIElements.prev().prev().length) {
									_selectedLIElements.prev().prev().addClass('selected').children('.usdinfo').slideDown('fast');
								} else {
									if ($(_dropDownParentElement).children('li:last').hasClass('title')) {
										$(_dropDownParentElement).children('li:last').prev().addClass('selected').children('.usdinfo').slideDown('fast');
									} else {
										$(_dropDownParentElement).children('li:last').addClass('selected').children('.usdinfo').slideDown('fast');
									}
								}
							} else {
								_selectedLIElements.prev().addClass('selected').children('.usdinfo').slideDown('fast');
							}
						}

					// No item selected, select first one
					} else if (_selectedLIElements.length == 0) {
						if ($(_dropDownParentElement).children('li:last').hasClass('title')) {
		 					$(_dropDownParentElement).children('li:last').prev().addClass('selected').children('.usdinfo').slideDown('fast');
						} else {
		 					$(_dropDownParentElement).children('li:last').addClass('selected').children('.usdinfo').slideDown('fast');
						}
					}


				// Enter: Trigger Selection
				} else if (_keyCode == 13) {
					// Does the menu exist?
					if (!$(_dropDownParentElement).children('.selected').children('a').length || !$(element).parent().hasClass('unifiedsearchcontainermenu')) {
						plugin.GetAndRender()
					} else {
						$(_dropDownParentElement).children('.selected').trigger('click');
					}
				}

				// Dont let the events pass through for the following keys
				if (_keyCode == 40 || _keyCode == 38 || _keyCode == 13 || _keyCode == 27) {
					_event.preventDefault();
					_event.stopPropagation();

					return false;
				}

				return true;
			});

			// Display!
			$(_dropDownParentElement).show();
			$(element).focus().parent().addClass('unifiedsearchcontainermenu');
		}

		plugin.init();
	};


    $.fn.UnifiedSearch = function(options) {

        return this.each(function() {
            if (undefined == $(this).data('UnifiedSearch')) {
                var plugin = new $.UnifiedSearch(this, options);
                $(this).data('UnifiedSearch', plugin);
            }
        });

    }
})(jQuery);
