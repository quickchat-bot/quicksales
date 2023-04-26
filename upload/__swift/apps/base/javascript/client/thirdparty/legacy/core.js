var _irsContents = ' ';
function ToggleTicketSubDepartments(_departmentID) {
	$("tr[class^='ticketsubdepartments_']").hide();
	$('.ticketsubdepartments_' + _departmentID).show();

}

function StartIRS() {
	var _ticketMessageContents = $('#ticketsubject').val() + ' ';

	if (typeof(tinyMCE) != "undefined" && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
		_ticketMessageContents = _ticketMessageContents + tinyMCE.activeEditor.getContent();
	}

	if (_ticketMessageContents != _irsContents) {
			$('#irscontainer').slideDown('medium');
		$.post(_baseName + '/Knowledgebase/Article/IRS', {
			'contents': _ticketMessageContents
		}, function(_data){
			$('#irscontainer').show().html(_data);
		});

		_irsContents = _ticketMessageContents;
	}
	setTimeout('StartIRS();', 2000);
}


function ArticleHelpful(_kbArticleID) {
	$('#kbratingcontainer').load(_baseName + '/Knowledgebase/Article/Rate/' + _kbArticleID + '/1');
}

function ArticleNotHelpful(_kbArticleID) {
	$('#kbratingcontainer').load(_baseName + '/Knowledgebase/Article/Rate/' + _kbArticleID + '/0');
}

function MoveCommentReply(_commentID) {
	$('#commentsformcontainer').appendTo('#commentreplycontainer_' + _commentID);
	$('#commentformparentcommentid').val(_commentID);
	$('#postnewcomment').hide();
	$('#replytocomment').show();
}

function ActivateLoginTab() {
	$('#leftloginsubscribeboxsubscribetab').addClass('inactive');
	$('#leftloginsubscribeboxlogintab').removeClass('inactive');

	$('#leftsubscribebox').removeClass('active');
	$('#leftloginbox').addClass('active');

	$('#leftsubscribebox').slideUp();
	$('#leftloginbox').slideDown();
}

function ActivateSubscribeTab() {
	$('#leftloginsubscribeboxlogintab').addClass('inactive');
	$('#leftloginsubscribeboxsubscribetab').removeClass('inactive');

	$('#leftloginbox').removeClass('active');
	$('#leftsubscribebox').addClass('active');

	$('#leftloginbox').slideUp();
	$('#leftsubscribebox').slideDown();
}

function LanguageSwitch(_isLiveChat) {
	if (!$('#languageid').length) {
		return false;
	}

	if (_isLiveChat == true) {
		window.location.href = window.location.href + '/_languageID=' + $('#languageid').val();
	} else {
		window.location.href = _baseName + '/Base/Language/Change/' + $('#languageid').val();
	}
};

function RenderCustomfields(_proactive) {
	_appendURL = '';
	if (window.location.href.indexOf('_filterDepartmentID') === -1) {
		_appendURL = '/_filterDepartmentID=' + encodeURIComponent($('input[name=filterdepartmentid]').val());
	} else if (window.location.href.indexOf('_proactive') === -1) {
		_appendURL = _appendURL + '/_proactive=' + _proactive;
	}

	if (window.location.href.indexOf('Start') >= 0) {
		window.location.href = _baseName + '/LiveChat/Chat/Start/_departmentID=' + $('select[name=departmentid]').val() + '/_languageID=' + $('#languageid').val() + '/_filterDepartmentID=' + encodeURIComponent($('input[name=filterdepartmentid]').val()) + '/_proactive=' + _proactive;
	} else if (window.location.href.indexOf('_departmentID') >= 0) {
		window.location.href = window.location.href.replace(/(_departmentID=)[^\&]+/, '$1' + $('select[name=departmentid]').val()) + _appendURL;
	} else {
		window.location.href = window.location.href + '/_departmentID=' + $('select[name=departmentid]').val() + _appendURL;
	}
}

var RecaptchaOptions = {
   theme : 'clean'
};

function ResetLabel(_inputObject, _labelText, _cssClass) {
	if ($(_inputObject).val() == _labelText && _labelText != '')
	{
		$(_inputObject).val('');
	}

	if (_cssClass)
	{
		$(_inputObject).removeClass().addClass(_cssClass);
	}

	return true;
};

function Redirect(_newLocation) {
	window.location.href = _newLocation;
};

function AddProfileEmail() {
	$('#profileemailcontainer').append('<div class="useremailitem"><div class="useremailitemdelete" onclick="javascript: $(this).parent().remove();">&nbsp;</div><input name="newemaillist[]" type="text" size="20" class="swifttextlarge" /></div>');
};


