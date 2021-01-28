<?php

include 'functions.php';

?>
<html>
<head>
    <title>Feed creator</title>
    <script type="text/javascript" src="//code.jquery.com/jquery.min.js"></script>
</head>
<body>
<h1>Feed Creator</h1>
<form method="get" id="form">
<label>URL: <input type="text" name="url" value="<?= htmlspecialchars($_GET['url'] ?? '') ?>"></label><br>
<label>Container: <input type="text" name="containersel" value="<?= htmlspecialchars($_GET['containersel'] ?? '') ?>" class="selector"></label><br>
<label>Title: <input type="text" name="titlesel" value="<?= htmlspecialchars($_GET['titlesel'] ?? '') ?>" class="selector"></label><br>
<label>Date: <input type="text" name="datesel" value="<?= htmlspecialchars($_GET['datesel'] ?? '') ?>" class="selector"></label>&nbsp;&nbsp;
<label>Date format: <input type="text" name="datefmt" value="<?= htmlspecialchars($_GET['datefmt'] ?? '') ?>"></label><br>
<label>Content: <input type="text" name="contentsel" value="<?= htmlspecialchars($_GET['contentsel'] ?? '') ?>" class="selector"></label> (blank for everything else)<br>
<div id="removesels">
<?php if (isset($_GET['removesel']) && is_array($_GET['removesel'])) { foreach ($_GET['removesel'] as $rs) { ?>
<label class="removesel-label">Remove this: <input type="text" name="removesel[]" value="<?= htmlspecialchars($rs) ?>" class="selector removesel"></label> <input type="button" class="delremovesel" value="x"><br>
<?php }} ?>
</div>
<input type="button" id="addremovesel" value="add a removal selector"><br>
<br>
<input type="submit" value="Apply"><br>
<textarea rows="2" cols="80" readonly><?= htmlspecialchars(absurl("feed.php") . '?' . $_SERVER['QUERY_STRING']) ?></textarea>
<!-- <input type="button" value="Preview" id="previewbtn"> -->
</form>
<iframe id="previewframe" style="width: 100%; height: 50%;" src="feed.php?preview=1&amp;<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>"></iframe>
<br>
<iframe id="pageframe" style="width: 100%; height: 50%;" src="proxy.php?<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>"></iframe>


<script type="text/javascript">
$("#addremovesel").click(function () {
    $("#removesels").append('<label class="removesel-label">Remove this: <input type="text" name="removesel[]" class="selector removesel"></label> <input type="button" class="delremovesel" value="x"><br>');
});

$("#removesels").on('click', '.delremovesel', function () {
    $(this).prev(".removesel-label").remove();
    $(this).next("br").remove();
    $(this).remove();
});
</script>
</body>
</html>