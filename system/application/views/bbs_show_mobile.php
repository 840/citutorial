<html>
<head>
<!-- モバイル版では文字エンコードをShift_JISに変換して送信しますので
charsetにShift_JISを指定します。 -->
<meta http-equiv="Content-Type" content="text/html; charset=shift_jis">
<title>ﾓﾊﾞｲﾙ掲示板</title>
</head>

<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td bgcolor="#9999FF"><a name="top">ﾓﾊﾞｲﾙ掲示板</a></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td bgcolor="#EEEEEE"><?=anchor('bbs/post', '&gt;&gt;新規投稿');?></td></tr>
<tr><td>&nbsp;</td></tr>
<!-- ページネーションを表示します。 -->
<?=$pagination?>
</table>
<!-- ここから、php endforeachまで、記事を表示するループです。 -->
<?php foreach($query->result() as $row): ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td bgcolor="#BBBBFF"><a name="id<?=$row->id?>">[<?=$row->id?>]</a> 
<?=form_prep($row->subject);?></td></tr>
<tr>
<td><?=form_prep($row->name);?>&nbsp;<?=form_prep($row->datetime);?></td>
</tr>
<tr><td bgcolor="#EEEEEE"><?=nl2br(form_prep($row->body));?></td></tr>
<!-- 記事を削除するためのフォームを表示します。 -->
<tr><td><?=form_open('bbs/delete/'. $row->id);?>
削除ﾊﾟｽﾜｰﾄﾞ:<br>
<input type="text" name="password" value="">
<input type="submit" value="削除">
<?=form_close();?></td></tr>
<tr><td>&nbsp;</td></tr>
<?=$pagination?>
</table>
<?php endforeach; ?>
<hr>
<a href="#top">上段に戻る</a>
</body>
</html>