function AddTicketFile() {
	$('#ticketattachmentcontainer').append('<div class="ticketattachmentitem"><div class="ticketattachmentitemdelete" onclick="javascript: $(this).parent().remove();">&nbsp;</div><input name="ticketattachments[]" type="file" size="20" class="swifttextlarge" /></div>');
};

function PopupSmallWindow(url) {
	screen_width = screen.width;
	screen_height = screen.height;
	widthm = (screen_width-400)/2;
	heightm = (screen_height-300)/2;
	window.open(url,"infowindow"+GetRandom(), "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=300,left="+widthm+",top="+heightm);
};

function checkMandatoryCustomFields() {
	// reset form
	$('#cancelticketpropertiesbutton').trigger('click');

	// get required custom fields in ticket form
	const customFields = $('*:read-write', $('*[class~=customfieldrequired]', 'div.viewticketcontentcontainer').parent().next());
	let emptyFields = [];

	// ok if there are no custom fields
	if (customFields.length === 0) {
		return true;
	}

	// check for required custom fields without value
	customFields.each(function(){
		if ($(this).val() === '') {
			$(this).addClass('swifttexterror');
			emptyFields.push(this);
		}
	});
	if (emptyFields.length > 0) {
		$('#customfieldrequirednotice').show();
		$(emptyFields[0]).focus();
		return false;
	}

	return true;
}

function enableCustomFields() {
	$('*[class~=customfieldrequired]', 'div.viewticketcontentcontainer').parent().removeClass('disabled');
	$('*:read-write', 'div.viewticketcontentcontainer').prop('disabled', false);
	$('#postreplycontainer').hide();
	$('#addreplybutton').show();
}

function disableCustomFields() {
	$('#cancelticketpropertiesbutton').trigger('click');
	$('*[class~=customfieldrequired]', 'div.viewticketcontentcontainer').parent().addClass('disabled');
	$('*:read-write', 'div.viewticketcontentcontainer').prop('disabled', true);
	$('#postreplycontainer').show();
	$('#addreplybutton').hide();
}

function QuoteTicketPost(_ticketID, _ticketPostID) {
   	$.ajax({
		type: 'POST',
		url: _baseName + '/Tickets/Ticket/GetQuote/' + _ticketID + '/' + _ticketPostID,
		data: '',
		success: function(_data){
			disableCustomFields();
			$('#replycontents').val(_data);
			if (typeof(tinyMCE) != "undefined" && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
				tinymce.activeEditor.execCommand('mceInsertContent', false, _data);
			}
		}
	});

}

function GetRandom()
{
	var num;
	now=new Date();
	num=(now.getSeconds());
	num=num+1;
	return num;
};

function LinkedSelectChanged(_selectObject, _fieldName) {
	var _selectValue = $(_selectObject).val();

	$('.linkedselectcontainer_' + _fieldName).hide();
	$('.linkedselectcontainer_' + _fieldName + ' select').prop('disabled', true); // SWIFT-2506

	if ($('#selectsuboptioncontainer_' + _selectValue).length) {
		$('#selectsuboptioncontainer_' + _selectValue).show();
		$('#selectsuboptioncontainer_' + _selectValue + ' select').prop('disabled', false); // SWIFT-2506
	}
};


function ClearDateField(_fieldName) {
	$('#' + _fieldName).val('');
	$('#' + _fieldName + '_hour').val('12');
	$('#' + _fieldName + '_minute').val('0');
	$('#' + _fieldName + '_meridian').val('am');
};

function ClearFunctionQueue() {
	for (var i=0;i<_uiOnParseCallbacks.length;i++)
	{
		window._uiOnParseCallbacks[i]();
	}

	window._uiOnParseCallbacks = new Array();

	return true;
};

window._uiOnParseCallbacks = new Array();

function QueueFunction(_functionData) {
	window._uiOnParseCallbacks[_uiOnParseCallbacks.length] = _functionData;

	return true;
};

function TriggerRating(_ratingURL, _ratingID, _typeID, _ratingValue, _isReadOnly) {
	$.post(_baseName + _ratingURL, {
		'ratingvalue': _ratingValue
	}, function(data){
	});

	if (_isReadOnly == true) {
		$('input[name=rating_' + _ratingID + '_' + _typeID + ']').rating('readOnly', true);
	}
}

function PreventClickJacking() {
	try {
		if (top.location.hostname != self.location.hostname) {
			throw 1;
		}
	} catch (e) {
		top.location.href = self.location.href;
	}
}

function PreventDoubleClicking(Object) {
	$(Object).attr('onclick','').unbind('click');
	return false;
}

