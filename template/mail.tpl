<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <title>Piwigo Mail</title>
  <meta http-equiv="Content-Type" content="text/html; charset={$CONTENT_ENCODING}">
  
  {math assign=tn_width equation="min(x,150)"  x=$derivative_params->max_width()}
  <style type="text/css">{strip}<!--
  {$UC_MAIL_CSS}
  #the_image a {ldelim}
    width:{$tn_width}px;
    height:{$tn_width}px;
  }
  #the_page {ldelim}
    width:{math equation="x*4+120" x=$tn_width}px;
  }
  -->{/strip}</style>
</head>

<body>
<div id="the_page">

<div id="the_header">
<b>{$MAIL_TITLE}</b>
</div>

<div id="the_content">

<p>
  {'Hello <b>%s</b>, %s sent you a photos collection from <a href="%s">%s</a>'|@translate|sprintf:$PARAMS.recipient_name:$PARAMS.sender_name:$GALLERY_URL:$GALLERY_TITLE}
</p>

<p id="the_image">
{foreach from=$thumbnails item=element}
  <a href="{$element.URL}">
    <span>{$element.NAME|truncate:25:"..."}</span>
    <img src="{$element.THUMB}" alt="{$element.TN_ALT}">
  </a>
{/foreach}
</p>

{if $PARAMS.message}
<p>
  <blockquote>{$PARAMS.message}</blockquote>
</p>
{/if}

<p class="button">
  <a href="{$COL_URL}">{'Click here to view the complete collection'|@translate}</a>
</p>

</div>

<div id="the_footer">
  {'Sent by'|@translate} <a href="{$GALLERY_URL}">{$GALLERY_TITLE}</a>
  - {'Powered by'|@translate} <a href="{$PHPWG_URL}" class="Piwigo">Piwigo</a> {$VERSION}
  - User Collections
</div>

</div>
</body>
</html>