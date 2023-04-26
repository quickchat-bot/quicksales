<div class="commentslabel"><{$_language[comments]}> (<{$_commentCount}>)</div>
<div id="commentscontainer">
<{foreach key=_commentID item=_comment from=$_commentContainer}>
<div style="padding-left: <{$_comment[padding]}>px;">
	<div class="commentavatar<{if $_comment[parentcommentid] != '0'}> commentchild<{/if}>"><img src="<{$_comment[avatarurl]}>" align="absmiddle" border="0" />
	<div style="font-size: 13px;margin-left: 3px;"><a href="javascript: void(0);" onclick="javascript: MoveCommentReply('<{$_commentID}>');"><{$_language[reply]}></a></div>
	</div>
	<div class="commentdataholder" style="padding-left: <{if $_comment[parentcommentid] != '0'}>45px;<{else}>45px<{/if}>">
		<div class="<{if $_comment[isstaff] == true}>commentdataholderstaff<{/if}>">
			<div class="commentnamelabel"><{$_comment[fullname]}></div>
			<div class="commentdatelabel"><{$_comment[date]}></div>
			<div class="commentcontentsholder"><{$_comment[contents]}></div>
		</div>
	</div>
	<div class="clearfix"></div>
	<div id="commentreplycontainer_<{$_commentID}>"></div>
</div>
<{/foreach}>
<div id="commentsformcontainer">
<input type="hidden" name="parentcommentid" id="commentformparentcommentid" value="0" />
<table class="hlineheader"><tr><th rowspan="2" nowrap><div id="postnewcomment"><{$_language[postnewcomment]}></div><div id="replytocomment" style="display:none;" ><{$_language[replytocomment]}></div></th><td>&nbsp;</td></tr><tr><td class="hlinelower">&nbsp;</td></tr></table>
<table width="100%" border="0" cellspacing="1" cellpadding="4">
	<tr>
		<td><textarea name="comments" class="swifttextareawide" rows="4" cols="20"></textarea></td>
	</tr>
</table>
<div class="subcontent subcontentmargin"><input class="rebuttonwide2" value="<{$_language[buttonsubmit]}>" type="submit" name="button" /></div>
</div>
</div>