<{include file="header.tpl"}>
<body>
<div class="loginformcontainer">
    <center>
        <script language="Javascript" type="text/javascript">
            $(function () {
                $('#username').focus();
                $('#newpassword').pstrength();
                $('#newpasswordagain').pstrength();
            });
        </script>
        <form method="post" action="<{$_baseName}><{$_templateGroupPrefix}>/Base/StaffLostPassword/Submit"
              name="resetpasswordform">
            <table width="500" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td colspan="2" align="center" valign="top">
                        <img class="loginlogo"
                                                                     src="<{$_themePath}>images/kayako-logo-dark.svg"/>
                        <!-- BEGIN DIALOG PROCESSING -->
                        <{foreach key=key item=_item from=$_errorContainer}>
                        <div class="dialogerror">
                            <div class="dialogerrorsub">
                                <div class="dialogerrorcontent"><{$_item[message]}></div>
                            </div>
                        </div>
                        <{/foreach}>
                        <{foreach key=key item=_item from=$_infoContainer}>
                        <div class="dialoginfo">
                            <div class="dialoginfosub">
                                <div class="dialoginfocontent"><{$_item[message]}></div>
                            </div>
                        </div>
                        <{/foreach}>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="boxcontainer">
                            <div class="boxcontainerlabel"><{$_language[lostpasswordtitle]}></div>

                            <div class="boxcontainercontent">
                                <{$_language[lostpassworddesc]}><br/><br/>
                                <table class="hlineheader">
                                    <tr>
                                        <th rowspan="2" nowrap><{$_language[staff]}></th>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td class="hlinelower">&nbsp;</td>
                                    </tr>
                                </table>
                                <table width="100%" border="0" cellspacing="1" cellpadding="4">
                                    <tr>
                                        <td width="200" align="left" valign="middle" class="zebraodd">
                                            <{$_language[staffemail]}>
                                        </td>
                                        <td><input name="email" type="text" size="25" class="swifttextlarge"
                                                   value="<{$_userEmail}>"/></td>
                                    </tr>
                                </table>
                                <br/>

                                <{if $_canCaptcha == true}>
                                <table class="hlineheader">
                                    <tr>
                                        <th rowspan="2"><{$_language[captchaverification]}></th>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td class="hlinelower">&nbsp;</td>
                                    </tr>
                                </table>
                                <{if  $_isRecaptcha == true}>
                                <div class="subcontent"><{$_language[recaptchadesc]}></div>
                                <{else}>
                                <div class="subcontent"><{$_language[captchadesc]}></div>
                                <{/if}>
                                <{$_captchaHTML}>
                            <br/>
                                <{/if}>

                                <div class="subcontent">
                                    <input class="rebuttonwide2 rebuttonwide2final" value="<{$_language[buttonsubmit]}>"
                                           type="submit" name="button"/>
                                    <input type="button" class="rebutton"
                                           onclick="location.href='<{$_baseName}><{$_templateGroupPrefix}>'"
                                           value="Cancel"/>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </form>

        <br/>
        <div class="smalltext"><{$_poweredByNotice}><br/><{$_copyright}></div>
        <br/>
    </center>
</div>
</body>
</html>
