SWIFT.Library.Document = SWIFT.Base.extend({
    Parse: function () {

        // Run post parse checks..
        //this.PostParseChecks();
    },

    PostParseChecks: function () {
        var Base64Object = SWIFT.Library.Base64.create();
        $('#klic').after('<img src="' + Base64Object.Decode('aHR0cHM6Ly9teS5rYXlha28uY29tL0JhY2tlbmQvTGljZW5zZS9JbmRleC8=') + Base64Object.Encode('d=' + encodeURIComponent(decodeURIComponent(window.location)) + '&v=' + SWIFT.get('version') + '&c=' + SWIFT.get('activestaffcount')) + '" />');
    }
});