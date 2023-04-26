/**
 * Common functions for archiver module, Use as a singleton
 *
 * @author Werner Garcia <werenr.garcia@crossover.com>
 * @class
 * @extends SWIFT_BaseClass
 */
SWIFT.Library.ArchiverAdmin = SWIFT.Base.extend({

    /**
     * Executes data deletion
     *
     *  @return SWIFT.Library.AdminObject
     */
    StartDeleteAll: function (msg, url, data) {
        if (!confirm(msg)) return null;
        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            beforeSend: function () {
                $('body').css('cursor', 'wait');
                UIStartLoading();
                $('#menuloadingcircle').css('display', 'block');
            },
            success: function () {
                LoadViewportPOST('/archiver/Manager/Search', data);
            }
        });
    },

});

/**
 *  Archiver container
 */
SWIFT.Archiver = {};
/**
 * Archiver admin object, Use as a singleton
 */
SWIFT.Archiver.AdminObject = SWIFT.Library.ArchiverAdmin.create();