/**
 * ###############################################
 * BEGIN FUNCTIONS ADDED AS PART OF KAYAKOC-2410
 * @author Banjo Paul <banjo.paul@aurea.com>
 * ###############################################
 */
var stripTags = function (html) {
    var txt = document.createElement('div');
    txt.innerHTML = html.replace(/[<>]*/g, '');
    return txt.textContent;
};
/**
 * ###############################################
 * END FUNCTIONS ADDED AS PART OF KAYAKOC-2410
 * ###############################################
 */

/**
* ###############################################
* BEGIN ON READY FUNCTIONS
* ###############################################
*/
$(function(){
	$("[form[name='SubmitTicketForm'], form[name='TicketReplyForm'] input[type='submit']").click(function() {
		var formTicket = $("input[name='ticketattachments[]']").closest('form');
		if (formTicket.attr('submitted')) {
			return true;
		}

		$.each($("input[name='ticketattachments[]']"), function(index, file) {
			if (file.value != '') {
				formTicket.attr('action', formTicket.attr('action') + '/1');
				formTicket.attr('submitted', 'true');
				return true;
			}
		});
	});

    $("[form[name='SubmitTicketForm'] input[type='submit']").click(function (e) {
        e.preventDefault();

        var attachments = $("input[name='ticketattachments[]']");
        var attachments_count = attachments.length;

        if (attachments_count <= 0)
            $("[form[name='SubmitTicketForm']").submit();

        var canSubmit = true;
        $('#ticketattachmenterror').hide();
        $.each(attachments, function (index, file) {
            if (file.value == '') {
                canSubmit = false;
                $('#ticketattachmenterror').css('display', 'inline-block');
                $(file).addClass('swifttexterror');
                $(file).focus();
            } else {
                $(file).removeClass('swifttexterror');
			}

            if (index == attachments_count - 1 && canSubmit)
                $("[form[name='SubmitTicketForm']").submit();
        });
    });

	if (typeof _baseName == 'string') {
		$.get(_swiftPath + 'cron/index.php?/Base/CronManager/Execute');
	}

	// Show save ticket properties button on properties change
	$('#ticketpropertiesform select, #ticketpropertiesform input, #ticketpropertiesform textarea').change(function() {
		$('#saveticketpropertiesbuttoncontainer').show();
	});

	// Show the custom field changed notice on custom field change
	$('#ticketpropertiesform .viewticketcontentcontainer select, #ticketpropertiesform .viewticketcontentcontainer input, #ticketpropertiesform .viewticketcontentcontainer textarea').change(function() {
		$('#customfieldchangednotice').show();
		$('#customfieldrequirednotice').hide();
		$(this).removeClass('swifttexterror');
	});


	$('#trisback').val('0');

	ClearFunctionQueue();

    /**
     * Bugfix KAYAKOC-3549: Prevent large images to be submitted
     *
     * @author Werner Garcia <werner.garcia@crossover.com>
     */

	//binds to onchange event of your input field
    $(document).on("change", 'input[name=profileimage]', function () {
        var _hasError = 0;
        var _maxFileSize = 5242880;
        var _btn = ' <a class="resetimagebutton" href="javascript: void(0);" onclick="javascript:$(\'input[name=profileimage]\').val(null);$(\'#error1, #error2\').slideUp(\'slow\', function(){$(this).remove();});$(\'input:submit\').attr(\'disabled\', false);">Reset</a>';
        var _err1 = '<div id="error1" style="display:none;color:#FFF;background:#e05720;padding: 5px;border-radius:5px;margin-top: 5px;">Invalid Image Format! Image format must be JPG, JPEG, PNG or GIF.' + _btn + '</div>';
        var _err2 = '<div id="error2" style="display:none;color:#FFF;background:#e05720;padding: 5px;border-radius:5px;margin-top: 5px;">Maximum File Size Limit is 5MB.' + _btn + '</div>';

        $(this).parent().append(_err1 + _err2);

        var ext = $(this).val().split('.').pop().toLowerCase();
        if ($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
            $('#error1').slideDown("slow");
            $('#error2').slideUp("slow");
            $('input:submit').attr('disabled', true);
            _hasError = 1;
        } else {
            var picsize = (this.files[0].size);
            if (picsize > _maxFileSize) {
                $('#error2').slideDown("slow");
                $('input:submit').attr('disabled', true);
                _hasError = 1;
            } else {
                $('#error2').slideUp("slow");
                _hasError = 0;
            }
            $('#error1').slideUp("slow");
            if (_hasError == 0) {
                $('#error1, #error2').remove();
                $('input:submit').attr('disabled', false);
            }
        }

        return !_hasError;
    });
});

/**
* ###############################################
* END ON READY FUNCTIONS
* ###############################################
*/
